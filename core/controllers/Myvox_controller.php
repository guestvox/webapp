<?php

defined('_EXEC') or die;

require_once 'plugins/nexmo/vendor/autoload.php';

// require 'plugins/aws/vendor/autoload.php';
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;
// use Aws\Ses\SesClient;
// use Aws\Exception\AwsException;

class Myvox_controller extends Controller
{
	private $lang1;
	private $lang2;

	public function __construct()
	{
		parent::__construct();

		$this->lang1 = Session::get_value('lang');
		$this->lang2 = (Session::exists_var('myvox') == true AND !empty(Session::get_value('myvox')['account'])) ? Session::get_value('myvox')['account']['language'] : Session::get_value('lang');
	}

    public function index($params)
    {
		$break = true;

		$account = $this->model->get_account($params[0]);

		if (!empty($account))
		{
			$owner = null;
			$url = '';

			if (!empty($params[1]) AND !empty($params[2]))
			{
				$owner = $params[2];
				$url = 'owner';
			}
			else
			{
				if (Session::exists_var('myvox') == true AND !empty(Session::get_value('myvox')['owner']))
					$owner = Session::get_value('myvox')['owner']['id'];

				$url = 'account';
			}

			$owner = $this->model->get_owner($owner);

			if (!empty($owner) OR $url == 'account')
			{
				if (!empty($owner) AND $account['type'] == 'hotel')
					$owner['reservation'] = $this->model->get_reservation($owner['number']);

				$myvox = [
					'account' => $account,
					'owner' => $owner,
					'url' => $url
				];

				Session::set_value('myvox', $myvox);

				$break = false;
			}
		}

		if ($break == false)
		{
			$template = $this->view->render($this, 'index');

			define('_title', Session::get_value('myvox')['account']['name'] . ' | {$lang.myvox}');

			$btn_new_request = '';
			$btn_new_incident = '';
			$btn_new_menu_order = '';

			if (Session::get_value('myvox')['account']['operation'] == true)
			{
				if (Session::get_value('myvox')['account']['settings']['myvox']['request']['status'] == true)
					$btn_new_request .= '<a href="/' . $params[0] . '/request">' . Session::get_value('myvox')['account']['settings']['myvox']['request']['title'][$this->lang1] . '</a>';

				if (Session::get_value('myvox')['account']['settings']['myvox']['incident']['status'] == true)
					$btn_new_incident .= '<a href="/' . $params[0] . '/incident">' . Session::get_value('myvox')['account']['settings']['myvox']['incident']['title'][$this->lang1] . '</a>';

				if (Session::get_value('myvox')['account']['type'] == 'hotel' OR Session::get_value('myvox')['account']['type'] == 'restaurant')
				{
					if (Session::get_value('myvox')['account']['settings']['myvox']['menu']['status'] == true)
						$btn_new_menu_order .= '<a href="/' . $params[0] . '/menu/products">' . Session::get_value('myvox')['account']['settings']['myvox']['menu']['title'][$this->lang1] . '</a>';
				}
			}

			$btn_new_survey_answer = '';

			if (Session::get_value('myvox')['account']['reputation'] == true)
			{
				if (Session::get_value('myvox')['account']['settings']['myvox']['survey']['status'] == true)
					$btn_new_survey_answer .= '<a href="/' . $params[0] . '/survey">' . Session::get_value('myvox')['account']['settings']['myvox']['survey']['title'][$this->lang1] . '</a>';
			}

			$replace = [
				'{$logotype}' => '{$path.uploads}' . Session::get_value('myvox')['account']['logotype'],
				'{$btn_new_request}' => $btn_new_request,
				'{$btn_new_incident}' => $btn_new_incident,
				'{$btn_new_menu_order}' => $btn_new_menu_order,
				'{$btn_new_survey_answer}' => $btn_new_survey_answer
			];

			$template = $this->format->replace($replace, $template);

			echo $template;
		}
		else
			header('Location: /');
    }

