<?php

defined('_EXEC') or die;

class Myvox_model extends Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_account($path)
	{
		$query = Functions::get_json_decoded_query($this->database->select('accounts', [
			'id',
			'name',
			'path',
			'type',
			'location',
			'language',
			'logotype',
			'digital_menu',
			'operation',
			'surveys',
			'zaviapms',
			'ambit',
			'whatsapp',
			'settings',
			'payment'
		], [
			'AND' => [
				'path' => $path,
				'status' => true
			]
		]));

		return !empty($query) ? $query[0] : null;
	}

    public function get_owner($token)
	{
		$query = Functions::get_json_decoded_query($this->database->select('owners', [
			'id',
			'token',
			'name',
			'number'
		], [
			'token' => $token
		]));

		return !empty($query) ? $query[0] : null;
	}

	public function get_reservation($number, $zaviapms)
	{
		$reservation = [
			'status' => 'success',
			'firstname' => '',
			'lastname' => '',
			'guest_id' => '',
			'reservation_number' => '',
			'check_in' => '',
			'check_out' => '',
			'nationality' => '',
			'input_channel' => '',
			'traveler_type' => '',
			'age_group' => ''
		];

		if (!empty($number) AND $zaviapms['status'] == true)
		{
			$query = Functions::api('zaviapms', $zaviapms, 'get', 'room', $number);

			$reservation['status'] = $query['Status'];

			if ($reservation['status'] == 'success')
			{
				$reservation['firstname'] = $query['Name'];
				$reservation['lastname'] = $query['LastName'];
				$reservation['guest_id'] = $query['FolioID'];
				$reservation['reservation_number'] = $query['FolioRefID'];
				$reservation['check_in'] = $query['StartDate'];
				$reservation['check_out'] = $query['EndDate'];
				$reservation['nationality'] = $query['Country'];
				$reservation['input_channel'] = $query['Channel'];
				$reservation['traveler_type'] = $query['TravelerType'];
				$reservation['age_group'] = $query['AgeGroup'];
			}
		}

		return $reservation;
	}

    public function get_opportunity_areas($type)
	{
		$query = Functions::get_json_decoded_query($this->database->select('opportunity_areas', [
			'id',
			'name'
		], [
			'AND' => [
				'account' => Session::get_value('myvox')['account']['id'],
				$type => true,
				'public' => true,
				'status' => true
			],
			'ORDER' => [
				'name' => 'ASC'
			]
		]));

		return $query;
	}

    public function get_opportunity_area($id)
	{
		$query = Functions::get_json_decoded_query($this->database->select('opportunity_areas', [
			'id',
			'name'
		], [
			'id' => $id
		]));

		return !empty($query) ? $query[0] : null;
	}

    public function get_opportunity_types($opportunity_area, $type)
	{
		$query = Functions::get_json_decoded_query($this->database->select('opportunity_types', [
			'id',
			'name'
		], [
			'AND' => [
				'opportunity_area' => $opportunity_area,
				$type => true,
				'public' => true,
				'status' => true
			],
			'ORDER' => [
				'name' => 'ASC'
			]
		]));

		return $query;
	}

    public function get_opportunity_type($id)
	{
		$query = Functions::get_json_decoded_query($this->database->select('opportunity_types', [
			'id',
			'name'
		], [
			'id' => $id
		]));

		return !empty($query) ? $query[0] : null;
	}

    public function get_locations($type)
	{
		$query = Functions::get_json_decoded_query($this->database->select('locations', [
			'id',
			'name'
		], [
			'AND' => [
				'account' => Session::get_value('myvox')['account']['id'],
				$type => true,
				'public' => true,
				'status' => true
			],
			'ORDER' => [
				'name' => 'ASC'
			]
		]));

		return $query;
	}

    public function get_location($id)
	{
		$query = Functions::get_json_decoded_query($this->database->select('locations', [
			'id',
			'name'
		], [
			'id' => $id
		]));

		return !empty($query) ? $query[0] : null;
	}

	public function get_assigned_users($opportunity_area)
	{
        $query = Functions::get_json_decoded_query($this->database->select('users', [
            'firstname',
            'lastname',
            'email',
            'phone',
            'opportunity_areas',
            'whatsapp'
        ], [
            'AND' => [
				'account' => Session::get_value('myvox')['account']['id'],
				'status' => true
			]
        ]));

        foreach ($query as $key => $value)
        {
            if (!in_array($opportunity_area, $value['opportunity_areas']))
				unset($query[$key]);
        }

		return $query;
	}

	public function get_countries()
	{
		$query1 = Functions::get_json_decoded_query($this->database->select('countries', [
			'name',
			'lada'
		], [
			'priority[>=]' => 1,
			'ORDER' => [
				'priority' => 'ASC'
			]
		]));

		$query2 = Functions::get_json_decoded_query($this->database->select('countries', [
			'name',
			'lada'
		], [
			'priority[=]' => null,
			'ORDER' => [
				'name' => 'ASC'
			]
		]));

		$query = array_merge($query1, $query2);

		return $query;
	}

	public function get_menu_categories()
	{
		$categories = [];

		$inner_join = [
			'[>]icons' => [
				'icon' => 'id'
			]
		];

		$fields = [
			'menu_categories.id',
			'menu_categories.name',
			'icons.url(icon_url)',
			'icons.type(icon_type)'
		];

		$query_1 = Functions::get_json_decoded_query($this->database->select('menu_categories', $inner_join, $fields, [
			'AND' => [
				'menu_categories.id' => Session::get_value('myvox')['menu_categories'],
				'menu_categories.position[>=]' => 1,
				'menu_categories.status' => true
			],
			'ORDER' => [
				'menu_categories.position' => 'ASC'
			]
		]));

		if (!empty($query_1))
		{
			foreach ($query_1 as $value)
				$categories[$value['id']] = $value;
		}

		$query_2 = Functions::get_json_decoded_query($this->database->select('menu_categories', $inner_join, $fields, [
			'AND' => [
				'menu_categories.id' => Session::get_value('myvox')['menu_categories'],
				'menu_categories.position[=]' => null,
				'menu_categories.status' => true
			],
			'ORDER' => [
				'menu_categories.name' => 'ASC'
			]
		]));

		if (!empty($query_2))
		{
			foreach ($query_2 as $value)
				$categories[$value['id']] = $value;
		}

		$query_3 = Functions::get_json_decoded_query($this->database->select('menu_categories', $inner_join, $fields, [
			'AND' => [
				'menu_categories.account' => Session::get_value('myvox')['account']['id'],
				'menu_categories.position[>=]' => 1,
				'menu_categories.status' => true
			],
			'ORDER' => [
				'menu_categories.position' => 'ASC'
			]
		]));

		if (!empty($query_3))
		{
			foreach ($query_3 as $value)
			{
				if (!array_key_exists($value['id'], $categories))
					$categories[$value['id']] = $value;
			}
		}

		$query_4 = Functions::get_json_decoded_query($this->database->select('menu_categories', $inner_join, $fields, [
			'AND' => [
				'menu_categories.account' => Session::get_value('myvox')['account']['id'],
				'menu_categories.position[=]' => null,
				'menu_categories.status' => true
			],
			'ORDER' => [
				'menu_categories.name' => 'ASC'
			]
		]));

		if (!empty($query_4))
		{
			foreach ($query_4 as $value)
			{
				if (!array_key_exists($value['id'], $categories))
					$categories[$value['id']] = $value;
			}
		}

		return $categories;
	}

	public function get_menu_products()
	{
		$inner_join = [
			'[>]icons' => [
				'icon' => 'id'
			]
		];

		$fields = [
			'menu_products.id',
			'menu_products.name',
			'menu_products.topics',
			'menu_products.price',
			'menu_products.available',
			'menu_products.avatar',
			'menu_products.image',
			'icons.url(icon_url)',
			'icons.type(icon_type)',
			'icons.color(icon_color)',
			'menu_products.categories',
			'menu_products.map'
		];

		$AND_1 = [
			'menu_products.account' => Session::get_value('myvox')['account']['id'],
			'menu_products.position[>=]' => 1,
			'menu_products.status' => true
		];

		$AND_2 = [
			'menu_products.account' => Session::get_value('myvox')['account']['id'],
			'menu_products.position[=]' => null,
			'menu_products.status' => true
		];

		if (!empty(Session::get_value('myvox')['menu_name']))
		{
			$AND_1['OR'] = [
				'menu_products.name[~]' => Session::get_value('myvox')['menu_name'],
				'menu_products.description[~]' => Session::get_value('myvox')['menu_name']
			];

			$AND_2['OR'] = [
				'menu_products.name[~]' => Session::get_value('myvox')['menu_name'],
				'menu_products.description[~]' => Session::get_value('myvox')['menu_name']
			];
		}

		if (!empty(Session::get_value('myvox')['menu_price']))
		{
			$AND_1['menu_products.price[<=]'] = Session::get_value('myvox')['menu_price'];
			$AND_2['menu_products.price[<=]'] = Session::get_value('myvox')['menu_price'];
		}

		$query_1 = Functions::get_json_decoded_query($this->database->select('menu_products', $inner_join, $fields, [
			'AND' => $AND_1,
			'ORDER' => [
				'menu_products.position' => 'ASC'
			]
		]));

		$query_2 = Functions::get_json_decoded_query($this->database->select('menu_products', $inner_join, $fields, [
			'AND' => $AND_2,
			'ORDER' => [
				'menu_products.name' => 'ASC'
			]
		]));

		$query = array_merge($query_1, $query_2);

		if (!empty(Session::get_value('myvox')['menu_categories']))
		{
			foreach ($query as $key => $value)
			{
				$go = false;

				foreach ($value['categories'] as $subkey => $subvalue)
				{
					if (in_array($subvalue, Session::get_value('myvox')['menu_categories']))
						$go = true;
				}

				if ($go == false)
					unset($query[$key]);
			}
		}

		return $query;
	}

	public function get_menu_product($id)
	{
		$query = Functions::get_json_decoded_query($this->database->select('menu_products', [
			'[>]icons' => [
				'icon' => 'id'
			]
		], [
			'menu_products.name',
			'menu_products.description',
			'menu_products.topics',
			'menu_products.price',
			'menu_products.avatar',
			'menu_products.image',
			'icons.type(icon_type)',
			'icons.url(icon_url)',
			'icons.color(icon_color)',
			'menu_products.map'
		], [
			'menu_products.id' => $id
		]));

		if (!empty($query))
		{
			foreach ($query[0]['topics'] as $key => $value)
			{
				foreach ($value as $subkey => $subvalue)
				{
					$subvalue['topic'] = Functions::get_json_decoded_query($this->database->select('menu_topics', [
						'name'
					], [
						'id' => $subvalue['id']
					]));

					if (!empty($subvalue['topic']))
						$query[0]['topics'][$key][$subkey]['name'] = $subvalue['topic'][0]['name'];
					else
						unset($query[0]['topics'][$key][$subkey]);
				}

				if (empty($query[0]['topics'][$key]))
					unset($query[0]['topics'][$key]);
			}

			return $query[0];
		}
		else
			return null;
	}

	public function get_menu_order_total($data = null, $topics = null, $quantity = null)
	{
		$total = 0;

		if (isset($topics))
		{
			foreach ($topics as $value)
				$total = $total + $value['price'];

			$total = ($total + $data) * $quantity;
		}
		else
		{
			foreach ($data as $value)
			{
				foreach ($value as $subvalue)
					$total = $total + $subvalue['total'];
			}
		}

		return $total;
	}

	public function get_menu_order_payment()
	{
		$query = $this->database->select('payments', [
			'token',
			'code',
			'response'
		], [
			'token' => Session::get_value('myvox')['menu_payment_token']
		]);

		return !empty($query) ? $query[0] : null;
	}

	public function new_menu_order($data)
	{
		$query = $this->database->insert('menu_orders', [
			'account' => Session::get_value('myvox')['account']['id'],
			'token' => Session::get_value('myvox')['menu_payment_token'],
			'type_service' => Session::get_value('myvox')['url'],
			'owner' => (Session::get_value('myvox')['url'] == 'owner') ? Session::get_value('myvox')['owner']['id'] : null,
			'delivery' => (Session::get_value('myvox')['url'] == 'delivery') ? $data['delivery'] : null,
			'contact' => (Session::get_value('myvox')['url'] == 'delivery') ? json_encode([
				'firstname' => $data['firstname'],
				'lastname' => $data['lastname'],
				'email' => $data['email'],
				'phone' => [
					'lada' => $data['phone_lada'],
					'number' => $data['phone_number']
				]
			]) : null,
			'address' => (Session::get_value('myvox')['url'] == 'delivery') ? $data['address'] : null,
			'location' => (Session::get_value('myvox')['url'] == 'delivery') ? json_encode([
				'lat' => $data['delivery_lat'],
				'lng' => $data['delivery_lng']
			]) : null,
			'date' => Functions::get_formatted_date($data['started_date']),
			'hour' => Functions::get_formatted_hour($data['started_hour']),
			'total' => Session::get_value('myvox')['menu_order']['total'],
			'payment_method' => $data['payment_method'],
			'payment_cash' => ($data['payment_method'] == 'cash') ? $data['payment_cash'] : null,
			'currency' => Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency'],
			'shopping_cart' => json_encode(Session::get_value('myvox')['menu_order']['shopping_cart']),
			'declined' => false,
			'accepted' => false,
			'delivered' => false
		]);

		return !empty($query) ? $this->database->id() : null;
	}

    public function new_vox($data)
	{
		$query = $this->database->insert('voxes', [
			'account' => Session::get_value('myvox')['account']['id'],
			'type' => $data['type'],
			'token' => $data['token'],
			'owner' => Session::get_value('myvox')['owner']['id'],
			'opportunity_area' => $data['opportunity_area'],
			'opportunity_type' => $data['opportunity_type'],
			'started_date' => Functions::get_formatted_date($data['started_date']),
			'started_hour' => Functions::get_formatted_hour($data['started_hour']),
			'death_line' => null,
			'location' => $data['location'],
			'cost' => null,
			'urgency' => 'medium',
			'confidentiality' => false,
			'assigned_users' => json_encode([]),
			'observations' => ($data['type'] == 'request' AND !empty($data['observations'])) ? $data['observations'] : null,
			'subject' => null,
			'description' => ($data['type'] == 'incident' AND !empty($data['description'])) ? $data['description'] : null,
			'action_taken' => null,
			'firstname' => null,
			'lastname' => null,
			'guest_treatment' => null,
			'guest_id' => null,
			'guest_type' => null,
			'reservation_number' => (Session::get_value('myvox')['account']['type'] == 'hotel' AND $data['type'] == 'incident' AND !empty(Session::get_value('myvox')['owner']['reservation']['reservation_number'])) ? Session::get_value('myvox')['owner']['reservation']['reservation_number'] : null,
			'reservation_status' => null,
			'check_in' => (Session::get_value('myvox')['account']['type'] == 'hotel' AND $data['type'] == 'incident' AND !empty(Session::get_value('myvox')['owner']['reservation']['check_in'])) ? Session::get_value('myvox')['owner']['reservation']['check_in'] : null,
			'check_out' => (Session::get_value('myvox')['account']['type'] == 'hotel' AND $data['type'] == 'incident' AND !empty(Session::get_value('myvox')['owner']['reservation']['check_out'])) ? Session::get_value('myvox')['owner']['reservation']['check_out'] : null,
			'attachments' => json_encode([]),
			'viewed_by' => json_encode([]),
			'comments' => json_encode([]),
			'changes_history' => json_encode([
				[
					'type' => 'created',
					'user' => null,
					'date' => Functions::get_current_date(),
					'hour' => Functions::get_current_hour()
				]
			]),
			'created_user' => null,
			'created_date' => Functions::get_current_date(),
			'created_hour' => Functions::get_current_hour(),
			'edited_user' => null,
			'edited_date' => null,
			'edited_hour' => null,
			'completed_user' => null,
			'completed_date' => null,
			'completed_hour' => null,
			'reopened_user' => null,
			'reopened_date' => null,
			'reopened_hour' => null,
			'automatic_start' => false,
			'status' => true,
			'origin' => 'myvox'
		]);

		return $query;
	}

	public function get_survey($token, $account = null)
	{
		$AND = [];

		if ($token == 'main')
		{
			$AND['account'] = Session::get_value('myvox')['account']['id'];
			$AND['main'] = true;
		}
		else
		{
			$AND['account'] = $account;
			$AND['token'] = $token;
		}

		$AND['status'] = true;

		$query = Functions::get_json_decoded_query($this->database->select('surveys', [
			'id',
			'token',
			'name',
			'text',
			'nps',
			'signature'
		], [
			'AND' => $AND
		]));

		return !empty($query) ? $query[0] : null;
	}

	public function get_surveys_questions($survey, $parent = null)
	{
		$nps = [];

		if ($survey['nps'] == true)
		{
			$nps = Functions::get_json_decoded_query($this->database->select('surveys_questions', [
				'id',
				'name',
				'type',
				'values'
			], [
				'AND' => [
					'type' => 'nps',
					'system' => true
				]
			]));
		}

		$and = [
			'account' => Session::get_value('myvox')['account']['id'],
			'survey' => $survey['id'],
			'status' => true
		];

		if (!isset($parent) OR empty($parent))
			$and['parent[=]'] = null;
		else
			$and['parent'] = $parent;

		$query = Functions::get_json_decoded_query($this->database->select('surveys_questions', [
			'id',
			'name',
			'type',
			'values'
		], [
			'AND' => $and,
			'ORDER' => [
				'id' => 'ASC'
			]
		]));

		return !empty($query) ? array_merge($nps, $query) : $query;
	}

	public function new_survey_answer($data)
	{
		$data['values'] = $data;

		unset($data['values']['owner']);
		unset($data['values']['firstname']);
		unset($data['values']['lastname']);
		unset($data['values']['email']);
		unset($data['values']['comment']);
		unset($data['values']['signature']);
		unset($data['values']['action']);
		unset($data['values']['token']);
		unset($data['values']['survey']);

		foreach($data['values'] as $key => $value)
		{
			if (!isset($value) OR empty($value))
				unset($data['values'][$key]);
		}

		$query = $this->database->insert('surveys_answers', [
			'account' => Session::get_value('myvox')['account']['id'],
			'survey' => Session::get_value('myvox')['survey']['id'],
			'token' => $data['token'],
			'owner' => (Session::get_value('myvox')['url'] == 'survey') ? null : Session::get_value('myvox')['owner']['id'],
			'values' => json_encode($data['values']),
			'firstname' => (Session::get_value('myvox')['url'] == 'survey') ? $data['firstname'] : null,
			'lastname' => (Session::get_value('myvox')['url'] == 'survey') ? $data['lastname'] : null,
			'email' => (Session::get_value('myvox')['url'] == 'survey') ? $data['email'] : null,
			'comment' => !empty($data['comment']) ? $data['comment'] : null,
			'reservation' => (Session::get_value('myvox')['url'] == 'survey') ? null : ((Session::get_value('myvox')['account']['type'] == 'hotel') ? json_encode(Session::get_value('myvox')['owner']['reservation']) : null),
			'date' => Functions::get_current_date(),
			'hour' => Functions::get_current_hour(),
			'signature' => (Session::get_value('myvox')['survey']['signature'] == true) ? Functions::base_64($data['signature'], Session::get_value('myvox')['account']['path']) : null,
			'public' => false
		]);

		return !empty($query) ? $this->database->id() : null;
	}

	public function get_survey_average($id)
	{
		$average = 0;

		$query = Functions::get_json_decoded_query($this->database->select('surveys_answers', [
			'values'
		], [
			'id' => $id
		]));

		if (!empty($query))
		{
			$count = 0;

			foreach ($query[0]['values'] as $key => $value)
			{
				$value = $this->database->select('surveys_questions', [
					'type'
				], [
					'id' => $key
				]);

				$value = [
					'question' => $value[0]['type'],
					'answer' => $query[0]['values'][$key]
				];

				if ($value['question'] == 'rate')
				{
					$average = $average + $value['answer'];
					$count = $count + 1;
				}
			}

			if ($average > 0 AND $count > 0)
				$average = round(($average / $count), 1);
		}

		return $average;
	}

	public function get_sms()
	{
		$query = $this->database->select('accounts', [
			'sms'
		], [
			'id' => Session::get_value('myvox')['account']['id']
		]);

		return !empty($query) ? $query[0]['sms'] : null;
	}

	public function edit_sms($sms)
	{
		$query = $this->database->update('accounts', [
			'sms' => $sms
		], [
			'id' => Session::get_value('myvox')['account']['id']
		]);

		return $query;
	}

	public function get_whatsapp()
	{
		$query = $this->database->select('accounts', [
			'whatsapp'
		], [
			'id' => Session::get_value('myvox')['account']['id']
		]);

		return !empty($query) ? $query[0]['whatsapp'] : null;
	}

	public function edit_whatsapp($whatsapp)
	{
		$query = $this->database->update('accounts', [
			'whatsapp' => $whatsapp
		], [
			'id' => Session::get_value('myvox')['account']['id']
		]);

		return $query;
	}
}
