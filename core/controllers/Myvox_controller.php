<?php

defined('_EXEC') or die;

class Myvox_controller extends Controller
{
	public function __construct()
	{
		parent::__construct();
	}

    public function index($params)
    {
        $room = $this->model->get_room($params[0]);

        if (!empty($room))
        {
            $account = $this->model->get_account($room['account']);

            if (!empty($account))
			{
				if (Format::exist_ajax_request() == true)
	            {
	                if ($_POST['action'] == 'get_opt_opportunity_types')
	                {
	                    $data = '<option value="" selected hidden>{$lang.choose}</option>';

	                    foreach ($this->model->get_opportunity_types($_POST['opportunity_area'], $_POST['option']) as $value)
	                        $data .= '<option value="' . $value['id'] . '">' . $value['name'][Session::get_value('lang')] . '</option>';

	                    Functions::environment([
	                        'status' => 'success',
	                        'data' => $data
	                    ]);
	                }

	                if ($_POST['action'] == 'new_request')
	                {
	                    $labels = [];

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

	                    if (!empty($_POST['observations']) AND strlen($_POST['observations']) > 120)
	                        array_push($labels, ['observations','']);

	                    if (!isset($_POST['lastname']) OR empty($_POST['lastname']))
	                        array_push($labels, ['lastname','']);

	                    if (empty($labels))
	                    {
	                        $query = $this->model->new_request($_POST, $room['id'], $account['id']);

	                        if (!empty($query))
	                        {
	                            $_POST['assigned_users'] = $this->model->get_assigned_users($_POST['opportunity_area'], $account['id']);
	                            $_POST['opportunity_area'] = $this->model->get_opportunity_area($_POST['opportunity_area']);
	                            $_POST['opportunity_type'] = $this->model->get_opportunity_type($_POST['opportunity_type']);
	                            $_POST['location'] = $this->model->get_location($_POST['location']);

	                            $mail = new Mailer(true);

	                            try
	                            {
	                                if ($account['language'] == 'es')
	                                {
	                                    $mail_subject = 'Tienes una nueva petición en GuestVox';
	                                    $mail_room = 'Habitación: ';
	                                    $mail_opportunity_area = 'Área de oportunidad: ';
	                                    $mail_opportunity_type = 'Tipo de oportunidad: ';
	                                    $mail_started_date = 'Fecha de inicio: ';
	                                    $mail_started_hour = 'Hora de inicio: ';
	                                    $mail_location = 'Ubicación: ';

	                                    if (Functions::get_current_date_hour() < Functions::get_formatted_date_hour($_POST['started_date'], $_POST['started_hour']))
	                                        $mail_urgency = 'Urgencia: Programada';
	                                    else
	                                        $mail_urgency = 'Urgencia: Media';

	                                    $mail_observations = 'Observaciones: ';
	                                    $mail_give_follow_up = 'Dar seguimiento';
	                                }
	                                else if ($account['language'] == 'en')
	                                {
	                                    $mail_subject = 'You have a new request in GuestVox';
	                                    $mail_room = 'Room: ';
	                                    $mail_opportunity_area = 'Opportunity area: ';
	                                    $mail_opportunity_type = 'Opportunity type: ';
	                                    $mail_started_date = 'Start date: ';
	                                    $mail_started_hour = 'Start hour: ';
	                                    $mail_location = 'Location: ';

	                                    if (Functions::get_current_date_hour() < Functions::get_formatted_date_hour($_POST['started_date'], $_POST['started_hour']))
	                                        $mail_urgency = 'Urgency: Programmed';
	                                    else
	                                        $mail_urgency = 'Urgency: Medium';

	                                    $mail_observations = 'Observations: ';
	                                    $mail_give_follow_up = 'Give follow up';
	                                }

	                                $mail->isSMTP();
	                                $mail->setFrom('noreply@guestvox.com', 'GuestVox');

	                                foreach ($_POST['assigned_users'] as $value)
	                                    $mail->addAddress($value['email'], $value['firstname'] . ' ' . $value['lastname']);

	                                $mail->isHTML(true);
	                                $mail->Subject = $mail_subject;
	                                $mail->Body =
	                                '<html>
	                                    <head>
	                                        <title>' . $mail_subject . '</title>
	                                    </head>
	                                    <body>
	                                        <table style="width:600px;margin:0px;border:0px;padding:20px;box-sizing:border-box;background-color:#eee">
	                                            <tr style="width:100%;margin:0px:margin-bottom:10px;border:0px;padding:0px;">
	                                                <td style="width:100%;margin:0px;border:0px;padding:40px 20px;box-sizing:border-box;background-color:#fff;">
	                                                    <figure style="width:100%;margin:0px;padding:0px;text-align:center;">
	                                                        <img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/images/logotype-color.png" />
	                                                    </figure>
	                                                </td>
	                                            </tr>
	                                            <tr style="width:100%;margin:0px;margin-bottom:10px;border:0px;padding:0px;">
	                                                <td style="width:100%;margin:0px;border:0px;padding:40px 20px;box-sizing:border-box;background-color:#fff;">
	                                                    <h4 style="font-size:24px;font-weight:600;text-align:center;color:#212121;margin:0px;margin-bottom:20px;padding:0px;">' . $mail_subject . '</h4>
	                                                    <h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_room . $room['name'] . '</h6>
	                                                    <h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_opportunity_area . $_POST['opportunity_area']['name'][$account['language']] . '</h6>
	                                                    <h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_opportunity_type . $_POST['opportunity_type']['name'][$account['language']] . '</h6>
	                                                    <h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_started_date . Functions::get_formatted_date($_POST['started_date'], 'd M, Y') . '</h6>
	                                                    <h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_started_hour . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '</h6>
	                                                    <h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_location . $_POST['location']['name'][$account['language']] . '</h6>
	                                                    <h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_urgency . '</h6>
	                                                    <p style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;padding:0px;">' . $mail_observations . $_POST['observations'] . '</p>
	                                                    <a style="width:100%;display:block;margin:15px 0px 20px 0px;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;background-color:#201d33;" href="https://' . Configuration::$domain . '/voxes/view/' . $query . '">' . $mail_give_follow_up . '</a>
	                                                </td>
	                                            </tr>
	                                            <tr style="width:100%;margin:0px;border:0px;padding:0px;">
	                                                <td style="width:100%;margin:0px;border:0px;padding:20px;box-sizing:border-box;background-color:#fff;">
	                                                    <a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#201d33;" href="https://' . Configuration::$domain . '">' . Configuration::$domain . '</a>
	                                                </td>
	                                            </tr>
	                                        </table>
	                                    </body>
	                                </html>';
	                                $mail->AltBody = '';
	                                $mail->send();
	                            }
	                            catch (Exception $e) { }

	                            Functions::environment([
	                                'status' => 'success',
	                                'message' => '{$lang.thanks_trus_us_vox_send_correctly}',
	                                'path' => '/myvox/' . $room['token']
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

	                if ($_POST['action'] == 'new_incident')
	                {
	                    $labels = [];

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

	                    if (!isset($_POST['description']) OR empty($_POST['description']))
	                        array_push($labels, ['description','']);

	                    if (!isset($_POST['lastname']) OR empty($_POST['lastname']))
	                        array_push($labels, ['lastname','']);

	                    if (empty($labels))
	                    {
	                        $query = $this->model->new_incident($_POST, $room['id'], $account['id']);

	                        if (!empty($query))
	                        {
	                            $_POST['assigned_users'] = $this->model->get_assigned_users($_POST['opportunity_area'], $account['id']);
	                            $_POST['opportunity_area'] = $this->model->get_opportunity_area($_POST['opportunity_area']);
	                            $_POST['opportunity_type'] = $this->model->get_opportunity_type($_POST['opportunity_type']);
	                            $_POST['location'] = $this->model->get_location($_POST['location']);

	                            $mail = new Mailer(true);

	                            try
	                            {
	                                if ($account['language'] == 'es')
	                                {
	                                    $mail_subject = 'Tienes una nueva incidencia en GuestVox';
	                                    $mail_room = 'Habitación: ';
	                                    $mail_opportunity_area = 'Área de oportunidad: ';
	                                    $mail_opportunity_type = 'Tipo de oportunidad: ';
	                                    $mail_started_date = 'Fecha de inicio: ';
	                                    $mail_started_hour = 'Hora de inicio: ';
	                                    $mail_location = 'Ubicación: ';

	                                    if (Functions::get_current_date_hour() < Functions::get_formatted_date_hour($_POST['started_date'], $_POST['started_hour']))
	                                        $mail_urgency = 'Urgencia: Programada';
	                                    else
	                                        $mail_urgency = 'Urgencia: Media';

	                                    $mail_confidentiality = 'Confidencialidad: No';
	                                    $mail_description = 'Descripción: ';
	                                    $mail_give_follow_up = 'Dar seguimiento';
	                                }
	                                else if ($account['language'] == 'en')
	                                {
	                                    $mail_subject = 'You have a new incident in GuestVox';
	                                    $mail_room = 'Room: ';
	                                    $mail_opportunity_area = 'Opportunity area: ';
	                                    $mail_opportunity_type = 'Opportunity type: ';
	                                    $mail_started_date = 'Start date: ';
	                                    $mail_started_hour = 'Start hour: ';
	                                    $mail_location = 'Location: ';

	                                    if (Functions::get_current_date_hour() < Functions::get_formatted_date_hour($_POST['started_date'], $_POST['started_hour']))
	                                        $mail_urgency = 'Urgency: Programmed';
	                                    else
	                                        $mail_urgency = 'Urgency: Medium';

	                                    $mail_confidentiality = 'Confidentiality: No';
	                                    $mail_description = 'Description: ';
	                                    $mail_give_follow_up = 'Give follow up';
	                                }

	                                $mail->isSMTP();
	                                $mail->setFrom('noreply@guestvox.com', 'GuestVox');

	                                foreach ($_POST['assigned_users'] as $value)
	                                    $mail->addAddress($value['email'], $value['firstname'] . ' ' . $value['lastname']);

	                                $mail->isHTML(true);
	                                $mail->Subject = $mail_subject;
	                                $mail->Body =
	                                '<html>
	                                    <head>
	                                        <title>' . $mail_subject . '</title>
	                                    </head>
	                                    <body>
	                                        <table style="width:600px;margin:0px;border:0px;padding:20px;box-sizing:border-box;background-color:#eee">
	                                            <tr style="width:100%;margin:0px:margin-bottom:10px;border:0px;padding:0px;">
	                                                <td style="width:100%;margin:0px;border:0px;padding:40px 20px;box-sizing:border-box;background-color:#fff;">
	                                                    <figure style="width:100%;margin:0px;padding:0px;text-align:center;">
	                                                        <img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/images/logotype-color.png" />
	                                                    </figure>
	                                                </td>
	                                            </tr>
	                                            <tr style="width:100%;margin:0px;margin-bottom:10px;border:0px;padding:0px;">
	                                                <td style="width:100%;margin:0px;border:0px;padding:40px 20px;box-sizing:border-box;background-color:#fff;">
	                                                    <h4 style="font-size:24px;font-weight:600;text-align:center;color:#212121;margin:0px;margin-bottom:20px;padding:0px;">' . $mail_subject . '</h4>
	                                                    <h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_room . $room['name'] . '</h6>
	                                                    <h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_opportunity_area . $_POST['opportunity_area']['name'][$account['language']] . '</h6>
	                                                    <h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_opportunity_type . $_POST['opportunity_type']['name'][$account['language']] . '</h6>
	                                                    <h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_started_date . Functions::get_formatted_date($_POST['started_date'], 'd M, Y') . '</h6>
	                                                    <h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_started_hour . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '</h6>
	                                                    <h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_location . $_POST['location']['name'][$account['language']] . '</h6>
	                                                    <h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_urgency . '</h6>
	                                                    <h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_confidentiality . '</h6>
	                                                    <p style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;padding:0px;">' . $mail_description . $_POST['description'] . '</p>
	                                                    <a style="width:100%;display:block;margin:15px 0px 20px 0px;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;background-color:#201d33;" href="https://' . Configuration::$domain . '/voxes/view/' . $query . '">' . $mail_give_follow_up . '</a>
	                                                </td>
	                                            </tr>
	                                            <tr style="width:100%;margin:0px;border:0px;padding:0px;">
	                                                <td style="width:100%;margin:0px;border:0px;padding:20px;box-sizing:border-box;background-color:#fff;">
	                                                    <a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#201d33;" href="https://' . Configuration::$domain . '">' . Configuration::$domain . '</a>
	                                                </td>
	                                            </tr>
	                                        </table>
	                                    </body>
	                                </html>';
	                                $mail->AltBody = '';
	                                $mail->send();
	                            }
	                            catch (Exception $e) { }

	                            Functions::environment([
	                                'status' => 'success',
	                                'message' => '{$lang.thanks_trus_us_vox_send_correctly}',
	                                'path' => '/myvox/' . $room['token']
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

	                if ($_POST['action'] == 'new_survey_answer')
	                {
						$labels = [];

						if (!empty($_POST['email']))
						{
							if (!isset($_POST['firstname']) OR empty($_POST['firstname']))
								array_push($labels, ['firstname','']);
						}
						if (!empty($_POST['email']))
						{
							if (!empty($_POST['email']) AND !isset($_POST['lastname']) OR empty($_POST['lastname']))
								array_push($labels, ['lastname','']);
						}

	                    if (empty($labels))
	                    {
	                        $_POST['answers'] = $_POST;

							unset($_POST['answers']['comment']);
							unset($_POST['answers']['firstname']);
							unset($_POST['answers']['lastname']);
							unset($_POST['answers']['email']);
							unset($_POST['answers']['action']);

							foreach ($_POST['answers'] as $key => $value)
		                    {
								$explode = explode('-', $key);

								if ($explode[0] == 'pr' OR $explode[0] == 'pt' OR $explode[0] == 'po')
								{
									if ($explode[0] == 'pr')
										$explode[0] = 'rate';
									else if ($explode[0] == 'pt')
										$explode[0] = 'twin';
									else if ($explode[0] == 'po')
										$explode[0] = 'open';

									$_POST['answers'][$explode[1]] = [
										'id' => $explode[1],
										'answer' => $value,
										'type' => $explode[0],
										'subanswers' => [],
									];

									unset($_POST['answers'][$key]);
								}
								else if ($explode[0] == 'sr' OR $explode[0] == 'st' OR $explode[0] == 'so')
								{
									if (!empty($value))
									{
										if ($explode[0] == 'sr')
											$explode[0] = 'rate';
										else if ($explode[0] == 'st')
											$explode[0] = 'twin';
										else if ($explode[0] == 'so')
											$explode[0] = 'open';

										array_push($_POST['answers'][$explode[1]]['subanswers'], [
											'id' => $explode[2],
											'type' => $explode[0],
											'answer' => $value
										]);
									}

									unset($_POST['answers'][$key]);
								}
		                    }

							sort($_POST['answers']);

							$_POST['date'] = Functions::get_current_date();
							$_POST['token'] = Functions::get_random(6);

							$query = $this->model->new_survey_answer($_POST, $room['id'], $account['id']);

							if (!empty($query))
		                    {
								if (!empty($_POST['email']))
								{
									$mail = new Mailer(true);

		                            try
		                            {
		                                if (Session::get_value('lang') == 'es')
		                                    $mail_subject = 'Gracias por contestar nuestra encuesta';
		                                else if (Session::get_value('lang') == 'en')
		                                    $mail_subject = 'Thanks for answers our surver';

		                                $mail->isSMTP();
		                                $mail->setFrom('noreply@guestvox.com', 'GuestVox');
										$mail->addAddress($_POST['email'], $_POST['firstname'] . ' ' . $_POST['lastname']);
		                                $mail->isHTML(true);
		                                $mail->Subject = $mail_subject;
		                                $mail->Body =
		                                '<html>
		                                    <head>
		                                        <title>' . $mail_subject . '</title>
		                                    </head>
		                                    <body>
		                                        <table style="width:600px;margin:0px;border:0px;padding:20px;box-sizing:border-box;background-color:#eee">
		                                            <tr style="width:100%;margin:0px:margin-bottom:10px;border:0px;padding:0px;">
		                                                <td style="width:100%;margin:0px;border:0px;padding:40px 20px;box-sizing:border-box;background-color:#fff;">
		                                                    <figure style="width:100%;margin:0px;padding:0px;text-align:center;">
		                                                        <img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/uploads/' . $account['logotype'] . '" />
		                                                    </figure>
		                                                </td>
		                                            </tr>
		                                            <tr style="width:100%;margin:0px;margin-bottom:10px;border:0px;padding:0px;">
		                                                <td style="width:100%;margin:0px;border:0px;padding:40px 20px;box-sizing:border-box;background-color:#fff;">
		                                                    <h4 style="font-size:24px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:20px;padding:0px;">' . $mail_subject . '</h4>
		                                                    <h6 style="font-size:24px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:20px;padding:0px;">' . $_POST['date'] . '</h6>
		                                                    <h6 style="font-size:40px;font-weight:600;text-align:center;color:#212121;margin:0px;padding:0px;">' . $_POST['token'] . '</h6>
		                                                </td>
		                                            </tr>
		                                            <tr style="width:100%;margin:0px;border:0px;padding:0px;">
		                                                <td style="width:100%;margin:0px;border:0px;padding:20px;box-sizing:border-box;background-color:#fff;">
		                                                    <a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#201d33;" href="https://' . Configuration::$domain . '">Powered by GuestVox</a>
		                                                </td>
		                                            </tr>
		                                        </table>
		                                    </body>
		                                </html>';
		                                $mail->AltBody = '';
		                                $mail->send();
		                            }
		                            catch (Exception $e) { }
								}

		                        Functions::environment([
		                            'status' => 'success',
		                            'message' => '{$lang.thanks_for_answering_our_survey}',
		                            'path' => '/myvox/' . $room['token']
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
	                define('_title', 'GuestVox - ' . $account['name']);

	                $template = $this->view->render($this, 'index');

					$weather = '';

					if ($account['city'] == 'Cancún')
					{
						$weather .=
						'<div id="cont_cd807792a632233c89079d9ce52455fd">
							<script type="text/javascript" async src="https://www.meteored.mx/wid_loader/cd807792a632233c89079d9ce52455fd"></script>
						</div>';
					}
					else if ($account['city'] == 'Playa el Carmen')
					{
						$weather .=
						'<div id="cont_1aa2ac92f7520eecb5e4c9c9af87d4cf">
							<script type="text/javascript" async src="https://www.meteored.mx/wid_loader/1aa2ac92f7520eecb5e4c9c9af87d4cf"></script>
						</div>';
					}
					else if ($account['city'] == 'Ciudad de México')
					{
						$weather .=
						'<div id="cont_14b51bac9dd36d8c525f0cb7c94d00e0">
				         	<script type="text/javascript" async src="https://www.meteored.mx/wid_loader/14b51bac9dd36d8c525f0cb7c94d00e0"></script>
				        </div>';
					}

					$a_new_request = '';
					$mdl_new_request = '';

					if ($account['myvox_request'] == true)
					{
						$a_new_request .= '<a data-button-modal="new_request">{$lang.make_a_request}</a>';

						$mdl_new_request .=
						'<section class="modal" data-modal="new_request">
						    <div class="content">
						        <header>
						            <h3>{$lang.make_a_request}</h3>
						        </header>
						        <main>
						            <form name="new_request">
						                <div class="row">
						                    <div class="span12">
						                        <div class="label">
						                            <label important>
						                                <p>{$lang.opportunity_area}</p>
						                                <select name="opportunity_area" data-type="request">
						                                    <option value="" selected hidden>{$lang.choose}</option>';

						foreach ($this->model->get_opportunity_areas('request', $account['id']) as $value)
							$mdl_new_request .= '<option value="' . $value['id'] . '">' . $value['name'][Session::get_value('lang')] . '</option>';

						$mdl_new_request .=
						'                                </select>
						                            </label>
						                        </div>
						                    </div>
						                    <div class="span12">
						                        <div class="label">
						                            <label important>
						                                <p>{$lang.opportunity_type}</p>
						                                <select name="opportunity_type" disabled>
						                                    <option value="" selected hidden>{$lang.choose}</option>
						                                </select>
						                            </label>
						                        </div>
						                    </div>
						                    <div class="span6">
						                        <div class="label">
						                            <label>
						                                <p>{$lang.date}</p>
						                                <input type="date" name="started_date" value="' . Functions::get_current_date('Y-m-d') . '">
						                                <p class="description">{$lang.what_date_need}</p>
						                            </label>
						                        </div>
						                    </div>
						                    <div class="span6">
						                        <div class="label">
						                            <label>
						                                <p>{$lang.hour}</p>
						                                <input type="time" name="started_hour" value="' . Functions::get_current_hour() . '">
						                                <p class="description">{$lang.what_hour_need}</p>
						                            </label>
						                        </div>
						                    </div>
						                    <div class="span12">
						                        <div class="label">
						                            <label important>
						                                <p>{$lang.location}</p>
						                                <select name="location">
						                                    <option value="" selected hidden>{$lang.choose}</option>';

						foreach ($this->model->get_locations('request', $account['id']) as $value)
							$mdl_new_request .= '<option value="' . $value['id'] . '">' . $value['name'][Session::get_value('lang')] . '</option>';

						$mdl_new_request .=
						'                                </select>
						                                <p class="description">{$lang.when_need}</p>
						                            </label>
						                        </div>
						                    </div>
						                    <div class="span12">
						                        <div class="label">
						                            <label>
						                                <p>{$lang.observations}</p>
						                                <input type="text" name="observations" maxlength="120" />
						                                <p class="description">{$lang.max_120_characters}</p>
						                            </label>
						                        </div>
						                    </div>
						                    <div class="span12">
						                        <div class="label">
						                            <label important>
						                                <p>{$lang.lastname}</p>
						                                <input type="text" name="lastname" />
						                            </label>
						                        </div>
						                    </div>
						                </div>
						            </form>
						        </main>
						        <footer>
						            <div class="action-buttons">
						                <button class="btn btn-flat" button-cancel>{$lang.cancel}</button>
						                <button class="btn" button-success>{$lang.accept}</button>
						            </div>
						        </footer>
						    </div>
						</section>';
					}

					$a_new_incident = '';
					$mdl_new_incident = '';

					if ($account['myvox_incident'] == true)
					{
						$a_new_incident .= '<a data-button-modal="new_incident">{$lang.i_want_to_leave_a_comment_complaint}</a>';

						$mdl_new_incident .=
						'<section class="modal" data-modal="new_incident">
							<div class="content">
								<header>
									<h3>{$lang.i_want_to_leave_a_comment_complaint}</h3>
								</header>
								<main>
									<form name="new_incident">
										<div class="row">
											<div class="span12">
												<div class="label">
													<label important>
														<p>{$lang.opportunity_area}</p>
														<select name="opportunity_area" data-type="incident">
															<option value="" selected hidden>{$lang.choose}</option>';

						foreach ($this->model->get_opportunity_areas('incident', $account['id']) as $value)
						   $mdl_new_incident .= '<option value="' . $value['id'] . '">' . $value['name'][Session::get_value('lang')] . '</option>';

						$mdl_new_incident .=
						'                                </select>
													</label>
												</div>
											</div>
											<div class="span12">
												<div class="label">
													<label important>
														<p>{$lang.opportunity_type}</p>
														<select name="opportunity_type" disabled>
															<option value="" selected hidden>{$lang.choose}</option>
														</select>
													</label>
												</div>
											</div>
											<div class="span6">
												<div class="label">
													<label>
														<p>{$lang.date}</p>
														<input type="date" name="started_date" value="' . Functions::get_current_date('Y-m-d') . '">
														<p class="description">{$lang.what_date_happened}</p>
													</label>
												</div>
											</div>
											<div class="span6">
												<div class="label">
													<label>
														<p>{$lang.hour}</p>
														<input type="time" name="started_hour" value="' . Functions::get_current_hour() . '">
														<p class="description">{$lang.what_hour_happened}</p>
													</label>
												</div>
											</div>
											<div class="span12">
												<div class="label">
													<label important>
														<p>{$lang.location}</p>
														<select name="location">
															<option value="" selected hidden>{$lang.choose}</option>';

						foreach ($this->model->get_locations('incident', $account['id']) as $value)
						   $mdl_new_incident .= '<option value="' . $value['id'] . '">' . $value['name'][Session::get_value('lang')] . '</option>';

						$mdl_new_incident .=
						'								</select>
														<p class="description">{$lang.when_happened}</p>
													</label>
												</div>
											</div>
											<div class="span12">
												<div class="label">
													<label important>
														<p>{$lang.description}</p>
														<textarea name="description"></textarea>
														<p class="description">{$lang.what_happened} {$lang.dont_forget_mention_relevant_details}</p>
													</label>
												</div>
											</div>
											<div class="span12">
												<div class="label">
													<label important>
														<p>{$lang.lastname}</p>
														<input type="text" name="lastname" />
													</label>
												</div>
											</div>
										</div>
									</form>
								</main>
								<footer>
									<div class="action-buttons">
										<button class="btn btn-flat" button-cancel>{$lang.cancel}</button>
										<button class="btn" button-success>{$lang.accept}</button>
									</div>
								</footer>
							</div>
						</section>';
					}

					$a_new_survey_answer = '';
					$mdl_new_survey_answer = '';

					if ($account['myvox_survey'] == true)
					{
						$a_new_survey_answer .= '<a data-button-modal="new_survey_answer" class="survey">' . $account['myvox_survey_title'][Session::get_value('lang')] . '<img src="{$path.images}gift.png" alt="Gift icon"></a>';

						$mdl_new_survey_answer .=
						'<section class="modal" data-modal="new_survey_answer">
						    <div class="content">
						        <header>
						            <h3>{$lang.answer_survey_right_now}</h3>
						        </header>
						        <main>
						            <form name="new_survey_answer">';

						foreach ($this->model->get_survey_questions($account['id']) as $value)
					   	{
						   	$mdl_new_survey_answer .=
						   	'<article>
							   <h6>' . $value['name'][Session::get_value('lang')] . '</h6>';

							if ($value['type'] == 'rate')
							{
								$mdl_new_survey_answer .=
								'<div>
								   <label>{$lang.appalling}</label>
								   <label><input type="radio" name="pr-' . $value['id'] . '" value="1" data-action="open_subquestion"></label>
								   <label><input type="radio" name="pr-' . $value['id'] . '" value="2" data-action="open_subquestion"></label>
								   <label><input type="radio" name="pr-' . $value['id'] . '" value="3" data-action="open_subquestion"></label>
								   <label><input type="radio" name="pr-' . $value['id'] . '" value="4" data-action="open_subquestion"></label>
								   <label><input type="radio" name="pr-' . $value['id'] . '" value="5" data-action="open_subquestion"></label>
								   <label>{$lang.excellent}</label>
								</div>';
							}
							else if ($value['type'] == 'twin')
							{
								$mdl_new_survey_answer .=
								'<div>
								   <label>{$lang.to_yes}</label>
								   <label><input type="radio" name="pt-' . $value['id'] . '" value="yes" data-action="open_subquestion"></label>
								   <label><input type="radio" name="pt-' . $value['id'] . '" value="no" data-action="open_subquestion"></label>
								   <label>{$lang.to_not}</label>
								</div>';
							}
							else if ($value['type'] == 'open')
							{
								$mdl_new_survey_answer .=
								'<div>
								   <input type="text" name="po-' . $value['id'] . '">
								</div>';
							}

							$mdl_new_survey_answer .=
							'</article>';

							if (!empty($value['subquestions']))
							{
								if ($value['type'] == 'rate')
								{
								   $mdl_new_survey_answer .=
								   '<article id="pr-' . $value['id'] . '" class="subquestions hidden">';
								}
								else if ($value['type'] == 'twin')
								{
								   $mdl_new_survey_answer .=
								   '<article id="pt-' . $value['id'] . '" class="subquestions hidden">';
								}

								foreach ($value['subquestions'] as $key => $subvalue)
								{
								   $mdl_new_survey_answer .=
								   '<h6>' . $subvalue['name'][Session::get_value('lang')] . '</h6>';

								   if ($subvalue['type'] == 'rate')
								   {
									   $mdl_new_survey_answer .=
									   '<div>
										   <label>{$lang.appalling}</label>
										   <label><input type="radio" name="sr-' . $value['id'] . '-' . $subvalue['id'] . '" value="1"></label>
										   <label><input type="radio" name="sr-' . $value['id'] . '-' . $subvalue['id'] . '" value="2"></label>
										   <label><input type="radio" name="sr-' . $value['id'] . '-' . $subvalue['id'] . '" value="3"></label>
										   <label><input type="radio" name="sr-' . $value['id'] . '-' . $subvalue['id'] . '" value="4"></label>
										   <label><input type="radio" name="sr-' . $value['id'] . '-' . $subvalue['id'] . '" value="5"></label>
										   <label>{$lang.excellent}</label>
									   </div>';
								   }
								   else if ($subvalue['type'] == 'twin')
								   {
									   $mdl_new_survey_answer .=
									   '<div>
										   <label>{$lang.to_yes}</label>
										   <label><input type="radio" name="st-' . $value['id'] . '-' . $subvalue['id'] . '" value="yes"></label>
										   <label><input type="radio" name="st-' . $value['id'] . '-' . $subvalue['id'] . '" value="no"></label>
										   <label>{$lang.to_not}</label>
									  </div>';
								   }
								   else if ($subvalue['type'] == 'open')
								   {
									   $mdl_new_survey_answer .=
									   '<div>
										   <input type="text" name="so-' . $value['id'] . '-' . $subvalue['id'] . '">
									   </div>';
								   }
								}

								$mdl_new_survey_answer .=
								'</article>';
							}
					   }

						$mdl_new_survey_answer .=
						'                <div class="row">
						                    <div class="span12">
						                        <div class="label">
						                            <label>
						                                <p>{$lang.comments}</p>
						                                <textarea name="comment"></textarea>
						                            </label>
						                        </div>
						                    </div>
						                    <div class="span12">
						                        <div class="label">
						                            <label important>
						                                <p>{$lang.firstname}</p>
						                                <input type="text" name="firstname">
						                            </label>
						                        </div>
						                    </div>
						                    <div class="span12">
						                        <div class="label">
						                            <label important>
						                                <p>{$lang.lastname}</p>
						                                <input type="text" name="lastname">
						                            </label>
						                        </div>
						                    </div>
						                    <div class="span12">
						                        <div class="label">
						                            <label important>
						                                <p>{$lang.email}</p>
						                                <input type="email" name="email">
						                            </label>
						                        </div>
						                    </div>
						                </div>
						            </form>
						        </main>
						        <footer>
						            <div class="action-buttons">
						                <button class="btn btn-flat" button-cancel>{$lang.cancel}</button>
						                <button class="btn" button-success>{$lang.accept}</button>
						            </div>
						        </footer>
						    </div>
						</section>
						<section class="modal" data-modal="tripadvisor">
						    <div class="content">
						        <header>
						            <h3>TripAdvisor</h3>
						        </header>
						        <main>
						            <div id="TA_cdswritereviewlg4" class="TA_cdswritereviewlg" style="width:100%;">
						                <ul id="I7hJOKLd" class="TA_links FJfwMuzhhq">
						                    <li id="SnaJrr" class="KBMX9k">
						                        <a target="_blank" href="https://www.tripadvisor.com.mx/"><img src="https://www.tripadvisor.com.mx/img/cdsi/img2/branding/medium-logo-12097-2.png" alt=Trip Advisor logotype""/></a>
						                    </li>
						                </ul>
						            </div>
						            <script async src="https://www.jscache.com/wejs?wtype=cdswritereviewlg&amp;uniq=4&amp;locationId=154652&amp;lang=es_MX&amp;lang=es_MX&amp;display_version=2" data-loadtrk onload="this.loadtrk=true"></script>
						        </main>
						        <footer>
						            <div class="action-buttons">
						                <button class="btn" button-close>{$lang.close}</button>
						            </div>
						        </footer>
						    </div>
						</section>';
				   	}

	                $replace = [
						'{$logotype}' => !empty($account['logotype']) ? '{$path.uploads}' . $account['logotype'] : '{$path.images}empty.png',
						'{$weather}' => $weather,
	                    '{$a_new_request}' => $a_new_request,
	                    '{$mdl_new_request}' => $mdl_new_request,
						'{$a_new_incident}' => $a_new_incident,
	                    '{$mdl_new_incident}' => $mdl_new_incident,
	                    '{$a_new_survey_answer}' => $a_new_survey_answer,
	                    '{$mdl_new_survey_answer}' => $mdl_new_survey_answer,
	                ];

	                $template = $this->format->replace($replace, $template);

	                echo $template;
	            }
			}
			else
				header('Location: /');
        }
        else
            header('Location: /');
    }
}