    public function request($params)
    {
		$break = true;

		if (Session::exists_var('myvox') == true AND !empty(Session::get_value('myvox')['account']))
		{
			if (Session::get_value('myvox')['account']['operation'] == true AND Session::get_value('myvox')['account']['settings']['myvox']['request']['status'] == true)
			{
				if (!empty(Session::get_value('myvox')['url']))
				{
					if (Session::get_value('myvox')['url'] == 'account')
						$break = false;
					else if (Session::get_value('myvox')['url'] == 'owner' AND !empty(Session::get_value('myvox')['owner']))
						$break = false;
				}
			}
		}

		if ($break == false)
		{
			if (Format::exist_ajax_request() == true)
			{
				if ($_POST['action'] == 'get_owner')
				{
					$owner = $this->model->get_owner($_POST['owner']);

					if (!empty($owner))
					{
						if (Session::get_value('myvox')['account']['type'] == 'hotel')
							$owner['reservation'] = $this->model->get_reservation($owner['number']);

						$myvox = Session::get_value('myvox');

						$myvox['owner'] = $owner;

						Session::set_value('myvox', $myvox);

						Functions::environment([
							'status' => 'success'
						]);
					}
					else
					{
						Functions::environment([
							'status' => 'error',
							'message' => '{$lang.operation_error}'
						]);
					}
				}

				if ($_POST['action'] == 'get_opt_opportunity_types')
				{
					$html = '<option value="" hidden>{$lang.choose}</option>';

					foreach ($this->model->get_opportunity_types($_POST['opportunity_area'], 'request') as $value)
						$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang1] . '</option>';

					Functions::environment([
						'status' => 'success',
						'html' => $html
					]);
				}

				if ($_POST['action'] == 'new_request')
				{
					$labels = [];

					if (Session::get_value('myvox')['url'] == 'account')
					{
						if (!isset($_POST['owner']) OR empty($_POST['owner']))
							array_push($labels, ['owner','']);
					}

					if (!isset($_POST['opportunity_area']) OR empty($_POST['opportunity_area']))
						array_push($labels, ['opportunity_area','']);

					if (!isset($_POST['opportunity_type']) OR empty($_POST['opportunity_type']))
						array_push($labels, ['opportunity_type','']);

					if (!isset($_POST['started_date']) OR empty($_POST['started_date']))
						array_push($labels, ['started_date','']);

					if (!isset($_POST['started_hour']) OR empty($_POST['started_hour']))
						array_push($labels, ['started_hour','']);

					if (!isset($_POST['location']) OR empty($_POST['location']))
						array_push($labels, ['location','']);

					if (!empty($_POST['firstname']) OR !empty($_POST['lastname']))
					{
						if (!isset($_POST['firstname']) OR empty($_POST['firstname']))
							array_push($labels, ['firstname','']);

						if (!isset($_POST['lastname']) OR empty($_POST['lastname']))
							array_push($labels, ['lastname','']);
					}

					if (!isset($_POST['email']) OR empty($_POST['email']) OR Functions::check_email($_POST['email']) == false)
						array_push($labels, ['email','']);

					if (!empty($_POST['phone_lada']) OR !empty($_POST['phone_number']))
					{
						if (!isset($_POST['phone_lada']) OR empty($_POST['phone_lada']))
							array_push($labels, ['phone_lada','']);

						if (!isset($_POST['phone_number']) OR empty($_POST['phone_number']))
							array_push($labels, ['phone_number','']);
					}

					if (empty($labels))
					{
						$_POST['type'] = 'request';
						$_POST['token'] = strtolower(Functions::get_random(8));

						$query = $this->model->new_vox($_POST);

						if (!empty($query))
						{
							$mail1 = new Mailer(true);

							try
							{
								$mail1->setFrom('noreply@guestvox.com', 'Guestvox');
								$mail1->addAddress($_POST['email'], ((!empty($_POST['firstname']) AND !empty($_POST['lastname'])) ? $_POST['firstname'] . ' ' . $_POST['lastname'] : Languages::email('not_name')[$this->lang1]));
								$mail1->Subject = Languages::email('thanks_received_request')[$this->lang1];
								$mail1->Body =
								'<html>
									<head>
										<title>' . $mail1->Subject . '</title>
									</head>
									<body>
										<table style="width:600px;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#eee">
											<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
												<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
													<figure style="width:100%;margin:0px;padding:0px;text-align:center;">
														<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/uploads/' . Session::get_value('myvox')['account']['logotype'] . '">
													</figure>
												</td>
											</tr>
											<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
												<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
													<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:18px;font-weight:600;text-align:center;color:#212121;">' . $mail1->Subject . '</h4>
													<h6 style="width:100%;margin:0px;padding:0px;font-size:14px;font-weight:400;text-align:center;color:#757575;">' . Languages::email('token')[$this->lang1] . ': ' . $_POST['token'] . '</h6>
												</td>
											</tr>
											<tr style="width:100%;margin:0px;padding:0px;border:0px;">
												<td style="width:100%;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#fff;">
													<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://' . Configuration::$domain . '">Power by Guestvox</a>
												</td>
											</tr>
										</table>
									</body>
								</html>';
								$mail1->send();
							}
							catch (Exception $e) { }

							if (!empty($_POST['phone_lada']) AND !empty($_POST['phone_number']))
							{
								$sms1 = $this->model->get_sms();

								if ($sms1 > 0)
								{
									$sms1_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
									$sms1_client = new \Nexmo\Client($sms1_basic);
									$sms1_text = Session::get_value('myvox')['account']['name'] . '. ' . Languages::email('thanks_received_request')[$this->lang1] . '. ' . Languages::email('token')[$this->lang1] . ': ' . $_POST['token'] . '. Power by Guestvox.';

									try
									{
										$sms1_client->message()->send([
											'to' => $_POST['phone_lada'] . $_POST['phone_number'],
											'from' => 'Guestvox',
											'text' => $sms1_text
										]);

										$sms1 = $sms1 - 1;
									}
									catch (Exception $e) { }

									$this->model->edit_sms($sms1);
								}
							}

							$_POST['opportunity_area'] = $this->model->get_opportunity_area($_POST['opportunity_area']);
							$_POST['opportunity_type'] = $this->model->get_opportunity_type($_POST['opportunity_type']);
							$_POST['location'] = $this->model->get_location($_POST['location']);
							$_POST['assigned_users'] = $this->model->get_assigned_users($_POST['opportunity_area']['id']);

							$mail2 = new Mailer(true);

							try
							{
								$mail2->setFrom('noreply@guestvox.com', 'Guestvox');

								foreach ($_POST['assigned_users'] as $value)
									$mail2->addAddress($value['email'], $value['firstname'] . ' ' . $value['lastname']);

								$mail2->Subject = Languages::email('new', 'request')[$this->lang2];
								$mail2->Body =
								'<html>
									<head>
										<title>' . $mail2->Subject . '</title>
									</head>
									<body>
										<table style="width:600px;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#eee">
											<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
												<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
													<figure style="width:100%;margin:0px;padding:0px;text-align:center;">
														<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/images/logotype_color.png">
													</figure>
												</td>
											</tr>
											<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
												<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
													<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:18px;font-weight:600;text-align:center;color:#212121;">' . $mail2->Subject . '</h4>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('token')[$this->lang2] . ': ' . $_POST['token'] . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('owner')[$this->lang2] . ': ' . Session::get_value('myvox')['owner']['name'][$this->lang2] . (!empty(Session::get_value('myvox')['owner']['number']) ? ' #' . Session::get_value('myvox')['owner']['number'] : '') . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('opportunity_area')[$this->lang2] . ': ' . $_POST['opportunity_area']['name'][$this->lang2] . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('opportunity_type')[$this->lang2] . ': ' . $_POST['opportunity_type']['name'][$this->lang2] . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('started_date')[$this->lang2] . ': ' . Functions::get_formatted_date($_POST['started_date'], 'd.m.Y') . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('started_hour')[$this->lang2] . ': ' . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('location')[$this->lang2] . ': ' . $_POST['location']['name'][$this->lang2] . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('urgency')[$this->lang2] . ': ' . Languages::email('medium')[$this->lang2] . '</h6>
													<p style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('observations')[$this->lang2] . ': ' . (!empty($_POST['observations']) ? $_POST['observations'] : Languages::email('not_observations')[$this->lang2]) . '</p>
													<a style="width:100%;display:block;margin:0px;padding:20px 0px;border-radius:40px;box-sizing:border-box;background-color:#00a5ab;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;" href="https://' . Configuration::$domain . '/voxes/details/' . $_POST['token'] . '">' . Languages::email('give_follow_up')[$this->lang2] . '</a>
												</td>
											</tr>
											<tr style="width:100%;margin:0px;padding:0px;border:0px;">
												<td style="width:100%;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#fff;">
													<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://' . Configuration::$domain . '">' . Configuration::$domain . '</a>
												</td>
											</tr>
										</table>
									</body>
								</html>';
								$mail2->send();
							}
							catch (Exception $e) { }

							$sms2 = $this->model->get_sms();

							if ($sms2 > 0)
							{
								$sms2_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
								$sms2_client = new \Nexmo\Client($sms2_basic);
								$sms2_text = 'Guestvox. ' . Languages::email('new', 'request')[$this->lang2] . '. ';
								$sms2_text .= Languages::email('token')[$this->lang2] . ': ' . $_POST['token'] . '. ';
								$sms2_text .= Languages::email('owner')[$this->lang2] . ': ' . Session::get_value('myvox')['owner']['name'][$this->lang2] . (!empty(Session::get_value('myvox')['owner']['number']) ? ' #' . Session::get_value('myvox')['owner']['number'] : '') . '. ';
								$sms2_text .= Languages::email('opportunity_area')[$this->lang2] . ': ' . $_POST['opportunity_area']['name'][$this->lang2] . '. ';
								$sms2_text .= Languages::email('opportunity_type')[$this->lang2] . ': ' . $_POST['opportunity_type']['name'][$this->lang2] . '. ';
								$sms2_text .= Languages::email('started_date')[$this->lang2] . ': ' . Functions::get_formatted_date($_POST['started_date'], 'd M y') . '. ';
								$sms2_text .= Languages::email('started_hour')[$this->lang2] . ': ' . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '. ';
								$sms2_text .= Languages::email('location')[$this->lang2] . ': ' . $_POST['location']['name'][$this->lang2] . '. ';
								$sms2_text .= Languages::email('urgency')[$this->lang2] . ': ' . Languages::email('medium')[$this->lang2] . '. ';
								$sms2_text .= Languages::email('observations')[$this->lang2] . ': ' . (!empty($_POST['observations']) ? $_POST['observations'] : Languages::email('not_observations')[$this->lang2]) . '. ';
								$sms2_text .= 'https://' . Configuration::$domain . '/voxes/details/' . $_POST['token'];

								foreach ($_POST['assigned_users'] as $value)
								{
									if ($sms2 > 0)
									{
										try
										{
											$sms2_client->message()->send([
												'to' => $value['phone']['lada'] . $value['phone']['number'],
												'from' => 'Guestvox',
												'text' => $sms2_text
											]);

											$sms2 = $sms2 - 1;
										}
										catch (Exception $e) { }
									}
								}

								$this->model->edit_sms($sms2);
							}

							if (Session::get_value('myvox')['url'] == 'account')
							{
								$myvox = Session::get_value('myvox');

								$myvox['owner'] = null;

								Session::set_value('myvox', $myvox);
							}

							Functions::environment([
								'status' => 'success',
								'message' => '{$lang.thanks_received_request} <strong>' . $_POST['email'] . '</strong> {$lang.thanks_received_vox}',
								'path' => '/' . $params[0] . '/myvox' . ((Session::get_value('myvox')['url'] == 'owner') ? '/owner/' . Session::get_value('myvox')['owner']['token'] : '')
							]);
						}
						else
						{
							Functions::environment([
								'status' => 'error',
								'message' => '{$lang.operation_error}'
							]);
						}
					}
					else
					{
						Functions::environment([
							'status' => 'error',
							'labels' => $labels
						]);
					}
				}
			}
			else
			{
				$template = $this->view->render($this, 'request');

				define('_title', 'Guestvox | {$lang.myvox} | {$lang.request}');

				$html =
				'<form name="new_request">
					<div class="row">';

				if (Session::get_value('myvox')['url'] == 'account')
				{
					$html .=
					'<div class="span12">
						<div class="label">
							<label required>
								<p>{$lang.owner} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
								<select name="owner">
									<option value="" hidden>{$lang.choose}</option>';

					foreach ($this->model->get_owners('request') as $value)
						$html .= '<option value="' . $value['id'] . '" ' . ((!empty(Session::get_value('myvox')['owner']) AND Session::get_value('myvox')['owner']['id'] == $value['id']) ? 'selected' : '') . '>' . $value['name'][$this->lang1] . (!empty($value['number']) ? ' #' . $value['number'] : '') . '</option>';

					$html .=
					'			</select>
							</label>
						</div>
					</div>';
				}

				$html .=
				'<div class="span6">
					<div class="label">
						<label required>
							<p>{$lang.opportunity_area} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<select name="opportunity_area">
								<option value="" hidden>{$lang.choose}</option>';

				foreach ($this->model->get_opportunity_areas('request') as $value)
					$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang1] . '</option>';

				$html .=
				'			</select>
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label required>
							<p>{$lang.opportunity_type} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<select name="opportunity_type" disabled>
								<option value="" hidden>{$lang.choose}</option>
							</select>
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label required>
							<p>{$lang.date} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<input type="date" name="started_date" value="' . Functions::get_current_date('Y-m-d') . '">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label required>
							<p>{$lang.hour} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<input type="time" name="started_hour" value="' . Functions::get_current_hour() . '">
						</label>
					</div>
				</div>
				<div class="span12">
					<div class="label">
						<label required>
							<p>{$lang.location} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<select name="location">
								<option value="" hidden>{$lang.choose}</option>';

				foreach ($this->model->get_locations('request') as $value)
					$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang1] . '</option>';

