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
			'accounts.qr(account_qr)',
			'accounts.operation(account_operation)',
			'accounts.reputation(account_reputation)',
			'accounts.siteminder(account_siteminder)',
			'accounts.zaviapms(account_zaviapms)',
			'accounts.settings(account_settings)',
			'accounts.status(account_status)',
			'packages.id(package_id)',
			'packages.quantity_end(package_quantity_end)'
		], [
			'users.username' => $data['username']
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
					'qr' => $query[0]['account_qr'],
					'package' => [
						'id' => $query[0]['package_id'],
						'quantity_end' => $query[0]['package_quantity_end']
					],
					'operation' => $query[0]['account_operation'],
					'reputation' => $query[0]['account_reputation'],
					'siteminder' => $query[0]['account_siteminder'],
					'zaviapms' => $query[0]['account_zaviapms'],
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
						'filter' => [
							'type' => 'all',
							'urgency' => 'all',
							'date' => 'up',
							'status' => 'open'
						]
					],
					'surveys' => [
						'answers' => [
							'filter' => [
								'started_date' => Functions::get_past_date(Functions::get_current_date(), '7', 'days'),
								'end_date' => Functions::get_current_date(),
								'owner' => 'all',
								'rating' => 'all'
							]
						],
						'stats' => [
							'filter' => [
								'started_date' => Functions::get_past_date(Functions::get_current_date(), '7', 'days'),
								'end_date' => Functions::get_current_date(),
								'owner' => 'all'
							]
						]
					]
				]
			];

			return $data;
		}
		else
			return null;
	}
}
