<?php

defined('_EXEC') or die;

class Login_model extends Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_login($data)
	{
		$query = Functions::get_json_decoded_query($this->database->select('users', [
			'[>]accounts' => [
				'account' => 'id'
			],
			'[>]packages' => [
				'accounts.package' => 'id'
			]
		], [
			'users.id(user_id)',
			'users.firstname(user_firstname)',
			'users.lastname(user_lastname)',
			'users.avatar(user_avatar)',
			'users.password(user_password)',
			'users.permissions(user_permissions)',
			'users.opportunity_areas(user_opportunity_areas)',
			'users.status(user_status)',
			'accounts.id(account_id)',
			'accounts.token(account_token)',
			'accounts.name(account_name)',
			'accounts.path(account_path)',
			'accounts.type(account_type)',
			'accounts.time_zone(account_time_zone)',
			'accounts.currency(account_currency)',
			'accounts.language(account_language)',
			'accounts.logotype(account_logotype)',
			'accounts.qrs(account_qrs)',
			'accounts.digital_menu(account_digital_menu)',
			'accounts.operation(account_operation)',
			'accounts.surveys(account_surveys)',
			'accounts.siteminder(account_siteminder)',
			'accounts.zaviapms(account_zaviapms)',
			'accounts.ambit(account_ambit)',
			'accounts.settings(account_settings)',
			'accounts.status(account_status)',
			'packages.id(package_id)',
			'packages.quantity_end(package_quantity_end)'
		], [
			'users.username' => $data['username'],
		]));

		if (!empty($query))
		{
			foreach ($query[0]['user_permissions'] as $key => $value)
			{
				$value = $this->database->select('permissions', [
					'code'
				], [
					'id' => $value
				]);

				if (!empty($value))
					$query[0]['user_permissions'][$key] = $value[0]['code'];
				else
					unset($query[0]['user_permissions'][$key]);
			}

			$data = [
				'user' => [
					'id' => $query[0]['user_id'],
					'firstname' => $query[0]['user_firstname'],
					'lastname' => $query[0]['user_lastname'],
					'avatar' => $query[0]['user_avatar'],
					'password' => $query[0]['user_password'],
					'permissions' => $query[0]['user_permissions'],
					'opportunity_areas' => $query[0]['user_opportunity_areas'],
					'status' => $query[0]['user_status']
				],
				'account' => [
					'id' => $query[0]['account_id'],
					'token' => $query[0]['account_token'],
					'name' => $query[0]['account_name'],
					'path' => $query[0]['account_path'],
					'type' => $query[0]['account_type'],
					'time_zone' => $query[0]['account_time_zone'],
					'currency' => $query[0]['account_currency'],
					'language' => $query[0]['account_language'],
					'logotype' => $query[0]['account_logotype'],
					'qrs' => $query[0]['account_qrs'],
					'package' => [
						'id' => $query[0]['package_id'],
						'quantity_end' => $query[0]['package_quantity_end']
					],
					'digital_menu' => $query[0]['account_digital_menu'],
					'operation' => $query[0]['account_operation'],
					'surveys' => $query[0]['account_surveys'],
					'siteminder' => $query[0]['account_siteminder'],
					'zaviapms' => $query[0]['account_zaviapms'],
					'ambit' => $query[0]['account_ambit'],
					'settings' => [
						'menu' => [
							'currency' => $query[0]['account_settings']['myvox']['menu']['currency'],
							'multi' => $query[0]['account_settings']['myvox']['menu']['multi']
						]
					],
					'status' => $query[0]['account_status']
				],
				'settings' => [
					'voxes' => [
						'voxes' => [
							'filter' => [
								'type' => 'all',
								'owner' => 'all',
								'opportunity_area' => 'all',
								'opportunity_type' => 'all',
								'location' => 'all',
								'urgency' => 'all',
								'assigned' => 'all',
								'order' => 'date_down',
								'status' => 'open'
							]
						],
						'stats' => [
							'filter' => [
								'started_date' => Functions::get_past_date(Functions::get_current_date(), '7', 'days'),
								'end_date' => Functions::get_current_date(),
								'type' => 'all'
							]
						],
						'opportunity_areas' => [
							'filter' => [
								'id' => 'all',
							]
						]
					],
					'surveys' => [
						'reports' => [
							'filter' => [
								'search' => 'period',
								'period_type' => 'days',
								'period_number' => '7',
								'started_date' => Functions::get_past_date(Functions::get_current_date(), '7', 'days'),
								'end_date' => Functions::get_current_date(),
								'started_hour' => '00:00:01',
								'end_hour' => '23:59:59',
								'owner' => 'all',
								'rating' => 'all',
								'general' => true,
								'channels' => true,
								'comments' => true
							]
						],
						'answers' => [
							'filter' => [
								'started_date' => Functions::get_past_date(Functions::get_current_date(), '7', 'days'),
								'end_date' => Functions::get_current_date(),
								'started_hour' => '00:00:01',
								'end_hour' => '23:59:59',
								'owner' => 'all',
								'rating' => 'all'
							]
						],
						'stats' => [
							'filter' => [
								'started_date' => Functions::get_past_date(Functions::get_current_date(), '7', 'days'),
								'end_date' => Functions::get_current_date(),
								'started_hour' => '00:00:01',
								'end_hour' => '23:59:59',
								'owner' => 'all'
							]
						]
					],
					'menu' => [
						'orders' => [
							'filter' => [
								'type_service' => 'all',
								'delivery' => 'all',
								'accepted' => 'all',
								'delivered' => 'all'
							]
						],
						'categories' => [
							'filter' => [
								'id' => 'all',
							]
						]
					]
				],
				'temporal' => [
					'menu_topics_groups' => [],
					'token_ambit' => ''
				]
			];

			return $data;
		}
		else
			return null;
	}
}
