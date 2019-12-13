<?php

defined('_EXEC') or die;

require 'plugins/php-qr-code/qrlib.php';

class Index_model extends Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_countries()
	{
		$query1 = Functions::get_json_decoded_query($this->database->select('countries', [
			'name',
			'code',
			'lada'
		], [
			'priority[>=]' => 1,
			'ORDER' => [
				'priority' => 'ASC'
			]
		]));

		$query2 = Functions::get_json_decoded_query($this->database->select('countries', [
			'name',
			'code',
			'lada'
		], [
			'priority[=]' => null,
			'ORDER' => [
				'name' => 'ASC'
			]
		]));

		return array_merge($query1, $query2);
	}

	public function get_time_zones()
	{
		$query = $this->database->select('time_zones', [
			'code'
		], [
			'ORDER' => [
				'zone' => 'ASC'
			]
		]);

		return $query;
	}

	public function get_currencies()
	{
		$query1 = Functions::get_json_decoded_query($this->database->select('currencies', [
			'name',
			'code'
		], [
			'priority[>=]' => 1,
			'ORDER' => [
				'priority' => 'ASC'
			]
		]));

		$query2 = Functions::get_json_decoded_query($this->database->select('currencies', [
			'name',
			'code'
		], [
			'priority[=]' => null,
			'ORDER' => [
				'name' => 'ASC'
			]
		]));

		return array_merge($query1, $query2);
	}

	public function get_languages()
	{
		$query = $this->database->select('languages', [
			'name',
			'code'
		], [
			'ORDER' => [
				'priority' => 'ASC'
			]
		]);

		return $query;
	}

	public function get_room_package($rooms_number)
	{
		$query = Functions::get_json_decoded_query($this->database->select('room_packages', [
			'id',
			'quantity_end',
			'price'
		], [
			'AND' => [
				'quantity_start[<=]' => $rooms_number,
				'quantity_end[>=]' => $rooms_number
			]
		]));

		return !empty($query) ? $query[0] : null;
	}

	public function get_table_package($tables_number)
	{
		$query = Functions::get_json_decoded_query($this->database->select('table_packages', [
			'id',
			'quantity_end',
			'price'
		], [
			'AND' => [
				'quantity_start[<=]' => $tables_number,
				'quantity_end[>=]' => $tables_number
			]
		]));

		return !empty($query) ? $query[0] : null;
	}

	public function check_exist_account($field, $value)
	{
		$count = $this->database->count('accounts', [
			$field => $value
		]);

		return ($count > 0) ? true : false;
	}

	public function check_exist_user($field, $value)
	{
		$count = $this->database->count('users', [
			$field => $value
		]);

		return ($count > 0) ? true : false;
	}

	public function new_signup($data)
	{
		$qr_dir = PATH_UPLOADS;
		$qr_code = Functions::get_random(8);
		$qr_filename = 'qr_' . $qr_code . '.png';
		$qr_size = 5;
		$qr_frame_size = 3;
		$qr_level = 'H';
		$qr_content = 'https://' . Configuration::$domain . '/myvox/' . $qr_code;

		QRcode::png($qr_content, $qr_dir . $qr_filename, $qr_level, $qr_size, $qr_frame_size);

		$qr_dir . basename($qr_filename);

		$this->database->insert('accounts', [
			'token' => strtoupper($qr_code),
			'name' => $data['name'],
			'type' => $data['type'],
			'zip_code' => $data['zip_code'],
			'country' => $data['country'],
			'city' => $data['city'],
			'address' => $data['address'],
			'time_zone' => $data['time_zone'],
			'currency' => $data['currency'],
			'language' => $data['language'],
			'room_package' => ($data['type'] == 'hotel') ? $this->get_room_package($data['rooms_number'])['id'] : null,
			'table_package' => ($data['type'] == 'restaurant') ? $this->get_table_package($data['tables_number'])['id'] : null,
			'logotype' => Functions::uploader($data['logotype']),
			'fiscal' => json_encode([
				'id' => strtoupper($data['fiscal_id']),
				'name' => $data['fiscal_name'],
				'address' => $data['fiscal_address']
			]),
			'contact' => json_encode([
				'firstname' => $data['contact_firstname'],
				'lastname' => $data['contact_lastname'],
				'department' => $data['contact_department'],
				'email' => $data['contact_email'],
				'phone' => [
					'lada' => $data['contact_phone_lada'],
					'number' => $data['contact_phone_number']
				]
			]),
			'payment' => json_encode([
				'type' => 'demo'
			]),
			'qr' => $qr_filename,
			'operation' => !empty($data['operation']) ? true : false,
			'reputation' => !empty($data['reputation']) ? true : false,
			'zaviapms' => json_encode([
				'status' => false,
				'username' => '',
				'password' => '',
			]),
			'sms' => 0,
			'myvox_settings' => json_encode([
				'request' => !empty($data['operation']) ? true : false,
				'incident' => !empty($data['operation']) ? true : false,
				'survey' => !empty($data['reputation']) ? true : false,
				'survey_title' => [
					'es' => 'Responder encuesta',
					'en' => 'Answer survey'
				]
			]),
			'signup_date' => Functions::get_current_date(),
			'status' => false
		]);

		$account = $this->database->id();

		$this->database->insert('users', [
			'account' => $account,
			'firstname' => $data['firstname'],
			'lastname' => $data['lastname'],
			'email' => $data['email'],
			'phone' => json_encode([
				'lada' => $data['phone_lada'],
				'number' => $data['phone_number'],
			]),
			'avatar' => null,
			'username' => $data['username'],
			'password' => $this->security->create_password($data['password']),
			'user_permissions' => '["46","47","25","39","38","40","29","30","31","32","33","34","10","11","12","4","5","6","7","8","9","35","36","37","13","14","15","42","43","44","45","48","16","17","19","20","21","18","22","23","24","41","49","50","26","1","2","3"]',
			'opportunity_areas' => '[]',
			'status' => false,
		]);

		$user = $this->database->id();

		if ($data['language'] == 'es')
		{
			$administrator = 'Administrador';
			$director = 'Director';
			$manager = 'Gerente';
			$supervisor = 'Supervisor';
			$operator = 'Operador';
		}
		else if ($data['language'] == 'en')
		{
			$administrator = 'Administrator';
			$director = 'Director';
			$manager = 'Manager';
			$supervisor = 'Supervisor';
			$operator = 'Operator';
		}

		$this->database->insert('user_levels', [
			[
				'account' => $account,
				'name' => $administrator,
				'user_permissions' => '["46","47","25","39","38","40","29","30","31","32","33","34","10","11","12","4","5","6","7","8","9","35","36","37","13","14","15","42","43","44","45","48","16","17","19","20","21","18","22","23","24","41","49","50","26","1","2","3"]',
			],
			[
				'account' => $account,
				'name' => $director,
				'user_permissions' => '["46","47","25","39","41","38","26","1","2","3"]',
			],
			[
				'account' => $account,
				'name' => $manager,
				'user_permissions' => '["46","47","25","39","41","38","28","1","2","3"]',
			],
			[
				'account' => $account,
				'name' => $supervisor,
				'user_permissions' => '["46","47","39","41","38","28","1","2","3"]',
			],
			[
				'account' => $account,
				'name' => $operator,
				'user_permissions' => '["27","1","2","3"]',
			]
		]);

		if ($data['language'] == 'es')
		{
			$vacational_club = 'Club vacacional';
			$day_pass = 'Day pass';
			$external = 'Externo';
			$gold = 'Gold';
			$platinium = 'Platinium';
			$regular = 'Regular';
			$vip = 'V.I.P.';
		}
		else if ($data['language'] == 'en')
		{
			$vacational_club = 'Vacational club';
			$day_pass = 'Day pass';
			$external = 'External';
			$gold = 'Gold';
			$platinium = 'Platinium';
			$regular = 'Regular';
			$vip = 'V.I.P.';
		}

		$this->database->insert('guest_types', [
			[
				'account' => $account,
				'name' => $vacational_club,
			],
			[
				'account' => $account,
				'name' => $day_pass,
			],
			[
				'account' => $account,
				'name' => $external,
			],
			[
				'account' => $account,
				'name' => $gold,
			],
			[
				'account' => $account,
				'name' => $platinium,
			],
			[
				'account' => $account,
				'name' => $regular,
			],
			[
				'account' => $account,
				'name' => $vip,
			]
		]);

		if ($data['language'] == 'es')
		{
			$in_house = 'En casa';
			$outside_house = 'Fuera de casa';
			$pre_arrival = 'Pre llegada';
			$arrival = 'Llegada';
			$pre_departure = 'Pre salida';
			$departure = 'Salida';
		}
		else if ($data['language'] == 'en')
		{
			$in_house = 'In house';
			$outside_house = 'Outside of house';
			$pre_arrival = 'Pre arrival';
			$arrival = 'Arrival';
			$pre_departure = 'Pre departure';
			$departure = 'Departure';
		}

		$this->database->insert('reservation_statuses', [
			[
				'account' => $account,
				'name' => $in_house,
			],
			[
				'account' => $account,
				'name' => $outside_house,
			],
			[
				'account' => $account,
				'name' => $pre_arrival,
			],
			[
				'account' => $account,
				'name' => $arrival,
			],
			[
				'account' => $account,
				'name' => $departure,
			],
			[
				'account' => $account,
				'name' => $pre_departure,
			]
		]);

		if ($data['language'] == 'es')
		{
			$mr = 'Sr.';
			$miss = 'Sra.';
			$mrs = 'Srita.';
		}
		else if ($data['language'] == 'en')
		{
			$mr = 'Mr.';
			$miss = 'Miss.';
			$mrs = 'Mrs.';
		}

		$this->database->insert('guest_treatments', [
			[
				'account' => $account,
				'name' => $mr,
			],
			[
				'account' => $account,
				'name' => $miss,
			],
			[
				'account' => $account,
				'name' => $mrs,
			]
		]);

		return [
			'account' => $account,
			'user' => $user,
		];
	}

	public function get_login($data)
	{
		$query = Functions::get_json_decoded_query($this->database->select('users', [
			'[>]accounts' => [
				'account' => 'id'
			],
			'[>]room_packages' => [
				'account.room_package' => 'id'
			],
			'[>]table_packages' => [
				'account.table_package' => 'id'
			]
		], [
			'users.id(user_id)',
			'users.firstname(user_firstname)',
			'users.lastname(user_lastname)',
			'users.avatar(user_avatar)',
			'users.username(user_username)',
			'users.password(user_password)',
			'users.user_permissions(user_user_permissions)',
			'users.opportunity_areas(user_opportunity_areas)',
			'users.status(user_status)',
			'accounts.id(account_id)',
			'accounts.name(account_name)',
			'accounts.type(account_type)',
			'accounts.time_zone(account_time_zone)',
			'accounts.currency(account_currency)',
			'accounts.language(account_language)',
			'accounts.logotype(account_logotype)',
			'accounts.operation(account_operation)',
			'accounts.reputation(account_reputation)',
			'accounts.zaviapms(account_zaviapms)',
			'accounts.sms(account_sms)',
			'accounts.status(account_status)',
			'room_packages.id(room_package_id)',
			'room_packages.quantity_end(room_package_quantity_end)',
			'table_packages.id(table_package_id)',
			'table_packages.quantity_end(table_package_quantity_end)'
		], [
			'AND' => [
				'OR' => [
					'users.email' => $data['username'],
					'users.username' => $data['username']
				]
			]
		]));

		if (!empty($query))
		{
			foreach ($query[0]['user_user_permissions'] as $key => $value)
			{
				$value = $this->database->select('user_permissions', [
					'code'
				], [
					'id' => $value
				]);

				if (!empty($value))
					$query[0]['user_user_permissions'][$key] = $value[0]['code'];
				else
					unset($query[0]['user_user_permissions'][$key]);
			}

			$data = [
				'user' => [
					'id' => $query[0]['user_id'],
					'firstname' => $query[0]['user_firstname'],
					'lastname' => $query[0]['user_lastname'],
					'avatar' => $query[0]['user_avatar'],
					'username' => $query[0]['user_username'],
					'password' => $query[0]['user_password'],
					'user_permissions' => $query[0]['user_user_permissions'],
					'opportunity_areas' => $query[0]['user_opportunity_areas'],
					'status' => $query[0]['user_status']
				],
				'account' => [
					'id' => $query[0]['account_id'],
					'name' => $query[0]['account_name'],
					'type' => $query[0]['account_type'],
					'time_zone' => $query[0]['account_time_zone'],
					'currency' => $query[0]['account_currency'],
					'language' => $query[0]['account_language'],
					'logotype' => $query[0]['account_logotype'],
					'operation' => $query[0]['account_operation'],
					'reputation' => $query[0]['account_reputation'],
					'zaviapms' => $query[0]['account_zaviapms'],
					'sms' => $query[0]['account_sms'],
					'status' => $query[0]['account_status']
				]
			];

			if ($query[0]['account_type'] == 'hotel')
			{
				$data['account']['room_package'] = [
					'id' => $query[0]['room_packages_id'],
					'quantity_end' => $query[0]['room_packages_quantity_end']
				];
			}
			else if ($query[0]['account_type'] == 'restaurant')
			{
				$data['account']['table_package'] = [
					'id' => $query[0]['table_packages_id'],
					'quantity_end' => $query[0]['table_packages_quantity_end']
				];
			}

			return $data;
		}
		else
			return null;
	}

	public function new_validation($data)
	{
		if ($data[0] == 'account')
		{
			$query = Functions::get_json_decoded_query($this->database->select('accounts', [
				'name',
				'language',
				'contact'
			], [
				'AND' => [
					'id' => $data[1],
					'status' => false
				]
			]));

			if (!empty($query))
			{
				$this->database->update('accounts', [
					'status' => true
				], [
					'id' => $data[1]
				]);
			}
		}
		else if ($data[0] == 'user')
		{
			$query = $this->database->select('users', [
				'[>]accounts' => [
					'account' => 'id'
				]
			], [
				'users.firstname',
				'users.lastname',
				'users.email',
				'users.username',
				'accounts.language'
			], [
				'AND' => [
					'users.id' => $data[1],
					'users.status' => false
				]
			]);

			if (!empty($query))
			{
				$this->database->update('users', [
					'status' => true
				], [
					'id' => $data[1]
				]);
			}
		}

		return !empty($query) ? $query[0] : null;
	}
}