				$html .=
				'			</select>
						</label>
					</div>
				</div>
				<div class="span12">
					<div class="label">
						<label unrequired>
							<p>{$lang.observations} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<textarea name="observations"></textarea>
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.firstname}</p>
							<input type="text" name="firstname">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.lastname}</p>
							<input type="text" name="lastname">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label required>
							<p>{$lang.email}</p>
							<input type="email" name="email">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label unrequired>
							<p>{$lang.lada}</p>
							<select name="phone_lada">
								<option value="">{$lang.empty} ({$lang.choose})</option>';

				foreach ($this->model->get_countries() as $value)
					$html .= '<option value="' . $value['lada'] . '">' . $value['name'][$this->lang1] . ' (+' . $value['lada'] . ')</option>';

				$html .=
				'					</select>
								</label>
							</div>
						</div>
						<div class="span3">
							<div class="label">
								<label unrequired>
									<p>{$lang.phone}</p>
									<input type="number" name="phone_number">
								</label>
							</div>
						</div>
						<div class="span12">
							<div class="buttons">
								<a href="/' . $params[0] . '/myvox' . ((Session::get_value('myvox')['url'] == 'owner') ? '/owner/' . Session::get_value('myvox')['owner']['token'] : '') . '"><i class="fas fa-times"></i></a>
								<button type="submit"><i class="fas fa-check"></i></button>
							</div>
						</div>
					</div>
				</form>';

				$replace = [
					'{$logotype}' => '{$path.uploads}' . Session::get_value('myvox')['account']['logotype'],
					'{$btn_home}' => '<a href="/' . $params[0] . '/myvox' . ((Session::get_value('myvox')['url'] == 'owner') ? '/owner/' . Session::get_value('myvox')['owner']['token'] : '') . '"><i class="fas fa-home"></i></a>',
					'{$html}' => $html
				];

				$template = $this->format->replace($replace, $template);

				echo $template;
			}
		}
		else
			header('Location: /');
    }

	public function incident($params)
    {
		$break = true;

		if (Session::exists_var('myvox') == true AND !empty(Session::get_value('myvox')['account']))
		{
			if (Session::get_value('myvox')['account']['operation'] == true AND Session::get_value('myvox')['account']['settings']['myvox']['incident']['status'] == true)
			{
				if (!empty(Session::get_value('myvox')['url']))
				{
					if (Session::get_value('myvox')['url'] == 'account')
						$break = false;
					else if (Session::get_value('myvox')['url'] == 'owner' AND !empty(Session::get_value('myvox')['owner']))
						$break = false;
				}
			}
		}

		if ($break == false)
		{
			if (Format::exist_ajax_request() == true)
			{
				if ($_POST['action'] == 'get_owner')
				{
					$owner = $this->model->get_owner($_POST['owner']);

					if (!empty($owner))
					{
						if (Session::get_value('myvox')['account']['type'] == 'hotel')
							$owner['reservation'] = $this->model->get_reservation($owner['number']);

						$myvox = Session::get_value('myvox');

						$myvox['owner'] = $owner;

						Session::set_value('myvox', $myvox);

						Functions::environment([
							'status' => 'success'
						]);
					}
					else
					{
						Functions::environment([
							'status' => 'error',
							'message' => '{$lang.operation_error}'
						]);
					}
				}

				if ($_POST['action'] == 'get_opt_opportunity_types')
				{
					$html = '<option value="" hidden>{$lang.choose}</option>';

					foreach ($this->model->get_opportunity_types($_POST['opportunity_area'], 'incident') as $value)
						$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang1] . '</option>';

					Functions::environment([
						'status' => 'success',
						'html' => $html
					]);
				}

				if ($_POST['action'] == 'new_incident')
				{
					$labels = [];

					if (Session::get_value('myvox')['url'] == 'account')
					{
						if (!isset($_POST['owner']) OR empty($_POST['owner']))
							array_push($labels, ['owner','']);
					}

					if (!isset($_POST['opportunity_area']) OR empty($_POST['opportunity_area']))
						array_push($labels, ['opportunity_area','']);

					if (!isset($_POST['opportunity_type']) OR empty($_POST['opportunity_type']))
						array_push($labels, ['opportunity_type','']);

					if (!isset($_POST['started_date']) OR empty($_POST['started_date']))
						array_push($labels, ['started_date','']);

					if (!isset($_POST['started_hour']) OR empty($_POST['started_hour']))
						array_push($labels, ['started_hour','']);

					if (!isset($_POST['location']) OR empty($_POST['location']))
						array_push($labels, ['location','']);

					if (!empty($_POST['firstname']) OR !empty($_POST['lastname']))
					{
						if (!isset($_POST['firstname']) OR empty($_POST['firstname']))
							array_push($labels, ['firstname','']);

						if (!isset($_POST['lastname']) OR empty($_POST['lastname']))
							array_push($labels, ['lastname','']);
					}

					if (!isset($_POST['email']) OR empty($_POST['email']) OR Functions::check_email($_POST['email']) == false)
						array_push($labels, ['email','']);

					if (!empty($_POST['phone_lada']) OR !empty($_POST['phone_number']))
					{
						if (!isset($_POST['phone_lada']) OR empty($_POST['phone_lada']))
							array_push($labels, ['phone_lada','']);

						if (!isset($_POST['phone_number']) OR empty($_POST['phone_number']))
							array_push($labels, ['phone_number','']);
					}

					if (empty($labels))
					{
						$_POST['type'] = 'incident';
						$_POST['token'] = strtolower(Functions::get_random(8));

						$query = $this->model->new_vox($_POST);

						if (!empty($query))
						{
							$mail1 = new Mailer(true);

							try
							{
								$mail1->setFrom('noreply@guestvox.com', 'Guestvox');
								$mail1->addAddress($_POST['email'], ((!empty($_POST['firstname']) AND !empty($_POST['lastname'])) ? $_POST['firstname'] . ' ' . $_POST['lastname'] : Languages::email('not_name')[$this->lang1]));
								$mail1->Subject = Languages::email('thanks_received_incident')[$this->lang1];
								$mail1->Body =
								'<html>
									<head>
										<title>' . $mail1->Subject . '</title>
									</head>
									<body>
										<table style="width:600px;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#eee">
											<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
												<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
													<figure style="width:100%;margin:0px;padding:0px;text-align:center;">
														<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/uploads/' . Session::get_value('myvox')['account']['logotype'] . '">
													</figure>
												</td>
											</tr>
											<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
												<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
													<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:18px;font-weight:600;text-align:center;color:#212121;">' . $mail1->Subject . '</h4>
													<h6 style="width:100%;margin:0px;padding:0px;font-size:14px;font-weight:400;text-align:center;color:#757575;">' . Languages::email('token')[$this->lang1] . ': ' . $_POST['token'] . '</h6>
												</td>
											</tr>
											<tr style="width:100%;margin:0px;padding:0px;border:0px;">
												<td style="width:100%;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#fff;">
													<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://' . Configuration::$domain . '">Power by Guestvox</a>
												</td>
											</tr>
										</table>
									</body>
								</html>';
								$mail1->send();
							}
							catch (Exception $e) { }

							if (!empty($_POST['phone_lada']) AND !empty($_POST['phone_number']))
							{
								$sms1 = $this->model->get_sms();

								if ($sms1 > 0)
								{
									$sms1_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
									$sms1_client = new \Nexmo\Client($sms1_basic);
									$sms1_text = Session::get_value('myvox')['account']['name'] . '. ' . Languages::email('thanks_received_incident')[$this->lang1] . '. ' . Languages::email('token')[$this->lang1] . ': ' . $_POST['token'] . '. Power by Guestvox.';

									try
									{
										$sms1_client->message()->send([
											'to' => $_POST['phone_lada'] . $_POST['phone_number'],
											'from' => 'Guestvox',
											'text' => $sms1_text
										]);

										$sms1 = $sms1 - 1;
									}
									catch (Exception $e) { }

									$this->model->edit_sms($sms1);
								}
							}

							$_POST['opportunity_area'] = $this->model->get_opportunity_area($_POST['opportunity_area']);
							$_POST['opportunity_type'] = $this->model->get_opportunity_type($_POST['opportunity_type']);
							$_POST['location'] = $this->model->get_location($_POST['location']);
							$_POST['assigned_users'] = $this->model->get_assigned_users($_POST['opportunity_area']['id']);

							$mail2 = new Mailer(true);

							try
							{
								$mail2->setFrom('noreply@guestvox.com', 'Guestvox');

								foreach ($_POST['assigned_users'] as $value)
									$mail2->addAddress($value['email'], $value['firstname'] . ' ' . $value['lastname']);

								$mail2->Subject = Languages::email('new', 'incident')[$this->lang2];
								$mail2->Body =
								'<html>
									<head>
										<title>' . $mail2->Subject . '</title>
									</head>
									<body>
										<table style="width:600px;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#eee">
											<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
												<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
													<figure style="width:100%;margin:0px;padding:0px;text-align:center;">
														<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/images/logotype_color.png">
													</figure>
												</td>
											</tr>
											<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
												<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
													<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:18px;font-weight:600;text-align:center;color:#212121;">' . $mail2->Subject . '</h4>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('token')[$this->lang2] . ': ' . $_POST['token'] . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('owner')[$this->lang2] . ': ' . Session::get_value('myvox')['owner']['name'][$this->lang2] . (!empty(Session::get_value('myvox')['owner']['number']) ? ' #' . Session::get_value('myvox')['owner']['number'] : '') . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('opportunity_area')[$this->lang2] . ': ' . $_POST['opportunity_area']['name'][$this->lang2] . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('opportunity_type')[$this->lang2] . ': ' . $_POST['opportunity_type']['name'][$this->lang2] . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('started_date')[$this->lang2] . ': ' . Functions::get_formatted_date($_POST['started_date'], 'd.m.Y') . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('started_hour')[$this->lang2] . ': ' . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('location')[$this->lang2] . ': ' . $_POST['location']['name'][$this->lang2] . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('urgency')[$this->lang2] . ': ' . Languages::email('medium')[$this->lang2] . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('confidentiality')[$this->lang2] . ': ' . Languages::email('not')[$this->lang2] . '</h6>
													<p style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('description')[$this->lang2] . ': ' . (!empty($_POST['description']) ? $_POST['description'] : Languages::email('not_description')[$this->lang2]) . '</p>
													<a style="width:100%;display:block;margin:0px;padding:20px 0px;border-radius:40px;box-sizing:border-box;background-color:#00a5ab;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;" href="https://' . Configuration::$domain . '/voxes/details/' . $_POST['token'] . '">' . Languages::email('give_follow_up')[$this->lang2] . '</a>
												</td>
											</tr>
											<tr style="width:100%;margin:0px;padding:0px;border:0px;">
												<td style="width:100%;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#fff;">
													<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://' . Configuration::$domain . '">' . Configuration::$domain . '</a>
												</td>
											</tr>
										</table>
									</body>
								</html>';
								$mail2->send();
							}
							catch (Exception $e) { }

							$sms2 = $this->model->get_sms();

							if ($sms2 > 0)
							{
								$sms2_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
								$sms2_client = new \Nexmo\Client($sms2_basic);
								$sms2_text = 'Guestvox. ' . Languages::email('new', 'incident')[$this->lang2] . '. ';
								$sms2_text .= Languages::email('token')[$this->lang2] . ': ' . $_POST['token'] . '. ';
								$sms2_text .= Languages::email('owner')[$this->lang2] . ': ' . Session::get_value('myvox')['owner']['name'][$this->lang2] . (!empty(Session::get_value('myvox')['owner']['number']) ? ' #' . Session::get_value('myvox')['owner']['number'] : '') . '. ';
								$sms2_text .= Languages::email('opportunity_area')[$this->lang2] . ': ' . $_POST['opportunity_area']['name'][$this->lang2] . '. ';
								$sms2_text .= Languages::email('opportunity_type')[$this->lang2] . ': ' . $_POST['opportunity_type']['name'][$this->lang2] . '. ';
								$sms2_text .= Languages::email('started_date')[$this->lang2] . ': ' . Functions::get_formatted_date($_POST['started_date'], 'd M y') . '. ';
								$sms2_text .= Languages::email('started_hour')[$this->lang2] . ': ' . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '. ';
								$sms2_text .= Languages::email('location')[$this->lang2] . ': ' . $_POST['location']['name'][$this->lang2] . '. ';
								$sms2_text .= Languages::email('urgency')[$this->lang2] . ': ' . Languages::email('medium')[$this->lang2] . '. ';
								$sms2_text .= Languages::email('confidentiality')[$this->lang2] . ': ' . Languages::email('not')[$this->lang2] . '. ';
								$sms2_text .= Languages::email('description')[$this->lang2] . ': ' . (!empty($_POST['description']) ? $_POST['description'] : Languages::email('not_description')[$this->lang2]) . '. ';
								$sms2_text .= 'https://' . Configuration::$domain . '/voxes/details/' . $_POST['token'];

								foreach ($_POST['assigned_users'] as $value)
								{
									if ($sms2 > 0)
									{
										try
										{
											$sms2_client->message()->send([
												'to' => $value['phone']['lada'] . $value['phone']['number'],
												'from' => 'Guestvox',
												'text' => $sms2_text
											]);

											$sms2 = $sms2 - 1;
										}
										catch (Exception $e) { }
									}
								}

								$this->model->edit_sms($sms2);
							}

							if (Session::get_value('myvox')['url'] == 'account')
							{
								$myvox = Session::get_value('myvox');

								$myvox['owner'] = null;

								Session::set_value('myvox', $myvox);
							}

							Functions::environment([
								'status' => 'success',
								'message' => '{$lang.thanks_received_incident} <strong>' . $_POST['email'] . '</strong> {$lang.thanks_received_vox}',
								'path' => '/' . $params[0] . '/myvox' . ((Session::get_value('myvox')['url'] == 'owner') ? '/owner/' . Session::get_value('myvox')['owner']['token'] : '')
							]);
						}
						else
						{
							Functions::environment([
								'status' => 'error',
								'message' => '{$lang.operation_error}'
							]);
						}
					}
					else
					{
						Functions::environment([
							'status' => 'error',
							'labels' => $labels
						]);
					}
				}
			}
			else
			{
				$template = $this->view->render($this, 'incident');

				define('_title', 'Guestvox | {$lang.myvox} | {$lang.incident}');

				$html =
				'<form name="new_incident">
					<div class="row">';

				if (Session::get_value('myvox')['url'] == 'account')
				{
					$html .=
					'<div class="span12">
						<div class="label">
							<label required>
								<p>{$lang.owner} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
								<select name="owner">
									<option value="" hidden>{$lang.choose}</option>';

					foreach ($this->model->get_owners('incident') as $value)
						$html .= '<option value="' . $value['id'] . '" ' . ((!empty(Session::get_value('myvox')['owner']) AND Session::get_value('myvox')['owner']['id'] == $value['id']) ? 'selected' : '') . '>' . $value['name'][$this->lang1] . (!empty($value['number']) ? ' #' . $value['number'] : '') . '</option>';

					$html .=
					'			</select>
							</label>
						</div>
					</div>';
				}

				$html .=
				'<div class="span6">
					<div class="label">
						<label required>
							<p>{$lang.opportunity_area} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<select name="opportunity_area">
								<option value="" hidden>{$lang.choose}</option>';

				foreach ($this->model->get_opportunity_areas('incident') as $value)
					$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang1] . '</option>';

				$html .=
				'			</select>
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label required>
							<p>{$lang.opportunity_type} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<select name="opportunity_type" disabled>
								<option value="" hidden>{$lang.choose}</option>
							</select>
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label required>
							<p>{$lang.date} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<input type="date" name="started_date" value="' . Functions::get_current_date('Y-m-d') . '">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label required>
							<p>{$lang.hour} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<input type="time" name="started_hour" value="' . Functions::get_current_hour() . '">
						</label>
					</div>
				</div>
				<div class="span12">
					<div class="label">
						<label required>
							<p>{$lang.location} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<select name="location">
								<option value="" hidden>{$lang.choose}</option>';

				foreach ($this->model->get_locations('incident') as $value)
					$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang1] . '</option>';

				$html .=
				'			</select>
						</label>
					</div>
				</div>
				<div class="span12">
					<div class="label">
						<label unrequired>
							<p>{$lang.description} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<textarea name="description"></textarea>
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.firstname}</p>
							<input type="text" name="firstname">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.lastname}</p>
							<input type="text" name="lastname">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label required>
							<p>{$lang.email}</p>
							<input type="email" name="email">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label unrequired>
							<p>{$lang.lada}</p>
							<select name="phone_lada">
								<option value="">{$lang.empty} ({$lang.choose})</option>';

				foreach ($this->model->get_countries() as $value)
					$html .= '<option value="' . $value['lada'] . '">' . $value['name'][$this->lang1] . ' (+' . $value['lada'] . ')</option>';

				$html .=
				'					</select>
								</label>
							</div>
						</div>
						<div class="span3">
							<div class="label">
								<label unrequired>
									<p>{$lang.phone}</p>
									<input type="number" name="phone_number">
								</label>
							</div>
						</div>
						<div class="span12">
							<div class="buttons">
								<a href="/' . $params[0] . '/myvox' . ((Session::get_value('myvox')['url'] == 'owner') ? '/owner/' . Session::get_value('myvox')['owner']['token'] : '') . '"><i class="fas fa-times"></i></a>
								<button type="submit"><i class="fas fa-check"></i></button>
							</div>
						</div>
					</div>
				</form>';

				$replace = [
					'{$logotype}' => '{$path.uploads}' . Session::get_value('myvox')['account']['logotype'],
					'{$btn_home}' => '<a href="/' . $params[0] . '/myvox' . ((Session::get_value('myvox')['url'] == 'owner') ? '/owner/' . Session::get_value('myvox')['owner']['token'] : '') . '"><i class="fas fa-home"></i></a>',
					'{$html}' => $html
				];

				$template = $this->format->replace($replace, $template);

				echo $template;
			}
		}
		else
			header('Location: /');
    }

    public function menu($params)
    {
		$break = true;

		if (Session::exists_var('myvox') == true AND !empty(Session::get_value('myvox')['account']))
		{
			if (Session::get_value('myvox')['account']['type'] == 'hotel' OR Session::get_value('myvox')['account']['type'] == 'restaurant')
			{
				if (Session::get_value('myvox')['account']['operation'] == true AND Session::get_value('myvox')['account']['settings']['myvox']['menu']['status'] == true)
				{
					if (!empty(Session::get_value('myvox')['url']))
					{
						if (Session::get_value('myvox')['url'] == 'account')
							$break = false;
						else if (Session::get_value('myvox')['url'] == 'owner' AND !empty(Session::get_value('myvox')['owner']))
							$break = false;
					}
				}
			}
		}

		if ($break == false)
		{
			if (!isset(Session::get_value('myvox')['menu_order']) OR empty(Session::get_value('myvox')['menu_order']))
			{
				$myvox = Session::get_value('myvox');

				$myvox['menu_order'] = [
					'total' => 0,
					'shopping_cart' => []
				];

				Session::set_value('myvox', $myvox);
			}

			if (Format::exist_ajax_request() == true)
			{
				if ($_POST['action'] == 'filter_menu_products_by_category')
				{
					if ($_POST['id'] == 'all')
						$query = $this->model->get_menu_products();
					else
						$query = $this->model->get_menu_products('categories', $_POST['id']);

					if (!empty($query))
					{
						$html = '';

						foreach ($query as $value)
						{
							$html .=
							'<div>
								<figure>
									<img src="{$path.uploads}' . $value['avatar'] . '">
								</figure>
								<div>
									<h2>' . $value['name'][$this->lang1] . '</h2>
									<span>' . Functions::get_formatted_currency($value['price'], Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency']) . '</span>
									<div>
										<a data-action="preview_menu_product" data-id="' . $value['id'] . '"><i class="fas fa-info"></i></a>
										<a data-action="remove_to_menu_order" data-id="' . $value['id'] . '"><i class="fas fa-minus"></i></a>
										<span>' . ((array_key_exists($value['id'], Session::get_value('myvox')['menu_order']['shopping_cart'])) ? Session::get_value('myvox')['menu_order']['shopping_cart'][$value['id']]['quantity'] : '0') . '</span>
										<a data-action="add_to_menu_order" data-id="' . $value['id'] . '"><i class="fas fa-plus"></i></a>
									</div>
								</div>
							</div>';
						}

						Functions::environment([
							'status' => 'success',
							'html' => $html
						]);
					}
					else
					{
						Functions::environment([
							'status' => 'error',
							'message' => '{$lang.operation_error}'
						]);
					}
				}

				if ($_POST['action'] == 'preview_menu_product')
				{
					$query = $this->model->get_menu_product($_POST['id']);

					if (!empty($query))
					{
						$query['name'] = $query['name'][$this->lang1];
						$query['description'] = $query['description'][$this->lang1];
						$query['price'] = Functions::get_formatted_currency($query['price'], Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency']);

						Functions::environment([
							'status' => 'success',
							'data' => $query
						]);
					}
					else
					{
						Functions::environment([
							'status' => 'error',
							'message' => '{$lang.operation_error}'
						]);
					}
				}

				if ($_POST['action'] == 'remove_to_menu_order' OR $_POST['action'] == 'add_to_menu_order' OR $_POST['action'] == 'delete_to_menu_order')
				{
					$query = $this->model->get_menu_product($_POST['id']);

					if (!empty($query))
					{
						$menu_order = Session::get_value('myvox')['menu_order'];

						if ($_POST['action'] == 'remove_to_menu_order')
						{
							if (array_key_exists($_POST['id'], $menu_order['shopping_cart']))
							{
								$menu_order['total'] = $menu_order['total'] - $query['price'];

								$menu_order['shopping_cart'][$_POST['id']]['quantity'] = $menu_order['shopping_cart'][$_POST['id']]['quantity'] - 1;
								$menu_order['shopping_cart'][$_POST['id']]['total'] = $menu_order['shopping_cart'][$_POST['id']]['total'] - $query['price'];

								if ($menu_order['shopping_cart'][$_POST['id']]['quantity'] <= 0)
									unset($menu_order['shopping_cart'][$_POST['id']]);
							}
						}
						else if ($_POST['action'] == 'add_to_menu_order')
						{
							$menu_order['total'] = $menu_order['total'] + $query['price'];

							if (array_key_exists($_POST['id'], $menu_order['shopping_cart']))
							{
								$menu_order['shopping_cart'][$_POST['id']]['quantity'] = $menu_order['shopping_cart'][$_POST['id']]['quantity'] + 1;
								$menu_order['shopping_cart'][$_POST['id']]['total'] = $menu_order['shopping_cart'][$_POST['id']]['total'] + $query['price'];
							}
							else
							{
								$menu_order['shopping_cart'][$_POST['id']] = [
									'quantity' => 1,
									'id' => $_POST['id'],
									'name' => $query['name'],
									'price' => $query['price'],
									'total' => $query['price']
								];
							}
						}
						else if ($_POST['action'] == 'delete_to_menu_order')
						{
							if (array_key_exists($_POST['id'], $menu_order['shopping_cart']))
							{
								$menu_order['total'] = $menu_order['total'] - ($query['price'] * $menu_order['shopping_cart'][$_POST['id']]['quantity']);

								unset($menu_order['shopping_cart'][$_POST['id']]);
							}
						}

						$myvox = Session::get_value('myvox');

						$myvox['menu_order'] = $menu_order;

						Session::set_value('myvox', $myvox);

						Functions::environment([
							'status' => 'success',
							'data' => [
								'quantity' => (array_key_exists($_POST['id'], $menu_order['shopping_cart'])) ? $menu_order['shopping_cart'][$_POST['id']]['quantity'] : 0,
								'total' => Functions::get_formatted_currency($menu_order['total'], Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency'])
							]
						]);
					}
					else
					{
						Functions::environment([
							'status' => 'error',
							'message' => '{$lang.operation_error}'
						]);
					}
				}

				if ($_POST['action'] == 'get_owner')
				{
					$owner = $this->model->get_owner($_POST['owner']);

					if (!empty($owner))
					{
						if (Session::get_value('myvox')['account']['type'] == 'hotel')
							$owner['reservation'] = $this->model->get_reservation($owner['number']);

						$myvox = Session::get_value('myvox');

						$myvox['owner'] = $owner;

						Session::set_value('myvox', $myvox);

						Functions::environment([
							'status' => 'success'
						]);
					}
					else
					{
						Functions::environment([
							'status' => 'error',
							'message' => '{$lang.operation_error}'
						]);
					}
				}

				if ($_POST['action'] == 'new_menu_order')
				{
					if (Session::get_value('myvox')['menu_order']['total'] > 0 AND !empty(Session::get_value('myvox')['menu_order']['shopping_cart']))
					{
						$labels = [];

						if (Session::get_value('myvox')['account']['type'] == 'hotel')
						{
							if (Session::get_value('myvox')['url'] == 'account')
							{
								if (!isset($_POST['owner']) OR empty($_POST['owner']))
									array_push($labels, ['owner','']);
							}

							if (!isset($_POST['location']) OR empty($_POST['location']))
								array_push($labels, ['location','']);
						}
						else if (Session::get_value('myvox')['account']['type'] == 'restaurant')
						{
							if (Session::get_value('myvox')['url'] == 'account')
							{
								if (!isset($_POST['type_service']) OR empty($_POST['type_service']))
									array_push($labels, ['type_service','']);

								if ($_POST['type_service'] == 'restaurant')
								{
									if (!isset($_POST['owner']) OR empty($_POST['owner']))
										array_push($labels, ['owner','']);
								}
								else if ($_POST['type_service'] == 'home')
								{
									if (!isset($_POST['address']) OR empty($_POST['address']))
										array_push($labels, ['address','']);
								}
							}
						}

						if (!empty($_POST['firstname']) OR !empty($_POST['lastname']))
						{
							if (!isset($_POST['firstname']) OR empty($_POST['firstname']))
								array_push($labels, ['firstname','']);

							if (!isset($_POST['lastname']) OR empty($_POST['lastname']))
								array_push($labels, ['lastname','']);
						}

						if (!isset($_POST['email']) OR empty($_POST['email']) OR Functions::check_email($_POST['email']) == false)
							array_push($labels, ['email','']);

						if (Session::get_value('myvox')['account']['type'] == 'restaurant' AND Session::get_value('myvox')['url'] == 'account' AND $_POST['type_service'] == 'home')
						{
							if (!isset($_POST['phone_lada']) OR empty($_POST['phone_lada']))
								array_push($labels, ['phone_lada','']);

							if (!isset($_POST['phone_number']) OR empty($_POST['phone_number']))
								array_push($labels, ['phone_number','']);
						}
						else if (!empty($_POST['phone_lada']) OR !empty($_POST['phone_number']))
						{
							if (!isset($_POST['phone_lada']) OR empty($_POST['phone_lada']))
								array_push($labels, ['phone_lada','']);

							if (!isset($_POST['phone_number']) OR empty($_POST['phone_number']))
								array_push($labels, ['phone_number','']);
						}

						if (empty($labels))
						{
							$_POST['token'] = strtolower(Functions::get_random(8));
							$_POST['started_date'] = Functions::get_current_date();
							$_POST['started_hour'] = Functions::get_current_hour();

							$query = $this->model->new_menu_order($_POST);

							if (!empty($query))
							{
								$_POST['type'] = 'request';
								$_POST['menu_order'] = $query;

								$query = $this->model->new_vox($_POST, true);

								if (!empty($query))
								{
									$mail1 = new Mailer(true);

									try
									{
										$mail1->setFrom('noreply@guestvox.com', 'Guestvox');
										$mail1->addAddress($_POST['email'], ((!empty($_POST['firstname']) AND !empty($_POST['lastname'])) ? $_POST['firstname'] . ' ' . $_POST['lastname'] : Languages::email('not_name')[$this->lang1]));
										$mail1->Subject = Languages::email('thanks_received_menu_order')[$this->lang1];
										$mail1->Body =
										'<html>
											<head>
												<title>' . $mail1->Subject . '</title>
											</head>
											<body>
												<table style="width:600px;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#eee">
													<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
														<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
															<figure style="width:100%;margin:0px;padding:0px;text-align:center;">
																<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/uploads/' . Session::get_value('myvox')['account']['logotype'] . '">
															</figure>
														</td>
													</tr>
													<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
														<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
															<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:18px;font-weight:600;text-align:center;color:#212121;">' . $mail1->Subject . '</h4>
															<h6 style="width:100%;margin:0px;padding:0px;font-size:14px;font-weight:400;text-align:center;color:#757575;">' . Languages::email('token')[$this->lang1] . ': ' . $_POST['token'] . '</h6>
														</td>
													</tr>
													<tr style="width:100%;margin:0px;padding:0px;border:0px;">
														<td style="width:100%;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#fff;">
															<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://' . Configuration::$domain . '">Power by Guestvox</a>
														</td>
													</tr>
												</table>
											</body>
										</html>';
										$mail1->send();
									}
									catch (Exception $e) { }

									if (!empty($_POST['phone_lada']) AND !empty($_POST['phone_number']))
									{
										$sms1 = $this->model->get_sms();

										if ($sms1 > 0)
										{
											$sms1_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
											$sms1_client = new \Nexmo\Client($sms1_basic);
											$sms1_text = Session::get_value('myvox')['account']['name'] . '. ' . Languages::email('thanks_received_menu_order')[$this->lang1] . '. ' . Languages::email('token')[$this->lang1] . ': ' . $_POST['token'] . '. Power by Guestvox.';

											try
											{
												$sms1_client->message()->send([
													'to' => $_POST['phone_lada'] . $_POST['phone_number'],
													'from' => 'Guestvox',
													'text' => $sms1_text
												]);

												$sms1 = $sms1 - 1;
											}
											catch (Exception $e) { }

											$this->model->edit_sms($sms1);
										}
									}

									$_POST['opportunity_area'] = $this->model->get_opportunity_area(Session::get_value('myvox')['account']['settings']['myvox']['menu']['opportunity_area']);
									$_POST['opportunity_type'] = $this->model->get_opportunity_type(Session::get_value('myvox')['account']['settings']['myvox']['menu']['opportunity_type']);

									if (Session::get_value('myvox')['account']['type'] == 'hotel')
										$_POST['location'] = $this->model->get_location($_POST['location']);

									$_POST['assigned_users'] = $this->model->get_assigned_users(Session::get_value('myvox')['account']['settings']['myvox']['menu']['opportunity_area']);

									$mail2 = new Mailer(true);

									try
									{
										$mail2->setFrom('noreply@guestvox.com', 'Guestvox');

										foreach ($_POST['assigned_users'] as $value)
											$mail2->addAddress($value['email'], $value['firstname'] . ' ' . $value['lastname']);

										$mail2->Subject = Languages::email('new', 'request')[$this->lang2];
										$mail2->Body =
										'<html>
											<head>
												<title>' . $mail2->Subject . '</title>
											</head>
											<body>
												<table style="width:600px;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#eee">
													<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
														<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
															<figure style="width:100%;margin:0px;padding:0px;text-align:center;">
																<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/images/logotype_color.png">
															</figure>
														</td>
													</tr>
													<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
														<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
															<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:18px;font-weight:600;text-align:center;color:#212121;">' . $mail2->Subject . '</h4>
															<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('token')[$this->lang2] . ': ' . $_POST['token'] . '</h6>
															<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('owner')[$this->lang2] . ': ' . ((Session::get_value('myvox')['account']['type'] == 'hotel' OR (Session::get_value('myvox')['account']['type'] == 'restaurant' AND Session::get_value('myvox')['url'] == 'account' AND $_POST['type_service'] == 'restaurant') OR (Session::get_value('myvox')['account']['type'] == 'restaurant' AND Session::get_value('myvox')['url'] == 'owner')) ? Session::get_value('myvox')['owner']['name'][$this->lang2] . (!empty(Session::get_value('myvox')['owner']['number']) ? ' #' . Session::get_value('myvox')['owner']['number'] : '') : Languages::email('not_owner')[$this->lang2]) . '</h6>
															<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('opportunity_area')[$this->lang2] . ': ' . $_POST['opportunity_area']['name'][$this->lang2] . '</h6>
															<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('opportunity_type')[$this->lang2] . ': ' . $_POST['opportunity_type']['name'][$this->lang2] . '</h6>
															<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('started_date')[$this->lang2] . ': ' . Functions::get_formatted_date($_POST['started_date'], 'd.m.Y') . '</h6>
															<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('started_hour')[$this->lang2] . ': ' . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '</h6>
															<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('location')[$this->lang2] . ': ' . ((Session::get_value('myvox')['account']['type'] == 'hotel') ? $_POST['location']['name'][$this->lang2] : ((Session::get_value('myvox')['account']['type'] == 'restaurant' AND Session::get_value('myvox')['url'] == 'account' AND $_POST['type_service'] == 'home') ? $_POST['address'] : Languages::email('not_location')[$this->lang2])) . '</h6>
															<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('urgency')[$this->lang2] . ': ' . Languages::email('medium')[$this->lang2] . '</h6>
															<p style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('observations')[$this->lang2] . ': ' . Languages::email('not_observations')[$this->lang2] . '</p>';

										foreach (Session::get_value('myvox')['menu_order']['shopping_cart'] as $value)
											$mail2->Body .= '<p style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">(' . $value['quantity'] . ') ' . $value['name'][$this->lang2] . '</p>';

										$mail2->Body .=
										'					<a style="width:100%;display:block;margin:0px;padding:20px 0px;border-radius:40px;box-sizing:border-box;background-color:#00a5ab;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;" href="https://' . Configuration::$domain . '/voxes/details/' . $_POST['token'] . '">' . Languages::email('give_follow_up')[$this->lang2] . '</a>
														</td>
													</tr>
													<tr style="width:100%;margin:0px;padding:0px;border:0px;">
														<td style="width:100%;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#fff;">
															<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://' . Configuration::$domain . '">' . Configuration::$domain . '</a>
														</td>
													</tr>
												</table>
											</body>
										</html>';
										$mail2->send();
									}
									catch (Exception $e) { }

									$sms2 = $this->model->get_sms();

									if ($sms2 > 0)
									{
										$sms2_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
										$sms2_client = new \Nexmo\Client($sms2_basic);
										$sms2_text = 'Guestvox. ' . Languages::email('new', 'request')[$this->lang2] . '. ';
										$sms2_text .= Languages::email('token')[$this->lang2] . ': ' . $_POST['token'] . '. ';
										$sms2_text .= Languages::email('owner')[$this->lang2] . ': ' . ((Session::get_value('myvox')['account']['type'] == 'hotel' OR (Session::get_value('myvox')['account']['type'] == 'restaurant' AND Session::get_value('myvox')['url'] == 'account' AND $_POST['type_service'] == 'restaurant') OR (Session::get_value('myvox')['account']['type'] == 'restaurant' AND Session::get_value('myvox')['url'] == 'owner')) ? Session::get_value('myvox')['owner']['name'][$this->lang2] . (!empty(Session::get_value('myvox')['owner']['number']) ? ' #' . Session::get_value('myvox')['owner']['number'] : '') : Languages::email('not_owner')[$this->lang2]) . '. ';
										$sms2_text .= Languages::email('opportunity_area')[$this->lang2] . ': ' . $_POST['opportunity_area']['name'][$this->lang2] . '. ';
										$sms2_text .= Languages::email('opportunity_type')[$this->lang2] . ': ' . $_POST['opportunity_type']['name'][$this->lang2] . '. ';
										$sms2_text .= Languages::email('started_date')[$this->lang2] . ': ' . Functions::get_formatted_date($_POST['started_date'], 'd M y') . '. ';
										$sms2_text .= Languages::email('started_hour')[$this->lang2] . ': ' . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '. ';
										$sms2_text .= Languages::email('location')[$this->lang2] . ': ' . ((Session::get_value('myvox')['account']['type'] == 'hotel') ? $_POST['location']['name'][$this->lang2] : ((Session::get_value('myvox')['account']['type'] == 'restaurant' AND Session::get_value('myvox')['url'] == 'account' AND $_POST['type_service'] == 'home') ? $_POST['address'] : Languages::email('not_location')[$this->lang2])) . '. ';
										$sms2_text .= Languages::email('urgency')[$this->lang2] . ': ' . Languages::email('medium')[$this->lang2] . '. ';
										$sms2_text .= Languages::email('observations')[$this->lang2] . ': ' . Languages::email('not_observations')[$this->lang2] . '. ';

										foreach (Session::get_value('myvox')['menu_order']['shopping_cart'] as $value)
											$sms2_text .= '(' . $value['quantity'] . ') ' . $value['name'][$this->lang2] . '. ';

										$sms2_text .= 'https://' . Configuration::$domain . '/voxes/details/' . $_POST['token'];

										foreach ($_POST['assigned_users'] as $value)
										{
											if ($sms2 > 0)
											{
												try
												{
													$sms2_client->message()->send([
														'to' => $value['phone']['lada'] . $value['phone']['number'],
														'from' => 'Guestvox',
														'text' => $sms2_text
													]);

													$sms2 = $sms2 - 1;
												}
												catch (Exception $e) { }
											}
										}

										$this->model->edit_sms($sms2);
									}

									$myvox = Session::get_value('myvox');

									$myvox['menu_order'] = null;

									Session::set_value('myvox', $myvox);

									if (Session::get_value('myvox')['url'] == 'account')
									{
										$myvox = Session::get_value('myvox');

										$myvox['owner'] = null;

										Session::set_value('myvox', $myvox);
									}

									// 	$sender = 'saulantonio219@gmail.com';
									// 	$senderName = 'Saul Poot';
									//
									// 	$recipient = 'abrahamaldair16@gmail.com';
									// 	$usernameSmtp = 'AKIAVAEOB4AEJOVGS2CZ';
									// 	$passwordSmtp = 'BCvzm+cs2jbnrMGmPZYRyrtXZHcOUVGsFrYb4NBO4FQR';
									// 	$configurationSet = 'demo-gv';
									// 	$host = 'email-smtp.us-east-1.amazonaws.com';
									// 	$port = 587;
									//
									// 	$subject = 'Amazon SES test (SMTP interface accessed using PHP)';
									//
									// 	$bodyText =  "Email Test\r\nThis email was sent through the
									// 	    Amazon SES SMTP interface using the PHPMailer class.";
									//
									// 	$bodyHtml = '<h1>Email Test</h1>
									// 	    <p>This email was sent through the
									// 	    <a href="https://aws.amazon.com/ses">Amazon SES</a> SMTP
									// 	    interface using the <a href="https://github.com/PHPMailer/PHPMailer">
									// 	    PHPMailer</a> class.</p>';
									//
									// 	$mail = new Mailer(true);
									//
									// 	try {
									//     $mail->isSMTP();
									//     $mail->setFrom($sender, $senderName);
									//     $mail->Username   = $usernameSmtp;
									//     $mail->Password   = $passwordSmtp;
									//     $mail->Host       = $host;
									//     $mail->Port       = $port;
									//     $mail->SMTPAuth   = true;
									//     $mail->SMTPSecure = 'tls';
									//     $mail->addCustomHeader('X-SES-CONFIGURATION-SET', $configurationSet);
									//
									//     $mail->addAddress($recipient);
									//
									//     $mail->isHTML(true);
									//     $mail->Subject    = $subject;
									//     $mail->Body       = $bodyHtml;
									//     $mail->AltBody    = $bodyText;
									//     $mail->Send();
									//     echo "Email sent!" , PHP_EOL;
									// } catch (phpmailerException $e) {
									//     echo "An error occurred. {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
									// } catch (Exception $e) {
									//     echo "Email not sent. {$mail->ErrorInfo}", PHP_EOL; //Catch errors from Amazon SES.
									// }

									Functions::environment([
										'status' => 'success',
										'message' => '{$lang.thanks_received_menu_order} <strong>' . $_POST['email'] . '</strong> {$lang.thanks_received_vox}',
										'path' => '/' . $params[0] . '/menu/products'
									]);
								}
								else
								{
									Functions::environment([
										'status' => 'error',
										'message' => '{$lang.operation_error}'
									]);
								}
							}
							else
							{
								Functions::environment([
									'status' => 'error',
									'message' => '{$lang.operation_error}'
								]);
							}
						}
						else
						{
							Functions::environment([
								'status' => 'error',
								'labels' => $labels
							]);
						}
					}
					else
					{
						Functions::environment([
							'status' => 'error',
							'message' => '{$lang.shopping_cart_empty}'
						]);
					}
				}
			}
			else
			{
				$template = $this->view->render($this, 'menu');

				define('_title', Session::get_value('myvox')['account']['name'] . ' | {$lang.menu}');

				$html = '';

				if ($params[1] == 'products')
				{
					$html .=
					'<section data-menu-categories>
						<div>
							<a data-action="filter_menu_products_by_category" data-id="all"><i class="fas fa-ellipsis-v"></i></a>
							<span>{$lang.all}</span>
						</div>';

					foreach ($this->model->get_menu_categories() as $value)
					{
						$html .=
						'<div>
							<a data-action="filter_menu_products_by_category" data-id="' . $value['id'] . '">' . $value['icon'] . '</a>
							<span>' . $value['name'][$this->lang1] . '</span>
						</div>';
					}

					$html .=
				    '</section>
					<section data-menu-products>';

					foreach ($this->model->get_menu_products() as $value)
					{
						$html .=
						'<div>
							<figure>
								<img src="{$path.uploads}' . $value['avatar'] . '">
							</figure>
							<div>
								<h2>' . $value['name'][$this->lang1] . '</h2>
								<span>' . Functions::get_formatted_currency($value['price'], Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency']) . '</span>
								<div>
									<a data-action="preview_menu_product" data-id="' . $value['id'] . '"><i class="fas fa-info"></i></a>
									<a data-action="remove_to_menu_order" data-id="' . $value['id'] . '"><i class="fas fa-minus"></i></a>
									<span>' . ((array_key_exists($value['id'], Session::get_value('myvox')['menu_order']['shopping_cart'])) ? Session::get_value('myvox')['menu_order']['shopping_cart'][$value['id']]['quantity'] : '0') . '</span>
									<a data-action="add_to_menu_order" data-id="' . $value['id'] . '"><i class="fas fa-plus"></i></a>
								</div>
							</div>
						</div>';
					}

					$html .=
					'</section>
					<section class="modal fullscreen" data-modal="preview_menu_product">
					    <div class="content">
					        <main>
								<figure>
									<img src="">
								</figure>
								<h2></h2>
								<span></span>
								<p></p>
								<div class="buttons">
									<a button-close><i class="fas fa-check"></i></a>
								</div>
					        </main>
					    </div>
					</section>';
				}
				else if ($params[1] == 'order')
				{
					$html .=
					'<section data-total>
						<span>' . Functions::get_formatted_currency(Session::get_value('myvox')['menu_order']['total'], Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency']) . '</span>
					</section>
					<section data-shopping-cart>';

					foreach (Session::get_value('myvox')['menu_order']['shopping_cart'] as $value)
					{
						$html .=
						'<div>
							<h2>' . $value['name'][$this->lang1] . '</h2>
							<span>' . Functions::get_formatted_currency($value['price'], Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency']) . '</span>
							<div>
								<a data-action="remove_to_menu_order" data-id="' . $value['id'] . '"><i class="fas fa-minus"></i></a>
								<span>' . $value['quantity'] . '</span>
								<a data-action="add_to_menu_order" data-id="' . $value['id'] . '"><i class="fas fa-plus"></i></a>
								<a data-action="delete_to_menu_order" data-id="' . $value['id'] . '"><i class="fas fa-times"></i></a>
							</div>
						</div>';
					}

					$html .=
					'<form name="new_menu_order">
						<div class="row">';

					if (Session::get_value('myvox')['account']['type'] == 'hotel')
					{
						if (Session::get_value('myvox')['url'] == 'account')
						{
							$html .=
							'<div class="span12">
								<div class="label">
									<label required>
										<p>{$lang.owner} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
										<select name="owner">
											<option value="" hidden>{$lang.choose}</option>';

							foreach ($this->model->get_owners('request') as $value)
								$html .= '<option value="' . $value['id'] . '" ' . ((!empty(Session::get_value('myvox')['owner']) AND Session::get_value('myvox')['owner']['id'] == $value['id']) ? 'selected' : '') . '>' . $value['name'][$this->lang1] . (!empty($value['number']) ? ' #' . $value['number'] : '') . '</option>';

							$html .=
							'			</select>
									</label>
								</div>
							</div>';
						}

						$html .=
						'<div class="span12">
							<div class="label">
								<label required>
									<p>{$lang.location} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
									<select name="location">
										<option value="" hidden>{$lang.choose}</option>';

						foreach ($this->model->get_locations('request') as $value)
							$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang1] . '</option>';

						$html .=
						'			</select>
								</label>
							</div>
						</div>';
					}
					else if (Session::get_value('myvox')['account']['type'] == 'restaurant')
					{
						if (Session::get_value('myvox')['url'] == 'account')
						{
							$html .=
							'<div class="span12">
								<div class="checkboxes stl_1">
									<div>
										<input type="radio" name="type_service" value="restaurant" checked>
										<span><strong>{$lang.restaurant_service}</strong></span>
									</div>
									<div>
										<input type="radio" name="type_service" value="home">
										<span><strong>{$lang.home_service}</strong></span>
									</div>
								</div>
							</div>
							<div class="span12">
								<div class="label">
									<label required>
										<p>{$lang.owner} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
										<select name="owner">
											<option value="" hidden>{$lang.choose}</option>';

							foreach ($this->model->get_owners('request') as $value)
								$html .= '<option value="' . $value['id'] . '" ' . ((!empty(Session::get_value('myvox')['owner']) AND Session::get_value('myvox')['owner']['id'] == $value['id']) ? 'selected' : '') . '>' . $value['name'][$this->lang1] . (!empty($value['number']) ? ' #' . $value['number'] : '') . '</option>';

							$html .=
							'			</select>
									</label>
								</div>
							</div>
							<div class="span12 hidden">
								<div class="label">
									<label required>
										<p>{$lang.address} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
										<input type="text" name="address">
									</label>
								</div>
							</div>';
						}
					}

					$html .=
					'<div class="span6">
						<div class="label">
							<label unrequired>
								<p>{$lang.firstname}</p>
								<input type="text" name="firstname">
							</label>
						</div>
					</div>
					<div class="span6">
						<div class="label">
							<label unrequired>
								<p>{$lang.lastname}</p>
								<input type="text" name="lastname">
							</label>
						</div>
					</div>
					<div class="span6">
						<div class="label">
							<label required>
								<p>{$lang.email}</p>
								<input type="email" name="email">
							</label>
						</div>
					</div>
					<div class="span3">
						<div class="label">
							<label unrequired>
								<p>{$lang.lada}</p>
								<select name="phone_lada">
									<option value="">{$lang.empty} ({$lang.choose})</option>';

					foreach ($this->model->get_countries() as $value)
						$html .= '<option value="' . $value['lada'] . '">' . $value['name'][$this->lang1] . ' (+' . $value['lada'] . ')</option>';

					$html .=
					'						</select>
										</label>
									</div>
								</div>
								<div class="span3">
									<div class="label">
										<label unrequired>
											<p>{$lang.phone}</p>
											<input type="number" name="phone_number">
										</label>
									</div>
								</div>
								<div class="span12">
			                        <div class="buttons">
										<button type="submit">{$lang.generate_order}</button>
			                        </div>
			                    </div>
							</div>
						</form>
					</section>';
				}

				$html .=
				'<section class="buttons">
					<a href="/' . $params[0] . '/menu/products" ' . (($params[1] == 'products') ? 'class="active"' : '') . '><i class="fas fa-list-ul"></i><span>{$lang.products}</span></a>
					<a href="/' . $params[0] . '/menu/order" ' . (($params[1] == 'order') ? 'class="active"' : '') . '  data-total><i class="fas fa-shopping-cart"></i><span>' . Functions::get_formatted_currency(Session::get_value('myvox')['menu_order']['total'], Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency']) . '</span></a>
				</section>';

				$replace = [
					'{$logotype}' => '{$path.uploads}' . Session::get_value('myvox')['account']['logotype'],
					'{$btn_home}' => '<a href="/' . $params[0] . '/myvox' . ((Session::get_value('myvox')['url'] == 'owner') ? '/owner/' . Session::get_value('myvox')['owner']['token'] : '') . '"><i class="fas fa-home"></i></a>',
					'{$html}' => $html
				];

				$template = $this->format->replace($replace, $template);

				echo $template;
			}
		}
		else
			header('Location: /');
    }

    public function survey($params)
    {
		$break = true;

		if (Session::exists_var('myvox') == true AND !empty(Session::get_value('myvox')['account']))
		{
			if (Session::get_value('myvox')['account']['reputation'] == true AND Session::get_value('myvox')['account']['settings']['myvox']['survey']['status'] == true)
			{
				if (!empty(Session::get_value('myvox')['url']))
				{
					if (Session::get_value('myvox')['url'] == 'account')
						$break = false;
					else if (Session::get_value('myvox')['url'] == 'owner' AND !empty(Session::get_value('myvox')['owner']))
						$break = false;
				}
			}
		}

		if ($break == false)
		{
			if (Format::exist_ajax_request() == true)
			{
				if ($_POST['action'] == 'get_owner')
				{
					$owner = $this->model->get_owner($_POST['owner']);

					if (!empty($owner))
					{
						if (Session::get_value('myvox')['account']['type'] == 'hotel')
							$owner['reservation'] = $this->model->get_reservation($owner['number']);

						$myvox = Session::get_value('myvox');

						$myvox['owner'] = $owner;

						Session::set_value('myvox', $myvox);

						Functions::environment([
							'status' => 'success'
						]);
					}
					else
					{
						Functions::environment([
							'status' => 'error',
							'message' => '{$lang.operation_error}'
						]);
					}
				}

				if ($_POST['action'] == 'new_survey_answer')
				{
					$labels = [];

					if (Session::get_value('myvox')['url'] == 'account')
					{
						if (!isset($_POST['owner']) OR empty($_POST['owner']))
							array_push($labels, ['owner','']);
					}

					if (!empty($_POST['firstname']) OR !empty($_POST['lastname']))
					{
						if (!isset($_POST['firstname']) OR empty($_POST['firstname']))
							array_push($labels, ['firstname','']);

						if (!isset($_POST['lastname']) OR empty($_POST['lastname']))
							array_push($labels, ['lastname','']);
					}

					if (!isset($_POST['email']) OR empty($_POST['email']) OR Functions::check_email($_POST['email']) == false)
						array_push($labels, ['email','']);

					if (!empty($_POST['phone_lada']) OR !empty($_POST['phone_number']))
					{
						if (!isset($_POST['phone_lada']) OR empty($_POST['phone_lada']))
							array_push($labels, ['phone_lada','']);

						if (!isset($_POST['phone_number']) OR empty($_POST['phone_number']))
							array_push($labels, ['phone_number','']);
					}

					if (empty($labels))
					{
						$_POST['token'] = strtolower(Functions::get_random(8));

						$query = $this->model->new_survey_answer($_POST);

						if (!empty($query))
						{
							$mail = new Mailer(true);

							try
							{
								$mail->setFrom('noreply@guestvox.com', 'Guestvox');
								$mail->addAddress($_POST['email'], ((!empty($_POST['firstname']) AND !empty($_POST['lastname'])) ? $_POST['firstname'] . ' ' . $_POST['lastname'] : Languages::email('not_name')[$this->lang1]));
								$mail->Subject = Session::get_value('myvox')['account']['settings']['myvox']['survey']['mail']['subject'][$this->lang1];
								$mail->Body =
								'<html>
									<head>
										<title>' . $mail->Subject . '</title>
									</head>
									<body>
										<table style="width:600px;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#eee">
											<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
												<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
													<figure style="width:100%;margin:0px;padding:0px;text-align:center;">
														<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/uploads/' . Session::get_value('myvox')['account']['logotype'] . '">
													</figure>
												</td>
											</tr>
											<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
												<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
													<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:18px;font-weight:600;text-align:center;color:#212121;">' . $mail->Subject . '</h4>
													<h6 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:14px;font-weight:400;text-align:center;color:#757575;">' . Languages::email('token')[$this->lang1] . ': ' . $_POST['token'] . '</h6>
													<p style="width:100%;margin:0px;padding:0px;font-size:14px;font-weight:400;text-align:center;color:#757575;">' . Session::get_value('myvox')['account']['settings']['myvox']['survey']['mail']['description'][$this->lang1] . '</p>';

								if (!empty(Session::get_value('myvox')['account']['settings']['myvox']['survey']['mail']['image']))
								{
									$mail->Body .=
									'<figure style="width:100%;margin:20px 0px 0px 0px;padding:0px;">
										<img style="width:100%;" src="https://' . Configuration::$domain . '/uploads/' . Session::get_value('myvox')['account']['settings']['myvox']['survey']['mail']['image'] . '">
									</figure>';
								}

								if (!empty(Session::get_value('myvox')['account']['settings']['myvox']['survey']['mail']['attachment']))
									$mail->Body .= '<a style="width:100%;display:block;margin:20px 0px 0px 0px;padding:20px 0px;border-radius:40px;box-sizing:border-box;background-color:#00a5ab;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;" href="https://' . Configuration::$domain . '/uploads/' . Session::get_value('myvox')['account']['settings']['myvox']['survey']['mail']['attachment'] . '" download="https://' . Configuration::$domain . '/uploads/' . Session::get_value('myvox')['account']['settings']['myvox']['survey']['mail']['attachment'] . '">' . Languages::email('download_file')[$this->lang1] . '</a>';

								$mail->Body .=
								'				</td>
											</tr>
											<tr style="width:100%;margin:0px;padding:0px;border:0px;">
												<td style="width:100%;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#fff;">
													<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://' . Configuration::$domain . '">Power by Guestvox</a>
												</td>
											</tr>
										</table>
									</body>
								</html>';
								$mail->send();
							}
							catch (Exception $e) { }

							if (!empty($_POST['phone_lada']) AND !empty($_POST['phone_number']))
							{
								$sms = $this->model->get_sms();

								if ($sms > 0)
								{
									$sms_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
									$sms_client = new \Nexmo\Client($sms_basic);
									$sms_text = Session::get_value('myvox')['account']['name'] . '. ' . Session::get_value('myvox')['account']['settings']['myvox']['survey']['mail']['subject'][$this->lang1] . '. ' . Languages::email('token')[$this->lang1] . ': ' . $_POST['token'] . '. ' . Session::get_value('myvox')['account']['settings']['myvox']['survey']['mail']['description'][$this->lang1] . '. Power by Guestvox.';

									try
									{
										$sms_client->message()->send([
											'to' => $_POST['phone_lada'] . $_POST['phone_number'],
											'from' => 'Guestvox',
											'text' => $sms_text
										]);

										$sms = $sms - 1;
									}
									catch (Exception $e) { }

									$this->model->edit_sms($sms);
								}
							}

							$widget = false;

							if (!empty(Session::get_value('myvox')['account']['settings']['myvox']['survey']['widget']))
							{
								$average = $this->model->get_survey_average($query);

								if ($average >= 4)
									$widget = true;
							}

							Functions::environment([
								'status' => 'success',
								'message' => '{$lang.thanks_answering_survey_1} <strong>' . $_POST['email'] . ' </strong> {$lang.thanks_answering_survey_2}',
								'widget' => $widget
							]);
						}
						else
						{
							Functions::environment([
								'status' => 'error',
								'message' => '{$lang.operation_error}'
							]);
						}
					}
					else
					{
						Functions::environment([
							'status' => 'error',
							'labels' => $labels
						]);
					}
				}
			}
			else
			{
				$template = $this->view->render($this, 'survey');

				define('_title', 'Guestvox | {$lang.myvox} | {$lang.survey}');

				$this->model->get_survey_average(1);

				$html =
				'<form name="new_survey_answer">
					<div class="row">';

				if (Session::get_value('myvox')['url'] == 'account')
				{
					$html .=
					'<div class="span12">
						<div class="label">
							<label required>
								<p>{$lang.owner} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
								<select name="owner">
									<option value="" hidden>{$lang.choose}</option>';

					foreach ($this->model->get_owners('survey') as $value)
						$html .= '<option value="' . $value['id'] . '" ' . ((!empty(Session::get_value('myvox')['owner']) AND Session::get_value('myvox')['owner']['id'] == $value['id']) ? 'selected' : '') . '>' . $value['name'][$this->lang1] . (!empty($value['number']) ? ' #' . $value['number'] : '') . '</option>';

					$html .=
					'			</select>
							</label>
						</div>
					</div>';
				}

				$html .=
				'<div class="span12">
					<div class="tbl_stl_5" data-table>';

				foreach ($this->model->get_surveys_questions() as $value)
				{
					$html .=
					'<div>
						<div data-level="1">
							<h2>' . $value['name'][$this->lang1] . '</h2>
							<div class="' . $value['type'] . '">';

					if ($value['type'] == 'nps')
					{
						$html .=
						'<div>
							<label><i>1</i><input type="radio" name="' . $value['id'] . '" value="1"></label>
							<label><i>2</i><input type="radio" name="' . $value['id'] . '" value="2"></label>
							<label><i>3</i><input type="radio" name="' . $value['id'] . '" value="3"></label>
							<label><i>4</i><input type="radio" name="' . $value['id'] . '" value="4"></label>
							<label><i>5</i><input type="radio" name="' . $value['id'] . '" value="5"></label>
						</div>
					   	<div>
							<label><i>6</i><input type="radio" name="' . $value['id'] . '" value="6"></label>
							<label><i>7</i><input type="radio" name="' . $value['id'] . '" value="7"></label>
							<label><i>8</i><input type="radio" name="' . $value['id'] . '" value="8"></label>
							<label><i>9</i><input type="radio" name="' . $value['id'] . '" value="9"></label>
							<label><i>10</i><input type="radio" name="' . $value['id'] . '" value="10"></label>
						</div>';
					}
					else if ($value['type'] == 'open')
						$html .= '<input type="text" name="' . $value['id'] . '">';
					else if ($value['type'] == 'rate')
					{
						$html .=
						'<label><i class="fas fa-sad-cry"></i><input type="radio" name="' . $value['id'] . '" value="1"></label>
						<label><i class="fas fa-frown"></i><input type="radio" name="' . $value['id'] . '" value="2"></label>
						<label><i class="fas fa-meh-rolling-eyes"></i><input type="radio" name="' . $value['id'] . '" value="3"></label>
						<label><i class="fas fa-smile"></i><input type="radio" name="' . $value['id'] . '" value="4"></label>
						<label><i class="fas fa-grin-stars"></i><input type="radio" name="' . $value['id'] . '" value="5"></label>';
					}
					else if ($value['type'] == 'twin')
					{
						$html .=
						'<label><i class="fas fa-thumbs-down"></i><input type="radio" name="' . $value['id'] . '" value="not"></label>
						<label><i class="fas fa-thumbs-up"></i><input type="radio" name="' . $value['id'] . '" value="yes"></label>';
					}
					else if ($value['type'] == 'check')
					{
						$html .= '<div class="checkboxes stl_3">';

						foreach ($value['values'] as $subvalue)
						{
							$html .=
							'<div>
								<input type="checkbox" name="' . $value['id'] . '[]" value="' . $subvalue['token'] . '">
								<span>' . $subvalue[$this->lang1] . '</span>
							</div>';
						}

						$html .= '</div>';
					}

					$html .=
					'	</div>
					</div>';

					foreach ($this->model->get_surveys_questions($value['id']) as $subvalue)
					{
						$html .=
						'<div data-level="2">
							<h2>' . $subvalue['name'][$this->lang1] . '</h2>
							<div class="' . $subvalue['type'] . '">';

						if ($subvalue['type'] == 'open')
							$html .= '<input type="text" name="' . $subvalue['id'] . '" data-parent="' . $value['id'] . '">';
						else if ($subvalue['type'] == 'rate')
						{
							$html .=
							'<label><i class="fas fa-sad-cry"></i><input type="radio" name="' . $subvalue['id'] . '" value="1" data-parent="' . $value['id'] . '"></label>
							<label><i class="fas fa-frown"></i><input type="radio" name="' . $subvalue['id'] . '" value="2" data-parent="' . $value['id'] . '"></label>
							<label><i class="fas fa-meh-rolling-eyes"></i><input type="radio" name="' . $subvalue['id'] . '" value="3" data-parent="' . $value['id'] . '"></label>
							<label><i class="fas fa-smile"></i><input type="radio" name="' . $subvalue['id'] . '" value="4" data-parent="' . $value['id'] . '"></label>
							<label><i class="fas fa-grin-stars"></i><input type="radio" name="' . $subvalue['id'] . '" value="5" data-parent="' . $value['id'] . '"></label>';
						}
						else if ($subvalue['type'] == 'twin')
						{
							$html .=
							'<label><i class="fas fa-thumbs-up"></i><input type="radio" name="' . $subvalue['id'] . '" value="yes" data-parent="' . $value['id'] . '"></label>
							<label><i class="fas fa-thumbs-down"></i><input type="radio" name="' . $subvalue['id'] . '" value="not" data-parent="' . $value['id'] . '"></label>';
						}
						else if ($subvalue['type'] == 'check')
						{
							$html .= '<div class="checkboxes stl_3">';

							foreach ($subvalue['values'] as $parentvalue)
							{
								$html .=
								'<div>
									<input type="checkbox" name="' . $subvalue['id'] . '[]" value="' . $parentvalue['token'] . '" data-parent="' . $value['id'] . '">
									<span>' . $parentvalue[$this->lang1] . '</span>
								</div>';
							}

							$html .= '</div>';
						}

						$html .=
						'	</div>
						</div>';

						foreach ($this->model->get_surveys_questions($subvalue['id']) as $parentvalue)
						{
							$html .=
							'<div data-level="3">
								<h2>' . $parentvalue['name'][$this->lang1] . '</h2>
								<div class="' . $parentvalue['type'] . '">';

							if ($parentvalue['type'] == 'open')
								$html .= '<input type="text" name="' . $parentvalue['id'] . '" data-parent="' . $subvalue['id'] . '">';
							else if ($parentvalue['type'] == 'rate')
							{
								$html .=
								'<label><i class="fas fa-sad-cry"></i><input type="radio" name="' . $parentvalue['id'] . '" value="1" data-parent="' . $subvalue['id'] . '"></label>
								<label><i class="fas fa-frown"></i><input type="radio" name="' . $parentvalue['id'] . '" value="2" data-parent="' . $subvalue['id'] . '"></label>
								<label><i class="fas fa-meh-rolling-eyes"></i><input type="radio" name="' . $parentvalue['id'] . '" value="3" data-parent="' . $subvalue['id'] . '"></label>
								<label><i class="fas fa-smile"></i><input type="radio" name="' . $parentvalue['id'] . '" value="4" data-parent="' . $subvalue['id'] . '"></label>
								<label><i class="fas fa-grin-stars"></i><input type="radio" name="' . $parentvalue['id'] . '" value="5" data-parent="' . $subvalue['id'] . '"></label>';
							}
							else if ($parentvalue['type'] == 'twin')
							{
								$html .=
								'<label><i class="fas fa-thumbs-up"></i><input type="radio" name="' . $parentvalue['id'] . '" value="yes" data-parent="' . $subvalue['id'] . '"></label>
								<label><i class="fas fa-thumbs-down"></i><input type="radio" name="' . $parentvalue['id'] . '" value="not" data-parent="' . $subvalue['id'] . '"></label>';
							}
							else if ($parentvalue['type'] == 'check')
							{
								$html .= '<div class="checkboxes stl_3">';

								foreach ($parentvalue['values'] as $childvalue)
								{
									$html .=
									'<div>
										<input type="checkbox" name="' . $parentvalue['id'] . '[]" value="' . $childvalue['token'] . '" data-parent="' . $subvalue['id'] . '">
										<span>' . $childvalue[$this->lang1] . '</span>
									</div>';
								}

								$html .= '</div>';
							}

							$html .=
							'	</div>
							</div>';
						}
					}

					$html .= '</div>';
				}

				$html .=
				'	</div>
				</div>
				<div class="span12">
					<div class="label">
						<label unrequired>
							<p>{$lang.comment}</p>
							<textarea name="comment"></textarea>
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.firstname}</p>
							<input type="text" name="firstname">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.lastname}</p>
							<input type="text" name="lastname">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label required>
							<p>{$lang.email}</p>
							<input type="email" name="email">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label unrequired>
							<p>{$lang.lada}</p>
							<select name="phone_lada">
								<option value="">{$lang.empty} ({$lang.choose})</option>';

				foreach ($this->model->get_countries() as $value)
					$html .= '<option value="' . $value['lada'] . '">' . $value['name'][$this->lang1] . ' (+' . $value['lada'] . ')</option>';

				$html .=
				'					</select>
								</label>
							</div>
						</div>
						<div class="span3">
							<div class="label">
								<label unrequired>
									<p>{$lang.phone}</p>
									<input type="number" name="phone_number">
								</label>
							</div>
						</div>
						<div class="span12">
							<div class="buttons">
								<a href="/' . $params[0] . '/myvox' . ((Session::get_value('myvox')['url'] == 'owner') ? '/owner/' . Session::get_value('myvox')['owner']['token'] : '') . '"><i class="fas fa-times"></i></a>
								<button type="submit"><i class="fas fa-check"></i></button>
							</div>
						</div>
					</div>
				</form>';

				$mdl_widget = '';

				if (!empty(Session::get_value('myvox')['account']['settings']['myvox']['survey']['widget']))
				{
					$mdl_widget .=
					'<section class="modal" data-modal="widget">
						<div class="content">
							<main>
								<div>' . Session::get_value('myvox')['account']['settings']['myvox']['survey']['widget'] . '</div>
								<div class="buttons">
									<a button-close><i class="fas fa-times"></i></a>
								</div>
							</main>
						</div>
					</section>';
				}

				$replace = [
					'{$logotype}' => '{$path.uploads}' . Session::get_value('myvox')['account']['logotype'],
					'{$btn_home}' => '<a href="/' . $params[0] . '/myvox' . ((Session::get_value('myvox')['url'] == 'owner') ? '/owner/' . Session::get_value('myvox')['owner']['token'] : '') . '"><i class="fas fa-home"></i></a>',
					'{$html}' => $html,
					'{$mdl_widget}' => $mdl_widget
				];

				$template = $this->format->replace($replace, $template);

				echo $template;
			}
		}
		else
			header('Location: /');
    }
}
