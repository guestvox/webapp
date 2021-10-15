<?php

defined('_EXEC') or die;

require 'plugins/php_qr_code/qrlib.php';

class Surveys_model extends Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_surveys()
	{
		$query = Functions::get_json_decoded_query($this->database->select('surveys', [
			'id',
			'token',
			'name',
			'text',
			'nps',
			'signature',
			'main',
			'report',
			'qr',
			'status'
		], [
			'account' => Session::get_value('account')['id'],
			'ORDER' => [
				'main' => 'DESC',
				'name' => 'ASC'
			]
		]));

		return $query;
	}

	public function get_survey($id)
	{
		$query = Functions::get_json_decoded_query($this->database->select('surveys', [
			'id',
			'token',
			'name',
			'text',
			'nps',
			'signature',
			'main',
			'report',
			'qr'
		], [
			'id' => $id
		]));

		return !empty($query) ? $query[0] : null;
	}

	public function new_survey($data)
	{
		$data['token'] = strtolower(Functions::get_random(8));
		$data['qr']['filename'] = Session::get_value('account')['path'] . '_survey_qr_' . $data['token'] . '.png';
		$data['qr']['content'] = 'https://' . Configuration::$domain . '/' . Session::get_value('account')['path'] . '/survey/' . $data['token'];
		$data['qr']['dir'] = PATH_UPLOADS . $data['qr']['filename'];
		$data['qr']['level'] = 'H';
		$data['qr']['size'] = 5;
		$data['qr']['frame'] = 3;

		$query = $this->database->insert('surveys', [
			'account' => Session::get_value('account')['id'],
			'token' => $data['token'],
			'name' => json_encode([
				'es' => $data['name_es'],
				'en' => $data['name_en']
			]),
			'text' => json_encode([
				'es' => !empty($data['text_es']) ? $data['text_es'] : '',
				'en' => !empty($data['text_en']) ? $data['text_en'] : ''
			]),
			'nps' => !empty($data['nps']) ? true : false,
			'signature' => !empty($data['signature']) ? true : false,
			'main' => !empty($data['main']) ? true : false,
			'report' => json_encode([
				'status' => !empty($data['report_status']) ? true : false,
				'days' => !empty($data['report_status']) ? $data['report_days'] : '',
				'time' => !empty($data['report_status']) ? $data['report_time'] : '',
				'emails' => [
					'one' => !empty($data['report_status']) ? $data['report_email_1'] : '',
					'two' => (!empty($data['report_status']) AND !empty($data['report_email_2'])) ? $data['report_email_2'] : '',
					'three' => (!empty($data['report_status']) AND !empty($data['report_email_3'])) ? $data['report_email_3'] : '',
					'four' => (!empty($data['report_status']) AND !empty($data['report_email_4'])) ? $data['report_email_4'] : ''
				]
			]),
			'qr' => $data['qr']['filename'],
			'status' => true
		]);

		if (!empty($query) AND !empty($data['main']))
		{
			$this->database->update('surveys', [
				'main' => false
			], [
				'AND' => [
					'id[!]' => $this->database->id(),
					'account' => Session::get_value('account')['id']
				]
			]);
		}

		QRcode::png($data['qr']['content'], $data['qr']['dir'], $data['qr']['level'], $data['qr']['size'], $data['qr']['frame']);

		return $query;
	}

	public function edit_survey($data)
	{
		$query = $this->database->update('surveys', [
			'name' => json_encode([
				'es' => $data['name_es'],
				'en' => $data['name_en']
			]),
			'text' => json_encode([
				'es' => !empty($data['text_es']) ? $data['text_es'] : '',
				'en' => !empty($data['text_en']) ? $data['text_en'] : ''
			]),
			'nps' => !empty($data['nps']) ? true : false,
			'signature' => !empty($data['signature']) ? true : false,
			'main' => !empty($data['main']) ? true : false,
			'report' => json_encode([
				'status' => !empty($data['report_status']) ? true : false,
				'days' => !empty($data['report_status']) ? $data['report_days'] : '',
				'time' => !empty($data['report_status']) ? $data['report_time'] : '',
				'emails' => [
					'one' => !empty($data['report_status']) ? $data['report_email_1'] : '',
					'two' => (!empty($data['report_status']) AND !empty($data['report_email_2'])) ? $data['report_email_2'] : '',
					'three' => (!empty($data['report_status']) AND !empty($data['report_email_3'])) ? $data['report_email_3'] : '',
					'four' => (!empty($data['report_status']) AND !empty($data['report_email_4'])) ? $data['report_email_4'] : ''
				]
			])
		], [
			'id' => $data['id']
		]);

		if (!empty($query) AND !empty($data['main']))
		{
			$this->database->update('surveys', [
				'main' => false
			], [
				'AND' => [
					'id[!]' => $data['id'],
					'account' => Session::get_value('account')['id']
				]
			]);
		}

		return $query;
	}

	public function deactivate_survey($id)
	{
		$query = $this->database->update('surveys', [
			'status' => false
		], [
			'id' => $id
		]);

		return $query;
	}

	public function activate_survey($id)
	{
		$query = $this->database->update('surveys', [
			'status' => true
		], [
			'id' => $id
		]);

		return $query;
	}

	public function delete_survey($id)
	{
		$query = null;

		$deleted = $this->database->select('surveys', [
			'qr'
		], [
			'id' => $id
		]);

		if (!empty($deleted))
		{
			$this->database->delete('surveys_answers', [
				'survey' => $id
			]);

			$this->database->delete('surveys_questions', [
				'survey' => $id
			]);

			$query = $this->database->delete('surveys', [
				'id' => $id
			]);

			if (!empty($query))
				Functions::undoloader($deleted[0]['qr']);
		}

		return $query;
	}

	public function get_surveys_questions($survey, $parent = null)
	{
		$survey = Functions::get_json_decoded_query($this->database->select('surveys', [
			'id',
			'nps'
		], [
			'id' => $survey
		]));

		if (!empty($survey))
		{
			$nps = [];

			if ($survey[0]['nps'] == true)
			{
				$nps = Functions::get_json_decoded_query($this->database->select('surveys_questions', [
					'id',
					'name',
					'type',
					'values',
					'parent',
					'system',
					'status'
				], [
					'AND' => [
						'type' => 'nps',
						'system' => true
					]
				]));
			}

			$AND = [
				'account' => Session::get_value('account')['id'],
				'survey' => $survey[0]['id'],
				'status' => true
			];

			if (!isset($parent) OR empty($parent))
				$AND['parent[=]'] = null;
			else
				$AND['parent'] = $parent;

			$query = Functions::get_json_decoded_query($this->database->select('surveys_questions', [
				'id',
				'name',
				'type',
				'values',
				'parent',
				'system',
				'status'
			], [
				'AND' => $AND,
				'ORDER' => [
					'id' => 'ASC'
				]
			]));

			return !empty($query) ? array_merge($nps, $query) : $query;
		}
		else
			return null;
	}

	public function get_survey_question($id)
	{
		$query = Functions::get_json_decoded_query($this->database->select('surveys_questions', [
			'id',
			'name',
			'type',
			'values',
			'parent'
		], [
			'id' => $id
		]));

		return !empty($query) ? $query[0] : null;
	}

	public function new_survey_question($data)
	{
		$query = $this->database->insert('surveys_questions', [
			'account' => Session::get_value('account')['id'],
			'survey' => $data['survey'],
			'name' => json_encode([
				'es' => $data['name_es'],
				'en' => $data['name_en']
			]),
			'type' => $data['type'],
			'values' => ($data['type'] == 'check') ? json_encode($data['values']) : null,
			'parent' => !empty($data['parent']) ? $data['parent'] : null,
			'system' => false,
			'status' => true
		]);

		return $query;
	}

	public function edit_survey_question($data)
	{
		$query = $this->database->update('surveys_questions', [
			'name' => json_encode([
				'es' => $data['name_es'],
				'en' => $data['name_en']
			]),
			'type' => $data['type'],
			'values' => ($data['type'] == 'check') ? json_encode($data['values']) : null,
			'parent' => !empty($data['parent']) ? $data['parent'] : null
		], [
			'id' => $data['id']
		]);

		return $query;
	}

	public function deactivate_survey_question($id)
	{
		$ids = [];

		array_push($ids, $id);

		$childs1 = $this->database->select('surveys_questions', [
			'id'
		], [
			'parent' => $id
		]);

		foreach ($childs1 as $value)
		{
			array_push($ids, $value['id']);

			$childs2 = $this->database->select('surveys_questions', [
				'id'
			], [
				'parent' => $value['id']
			]);

			foreach ($childs2 as $subvalue)
				array_push($ids, $subvalue['id']);
		}

		$query = $this->database->update('surveys_questions', [
			'status' => false
		], [
			'id' => $ids
		]);

		return $query;
	}

	public function activate_survey_question($id)
	{
		$ids = [];

		array_push($ids, $id);

		$childs1 = $this->database->select('surveys_questions', [
			'id'
		], [
			'parent' => $id
		]);

		foreach ($childs1 as $value)
		{
			array_push($ids, $value['id']);

			$childs2 = $this->database->select('surveys_questions', [
				'id'
			], [
				'parent' => $value['id']
			]);

			foreach ($childs2 as $subvalue)
				array_push($ids, $subvalue['id']);
		}

		$query = $this->database->update('surveys_questions', [
			'status' => true
		], [
			'id' => $ids
		]);

		return $query;
	}

	public function delete_survey_question($id)
	{
		$ids = [];

		array_push($ids, $id);

		$childs1 = $this->database->select('surveys_questions', [
			'id'
		], [
			'parent' => $id
		]);

		foreach ($childs1 as $value)
		{
			array_push($ids, $value['id']);

			$childs2 = $this->database->select('surveys_questions', [
				'id'
			], [
				'parent' => $value['id']
			]);

			foreach ($childs2 as $subvalue)
				array_push($ids, $subvalue['id']);
		}

		$query = $this->database->delete('surveys_questions', [
			'id' => $ids
		]);

		return $query;
	}

	public function get_surveys_answers($survey, $option)
	{
		$AND = [
			'surveys_answers.account' => Session::get_value('account')['id'],
			'surveys_answers.survey' => $survey,
			'surveys_answers.date[<>]' => [Session::get_value('settings')['surveys']['reports']['filter']['started_date'],Session::get_value('settings')['surveys']['reports']['filter']['end_date']],
			'surveys_answers.hour[<>]' => [Session::get_value('settings')['surveys']['reports']['filter']['started_hour'],Session::get_value('settings')['surveys']['reports']['filter']['end_hour']]
		];

		if (Session::get_value('settings')['surveys']['reports']['filter']['owner'] == 'not_owner')
			$AND['owners.id'] = NULL;
		else if (Session::get_value('settings')['surveys']['reports']['filter']['owner'] != 'all')
			$AND['owners.id'] = Session::get_value('settings')['surveys']['reports']['filter']['owner'];

		$query = Functions::get_json_decoded_query($this->database->select('surveys_answers', [
			'[>]owners' => [
				'owner' => 'id'
			]
		], [
			'surveys_answers.id',
			'surveys_answers.token',
			'surveys_answers.owner',
			'owners.name(owner_name)',
			'owners.number(owner_number)',
			'surveys_answers.values',
			'surveys_answers.firstname',
			'surveys_answers.lastname',
			'surveys_answers.email',
			'surveys_answers.comment',
			'surveys_answers.reservation',
			'surveys_answers.date',
			'surveys_answers.hour',
			'surveys_answers.public'
		], [
			'AND' => $AND,
			'ORDER' => [
				'surveys_answers.id' => 'DESC'
			]
		]));

		foreach ($query as $key => $value)
		{
			if ($option == 'raters')
			{
				$average = 0;
				$count_1 = 0;

				foreach ($value['values'] as $subkey => $subvalue)
				{
					$subvalue = $this->database->select('surveys_questions', [
						'type'
					], [
						'id' => $subkey
					]);

					if (!empty($subvalue))
					{
						$subvalue = [
							'question' => $subvalue[0]['type'],
							'answer' => $value['values'][$subkey]
						];

						if ($subvalue['question'] == 'rate')
						{
							$average = $average + $subvalue['answer'];
							$count_1 = $count_1 + 1;
						}
					}
				}

				if ($average > 0 AND $count_1 > 0)
					$average = round(($average / $count_1), 1);

				$query[$key]['average'] = $average;

				if (Session::get_value('settings')['surveys']['reports']['filter']['rating'] == '1' AND $average >= 2)
					unset($query[$key]);
				else if (Session::get_value('settings')['surveys']['reports']['filter']['rating'] == '2' AND ($average < 2 OR $average >= 3))
					unset($query[$key]);
				else if (Session::get_value('settings')['surveys']['reports']['filter']['rating'] == '3' AND ($average < 3 OR $average >= 4))
					unset($query[$key]);
				else if (Session::get_value('settings')['surveys']['reports']['filter']['rating'] == '4' AND ($average < 4 OR $average >= 5))
					unset($query[$key]);
				else if (Session::get_value('settings')['surveys']['reports']['filter']['rating'] == '5' AND $average < 5)
					unset($query[$key]);
			}
		}

		return $query;
	}

	public function get_survey_answer($id)
	{
		$query = Functions::get_json_decoded_query($this->database->select('surveys_answers', [
			'[>]owners' => [
				'owner' => 'id'
			]
		], [
			'surveys_answers.token',
			'surveys_answers.owner',
			'owners.id(owner_id)',
			'owners.name(owner_name)',
			'owners.number(owner_number)',
			'surveys_answers.values',
			'surveys_answers.firstname',
			'surveys_answers.lastname',
			'surveys_answers.email',
			'surveys_answers.comment',
			'surveys_answers.reservation',
			'surveys_answers.date',
			'surveys_answers.hour',
			'surveys_answers.signature'
		], [
			'surveys_answers.id' => $id
		]));

		if (!empty($query))
		{
			$average = 0;
			$count_1 = 0;

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
					$count_1 = $count_1 + 1;
				}
			}

			if ($average > 0 AND $count_1 > 0)
				$average = round(($average / $count_1), 1);

			$query[0]['average'] = $average;

			return $query[0];
		}
		else
			return null;
	}

	public function edit_survey_reservation($data)
	{
		$edit = Functions::get_json_decoded_query($this->database->select('surveys_answers', [
			'[>]owners' => [
				'owner' => 'id'
			]
		], [
			'surveys_answers.token',
			'owners.id(owner_id)',
			'owners.name(owner_name)',
			'owners.number(owner_number)',
			'surveys_answers.reservation',
		], [
			'surveys_answers.id' => $data['id']
		]));

		if (!empty($edit))
		{
			$query = $this->database->update('surveys_answers', [
				'reservation' => json_encode([
					'status' => $edit[0]['reservation']['status'],
					'firstname' => $data['firstname'],
					'lastname' => $data['lastname'],
					'guest_id' => $data['guest_id'],
					'reservation_number' => $data['reservation_number'],
					'check_in' => $data['check_in'],
					'check_out' => $data['check_out'],
					'nationality' => $data['nationality'],
					'input_channel' => $data['input_channel'],
					'traveler_type' => $data['traveler_type'],
					'age_group' => $data['age_group'],
				])
			], [
				'id' => $data['id']
			]);

			return $query;
		}
		else
			return null;
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

	public function public_survey_comment($id)
	{
		$query = $this->database->update('surveys_answers', [
			'public' => true
		], [
			'id' => $id
		]);

		return $query;
	}

	public function unpublic_survey_comment($id)
	{
		$query = $this->database->update('surveys_answers', [
			'public' => false
		], [
			'id' => $id
		]);

		return $query;
	}

	public function get_surveys_average($survey)
	{
		$AND = [
			'account' => Session::get_value('account')['id'],
		];

		if (Session::get_value('settings')['surveys']['reports']['filter']['owner'] == 'not_owner')
			$AND['id'] = NULL;
		else if (Session::get_value('settings')['surveys']['reports']['filter']['owner'] != 'all')
			$AND['id'] = Session::get_value('settings')['surveys']['reports']['filter']['owner'];

		if ($survey == 'all')
			$AND['date[<>]'] = [Functions::get_past_date(Functions::get_current_date(), '30', 'days'),Functions::get_current_date()];
		else
		{
			$AND['survey'] = $survey;
			$AND['date[<>]'] = [Session::get_value('settings')['surveys']['reports']['filter']['started_date'],Session::get_value('settings')['surveys']['reports']['filter']['end_date']];
		}

		$query = Functions::get_json_decoded_query($this->database->select('surveys_answers', [
			'values'
		], [
			'AND' => $AND
		]));

		$average = 0;
		$count_1 = 0;

		foreach ($query as $value)
		{
			foreach ($value['values'] as $subkey => $subvalue)
			{
				$subvalue = $this->database->select('surveys_questions', [
					'type'
				], [
					'id' => $subkey
				]);

				if (!empty($subvalue))
				{
					$subvalue = [
						'question' => $subvalue[0]['type'],
						'answer' => $value['values'][$subkey]
					];

					if ($subvalue['question'] == 'rate')
					{
						$average = $average + $subvalue['answer'];
						$count_1 = $count_1 + 1;
					}
				}
			}
		}

		if ($average > 0 AND $count_1 > 0)
			$average = round(($average / $count_1), 1);

		return $average;
	}

    public function get_surveys_percentage($survey, $option)
	{
		$AND = [
			'account' => Session::get_value('account')['id'],
		];

		if (Session::get_value('settings')['surveys']['reports']['filter']['owner'] == 'not_owner')
			$AND['id'] = NULL;
		else if (Session::get_value('settings')['surveys']['reports']['filter']['owner'] != 'all')
			$AND['id'] = Session::get_value('settings')['surveys']['reports']['filter']['owner'];

		if ($survey == 'all')
			$AND['date[<>]'] = [Functions::get_past_date(Functions::get_current_date(), '30', 'days'),Functions::get_current_date()];
		else
		{
			$AND['survey'] = $survey;
			$AND['date[<>]'] = [Session::get_value('settings')['surveys']['reports']['filter']['started_date'],Session::get_value('settings')['surveys']['reports']['filter']['end_date']];
		}

		$query = Functions::get_json_decoded_query($this->database->select('surveys_answers', [
			'values'
		], [
			'AND' => $AND
		]));

		$percentage = 0;
		$total = 0;

		foreach ($query as $value)
		{
			$average = 0;
			$count_1 = 0;

			foreach ($value['values'] as $subkey => $subvalue)
			{
				$subvalue = $this->database->select('surveys_questions', [
					'type'
				], [
					'id' => $subkey
				]);

				if (!empty($subvalue))
				{
					$subvalue = [
						'question' => $subvalue[0]['type'],
						'answer' => $value['values'][$subkey]
					];

					if ($subvalue['question'] == 'rate')
					{
						$average = $average + $subvalue['answer'];
						$count_1 = $count_1 + 1;
					}
				}
			}

			if ($average > 0 AND $count_1 > 0)
				$average = round(($average / $count_1), 2);

			if ($option == 'one' AND $average >= 1 AND $average < 1.8)
				$percentage = $percentage + 1;
			else if ($option == 'two' AND $average >= 1.8 AND $average < 2.8)
				$percentage = $percentage + 1;
			else if ($option == 'tree' AND $average >= 2.8 AND $average < 3.8)
				$percentage = $percentage + 1;
			else if ($option == 'four' AND $average >= 3.8 AND $average < 4.8)
				$percentage = $percentage + 1;
			else if ($option == 'five' AND $average > 4.8 AND $average <= 5)
				$percentage = $percentage + 1;

			$total = $total + 1;
		}

		if ($percentage > 0 AND $total > 0)
			$percentage = round((($percentage / $total) * 100), 2);

		return $percentage;
	}

	public function get_chart_data($survey, $option)
	{
		$data = null;

		if ($option == 's1_chart' OR $option == 's2_chart')
		{
			$query1_AND = [
				'account' => Session::get_value('account')['id']
			];

			if (Session::get_value('settings')['surveys']['reports']['filter']['owner'] == 'not_owner')
				$query1_AND['id'] = NULL;
			else if (Session::get_value('settings')['surveys']['reports']['filter']['owner'] != 'all')
				$query1_AND['id'] = Session::get_value('settings')['surveys']['reports']['filter']['owner'];

			if ($survey == 'all')
				$AND['date[<>]'] = [Functions::get_past_date(Functions::get_current_date(), '30', 'days'),Functions::get_current_date()];
			else
			{
				$AND['survey'] = $survey;
				$AND['date[<>]'] = [Session::get_value('settings')['surveys']['reports']['filter']['started_date'],Session::get_value('settings')['surveys']['reports']['filter']['end_date']];
			}

			$query1 = Functions::get_json_decoded_query($this->database->select('surveys_answers', [
				'owner',
				'values'
			], [
				'AND' => $query1_AND
			]));

			if ($option == 's1_chart')
			{
				$query2 = Functions::get_json_decoded_query($this->database->select('owners', [
					'id',
					'name',
					'number'
				], [
					'account' => Session::get_value('account')['id']
				]));
			}

			$data = [
				'labels' => '',
				'datasets' => [
					'data' => '',
					'colors' => ''
				]
			];

			if (($option == 's1_chart' AND !empty($query1) AND !empty($query2)) OR ($option == 's2_chart' AND !empty($query1)))
			{
				if ($option == 's1_chart')
				{
					foreach ($query2 as $value)
					{
						$count_1 = 0;
						$count_2 = 0;
						$prom = 0;

						foreach ($query1 as $subvalue)
						{
							if ($value['id'] == $subvalue['owner'])
								$count_1 = $count_1 + 1;

							$count_2 = $count_2 + 1;
						}

						if ($count_1 > 0)
						{
							$prom = round((($count_1 / $count_2) * 100), 2);

							$data['labels'] .= "'" . $value['name'][Session::get_value('account')['language']] . " " . (!empty($value['number']) ? '#' . $value['number'] : '') . " (" . $prom . "%)',";
							$data['datasets']['data'] .= "'" . $count_1 . "',";
							$data['datasets']['colors'] .= "'#" . str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT) . str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT) . str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT) . "',";
						}
					}

					$count_1 = 0;
					$count_2 = 0;
					$prom = 0;

					foreach ($query1 as $subvalue)
					{
						if (!isset($subvalue['owner']) OR empty($subvalue['owner']))
							$count_1 = $count_1 + 1;

						$count_2 = $count_2 + 1;
					}

					if ($count_1 > 0)
					{
						$prom = round((($count_1 / $count_2) * 100), 2);

						$data['labels'] .= "'Sin propietario (" . $prom . "%)'";
						$data['datasets']['data'] .= "'" . $count_1 . "'";
						$data['datasets']['colors'] .= "'#" . str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT) . str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT) . str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT) . "'";
					}
				}
				else if ($option == 's2_chart')
				{
					$nps_1 = 0;
					$nps_2 = 0;
					$nps_3 = 0;
					$nps_4 = 0;
					$detractores = 0;
					$pasivos = 0;
					$promotores = 0;
					$answers = 0;

					foreach ($query1 as $key => $value)
					{
						$average = 0;
						$count_1 = 0;
						$go_to_nps = false;

						foreach ($value['values'] as $subkey => $subvalue)
						{
							$subvalue =  $this->database->select('surveys_questions', [
								'type'
							], [
								'id' => $subkey
							]);

							if (!empty($subvalue))
							{
								if ($subvalue[0]['type'] == 'nps')
								{
									$average = $average + $value['values'][$subkey];
									$count_1 = $count_1 + 1;
									$go_to_nps = true;
								}
							}
						}

						if ($average > 0 AND $count_1 > 0)
						{
							$average = round(($average / $count_1), 2);

							if ($average <= 6)
								$detractores = $detractores + 1;
							else if ($average > 6 AND $average <= 8)
								$pasivos = $pasivos + 1;
							else if ($average > 8)
								$promotores = $promotores + 1;
						}

						if ($go_to_nps == true)
							$answers = $answers + 1;
					}

					if ($answers > 0)
					{
						$nps_1 = round((($detractores / $answers) * 100), 2);
						$nps_2 = round((($pasivos / $answers) * 100), 2);
						$nps_3 = round((($promotores / $answers) * 100), 2);
						$nps_4 = round(($nps_3 - $nps_1));
					}

					$data['labels'] .= "'Detractores (" . $nps_1 . "%)','Pasivos (" . $nps_2 . "%)','Promotores (" . $nps_3 . "%)'";
					$data['nps'] = $nps_4;

					if ($nps_4 > 0)
					{
						$data['datasets']['data'] .= $detractores . ',' . $pasivos . ',' . $promotores;
						$data['datasets']['colors'] .= "'#f44336','#ff5722','#ff9800'";
					}
					else
					{
						$data['datasets']['data'] .= '1,1,1';
						$data['datasets']['colors'] .= "'#eee','#eee','#eee'";
					}
				}
			}
			else
			{
				$data['labels'] .= "'" . Languages::charts('empty')[Session::get_value('account')['language']] . "'";
				$data['datasets']['data'] .= '1';
				$data['datasets']['colors'] .= "'#eee'";
			}
		}
		// else if ($option == 's3_chart')
		// {
		// 	// if ($parameters[2] == 'all')
		// 	// {
		// 	// 	$where = [
		// 	// 		'account' => Session::get_value('account')['id']
		// 	// 	];
		// 	// }
		// 	// else
		// 	// {
		// 	// 	$where = [
		// 	// 		'id' => $parameters[2]
		// 	// 	];
		// 	// }
		//
		// 	$query1 = Functions::get_json_decoded_query($this->database->select('survey_questions', [
		// 		'id',
		// 		'name',
		// 		'type'
		// 	], $where));
		//
		// 	$data = [
		// 		'labels' => '',
		// 		'datasets' => ''
		// 	];
		//
		// 	$diff = Functions::get_diff_date(Session::get_value('settings')['surveys']['reports']['filter']['started_date'], Session::get_value('settings')['surveys']['reports']['filter']['end_date'], 'days', true);
		//
		// 	for ($i = 0; $i < $diff; $i++)
		// 		$data['labels'] .= "'" . Functions::get_future_date(Session::get_value('settings')['surveys']['reports']['filter']['started_date'], $i, 'days') . "',";
		//
		// 	if ($parameters[2] == 'all')
		// 	{
		// 		foreach ($query1 as $value)
		// 		{
		// 			if ($edit == true)
		// 				$datas = [];
		// 			else
		// 				$datas = '';
		//
		// 			$tmp = 0;
		// 			$break = 0;
		//
		// 			for ($i = 0; $i < $diff; $i++)
		// 			{
		// 				$query2 = Functions::get_json_decoded_query($this->database->select('survey_answers', [
		// 					'answers'
		// 				], [
		// 					'AND' => [
		// 						'account' => Session::get_value('account')['id'],
		// 						'date' => Functions::get_future_date($parameters[0], $i, 'days')
		// 					]
		// 				]));
		//
		// 				$average = 0;
		// 				$rate = 0;
		// 				$count_1 = 0;
		//
		// 				foreach ($query2 as $subvalue)
		// 				{
		// 					foreach ($subvalue['answers'] as $parentvalue)
		// 					{
		// 						if ($value['id'] == $parentvalue['id'])
		// 						{
		// 							if ($parentvalue['type'] == 'rate')
		// 							{
		// 								$rate = $rate + $parentvalue['answer'];
		// 								$count_1 = $count_1 + 1;
		// 							}
		//
		// 							foreach ($parentvalue['subanswers'] as $childvalue)
		// 							{
		// 								if ($childvalue['type'] == 'rate')
		// 								{
		// 									$rate = $rate + $childvalue['answer'];
		// 									$count_1 = $count_1 + 1;
		// 								}
		//
		// 								foreach ($childvalue['subanswers'] as $slavevalue)
		// 								{
		// 									if ($slavevalue['type'] == 'rate')
		// 									{
		// 										$rate = $rate + $slavevalue['answer'];
		// 										$count_1 = $count_1 + 1;
		// 									}
		// 								}
		// 							}
		// 						}
		// 					}
		// 				}
		//
		// 				if ($rate > 0 AND $count_1 > 0)
		// 					$average = round(($rate / $count_1), 2);
		//
		// 				if ($average <= 0 AND $tmp > 0)
		// 					$average = $tmp;
		//
		// 				$tmp = $average;
		// 				$break = $break + $average;
		//
		// 				if ($edit == true)
		// 					array_push($datas, $average);
		// 				else
		// 					$datas .= $average . ",";
		// 			}
		//
		// 			if ($break > 0)
		// 			{
		// 				$color = str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT) . str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT) . str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
		//
		// 				if ($edit == true)
		// 				{
		// 					array_push($data['datasets'], [
		// 						'label' => $value['name'][Session::get_value('account')['language']],
		// 						'data' => $datas,
		// 						'fill' => false,
		// 						'backgroundColor' => '#' . $color,
		// 						'borderColor' => '#' . $color
		// 					]);
		// 				}
		// 				else
		// 				{
		// 					$data['datasets'] .= "{
		// 						label: '" . $value['name'][Session::get_value('account')['language']] . "',
		// 						data: [" . $datas . "],
		// 						fill: false,
		// 						backgroundColor: '#" . $color . "',
		// 						borderColor: '#" . $color . "',
		// 					},";
		// 				}
		// 			}
		// 		}
		// 	}
		// 	else
		// 	{
		// 		$datas_level_1 = [];
		// 		$tmp_level_1 = 0;
		// 		$break_level_1 = 0;
		//
		// 		for ($i = 0; $i < $diff; $i++)
		// 		{
		// 			$query2 = Functions::get_json_decoded_query($this->database->select('survey_answers', [
		// 				'answers'
		// 			], [
		// 				'AND' => [
		// 					'account' => Session::get_value('account')['id'],
		// 					'date' => Functions::get_future_date($parameters[0], $i, 'days')
		// 				]
		// 			]));
		//
		// 			$average_level_1 = 0;
		// 			$rate_level_1 = 0;
		// 			$count_1_level_1 = 0;
		//
		// 			foreach ($query2 as $subvalue)
		// 			{
		// 				foreach ($subvalue['answers'] as $parentvalue)
		// 				{
		// 					if ($query1[0]['id'] == $parentvalue['id'])
		// 					{
		// 						if ($parentvalue['type'] == 'rate')
		// 						{
		// 							$rate_level_1 = $rate_level_1 + $parentvalue['answer'];
		// 							$count_1_level_1 = $count_1_level_1 + 1;
		// 						}
		//
		// 						foreach ($parentvalue['subanswers'] as $childvalue)
		// 						{
		// 							if ($childvalue['type'] == 'rate')
		// 							{
		// 								$rate_level_1 = $rate_level_1 + $childvalue['answer'];
		// 								$count_1_level_1 = $count_1_level_1 + 1;
		// 							}
		//
		// 							foreach ($childvalue['subanswers'] as $slavevalue)
		// 							{
		// 								if ($slavevalue['type'] == 'rate')
		// 								{
		// 									$rate_level_1 = $rate_level_1 + $slavevalue['answer'];
		// 									$count_1_level_1 = $count_1_level_1 + 1;
		// 								}
		// 							}
		// 						}
		// 					}
		// 				}
		// 			}
		//
		// 			if ($rate_level_1 > 0 AND $count_1_level_1 > 0)
		// 				$average_level_1 = round(($rate_level_1 / $count_1_level_1), 2);
		//
		// 			if ($average_level_1 <= 0 AND $tmp_level_1 > 0)
		// 				$average_level_1 = $tmp_level_1;
		//
		// 			$tmp_level_1 = $average_level_1;
		// 			$break_level_1 = $break_level_1 + $average_level_1;
		//
		// 			array_push($datas_level_1, $average_level_1);
		// 		}
		//
		// 		if ($break_level_1 > 0)
		// 		{
		// 			array_push($data['datasets'], [
		// 				'label' => 'A1. ' . $query1[0]['name'][Session::get_value('account')['language']],
		// 				'data' => $datas_level_1,
		// 				'fill' => false,
		// 				'backgroundColor' => '#00a5ab',
		// 				'borderColor' => '#00a5ab'
		// 			]);
		// 		}
		//
		// 		foreach ($query1[0]['subquestions'] as $value)
		// 		{
		// 			$datas_level_2 = [];
		// 			$tmp_level_2 = 0;
		// 			$break_level_2 = 0;
		//
		// 			for ($i = 0; $i < $diff; $i++)
		// 			{
		// 				$query2 = Functions::get_json_decoded_query($this->database->select('survey_answers', [
		// 					'answers'
		// 				], [
		// 					'AND' => [
		// 						'account' => Session::get_value('account')['id'],
		// 						'date' => Functions::get_future_date($parameters[0], $i, 'days')
		// 					]
		// 				]));
		//
		// 				$average_level_2 = 0;
		// 				$rate_level_2 = 0;
		// 				$count_1_level_2 = 0;
		//
		// 				foreach ($query2 as $subvalue)
		// 				{
		// 					foreach ($subvalue['answers'] as $parentvalue)
		// 					{
		// 						if ($query1[0]['id'] == $parentvalue['id'])
		// 						{
		// 							foreach ($parentvalue['subanswers'] as $childvalue)
		// 							{
		// 								if ($value['id'] == $childvalue['id'])
		// 								{
		// 									if ($childvalue['type'] == 'rate')
		// 									{
		// 										$rate_level_2 = $rate_level_2 + $childvalue['answer'];
		// 										$count_1_level_2 = $count_1_level_2 + 1;
		// 									}
		//
		// 									foreach ($childvalue['subanswers'] as $slavevalue)
		// 									{
		// 										if ($slavevalue['type'] == 'rate')
		// 										{
		// 											$rate_level_2 = $rate_level_2 + $slavevalue['answer'];
		// 											$count_1_level_2 = $count_1_level_2 + 1;
		// 										}
		// 									}
		// 								}
		// 							}
		// 						}
		// 					}
		// 				}
		//
		// 				if ($rate_level_2 > 0 AND $count_1_level_2 > 0)
		// 					$average_level_2 = round(($rate_level_2 / $count_1_level_2), 2);
		//
		// 				if ($average_level_2 <= 0 AND $tmp_level_2 > 0)
		// 					$average_level_2 = $tmp_level_2;
		//
		// 				$tmp_level_2 = $average_level_2;
		// 				$break_level_2 = $break_level_2 + $average_level_2;
		//
		// 				array_push($datas_level_2, $average_level_2);
		// 			}
		//
		// 			if ($break_level_2 > 0)
		// 			{
		// 				array_push($data['datasets'], [
		// 					'label' => 'B2. ' . $value['name'][Session::get_value('account')['language']],
		// 					'data' => $datas_level_2,
		// 					'fill' => false,
		// 					'backgroundColor' => '#3f51b5',
		// 					'borderColor' => '#3f51b5'
		// 				]);
		// 			}
		//
		// 			foreach ($value['subquestions'] as $subvalue)
		// 			{
		// 				$datas_level_3 = [];
		// 				$tmp_level_3 = 0;
		// 				$break_level_3 = 0;
		//
		// 				for ($i = 0; $i < $diff; $i++)
		// 				{
		// 					$query2 = Functions::get_json_decoded_query($this->database->select('survey_answers', [
		// 						'answers'
		// 					], [
		// 						'AND' => [
		// 							'account' => Session::get_value('account')['id'],
		// 							'date' => Functions::get_future_date($parameters[0], $i, 'days')
		// 						]
		// 					]));
		//
		// 					$average_level_3 = 0;
		// 					$rate_level_3 = 0;
		// 					$count_1_level_3 = 0;
		//
		// 					foreach ($query2 as $parentvalue)
		// 					{
		// 						foreach ($parentvalue['answers'] as $childvalue)
		// 						{
		// 							if ($query1[0]['id'] == $childvalue['id'])
		// 							{
		// 								foreach ($childvalue['subanswers'] as $slavevalue)
		// 								{
		// 									foreach ($slavevalue['subanswers'] as $intvalue)
		// 									{
		// 										if ($subvalue['id'] == $intvalue['id'])
		// 										{
		// 											if ($intvalue['type'] == 'rate')
		// 											{
		// 												$rate_level_3 = $rate_level_3 + $intvalue['answer'];
		// 												$count_1_level_3 = $count_1_level_3 + 1;
		// 											}
		// 										}
		// 									}
		// 								}
		// 							}
		// 						}
		// 					}
		//
		// 					if ($rate_level_3 > 0 AND $count_1_level_3 > 0)
		// 						$average_level_3 = round(($rate_level_3 / $count_1_level_3), 2);
		//
		// 					if ($average_level_3 <= 0 AND $tmp_level_3 > 0)
		// 						$average_level_3 = $tmp_level_3;
		//
		// 					$tmp_level_3 = $average_level_3;
		// 					$break_level_3 = $break_level_3 + $average_level_3;
		//
		// 					array_push($datas_level_3, $average_level_3);
		// 				}
		//
		// 				if ($break_level_3 > 0)
		// 				{
		// 					array_push($data['datasets'], [
		// 						'label' => 'C3. ' . $subvalue['name'][Session::get_value('account')['language']],
		// 						'data' => $datas_level_3,
		// 						'fill' => false,
		// 						'backgroundColor' => '#E91E63',
		// 						'borderColor' => '#E91E63'
		// 					]);
		// 				}
		// 			}
		// 		}
		// 	}
		// }
		else if ($option == 's4_chart' OR $option == 's5_chart' OR $option == 's6_chart' OR $option == 's7_chart')
		{
			$AND = [
				'account' => Session::get_value('account')['id'],
				'survey' => $survey,
				'date[<>]' => [Session::get_value('settings')['surveys']['reports']['filter']['started_date'],Session::get_value('settings')['surveys']['reports']['filter']['end_date']]
			];

			$query = Functions::get_json_decoded_query($this->database->select('surveys_answers', [
				'reservation'
			], [
				'AND' => $AND
			]));

			$data = [
				'labels' => '',
				'datasets' => [
					'data' => '',
					'colors' => ''
				]
			];

			$tmp = [];

			foreach ($query as $value)
			{
				if ($option == 's4_chart')
				{
					if (!empty($value['reservation']['nationality']))
					{
						if (array_key_exists($value['reservation']['nationality'], $tmp))
							$tmp[$value['reservation']['nationality']] += 1;
						else
							$tmp[$value['reservation']['nationality']] = 1;
					}
				}
				else if ($option == 's5_chart')
				{
					if (!empty($value['reservation']['input_channel']))
					{
						if (array_key_exists($value['reservation']['input_channel'], $tmp))
							$tmp[$value['reservation']['input_channel']] += 1;
						else
							$tmp[$value['reservation']['input_channel']] = 1;
					}
				}
				else if ($option == 's6_chart')
				{
					if (!empty($value['reservation']['traveler_type']))
					{
						if (array_key_exists($value['reservation']['traveler_type'], $tmp))
							$tmp[$value['reservation']['traveler_type']] += 1;
						else
							$tmp[$value['reservation']['traveler_type']] = 1;
					}
				}
				else if ($option == 's7_chart')
				{
					if (!empty($value['reservation']['age_group']))
					{
						if (array_key_exists($value['reservation']['age_group'], $tmp))
							$tmp[$value['reservation']['age_group']] += 1;
						else
							$tmp[$value['reservation']['age_group']] = 1;
					}
				}
			}

			if (!empty($tmp))
			{
				foreach ($tmp as $key => $value)
				{
					$data['labels'] .= "'" . $key . "',";
					$data['datasets']['data'] .= $value . ",";
					$data['datasets']['colors'] .= "'#" . str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT) . str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT) . str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT) . "',";
				}
			}
			else
			{
				$data['labels'] .= "'" . Languages::charts('empty')[Session::get_value('account')['language']] . "'";
				$data['datasets']['data'] .= '1';
				$data['datasets']['colors'] .= "'#eee'";
			}
		}

		return $data;
	}

	public function get_surveys_count($survey, $option)
	{
		$AND = [
			'account' => Session::get_value('account')['id'],
			'survey' => $survey
		];

		if ($option == 'today' OR $option == 'week' OR $option == 'month' OR $option == 'year')
		{
			if ($option == 'today')
				$AND['date'] = Functions::get_current_date();
			else if ($option == 'week')
				$AND['date[<>]'] = [Functions::get_current_week()[0],Functions::get_current_week()[1]];
			else if ($option == 'month')
				$AND['date[<>]'] = [Functions::get_current_month()[0],Functions::get_current_month()[1]];
			else if ($option == 'year')
				$AND['date[<>]'] = [Functions::get_current_year()[0],Functions::get_current_year()[1]];
		}

		$query = $this->database->count('surveys_answers', [
			'AND' => $AND
		]);

		return $query;
	}

	public function get_owners($type = 'all')
	{
		$AND['account'] = Session::get_value('account')['id'];

		if ($type != 'all')
			$AND[$type] = true;

		$AND['status'] = true;

		$query = Functions::get_json_decoded_query($this->database->select('owners', [
			'id',
			'name',
			'number'
		], [
			'AND' => $AND,
			'ORDER' => [
				'number' => 'ASC',
				'name' => 'ASC'
			]
		]));

		return $query;
	}
}
