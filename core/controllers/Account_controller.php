<?php

defined('_EXEC') or die;

class Account_controller extends Controller
{
	private $lang;

	public function __construct()
	{
		parent::__construct();

		$this->lang = Session::get_value('account')['language'];
	}

	public function index()
	{
		$account = $this->model->get_account();

		if (Format::exist_ajax_request() == true)
		{
			if ($_POST['action'] == 'get_account')
			{
				Functions::environment([
					'status' => 'success',
					'data' => $account
				]);
			}

			if ($_POST['action'] == 'edit_logotype')
			{
				$labels = [];

				if (!isset($_FILES['logotype']['name']) OR empty($_FILES['logotype']['name']))
					array_push($labels, ['logotype','']);

				if (empty($labels))
				{
					$query = $this->model->edit_logotype($_FILES);

					if (!empty($query))
					{
						$account = Session::get_value('account');

						$account['logotype'] = $query;

						Session::set_value('account', $account);

						Functions::environment([
							'status' => 'success',
							'message' => '{$lang.operation_success}'
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

			if ($_POST['action'] == 'edit_account')
			{
				$labels = [];

				if (!isset($_POST['name']) OR empty($_POST['name']) OR $this->model->check_exist_account('name', $_POST['name']) == true)
					array_push($labels, ['name','']);

				if (!isset($_POST['country']) OR empty($_POST['country']))
					array_push($labels, ['country','']);

				if (!isset($_POST['city']) OR empty($_POST['city']))
					array_push($labels, ['city','']);

				if (!isset($_POST['zip_code']) OR empty($_POST['zip_code']))
					array_push($labels, ['zip_code','']);

				if (!isset($_POST['address']) OR empty($_POST['address']))
					array_push($labels, ['address','']);

				if (!isset($_POST['time_zone']) OR empty($_POST['time_zone']))
					array_push($labels, ['time_zone','']);

				if (!isset($_POST['currency']) OR empty($_POST['currency']))
					array_push($labels, ['currency','']);

				if (!isset($_POST['language']) OR empty($_POST['language']))
					array_push($labels, ['language','']);

				if (empty($labels))
				{
					$query = $this->model->edit_account($_POST);

					if (!empty($query))
					{
						$account = Session::get_value('account');

						$account['name'] = $_POST['name'];
						$account['time_zone'] = $_POST['time_zone'];
						$account['currency'] = $_POST['currency'];
						$account['language'] = $_POST['language'];

						Session::set_value('account', $account);

						Functions::environment([
							'status' => 'success',
							'message' => '{$lang.operation_success}'
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

			if ($_POST['action'] == 'edit_billing')
			{
				$labels = [];

				if (!isset($_POST['fiscal_id']) OR empty($_POST['fiscal_id']))
					array_push($labels, ['fiscal_id','']);

				if (!isset($_POST['fiscal_name']) OR empty($_POST['fiscal_name']))
					array_push($labels, ['fiscal_name','']);

				if (!isset($_POST['fiscal_address']) OR empty($_POST['fiscal_address']))
					array_push($labels, ['fiscal_address','']);

				if (!isset($_POST['contact_firstname']) OR empty($_POST['contact_firstname']))
					array_push($labels, ['contact_firstname','']);

				if (!isset($_POST['contact_lastname']) OR empty($_POST['contact_lastname']))
					array_push($labels, ['contact_lastname','']);

				if (!isset($_POST['contact_department']) OR empty($_POST['contact_department']))
					array_push($labels, ['contact_department','']);

				if (!isset($_POST['contact_email']) OR empty($_POST['contact_email']) OR Functions::check_email($_POST['contact_email']) == false)
					array_push($labels, ['contact_email','']);

				if (!isset($_POST['contact_phone_lada']) OR empty($_POST['contact_phone_lada']))
					array_push($labels, ['contact_phone_lada','']);

				if (!isset($_POST['contact_phone_number']) OR empty($_POST['contact_phone_number']))
					array_push($labels, ['contact_phone_number','']);

				if (empty($labels))
				{
					$query = $this->model->edit_billing($_POST);

					if (!empty($query))
					{
						Functions::environment([
							'status' => 'success',
							'message' => '{$lang.operation_success}'
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

			if ($_POST['action'] == 'edit_location')
			{
				$labels = [];

				if (!isset($_POST['lat']) OR empty($_POST['lat']))
					array_push($labels, ['lat','']);

				if (!isset($_POST['lng']) OR empty($_POST['lng']))
					array_push($labels, ['lng','']);

				if (empty($labels))
				{
					$query = $this->model->edit_location($_POST);

					if (!empty($query))
					{
						Functions::environment([
							'status' => 'success',
							'message' => '{$lang.operation_success}'
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

			if ($_POST['action'] == 'get_opt_opportunity_types')
			{
				$html = '<option value="" hidden>{$lang.choose}</option>';

				if (!empty($_POST['opportunity_area']))
				{
					foreach ($this->model->get_opportunity_types($_POST['opportunity_area'], 'request') as $value)
						$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang] . '</option>';
				}

				Functions::environment([
					'status' => 'success',
					'html' => $html
				]);
			}

			if ($_POST['action'] == 'edit_myvox_menu_settings' OR $_POST['action'] == 'edit_myvox_request_settings' OR $_POST['action'] == 'edit_myvox_incident_settings' OR $_POST['action'] == 'edit_voxes_attention_times_settings' OR $_POST['action'] == 'edit_myvox_survey_settings' OR $_POST['action'] == 'edit_reviews_settings')
			{
				$labels = [];

				if ($_POST['action'] == 'edit_myvox_menu_settings')
				{
					if (!empty($_POST['status']))
					{
						if (!isset($_POST['title_es']) OR empty($_POST['title_es']))
							array_push($labels, ['title_es','']);

						if (!isset($_POST['title_en']) OR empty($_POST['title_en']))
							array_push($labels, ['title_en','']);

						if (!isset($_POST['email']) OR empty($_POST['email']))
							array_push($labels, ['email','']);

						if (!isset($_POST['phone_lada']) OR empty($_POST['phone_lada']))
							array_push($labels, ['phone_lada','']);

						if (!isset($_POST['phone_number']) OR empty($_POST['phone_number']))
							array_push($labels, ['phone_number','']);

						if (!isset($_POST['currency']) OR empty($_POST['currency']))
							array_push($labels, ['currency','']);

						if (!isset($_POST['schedule_monday_status']) OR empty($_POST['schedule_monday_status']))
							array_push($labels, ['schedule_monday_status','']);
						else if ($_POST['schedule_monday_status'] == 'open')
						{
							if (!isset($_POST['schedule_monday_opening']) OR empty($_POST['schedule_monday_opening']) OR $_POST['schedule_monday_opening'] >= $_POST['schedule_monday_closing'])
								array_push($labels, ['schedule_monday_opening','']);

							if (!isset($_POST['schedule_monday_closing']) OR empty($_POST['schedule_monday_closing']) OR $_POST['schedule_monday_closing'] <= $_POST['schedule_monday_opening'])
								array_push($labels, ['schedule_monday_closing','']);
						}

						if (!isset($_POST['schedule_tuesday_status']) OR empty($_POST['schedule_tuesday_status']))
							array_push($labels, ['schedule_tuesday_status','']);
						else if ($_POST['schedule_tuesday_status'] == 'open')
						{
							if (!isset($_POST['schedule_tuesday_opening']) OR empty($_POST['schedule_tuesday_opening']) OR $_POST['schedule_tuesday_opening'] >= $_POST['schedule_tuesday_closing'])
								array_push($labels, ['schedule_tuesday_opening','']);

							if (!isset($_POST['schedule_tuesday_closing']) OR empty($_POST['schedule_tuesday_closing']) OR $_POST['schedule_tuesday_closing'] <= $_POST['schedule_tuesday_opening'])
								array_push($labels, ['schedule_tuesday_closing','']);
						}

						if (!isset($_POST['schedule_wednesday_status']) OR empty($_POST['schedule_wednesday_status']))
							array_push($labels, ['schedule_wednesday_status','']);
						else if ($_POST['schedule_wednesday_status'] == 'open')
						{
							if (!isset($_POST['schedule_wednesday_opening']) OR empty($_POST['schedule_wednesday_opening']) OR $_POST['schedule_wednesday_opening'] >= $_POST['schedule_wednesday_closing'])
								array_push($labels, ['schedule_wednesday_opening','']);

							if (!isset($_POST['schedule_wednesday_closing']) OR empty($_POST['schedule_wednesday_closing']) OR $_POST['schedule_wednesday_closing'] <= $_POST['schedule_wednesday_opening'])
								array_push($labels, ['schedule_wednesday_closing','']);
						}

						if (!isset($_POST['schedule_thursday_status']) OR empty($_POST['schedule_thursday_status']))
							array_push($labels, ['schedule_thursday_status','']);
						else if ($_POST['schedule_thursday_status'] == 'open')
						{
							if (!isset($_POST['schedule_thursday_opening']) OR empty($_POST['schedule_thursday_opening']) OR $_POST['schedule_thursday_opening'] >= $_POST['schedule_thursday_closing'])
								array_push($labels, ['schedule_thursday_opening','']);

							if (!isset($_POST['schedule_thursday_closing']) OR empty($_POST['schedule_thursday_closing']) OR $_POST['schedule_thursday_closing'] <= $_POST['schedule_thursday_opening'])
								array_push($labels, ['schedule_thursday_closing','']);
						}

						if (!isset($_POST['schedule_friday_status']) OR empty($_POST['schedule_friday_status']))
							array_push($labels, ['schedule_friday_status','']);
						else if ($_POST['schedule_friday_status'] == 'open')
						{
							if (!isset($_POST['schedule_friday_opening']) OR empty($_POST['schedule_friday_opening']) OR $_POST['schedule_friday_opening'] >= $_POST['schedule_friday_closing'])
								array_push($labels, ['schedule_friday_opening','']);

							if (!isset($_POST['schedule_friday_closing']) OR empty($_POST['schedule_friday_closing']) OR $_POST['schedule_friday_closing'] <= $_POST['schedule_friday_opening'])
								array_push($labels, ['schedule_friday_closing','']);
						}

						if (!isset($_POST['schedule_saturday_status']) OR empty($_POST['schedule_saturday_status']))
							array_push($labels, ['schedule_saturday_status','']);
						else if ($_POST['schedule_saturday_status'] == 'open')
						{
							if (!isset($_POST['schedule_saturday_opening']) OR empty($_POST['schedule_saturday_opening']) OR $_POST['schedule_saturday_opening'] >= $_POST['schedule_saturday_closing'])
								array_push($labels, ['schedule_saturday_opening','']);

							if (!isset($_POST['schedule_saturday_closing']) OR empty($_POST['schedule_saturday_closing']) OR $_POST['schedule_saturday_closing'] <= $_POST['schedule_saturday_opening'])
								array_push($labels, ['schedule_saturday_closing','']);
						}

						if (!isset($_POST['schedule_sunday_status']) OR empty($_POST['schedule_sunday_status']))
							array_push($labels, ['schedule_sunday_status','']);
						else if ($_POST['schedule_sunday_status'] == 'open')
						{
							if (!isset($_POST['schedule_sunday_opening']) OR empty($_POST['schedule_sunday_opening']) OR $_POST['schedule_sunday_opening'] >= $_POST['schedule_sunday_closing'])
								array_push($labels, ['schedule_sunday_opening','']);

							if (!isset($_POST['schedule_sunday_closing']) OR empty($_POST['schedule_sunday_closing']) OR $_POST['schedule_sunday_closing'] <= $_POST['schedule_sunday_opening'])
								array_push($labels, ['schedule_sunday_closing','']);
						}

						if (($account['type'] == 'restaurant' OR $account['type'] == 'others') AND !empty($_POST['delivery']) AND (!isset($_POST['sell_radius']) OR empty($_POST['sell_radius'])))
							array_push($labels, ['sell_radius','']);
					}
				}
				else if ($_POST['action'] == 'edit_myvox_request_settings')
				{
					if (!empty($_POST['status']))
					{
						if (!isset($_POST['title_es']) OR empty($_POST['title_es']))
							array_push($labels, ['title_es','']);

						if (!isset($_POST['title_en']) OR empty($_POST['title_en']))
							array_push($labels, ['title_en','']);
					}
				}
				else if ($_POST['action'] == 'edit_myvox_incident_settings')
				{
					if (!empty($_POST['status']))
					{
						if (!isset($_POST['title_es']) OR empty($_POST['title_es']))
							array_push($labels, ['title_es','']);

						if (!isset($_POST['title_en']) OR empty($_POST['title_en']))
							array_push($labels, ['title_en','']);
					}
				}
				else if ($_POST['action'] == 'edit_voxes_attention_times_settings')
				{
					if (!isset($_POST['request_low']) OR empty($_POST['request_low']))
						array_push($labels, ['request_low','']);

					if (!isset($_POST['request_medium']) OR empty($_POST['request_medium']))
						array_push($labels, ['request_medium','']);

					if (!isset($_POST['request_high']) OR empty($_POST['request_high']))
						array_push($labels, ['request_high','']);

					if (!isset($_POST['incident_low']) OR empty($_POST['incident_low']))
						array_push($labels, ['incident_low','']);

					if (!isset($_POST['incident_medium']) OR empty($_POST['incident_medium']))
						array_push($labels, ['incident_medium','']);

					if (!isset($_POST['incident_high']) OR empty($_POST['incident_high']))
						array_push($labels, ['incident_high','']);
				}
				else if ($_POST['action'] == 'edit_myvox_survey_settings')
				{
					if (!empty($_POST['status']))
					{
						if (!isset($_POST['title_es']) OR empty($_POST['title_es']))
							array_push($labels, ['title_es','']);

						if (!isset($_POST['title_en']) OR empty($_POST['title_en']))
							array_push($labels, ['title_en','']);

						if (!isset($_POST['mail_subject_es']) OR empty($_POST['mail_subject_es']))
							array_push($labels, ['mail_subject_es','']);

						if (!isset($_POST['mail_subject_en']) OR empty($_POST['mail_subject_en']))
							array_push($labels, ['mail_subject_en','']);

						if (!isset($_POST['mail_description_es']) OR empty($_POST['mail_description_es']))
							array_push($labels, ['mail_description_es','']);

						if (!isset($_POST['mail_description_en']) OR empty($_POST['mail_description_en']))
							array_push($labels, ['mail_description_en','']);
					}
				}
				else if ($_POST['action'] == 'edit_reviews_settings')
				{
					if (!empty($_POST['status']))
					{
						if (!isset($_POST['email']) OR empty($_POST['email']) OR Functions::check_email($_POST['email']) == false)
							array_push($labels, ['email','']);

						if (!isset($_POST['phone_lada']) OR empty($_POST['phone_lada']))
							array_push($labels, ['phone_lada','']);

						if (!isset($_POST['phone_number']) OR empty($_POST['phone_number']))
							array_push($labels, ['phone_number','']);

						if (!isset($_POST['website']) OR empty($_POST['website']))
							array_push($labels, ['website','']);

						if (!isset($_POST['description_es']) OR empty($_POST['description_es']))
							array_push($labels, ['description_es','']);

						if (!isset($_POST['description_en']) OR empty($_POST['description_en']))
							array_push($labels, ['description_en','']);

						if (!isset($_POST['seo_keywords_es']) OR empty($_POST['seo_keywords_es']))
							array_push($labels, ['seo_keywords_es','']);

						if (!isset($_POST['seo_keywords_en']) OR empty($_POST['seo_keywords_en']))
							array_push($labels, ['seo_keywords_en','']);

						if (!isset($_POST['seo_description_es']) OR empty($_POST['seo_description_es']))
							array_push($labels, ['seo_description_es','']);

						if (!isset($_POST['seo_description_en']) OR empty($_POST['seo_description_en']))
							array_push($labels, ['seo_description_en','']);
					}
				}

				if (empty($labels))
				{
					if ($_POST['action'] == 'edit_myvox_menu_settings')
						$query = $this->model->edit_settings('myvox_menu', $_POST, $account);
					else if ($_POST['action'] == 'edit_myvox_request_settings')
						$query = $this->model->edit_settings('myvox_request', $_POST);
					else if ($_POST['action'] == 'edit_myvox_incident_settings')
						$query = $this->model->edit_settings('myvox_incident', $_POST);
					else if ($_POST['action'] == 'edit_voxes_attention_times_settings')
						$query = $this->model->edit_settings('voxes_attention_times', $_POST);
					else if ($_POST['action'] == 'edit_myvox_survey_settings')
					{
						if (!empty($_POST['status']))
						{
							$_POST['mail_image'] = $_FILES['mail_image'];
							$_POST['mail_attachment'] = $_FILES['mail_attachment'];
						}

						$query = $this->model->edit_settings('myvox_survey', $_POST);
					}
					else if ($_POST['action'] == 'edit_reviews_settings')
						$query = $this->model->edit_settings('reviews', $_POST);

					if (!empty($query))
					{
						if ($_POST['action'] == 'edit_myvox_menu_settings')
						{
							$account = Session::get_value('account');

							if (isset($_POST['currency']))
								$account['settings']['menu']['currency'] = $_POST['currency'];

							if (isset($_POST['multi']))
								$account['settings']['menu']['multi'] = !empty($_POST['multi']) ? true : false;

							Session::set_value('account', $account);
						}

						Functions::environment([
							'status' => 'success',
							'message' => '{$lang.operation_success}'
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

			if ($_POST['action'] == 'edit_rappi_settings')
			{
				$labels = [];

				if (!isset($_POST['rappi_client_id']) OR empty($_POST['rappi_client_id']))
					array_push($labels, ['rappi_client_id','']);

				if (!isset($_POST['rappi_client_secret']) OR empty($_POST['rappi_client_secret']))
					array_push($labels, ['rappi_client_secret','']);

				if (!isset($_POST['rappi_client_email']) OR empty($_POST['rappi_client_email']))
					array_push($labels, ['rappi_client_email','']);

				if (empty($labels))
				{
					$query = $this->model->edit_rappi_settings($_POST, $account);

					if (!empty($query))
					{
						Functions::environment([
							'status' => 'success',
							'message' => '{$lang.operation_success}'
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

			if ($_POST['action'] == 'edit_payment')
			{
				$labels = [];

				if (!empty($_POST['status']) AND $account['payment']['contract']['status'] == 'deactivated')
				{
					if (!isset($_POST['contract_titular_fiscal_person']) OR empty($_POST['contract_titular_fiscal_person']))
						array_push($labels, ['contract_titular_fiscal_person','']);

					if (!isset($_POST['contract_titular_fiscal_id']) OR empty($_POST['contract_titular_fiscal_id']))
						array_push($labels, ['contract_titular_fiscal_id','']);

					if (!isset($_POST['contract_titular_fiscal_name']) OR empty($_POST['contract_titular_fiscal_name']))
						array_push($labels, ['contract_titular_fiscal_name','']);

					if (!isset($_POST['contract_titular_fiscal_activity']) OR empty($_POST['contract_titular_fiscal_activity']))
						array_push($labels, ['contract_titular_fiscal_activity','']);

					if (!isset($_POST['contract_titular_address_street']) OR empty($_POST['contract_titular_address_street']))
						array_push($labels, ['contract_titular_address_street','']);

					if (!isset($_POST['contract_titular_address_external_number']) OR empty($_POST['contract_titular_address_external_number']))
						array_push($labels, ['contract_titular_address_external_number','']);

					if (!isset($_POST['contract_titular_address_cp']) OR empty($_POST['contract_titular_address_cp']))
						array_push($labels, ['contract_titular_address_cp','']);

					if (!isset($_POST['contract_titular_address_colony']) OR empty($_POST['contract_titular_address_colony']))
						array_push($labels, ['contract_titular_address_colony','']);

					if (!isset($_POST['contract_titular_address_delegation']) OR empty($_POST['contract_titular_address_delegation']))
						array_push($labels, ['contract_titular_address_delegation','']);

					if (!isset($_POST['contract_titular_address_city']) OR empty($_POST['contract_titular_address_city']))
						array_push($labels, ['contract_titular_address_city','']);

					if (!isset($_POST['contract_titular_address_state']) OR empty($_POST['contract_titular_address_state']))
						array_push($labels, ['contract_titular_address_state','']);

					if (!isset($_POST['contract_titular_address_country']) OR empty($_POST['contract_titular_address_country']))
						array_push($labels, ['contract_titular_address_country','']);

					if (!isset($_POST['contract_titular_bank_name']) OR empty($_POST['contract_titular_bank_name']))
						array_push($labels, ['contract_titular_bank_name','']);

					if (!isset($_POST['contract_titular_bank_branch']) OR empty($_POST['contract_titular_bank_branch']))
						array_push($labels, ['contract_titular_bank_branch','']);

					if (!isset($_POST['contract_titular_bank_checkbook']) OR empty($_POST['contract_titular_bank_checkbook']))
						array_push($labels, ['contract_titular_bank_checkbook','']);

					if (!isset($_POST['contract_titular_bank_clabe']) OR empty($_POST['contract_titular_bank_clabe']))
						array_push($labels, ['contract_titular_bank_clabe','']);

					if (!isset($_POST['contract_place']) OR empty($_POST['contract_place']))
						array_push($labels, ['contract_place','']);

					if (!isset($_POST['contract_signature']) OR empty($_POST['contract_signature']))
						array_push($labels, ['contract_signature','']);
				}

				if (empty($labels))
				{
					$_POST['contract_status'] = $account['payment']['contract']['status'];

					$query = $this->model->edit_payment($_POST);

					if (!empty($query))
					{
						if (!empty($_POST['status']) AND $account['payment']['contract']['status'] == 'deactivated')
						{
							$mail = new Mailer(true);

							try
							{
								$mail->setFrom('noreply@guestvox.com', 'Guestvox');
								$mail->addAddress('contacto@guestvox.com', 'Guestvox');
								$mail->Subject = 'Guestvox | Nueva solicitud de pasarela de pagos';
								$mail->Body =
								'Cuenta: ' . Session::get_value('account')['name'] . '<br>
								Fecha: ' . Functions::get_current_date();
								$mail->send();
							}
							catch (Exception $e) { }
						}

						Functions::environment([
							'status' => 'success',
							'message' => '{$lang.operation_success}'
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
			$template = $this->view->render($this, 'index');

			define('_title', 'Guestvox | {$lang.account}');

			$div_menu = '';

			if ($account['digital_menu'] == true)
			{
				$div_menu .=
				'<div class="stl_5">
					<i class="fas fa-fire-alt"></i>
					<h2>{$lang.menu}</h2>
					<span>' . (($account['settings']['myvox']['menu']['status'] == true) ? '{$lang.activated}' : '{$lang.deactivated}') . '</span>
					<div class="switch_group">
						<div class="switch">
							<input id="mnsw" type="checkbox" ' . (($account['settings']['myvox']['menu']['status'] == true) ? 'checked' : '') . ' data-switcher>
							<label for="mnsw"></label>
						</div>
						' . (($account['settings']['myvox']['menu']['status'] == true) ? '<a class="edit" data-action="edit_myvox_menu_settings"><i class="fas fa-pen" aria-hidden="true"></i></a>' : '') . '
					</div>
				</div>';
			}

			$div_public_requests = '';
			$div_public_incidents = '';
			$div_attention_times = '';
			$div_siteminder = '';
			$div_zaviapms = '';
			$div_ambit = '';
			$div_rappi = '';

			if ($account['operation'] == true)
			{
				$div_public_requests .=
				'<div class="stl_5">
	                <i class="fas fa-rocket"></i>
	                <h2>{$lang.public_requests}</h2>
	                <span>' . (($account['settings']['myvox']['request']['status'] == true) ? '{$lang.activated}' : '{$lang.deactivated}') . '</span>
					<div class="switch_group">
						<div class="switch">
							<input id="rqsw" type="checkbox" ' . (($account['settings']['myvox']['request']['status'] == true) ? 'checked' : '') . ' data-switcher>
							<label for="rqsw"></label>
						</div>
						' . (($account['settings']['myvox']['request']['status'] == true) ? '<a class="edit" data-action="edit_myvox_request_settings"><i class="fas fa-pen" aria-hidden="true"></i></a>' : '') . '
					</div>
	            </div>';

				$div_public_incidents .=
				'<div class="stl_5">
	                <i class="fas fa-meteor"></i>
	                <h2>{$lang.public_incidents}</h2>
	                <span>' . (($account['settings']['myvox']['incident']['status'] == true) ? '{$lang.activated}' : '{$lang.deactivated}') . '</span>
					<div class="switch_group">
						<div class="switch">
						    <input id="insw" type="checkbox" ' . (($account['settings']['myvox']['incident']['status'] == true) ? 'checked' : '') . ' data-switcher>
						    <label for="insw"></label>
						</div>
						' . (($account['settings']['myvox']['incident']['status'] == true) ? '<a class="edit" data-action="edit_myvox_incident_settings"><i class="fas fa-pen" aria-hidden="true"></i></a>' : '') . '
					</div>
	            </div>';

				$div_attention_times .=
				'<div class="stl_5">
	                <i class="fas fa-clock"></i>
	                <h2>{$lang.attention_times}</h2>
	                <span>{$lang.activated}</span>
					<a class="edit" data-action="edit_voxes_attention_times_settings"><i class="fas fa-pen"></i></a>
	            </div>';

				if ($account['type'] == 'hotel')
				{
					$div_siteminder .=
					'<div class="stl_5">
						<figure>
							<img src="{$path.images}siteminder.png">
						</figure>
						<h2>Siteminder</h2>
						<span>' . (($account['siteminder']['status'] == true) ? '{$lang.activated}' : '{$lang.deactivated}') . '</span>
	                </div>';

					$div_zaviapms .=
					'<div class="stl_5">
						<figure>
							<img src="{$path.images}zaviapms.png">
						</figure>
						<h2>Zavia PMS</h2>
						<span>' . (($account['zaviapms']['status'] == true) ? '{$lang.activated}' : '{$lang.deactivated}') . '</span>
	                </div>';
				}

				if ($account['type'] == 'hotel' OR $account['type'] == 'restaurant')
				{
					$div_ambit .=
					'<div class="stl_5">
						<figure>
							<img src="{$path.images}ambit.png">
						</figure>
						<h2>Ambit</h2>
						<span>' . (($account['ambit']['status'] == true) ? '{$lang.activated}' : '{$lang.deactivated}') . '</span>
					</div>';


				}

				if ($account['type'] == 'restaurant')
				{
					$div_rappi .=
					'<div class="stl_5">
						<figure>
							<img src="{$path.images}rappi.png">
						</figure>
						<h2>Rappi</h2>
						<span>' . (($account['rappi']['status'] == true) ? '{$lang.activated}' : '{$lang.deactivated}') . '</span>
						<div class="switch_group">
							<div class="switch">
								<input id="rpsw" type="checkbox" ' . (($account['rappi']['status'] == true) ? 'checked' : '') . ' data-switcher>
								<label for="rpsw"></label>
							</div>
							' . (($account['rappi']['status'] == true) ? '<a class="edit" data-action="edit_rappi_settings"><i class="fas fa-pen" aria-hidden="true"></i></a>' : '') . '
						</div>
					</div>';
				}
			}

			$div_answer_surveys = '';
			$div_reviews_page = '';

			if ($account['surveys'] == true)
			{
				$div_answer_surveys .=
				'<div class="stl_5">
	                <i class="fas fa-ghost"></i>
	                <h2>{$lang.answer_surveys}</h2>
	                <span>' . (($account['settings']['myvox']['survey']['status'] == true) ? '{$lang.activated}' : '{$lang.deactivated}') . '</span>
					<div class="switch_group">
						<div class="switch">
							<input id="susw" type="checkbox" ' . (($account['settings']['myvox']['survey']['status'] == true) ? 'checked' : '') . ' data-switcher>
							<label for="susw"></label>
						</div>
						' . (($account['settings']['myvox']['survey']['status'] == true) ? '<a class="edit" data-action="edit_myvox_survey_settings"><i class="fas fa-pen" aria-hidden="true"></i></a>' : '') . '
					</div>
	            </div>';

				$div_reviews_page .=
				'<div class="stl_5">
	                <i class="fas fa-star"></i>
	                <h2>{$lang.reviews_page}</h2>
	                <span>' . (($account['settings']['reviews']['status'] == true) ? '{$lang.activated}' : '{$lang.deactivated}') . '</span>
					<div class="switch_group">
						<div class="switch">
						    <input id="rvsw" type="checkbox" ' . (($account['settings']['reviews']['status'] == true) ? 'checked' : '') . ' data-switcher>
						    <label for="rvsw"></label>
						</div>
						' . (($account['settings']['reviews']['status'] == true) ? '<a class="edit" data-action="edit_reviews_settings"><i class="fas fa-pen" aria-hidden="true"></i></a>' : '') . '
					</div>
	            </div>';
			}

			$div_payment =
			'<div class="stl_5">
				<i class="fas fa-credit-card"></i>
				<h2>{$lang.payment}</h2>
				<span>' . (($account['payment']['status'] == true) ? '{$lang.activated}' : '{$lang.deactivated}') . '</span>
				<div class="switch_group">
					<div class="switch">
						<input id="pysw" type="checkbox" ' . (($account['payment']['status'] == true) ? 'checked' : '') . ' data-switcher>
						<label for="pysw"></label>
					</div>
					' . (($account['payment']['status'] == true) ? '<a class="edit" data-action="edit_payment"><i class="fas fa-pen" aria-hidden="true"></i></a>' : '') . '
				</div>
			</div>';

			$opt_countries = '';

			foreach ($this->model->get_countries() as $value)
				$opt_countries .= '<option value="' . $value['code'] . '">' . $value['name'][$this->lang] . '</option>';

			$opt_times_zones = '';

			foreach ($this->model->get_times_zones() as $value)
				$opt_times_zones .= '<option value="' . $value['code'] . '">' . $value['code'] . '</option>';

			$opt_currencies = '';

			foreach ($this->model->get_currencies() as $value)
				$opt_currencies .= '<option value="' . $value['code'] . '">' . $value['name'][$this->lang] . ' (' . $value['code'] . ')</option>';

			$opt_languages = '';

			foreach ($this->model->get_languages() as $value)
				$opt_languages .= '<option value="' . $value['code'] . '">' . $value['name'] . '</option>';

			$opt_ladas = '';

			foreach ($this->model->get_countries() as $value)
				$opt_ladas .= '<option value="' . $value['lada'] . '">' . $value['name'][$this->lang] . ' (+' . $value['lada'] . ')</option>';

			$mdl_edit_myvox_menu_settings = '';

			if ($account['digital_menu'] == true)
			{
				$mdl_edit_myvox_menu_settings .=
				'<section class="modal fullscreen" data-modal="edit_myvox_menu_settings">
					<div class="content">
						<main>
							<form name="edit_myvox_menu_settings">
								<div class="row">
									<div class="span6">
										<div class="label">
											<label required>
												<p>(ES) {$lang.title} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<input type="text" name="title_es" data-translates="menu_title">
											</label>
										</div>
									</div>
									<div class="span6">
										<div class="label">
											<label required>
												<p>(EN) {$lang.title} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<input type="text" name="title_en" data-translaten="menu_title">
											</label>
										</div>
									</div>
									<div class="span3">
										<div class="label">
											<label required>
												<p>{$lang.email} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<input type="email" name="email">
											</label>
										</div>
									</div>
									<div class="span3">
										<div class="label">
											<label required>
												<p>{$lang.lada} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<select name="phone_lada">
													<option value="" hidden>{$lang.choose}</option>';

				foreach ($this->model->get_countries() as $value)
					$mdl_edit_myvox_menu_settings .= '<option value="' . $value['lada'] . '">' . $value['name'][$this->lang] . ' (+' . $value['lada'] . ')</option>';

				$mdl_edit_myvox_menu_settings .=
				'								</select>
											</label>
										</div>
									</div>
									<div class="span3">
										<div class="label">
											<label required>
												<p>{$lang.phone} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<input type="text" name="phone_number">
											</label>
										</div>
									</div>
									<div class="span3">
										<div class="label">
											<label required>
												<p>{$lang.currency} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<select name="currency">
													<option value="" hidden>{$lang.choose}</option>';

				foreach ($this->model->get_currencies() as $value)
					$mdl_edit_myvox_menu_settings .= '<option value="' . $value['code'] . '">' . $value['name'][$this->lang] . ' (' . $value['code'] . ')</option>';

				$mdl_edit_myvox_menu_settings .=
				'			</select>
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label class="success">
							<p>{$lang.schedule} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<input type="text" value="{$lang.monday}" disabled>
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<p></p>
							<select name="schedule_monday_status">
								<option value="open">{$lang.open}</option>
								<option value="close">{$lang.close}</option>
							</select>
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<p>{$lang.opening} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<input type="time" name="schedule_monday_opening">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<p>{$lang.closing} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<input type="time" name="schedule_monday_closing">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label class="success">
							<input type="text" value="{$lang.tuesday}" disabled>
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<select name="schedule_tuesday_status">
								<option value="open">{$lang.open}</option>
								<option value="close">{$lang.close}</option>
							</select>
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<input type="time" name="schedule_tuesday_opening">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<input type="time" name="schedule_tuesday_closing">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label class="success">
							<input type="text" value="{$lang.wednesday}" disabled>
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<select name="schedule_wednesday_status">
								<option value="open">{$lang.open}</option>
								<option value="close">{$lang.close}</option>
							</select>
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<input type="time" name="schedule_wednesday_opening">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<input type="time" name="schedule_wednesday_closing">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label class="success">
							<input type="text" value="{$lang.thursday}" disabled>
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<select name="schedule_thursday_status">
								<option value="open">{$lang.open}</option>
								<option value="close">{$lang.close}</option>
							</select>
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<input type="time" name="schedule_thursday_opening">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<input type="time" name="schedule_thursday_closing">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label class="success">
							<input type="text" value="{$lang.friday}" disabled>
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<select name="schedule_friday_status">
								<option value="open">{$lang.open}</option>
								<option value="close">{$lang.close}</option>
							</select>
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<input type="time" name="schedule_friday_opening">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<input type="time" name="schedule_friday_closing">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label class="success">
							<input type="text" value="{$lang.saturday}" disabled>
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<select name="schedule_saturday_status">
								<option value="open">{$lang.open}</option>
								<option value="close">{$lang.close}</option>
							</select>
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<input type="time" name="schedule_saturday_opening">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<input type="time" name="schedule_saturday_closing">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label class="success">
							<input type="text" value="{$lang.sunday}" disabled>
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<select name="schedule_sunday_status">
								<option value="open">{$lang.open}</option>
								<option value="close">{$lang.close}</option>
							</select>
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<input type="time" name="schedule_sunday_opening">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<input type="time" name="schedule_sunday_closing">
						</label>
					</div>
				</div>';

				if ($account['type'] == 'hotel' OR $account['type'] == 'restaurant')
				{
					$mdl_edit_myvox_menu_settings .=
					'<div class="' . (($account['type'] == 'hotel') ? 'span12' : 'span6') . '">
						<div class="label">
							<label unrequired>
								<p>{$lang.receive_requests_' . $account['type'] . '} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
								<div class="switch">
									<input id="rrsw" type="checkbox" name="requests" data-switcher>
									<label for="rrsw"></label>
								</div>
							</label>
						</div>
					</div>';
				}

				if ($account['type'] == 'restaurant' OR $account['type'] == 'others')
				{
					$mdl_edit_myvox_menu_settings .=
					'<div class="' . (($account['type'] == 'others') ? 'span12' : 'span6') . '">
						<div class="label">
							<label unrequired>
								<p>{$lang.receive_delivery_from_home} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
								<div class="switch">
									<input id="rdsw" type="checkbox" name="delivery" data-switcher>
									<label for="rdsw"></label>
								</div>
							</label>
						</div>
					</div>
					<div class="span12">
						<div id="menu_map" data-lat="' . $account['location']['lat'] . '" data-lng="' . $account['location']['lng'] . '" data-rad="' . $account['settings']['myvox']['menu']['sell_radius'] . '"></div>
					</div>
					<div class="span12">
						<div class="label">
							<label required>
								<p>{$lang.sell_radius} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
								<input id="menu_rad" type="number" name="sell_radius">
							</label>
						</div>
					</div>';
				}

				$mdl_edit_myvox_menu_settings .=
				'					<!-- <div class="span4">
										<div class="label">
											<label unrequired>
												<p>{$lang.multi} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<div class="switch">
													<input id="mlsw" type="checkbox" name="multi" data-switcher>
													<label for="mlsw"></label>
												</div>
											</label>
										</div>
									</div> -->
									<div class="span12">
										<div class="buttons">
											<a class="delete" button-cancel><i class="fas fa-times"></i></a>
											<button type="submit" class="new"><i class="fas fa-check"></i></button>
										</div>
									</div>
								</div>
							</form>
						</main>
					</div>
				</section>';
			}

			$mdl_edit_rappi_settings = '';

			if ($account['type'] == 'restaurant')
			{
				$mdl_edit_rappi_settings .=
				'<section class="modal fullscreen" data-modal="edit_rappi_settings">
					<div class="content">
						<main>
							<form name="edit_rappi_settings">
								<div class="row">
									<div class="span6">
										<div class="label">
											<label required>
												<p>ID de cliente <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<input type="text" name="rappi_client_id">
											</label>
										</div>
									</div>
									<div class="span6">
										<div class="label">
											<label required>
												<p>Clave secreta <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<input type="text" name="rappi_client_secret">
											</label>
										</div>
									</div>
									<div class="span12">
										<div class="label">
											<label required>
												<p>{$lang.email} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<input type="email" name="rappi_client_email">
											</label>
										</div>
									</div>
									<div class="span12">
										<div class="buttons">
											<a class="delete" button-cancel><i class="fas fa-times"></i></a>
											<button type="submit" class="new"><i class="fas fa-check"></i></button>
										</div>
									</div>
								</div>
							</form>
						</main>
					</div>
				</section>';
			}

			$mdl_edit_myvox_request_settings = '';
			$mdl_edit_myvox_incident_settings = '';
			$mdl_edit_voxes_attention_times_settings = '';

			if ($account['operation'] == true)
			{
				$mdl_edit_myvox_request_settings .=
				'<section class="modal fullscreen" data-modal="edit_myvox_request_settings">
					<div class="content">
						<main>
							<form name="edit_myvox_request_settings">
								<div class="row">
									<div class="span6">
										<div class="label">
											<label required>
												<p>(ES) {$lang.title} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<input type="text" name="title_es" data-translates="request_title">
											</label>
										</div>
									</div>
									<div class="span6">
										<div class="label">
											<label required>
												<p>(EN) {$lang.title} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<input type="text" name="title_en" data-translaten="request_title">
											</label>
										</div>
									</div>
									<div class="span12">
										<div class="buttons">
											<a class="delete" button-cancel><i class="fas fa-times"></i></a>
											<button type="submit" class="new"><i class="fas fa-check"></i></button>
										</div>
									</div>
								</div>
							</form>
						</main>
					</div>
				</section>';

				$mdl_edit_myvox_incident_settings .=
				'<section class="modal fullscreen" data-modal="edit_myvox_incident_settings">
					<div class="content">
						<main>
							<form name="edit_myvox_incident_settings">
								<div class="row">
									<div class="span6">
										<div class="label">
											<label required>
												<p>(ES) {$lang.title} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<input type="text" name="title_es" data-translates="incident_title">
											</label>
										</div>
									</div>
									<div class="span6">
										<div class="label">
											<label required>
												<p>(EN) {$lang.title} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<input type="text" name="title_en" data-translaten="incident_title">
											</label>
										</div>
									</div>
									<div class="span12">
										<div class="buttons">
											<a class="delete" button-cancel><i class="fas fa-times"></i></a>
											<button type="submit" class="new"><i class="fas fa-check"></i></button>
										</div>
									</div>
								</div>
							</form>
						</main>
					</div>
				</section>';

				$mdl_edit_voxes_attention_times_settings .=
				'<section class="modal fullscreen" data-modal="edit_voxes_attention_times_settings">
					<div class="content">
						<main>
							<form name="edit_voxes_attention_times_settings">
								<div class="row">
									<div class="span4">
										<div class="label">
											<label required>
												<p>{$lang.request}</p>
												<p>{$lang.urgency_low} ({$lang.minutes})</p>
												<input type="number" name="request_low">
											</label>
										</div>
									</div>
									<div class="span4">
										<div class="label">
											<label required>
												<p>{$lang.request}</p>
												<p>{$lang.urgency_medium} ({$lang.minutes})</p>
												<input type="number" name="request_medium">
											</label>
										</div>
									</div>
									<div class="span4">
										<div class="label">
											<label required>
												<p>{$lang.request}</p>
												<p>{$lang.urgency_high} ({$lang.minutes})</p>
												<input type="number" name="request_high">
											</label>
										</div>
									</div>
									<div class="span4">
										<div class="label">
											<label required>
												<p>{$lang.incident}</p>
												<p>{$lang.urgency_low} ({$lang.minutes})</p>
												<input type="number" name="incident_low">
											</label>
										</div>
									</div>
									<div class="span4">
										<div class="label">
											<label required>
												<p>{$lang.incident}</p>
												<p>{$lang.urgency_medium} ({$lang.minutes})</p>
												<input type="number" name="incident_medium">
											</label>
										</div>
									</div>
									<div class="span4">
										<div class="label">
											<label required>
												<p>{$lang.incident}</p>
												<p>{$lang.urgency_high} ({$lang.minutes})</p>
												<input type="number" name="incident_high">
											</label>
										</div>
									</div>
									<div class="span12">
										<div class="buttons">
											<a class="delete" button-cancel><i class="fas fa-times"></i></a>
											<button type="submit" class="new"><i class="fas fa-check"></i></button>
										</div>
									</div>
								</div>
							</form>
						</main>
					</div>
				</section>';
			}

			$mdl_edit_myvox_survey_settings = '';
			$mdl_edit_reviews_settings = '';

			if ($account['surveys'] == true)
			{
				$mdl_edit_myvox_survey_settings .=
				'<section class="modal fullscreen" data-modal="edit_myvox_survey_settings">
					<div class="content">
						<main>
							<form name="edit_myvox_survey_settings">
								<div class="row">
									<div class="span6">
										<div class="label">
											<label required>
												<p>(ES) {$lang.title} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<input type="text" name="title_es" data-translates="survey_title">
											</label>
										</div>
									</div>
									<div class="span6">
										<div class="label">
											<label required>
												<p>(EN) {$lang.title} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<input type="text" name="title_en" data-translaten="survey_title">
											</label>
										</div>
									</div>
									<div class="span6">
										<div class="label">
											<label required>
												<p>(ES) {$lang.subject} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<input type="text" name="mail_subject_es" data-translates="survey_subject">
											</label>
										</div>
									</div>
									<div class="span6">
										<div class="label">
											<label required>
												<p>(EN) {$lang.subject} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<input type="text" name="mail_subject_en" data-translaten="survey_subject">
											</label>
										</div>
									</div>
									<div class="span6">
										<div class="label">
											<label required>
												<p>(ES) {$lang.description} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<textarea name="mail_description_es" data-translates="survey_description"></textarea>
											</label>
										</div>
									</div>
									<div class="span6">
										<div class="label">
											<label required>
												<p>(EN) {$lang.description} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<textarea name="mail_description_en" data-translaten="survey_description"></textarea>
											</label>
										</div>
									</div>
									<div class="span12">
										<div class="stl_2" data-uploader="low">
											<p>{$lang.image} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
											<figure data-preview>
												<img src="{$path.images}empty.png">
												<a data-select><i class="fas fa-upload"></i></a>
												<input type="file" name="mail_image" accept="image/*" data-upload>
											</figure>
										</div>
									</div>
									<div class="span12">
										<div class="stl_2" data-uploader="low">
											<p>{$lang.attachment} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
											<figure data-preview>
												<img src="{$path.images}empty.png">
												<a data-select><i class="fas fa-upload"></i></a>
												<input type="file" name="mail_attachment" accept="image/*, application/pdf, application/vnd.ms-word, application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" data-upload>
											</figure>
										</div>
									</div>
									<!-- <div class="span12">
										<div class="label">
											<label unrequired>
												<p>{$lang.widget} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<textarea name="widget"></textarea>
											</label>
										</div>
									</div> -->
									<div class="span12">
										<div class="buttons">
											<a class="delete" button-cancel><i class="fas fa-times"></i></a>
											<button type="submit" class="new"><i class="fas fa-check"></i></button>
										</div>
									</div>
								</div>
							</form>
						</main>
					</div>
				</section>';

				$mdl_edit_reviews_settings .=
				'<section class="modal fullscreen" data-modal="edit_reviews_settings">
				    <div class="content">
				        <main>
				            <form name="edit_reviews_settings">
				                <div class="row">
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
											<label required>
												<p>{$lang.lada}</p>
												<select name="phone_lada">
													<option value="" hidden>{$lang.choose}</option>';

				foreach ($this->model->get_countries() as $value)
					$mdl_edit_reviews_settings .= '<option value="' . $value['lada'] . '">' . $value['name'][$this->lang] . ' (+' . $value['lada'] . ')</option>';

				$mdl_edit_reviews_settings .=
				'								</select>
											</label>
										</div>
									</div>
									<div class="span3">
										<div class="label">
											<label required>
												<p>{$lang.phone}</p>
												<input type="text" name="phone_number">
											</label>
										</div>
									</div>
									<div class="span12">
										<div class="label">
											<label required>
												<p>{$lang.website}</p>
												<input type="text" name="website">
											</label>
										</div>
									</div>
									<div class="span6">
										<div class="label">
											<label required>
												<p>(ES) {$lang.description} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<textarea name="description_es" data-translates="reviews_description"></textarea>
											</label>
										</div>
									</div>
									<div class="span6">
										<div class="label">
											<label required>
												<p>(EN) {$lang.description} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<textarea name="description_en" data-translaten="reviews_description"></textarea>
											</label>
										</div>
									</div>
									<div class="span6">
										<div class="label">
											<label required>
												<p>(ES) {$lang.seo_keywords} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<input type="text" name="seo_keywords_es" data-translates="reviews_seo_keywords">
											</label>
										</div>
									</div>
									<div class="span6">
										<div class="label">
											<label required>
												<p>(EN) {$lang.seo_keywords} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<input type="text" name="seo_keywords_en" data-translaten="reviews_seo_keywords">
											</label>
										</div>
									</div>
									<div class="span6">
										<div class="label">
											<label required>
												<p>(ES) {$lang.seo_description} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<textarea name="seo_description_es" data-translates="reviews_seo_description"></textarea>
											</label>
										</div>
									</div>
									<div class="span6">
										<div class="label">
											<label required>
												<p>(EN) {$lang.seo_description} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<textarea name="seo_description_en" data-translaten="reviews_seo_description"></textarea>
											</label>
										</div>
									</div>
									<div class="span12">
										<div class="label">
											<label unrequired>
												<p>Facebook</p>
												<input type="text" name="social_media_facebook">
											</label>
										</div>
									</div>
									<div class="span12">
										<div class="label">
											<label unrequired>
												<p>Instagram</p>
												<input type="text" name="social_media_instagram">
											</label>
										</div>
									</div>
									<div class="span12">
										<div class="label">
											<label unrequired>
												<p>Twitter</p>
												<input type="text" name="social_media_twitter">
											</label>
										</div>
									</div>
									<div class="span12">
										<div class="label">
											<label unrequired>
												<p>LinkedIn</p>
												<input type="text" name="social_media_linkedin">
											</label>
										</div>
									</div>
									<div class="span12">
										<div class="label">
											<label unrequired>
												<p>YouTube</p>
												<input type="text" name="social_media_youtube">
											</label>
										</div>
									</div>
									<div class="span12">
										<div class="label">
											<label unrequired>
												<p>Google</p>
												<input type="text" name="social_media_google">
											</label>
										</div>
									</div>
									<div class="span12">
										<div class="label">
											<label unrequired>
												<p>TripAdvisor</p>
												<input type="text" name="social_media_tripadvisor">
											</label>
										</div>
									</div>
									<div class="span12">
										<div class="buttons">
											<a class="delete" button-cancel><i class="fas fa-times"></i></a>
											<button type="submit" class="new"><i class="fas fa-check"></i></button>
										</div>
									</div>
				                </div>
				            </form>
				        </main>
				    </div>
				</section>';
			}

			$mdl_edit_payment =
			'<section class="modal fullscreen" data-modal="edit_payment">
				<div class="content">
					<main>
						<form name="edit_payment">
							<div class="row">';

			if ($account['payment']['contract']['status'] == 'activated')
			{
				if ($account['payment']['type'] == 'mit')
				{
					$mdl_edit_payment .=
					'<div class="span6">
						<div class="label">
							<label unrequired>
								<p>Código de transacción</p>
								<input type="text" value="' . $account['payment']['mit']['code'] . '" disabled>
							</label>
						</div>
					</div>
					<div class="span6">
						<div class="label">
							<label unrequired>
								<p>Tipos de transacción</p>
								<input type="text" value="' . $account['payment']['mit']['types'] . '" disabled>
							</label>
						</div>
					</div>';
				}
			}
			else if ($account['payment']['contract']['status'] == 'pending')
			{
				$mdl_edit_payment .=
				'<div class="span12">
					<div class="message">
						<p>{$lang.contract_pending}</p>
					</div>
				</div>';
			}
			else if ($account['payment']['contract']['status'] == 'deactivated')
			{
				$mdl_edit_payment .=
				'<h6><strong>{$lang.transaction_processing_contract}</strong></h6>
				<h6>{$lang.titular_data}</h6>
				<div class="span4">
					<div class="label">
						<label required>
							<p>{$lang.fiscal_name}</p>
							<input type="text" name="contract_titular_fiscal_name">
						</label>
					</div>
				</div>
				<div class="span4">
					<div class="label">
						<label required>
							<p>{$lang.rfc}</p>
							<input type="text" name="contract_titular_fiscal_id">
						</label>
					</div>
				</div>
				<div class="span4">
					<div class="label">
						<label required>
							<p>{$lang.person}</p>
							<select name="contract_titular_fiscal_person">
								<option value="" hidden>{$lang.choose}</option>
								<option value="physical">{$lang.physical_person}</option>
								<option value="moral">{$lang.moral_person}</option>
							</select>
						</label>
					</div>
				</div>
				<div class="span12">
					<div class="label">
						<label required>
							<p>{$lang.activity} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<input type="text" name="contract_titular_fiscal_activity">
						</label>
					</div>
				</div>
				<div class="span4">
					<div class="label">
						<label required>
							<p>{$lang.street}</p>
							<input type="text" name="contract_titular_address_street">
						</label>
					</div>
				</div>
				<div class="span4">
					<div class="label">
						<label required>
							<p>{$lang.external_number}</p>
							<input type="text" name="contract_titular_address_external_number">
						</label>
					</div>
				</div>
				<div class="span4">
					<div class="label">
						<label unrequired>
							<p>{$lang.internal_number}</p>
							<input type="text" name="contract_titular_address_internal_number">
						</label>
					</div>
				</div>
				<div class="span4">
					<div class="label">
						<label required>
							<p>{$lang.cp}</p>
							<input type="text" name="contract_titular_address_cp">
						</label>
					</div>
				</div>
				<div class="span4">
					<div class="label">
						<label required>
							<p>{$lang.colony}</p>
							<input type="text" name="contract_titular_address_colony">
						</label>
					</div>
				</div>
				<div class="span4">
					<div class="label">
						<label required>
							<p>{$lang.delegation}</p>
							<input type="text" name="contract_titular_address_delegation">
						</label>
					</div>
				</div>
				<div class="span4">
					<div class="label">
						<label required>
							<p>{$lang.city}</p>
							<input type="text" name="contract_titular_address_city">
						</label>
					</div>
				</div>
				<div class="span4">
					<div class="label">
						<label required>
							<p>{$lang.state}</p>
							<input type="text" name="contract_titular_address_state">
						</label>
					</div>
				</div>
				<div class="span4">
					<div class="label">
						<label required>
							<p>{$lang.country}</p>
							<input type="text" name="contract_titular_address_country">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<p>{$lang.bank}</p>
							<input type="text" name="contract_titular_bank_name">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<p>{$lang.branch}</p>
							<input type="text" name="contract_titular_bank_branch">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<p>{$lang.checkbook}</p>
							<input type="text" name="contract_titular_bank_checkbook">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label required>
							<p>{$lang.clabe}</p>
							<input type="text" name="contract_titular_bank_clabe">
						</label>
					</div>
				</div>
				<h6>{$lang.company_data}</h6>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.writing_number}</p>
							<input type="text" name="contract_company_writing_number">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.writing_date}</p>
							<input type="date" name="contract_company_writing_date">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.public_record_folio}</p>
							<input type="text" name="contract_company_public_record_folio">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.public_record_date}</p>
							<input type="date" name="contract_company_public_record_date">
						</label>
					</div>
				</div>
				<div class="span4">
					<div class="label">
						<label unrequired>
							<p>{$lang.notary}</p>
							<input type="text" name="contract_company_notary_name">
						</label>
					</div>
				</div>
				<div class="span4">
					<div class="label">
						<label unrequired>
							<p>{$lang.number}</p>
							<input type="text" name="contract_company_notary_number">
						</label>
					</div>
				</div>
				<div class="span4">
					<div class="label">
						<label unrequired>
							<p>{$lang.city}</p>
							<input type="text" name="contract_company_city">
						</label>
					</div>
				</div>
				<h6>{$lang.legal_representative_data}</h6>
				<div class="span12">
					<div class="label">
						<label unrequired>
							<p>{$lang.name}</p>
							<input type="text" name="contract_company_legal_representative_name">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.writing_number}</p>
							<input type="text" name="contract_company_legal_representative_writing_number">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.writing_date}</p>
							<input type="date" name="contract_company_legal_representative_writing_date">
						</label>
					</div>
				</div>
				<div class="span4">
					<div class="label">
						<label unrequired>
							<p>{$lang.notary}</p>
							<input type="text" name="contract_company_legal_representative_notary_name">
						</label>
					</div>
				</div>
				<div class="span4">
					<div class="label">
						<label unrequired>
							<p>{$lang.number}</p>
							<input type="text" name="contract_company_legal_representative_notary_number">
						</label>
					</div>
				</div>
				<div class="span4">
					<div class="label">
						<label unrequired>
							<p>{$lang.city}</p>
							<input type="text" name="contract_company_legal_representative_city">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label unrequired>
							<p>{$lang.card}</p>
							<select name="contract_company_legal_representative_card_type">
								<option value="" hidden>{$lang.choose}</option>
								<option value="ine">{$lang.ine}</option>
							</select>
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label unrequired>
							<p>{$lang.number}</p>
							<input type="text" name="contract_company_legal_representative_card_number">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label unrequired>
							<p>{$lang.expedition_date}</p>
							<input type="date" name="contract_company_legal_representative_card_expedition_date">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label unrequired>
							<p>{$lang.validity}</p>
							<input type="date" name="contract_company_legal_representative_card_validity">
						</label>
					</div>
				</div>
				<h6>{$lang.contract}</h6>
				<div class="span12">
					<div class="row">
						<div class="span4">
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;"><strong>CONTRATO DE PRESTACIÓN DE SERVICIOS Y PROCESAMIENTO DE TRANSACCIONES CON TARJETAS DE CRÉDITO O DEBITO (EN LO SUCESIVO EL “CONTRATO”) QUE CELEBRAN:(I) LA PERSONA FÍSICA O MORAL CUYO NOMBRE; DENOMINACIÓN O RAZÓN SOCIAL HAN SIDO SEÑALADOS EN LA CARÁTULA DE ESTE CONTRATO (“EMPRESA”), REPRESENTADA POR LA PERSONA QUE ACEPTA ESTE CONTRATO Y QUIEN SE IDENTIFICA COMO REPRESENTANTE LEGAL DE LA MISMA;(II) MERCADOTECNIA IDEAS Y TECNOLOGÍA, S.A. DE C.V. (“PROCESADOR”), (III) LA PERSONA FÍSICA O MORAL CUYO NOMBRE; DENOMINACIÓN O RAZÓN SOCIAL HAN SIDO SEÑALADOS EN LA CARÁTULA DE ESTE CONTRATO (QUE PARA LOS EFECTOS DE INTERPRETACIÓN DEL PRESENTE CONTRATO SE DENOMINARA COMO EL INTEGRADOR O EL AGREGADOR),REPRESENTADA POR LA PERSONA QUE ACEPTA ESTE CONTRATO Y QUIEN SE IDENTIFICA COMO REPRESENTANTE LEGAL DE LA MISMAQUIENES DE MANERA CONJUNTA EN LO SUCESIVO SE LES DENOMINARÁ COMO LAS “PARTES”, AL TENOR DE LAS SIGUIENTES DECLARACIONES Y CLÁUSULAS.</strong></p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">D E C L A R A C I O N E S</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">I. Las PARTES declaran que el presente CONTRATO y sus Cláusulas son obligatorios para las PARTES, por tanto,constituye un acuerdo total entre las mismas para la prestación de los servicios y expresamente convienen que con la aceptación electrónica o de cualquier otra forma del mismo por parte de la EMPRESA y el INTEGRADOR, tendrá plenos efectos jurídicos entre las PARTES.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">II. La EMPRESA declara que en caso de ser persona física es mayor de edad, con plena capacidad jurídica paraobligarse de conformidad con este CONTRATO y tener medios suficientes para hacer frente a sus obligaciones. Para el caso de representar a una persona moral,declara que su representada se encuentra debidamente constituida y que cuenta con las facultades legales necesarias para comprometer a su representada de acuerdo con este CONTRATO y que las mismas no le han sido revocadas, modificadas o limitadas en forma alguna. En consecuencia, manifiesta que todos los datos precisados en el CONTRATO y la carátula del mismo son reales, veraces y comprobables. Del mismo modo, tiene contratada una cuenta de depósito a la vista (“Cuenta de Cheques”), cuyos datos han sido especificados en la carátula de este CONTRATO, aceptando que en ellas le sean abonadas o cargadas las cantidades que se generen de conformidad con el presente CONTRATO. Ha adquirido lícitamente un Terminal Punto de Venta (“TPV”) y desea celebrar el presente CONTRATO en los términos y condiciones que se pactarán en el clausulado del mismo.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">III. Declara el PROCESADOR que es una sociedad mercantil debidamente constituida de conformidad con las leyes de los Estados Unidos Mexicanos según consta en la Escritura Pública número 21,660, de fecha 7 de octubre de 2004, otorgada ante la fe del licenciado Héctor Manuel Cárdenas Villarreal, Notario Público número 201 de la Ciudad de México, debidamente inscrita en el Registro Público de Comercio de la Ciudad de México, bajo el Folio Mercantil número 327600 de fecha 3 de noviembre de 2004. Se encuentra alcorriente en el cumplimiento de sus obligaciones fiscales. Y que su domicilio se ubica en Corregidora 92,Colonia Miguel Hidalgo, C.P. 14260, en la Ciudad de México. Asimismo, ha obtenido el registro ante las entidades correspondientes para operar este modelode negocio conocido como facilitador de pagos, agregador o proveedor de servicios de pagos y cuenta con las afiliaciones bancarias para aceptar pagos con Tarjetas. Que con la aceptación expresa y de manera electrónica por parte de la EMPRESA a este Contrato, el PROCESADOR queda expresamente obligado de conformidad con el clausulado de este Contrato.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">II. El INTEGRADOR declara bajo protesta de decir verdad, que en caso de ser persona física es mayor de edad, con plena capacidad jurídica para obligarse de cargo a la cuenta CLABE de la EMPRESA y/o descuente de las cantidades pendientes a depositar el monto del quebranto sufrido.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">CUARTA. - OFERTA DE MEJORES CONDICIONES. LasPARTES reconocen que este Servicio ha sido diseñado para personas físicas o morales que se encuentran en proceso de expansión, y mantienen una actividad comercial reducida o eventual, razón por la cual, la aceptación de pagos con Tarjeta, será en consecuencia reducida, sin embargo, si la actividad comercial de la EMPRESA se incrementara a grado tal que los términos económicos de algún Banco Adquirente con quien el PROCESADOR mantenga una relación comercial, sean mejores para la EMPRESA, el PROCESADOR podrá notificar a la EMPRESA este hecho y ofrecerle la alternativa para que sea atendido directamente por dicha institución. En el caso de que la EMPRESA no acepte este ofrecimiento, el PROCESADOR se reserva el derecho de ajustar los límites mensuales de autorización y/o dar por terminado el presente CONTRATO, previa notificación correspondiente realizada con 15 días de anticipación a la fecha efectiva de cancelación.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">QUINTA. - PROHIBICIONES.Por acuerdo expreso de las PARTES, la EMPRESA no podrá: i)solicitar autorizaciones de cobro a Tarjetas cuando sepa o deba saber que son fraudulentas o ilegales; ii)almacenar información del Tarjetahabiente salvo el pagaré emitido por la TPV; iii) cobrar cantidad alguna por la aceptación de pagos con Tarjeta; iv) negar la aceptación de pago con Tarjeta; v) fraccionar el pago de bienes o servicios realizando más de un cargo a la Tarjeta para cubrir el importe de su venta.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">En el caso de que la EMPRESA realice cualquiera de las acciones que tiene prohibidas y por causa de esto el PROCESADOR sufra un quebranto económico, el PROCESADOR efectuará un cargo a la cuenta CLABE de la EMPRESA y/o descontará de las cantidades pendientes a depositar el monto del quebranto sufrido.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">SEXTA. - VIGENCIA. Las PARTES convienen que el presente CONTRATO entrará en vigor en su fecha de firma y estará vigente de manera indefinida. No obstante, el PROCESADOR o la EMPRESA podrán dar por terminado este CONTRATO de manera anticipada, sin responsabilidad para ellas, mediando aviso previo y por escrito a la otra Parte, con cuando menos 15 (quince) días naturales de anticipación a la fecha en que surta efectos la terminación debiendo las PARTES cumplir con todas las obligaciones pendientes a su cargo a la fecha efectiva de terminación.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">Sin perjuicio de lo anterior, en caso de que el Banco Adquirente, o los Titulares de Marca decidan suspender este modelo de procesamiento al PROCESADOR, los efectos de este convenio cesarán y subsistirá la obligación de las PARTES de dar cumplimento a las obligaciones pendientes entre ambos.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">SÉPTIMA. - RESCISIÓN. El PROCESADOR o la EMPRESA podrán rescindir el presente CONTRATO de pleno derecho y sin necesidad de declaración judicial, por incumplimiento de la otra en cualquiera de sus obligaciones derivadas del presente CONTRATO. Las PARTES acuerdan que en caso de incumplimiento total o parcial de cualquiera de las obligaciones derivadas del presente CONTRATO o de la ley aplicable, la PARTE afectada notificará a la otra PARTE, mediante simple comunicado por escrito de su incumplimiento, para que ésta cumpla con su obligación en un plazo que no exceda de 15 (quince) días hábiles contados a partir de la fecha en que sea recibido el comunicado del incumplimiento. Si transcurrido dicho plazo la parte que incumplió no corrige dicho incumplimiento, la parte afectada podrá dar por rescindido el presente CONTRATO. Asimismo, en el caso de rescisión, ambas PARTES se comprometen a pagar los daños y perjuicios que en su caso se ocasionen.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">OCTAVA. - CONFIDENCIALIDAD. La EMPRESA se obliga a que durante la vigencia de este CONTRATO y durante un plazo de 5 (cinco) años posteriores a su terminación, este instrumento legal. En consecuencia, la aceptación por medios electrónicos constituye la libre expresión de voluntad de las PARTES, así como la aseveración de la veracidad y buena fe de sus declaraciones, mismas que son el motivo determinante de este instrumento. Sin perjuicio de lo anterior, para el caso en que sea necesario ratificar el presente documento mediante la</p>
						</div>
						<div class="span4">
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">conformidad con este CONTRATO y tener medios suficientes para hacer frente a sus obligaciones. Para el caso de representar a una persona moral, declara que su representada se encuentra debidamente constituida y que cuenta con las facultades legales necesarias para comprometer a su representada de acuerdo con este CONTRATO y que las mismas no le han sido revocadas, modificadas o limitadas en forma alguna. En consecuencia, manifiesta que todos los datos precisados en el CONTRATO y la carátula del mismo son reales, veraces y comprobables. Del mismo modo, Celebró el Contrato señalado en la carátula con el hoy PROCESADOR y desea celebrar el presente CONTRATO en los términos y condiciones que se pactarán en el clausulado del mismo</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">DEFINICIONES</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">Las siguientes definiciones podrán ser usadas en singular o plural en este CONTRATO, manteniendo su significado.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">Banco Adquirente: Es la entidad Participante en Redes que provee servicios de pagos al PROCESADOR en las Redes de Pagos con Tarjeta.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">Banco Emisor: Es la entidad Participante en Redes que expide Tarjetas.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">Comisión: Cantidad descontada por el PROCESADOR del importe asociado a una autorización solicitada por la EMPRESA a través de la TPV. El importe de la Comisión se obtiene multiplicando el importe autorizado a través de la TPV por un porcentaje pactado entre las PARTES y adicionando a la cantidad resultante el Impuesto al Valor Agregado. La Comisión máxima puede ser consultada en el sitio web www.spug.com.mx.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">Costo de Depósito: Es el costo acordado entre las PARTES por depositar los importes asociados a las autorizaciones de pagos con Tarjetas a la cuenta CLABE de la EMPRESA. El Costo de Depósito máximo puede ser consultado en el sitio web www.spug.com.mx.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">Servicio: Consiste en transportar peticiones de autorización de cargo a una Tarjeta, originadas en una TPV, y depositar el importe asociado a esa autorización menos la Comisión y Costo de Depósito en la cuenta de cheques de la EMPRESA a través de su cuenta CLABE.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">3-D Secure: Es un protocolo de autenticación basado en un modelo de tres dominios donde el Dominio del Adquirente y el Dominio del Emisor son conectados a través del Dominio de Interoperabilidad, el propósito de este modelo es autenticar al tarjetahabiente duranteuna transacción de comercio electrónico (e-commerce), en adelante “3DS”.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">Tarjetahabiente: Es la persona física o moral que decide adquirir un bien o la prestación de un servicio a la EMPRESA y que utiliza como medio de pago una Tarjeta.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">Tarjeta: Se refiere indistintamente a una Tarjeta deCrédito o Tarjeta de Débito.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">Tarjeta de Crédito: Elemento plástico que identifica y asocia al Tarjetahabiente con la línea de crédito revolvente que le ha sido otorgada por un Banco Emisor.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">Tarjeta de Débito: Elemento plástico que identifica y/o asocia al titular de la misma con depósitos de dinero o cantidades que mantiene a su favor un Banco Emisor.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">Terminal Punto de Venta (TPV): Es un dispositivo electrónico adquirido por la EMPRESA, que se encuentra acoplado a la plataforma del PROCESADOR con el objeto de gestionar las peticiones de autorización de pago con Tarjeta.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">Titular de Marca: Participante en Redes que sea titular de una marca susceptible de utilizarse en las Tarjetas.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">CLÁUSULAS</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">PRIMERA.-OBJETO. El PROCESADOR se obliga a realizar a favor de la EMPRESA las siguientes actividades durante la vigencia del Contrato: i) prestar el Servicio a la EMPRESA; ii) depositar los importes asociados a las autorizaciones de pagos con Tarjetas menos la Comisión y Costo de Depósito pactados entre las PARTES, dentro de los 5 (cinco) días hábiles posteriores a la fecha de aprobación de la petición de cargo a la Tarjeta. Este plazo se verá interrumpido en tanto la EMPRESA no mantendrá en estricto secreto y hará que cualquier persona relacionada con ella, directa o indirectamente, mantenga en secreto y por lo tanto, no divulgue o revele, salvo para cumplir con sus obligaciones derivadas de este CONTRATO, cualquier información identificada como confidencial y/o propiedad del PROCESADOR.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">NOVENA. - PROTECCIÓN DE DATOS PERSONALES. Para el caso en que, con motivo de la realización del Objeto de este CONTRATO o la prestación del Servicio, la EMPRESA recabe Datos Personales de particulares, las PARTES acuerdan que la EMPRESA será el “Encargado” de los Datos Personales que de los particulares se recaben y de su Tratamiento, y el PROCESADOR fungirá como el “Responsable” de dichos Datos Personales con el objeto de poder darle Tratamiento a los mismos. Para efectos de lo establecido en la presente cláusula, se entenderá por los términos “Datos Personales”, “Responsable”, “Encargado” y “Tratamiento” la definición que para dichos términos establece la Ley Federal de Protección de Datos Personales en Posesión de los Particulares (en adelante “Ley de Datos Personales”) por lo que las PARTES asumen las obligaciones que la Ley de Datos Personales les impone como “Responsable” y “Encargado”, según corresponda. Asimismo, las PARTES acuerdan que el Tratamiento de los Datos Personales se hará de conformidad con el Aviso de Privacidad del PROCESADOR, mismo que será puesto a disposición de la EMPRESA en el sitio web www.spug.com.mx.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">DÉCIMA. - RELACIÓN ENTRE LAS PARTES. Este CONTRATO no pretende y nada de lo incluido en el mismo deberá interpretarse en el sentido de que se crea una relación de mandante y mandatario, comitente y comisionista, patrón/patrón sustituto y empleado, socio y asociado entre el PROCESADOR y la EMPRESA. Ninguna de las PARTES estará facultada para representar y obligar a la otra de manera alguna, y cada una de las PARTES será responsable exclusivamente de sus propios actos.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">DÉCIMA PRIMERA. - RESPONSABILIDAD LABORAL. Cada una de las PARTES será la única responsable de las obligaciones derivadas de las disposiciones legales y demás ordenamientos en materia de trabajo y de seguridad social, en su carácter de patrón del personal que cada una de ellas ocupa con motivo del cumplimiento del objeto del presente CONTRATO. Bajo protesta de decir verdad, las PARTES manifiestan que cuentan con los elementos propios y suficientes para cumplir con las obligaciones que se deriven de la relación con sus trabajadores. Por lo expuesto, las PARTES se obligan mutuamente a mantenerse en paz y a salvo de cualquier reclamación que pudiera surgir por conflicto o motivo de carácter laboral o de cualquier índole, de sus respectivos empleados.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">DÉCIMA SEGUNDA. - CASO FORTUITO O FUERZA MAYOR. Las PARTES no serán responsables por incumplimiento al CONTRATO por caso fortuito o fuerza mayor. Sin embargo, la existencia de estas circunstancias no da derecho a la PARTE afectada a no pagar oportunamente las cantidades o cumplir con sus obligaciones, previas a la aparición del caso fortuito o fuerza mayor.La parte afectada notificará a la otra por escrito, dentro del plazo de 10 (diez) días naturales siguientes a que se tenga conocimiento del caso fortuito o de fuerza mayor. En caso de subsistir tales circunstancias por más de 10 (diez) días naturales a partir la notificación, cualquiera de las PARTES podrá dar por terminado el presente contrato mediante aviso por escrito a la otra parte con 5 (cinco) días naturales de anticipación y sin necesidad de que medie resolución judicial para tal efecto.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">DÉCIMA TERCERA. -PROPIEDAD INTELECTUAL. La EMPRESA y el INTEGRADOR, expresamente reconocen que el PROCESADOR es el titular de los derechos de propiedad industrial y en materia de derechos de autor que serán utilizados en relación a la ejecución del Objeto de este CONTRATO, o bien, que cuenta con las firma autógrafa de las PARTES, estas se comprometen a realizar tal ratificación tan pronto como sea posible.
Jurisdicción. Para la resolución de cualquier disputa, controversia, reclamación o diferencia que surja de o se relacione con el presente CONTRATO, las PARTES se someten expresamente a la jurisdicción a los Tribunales de la Ciudad de México, México, renunciando a</p>
						</div>
						<div class="span4">
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">cumpla con los establecido en los incisos i) y v) de la Cláusula Segunda; iii) publicar el estado de cuenta de las transacciones realizadas por la EMPRESA, con periodicidad trimestral como máximo, en el sitio web www.spug.com.mx; iv) proporcionar asistencia telefónica a la EMPRESA a través del teléfono (55) 1500 9024 o por correo electrónico a través de la dirección de correo contacto@spug.com.mx; v) poner a disposición de la EMPRESA su Aviso de Privacidad en www.spug.com.mx.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">La EMPRESA acepta y reconoce expresamente que las actividades señaladas en esta Cláusula serán las únicas a las que el PROCESADOR se obliga, por tanto, libera al PROCESADOR de cualquier otra responsabilidad. Asimismo, el PROCESADOR no será responsable por la entrega de los bienes o servicios que la EMPRESA convenga con el Tarjetahabiente.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">SEGUNDA.-OBLIGACIONES DE LA EMPRESA. La EMPRESA con la ayuda en el proceso del INTEGRADOR, se obliga de conformidad con el presente Contrato a: i) registrar su TPV enviando físicamente la documentación que se especifica a la dirección del PROCESADOR o digitalmente a través del correo electrónico a contacto@spug.com.mx (CONTRATO firmado autógrafamente o con Firma electrónica avanzada; copia de identificación oficial con fotografía (IFE/INE, pasaporte o cartilla del servicio militar nacional) del representante legal o persona física que por su propio derecho firme el CONTRATO; copia del estado de cuenta bancario con antigüedad máxima de 3 (tres) meses y en donde aparezca la cuenta CLABE asociada, debiendo estar a nombre de la persona moral o persona física que sea PARTE en el presente CONTRATO); ii) cuando la TPV lo requiera; solicitar la firma del TARJETAHABIENTE en el pagaré impreso o mostrado en pantalla de la TPV y compararla contra la firma en la Tarjeta; iii) entregar al Tarjetahabiente copia del pagaré, ya sea impreso o enviándolo a su cuenta de correo electrónico; iv) custodiar los pagarés impresos por 180 (ciento ochenta) días; v) entregar el o los pagarés impresos en custodia dentro de las 24 (veinticuatro) horas siguientes de la solicitud por parte del PROCESADOR al correo electrónico de la EMPRESA; vi) exhibir los elementos de señalización de aceptación de pagos con Tarjeta proporcionadas por el PROCESADOR; vii) informar al PROCESADOR de cualquier cambio que sufra en su giro, su actividad, en sus representantes, en su domicilio o en su cuenta bancaria.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">TERCERA.–USO DE LA PLATAFORMA 3D SECURE.El PROCESADOR ofrece a la EMPRESA, el acceso al servicio para conectarse y hacer uso del protocolo “3DS”, a través del cual, la EMPRESA, podrá llevar a cabo la autenticación de los Tarjetahabientes y de esta forma, limitar su responsabilidad ante las posibles reclamaciones de los Tarjetahabientes sobre los cobros con tarjeta no presente.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">CUARTA.- ACEPTACIÓN DE RIESGO. La EMPRESA se obliga a operar con el protocolo “3DS”, la EMPRESA expresamente reconoce, acepta y deberá aceptar que El PROCESADOR, cargue a la cuenta que asignen el importe de cualquier contracargo que se presente, incluyendo sin limitar, aquellos que se originen ya sea por desconocimiento del tarjetahabiente con respecto al cargo, debido a que la o las mercancías, servicio o pago no cumplen con las condiciones o características ofrecidas al TARJETAHABIENTE o bien si la mercancía adquirida no fue entregada, el servicio no fue prestado oel pago realizado no fue debidamente aplicado.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">CASOS ESPECIALES.Encaso queexista una reclamación o aclaración sobre cualquier transacción, la EMPRESA y el INTEGRADOR tendrán la obligación en un plazo no mayor a 10 (diez) días, de aportar todos los elementos y evidencias de que el producto o servicio fue entregado; como pueden ser el pagaré impreso y firmado por el Tarjetahabiente entre otros. Cuando el PROCESADOR sufra un quebranto económico como consecuencia de la no entrega de la evidencia suficiente para comprobar la entrega del producto o servicio, la EMPRESA faculta al PROCESADOR para que efectúe un</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">licencias y/o autorizaciones correspondientes para poder usar y explotar dichos derechos, así como para sublicenciarlos a terceros (la “Propiedad Intelectual”), por lo que en este acto el PROCESADOR autoriza el uso y la reproducción parcial o total su Propiedad Intelectual con la finalidad de realizar el objeto de este CONTRATO. Lo anterior, no supone la concesión de cualquier tipo de derecho distinto o transferencia de activo alguno a la EMPRESA o al INTEGRADOR. La EMPRESA y el INTEGRADOR se obligan a sacar en paz y a salvo al PROCESADOR en relación a cualquier queja, reclamación, denuncia, demanda, procedimiento (administrativo, civil, penal, etc.) y/o acción de cualquier índole por el uso indebido o no autorizado de la Propiedad Intelectual, debiendo pagar cualquier daño o perjuicio que le ocasione al PROCESADOR, incluyendo sin limitar, gastos de abogados o cualesquier otros gastos que se puedan generar en conexión con lo estipulado en la presente cláusula. El incumplimiento a las disposiciones de esta Cláusula dará lugar a la terminación anticipada del Contrato, sin necesidad de declaración judicial.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">DÉCIMA CUARTA. - DISPOSICIONES GENERALES.
Reformas o Modificaciones. Las PARTES aceptan y reconocen que el presente CONTRATO podrá ser modificado en exclusiva y a entera discreción del PROCESADOR, dichos cambios serán reflejados en el sitio web del PROCESADOR, por lo cual la EMPRESA acepta que dichas modificaciones tendrán efecto una vez que la EMPRESA solicite una transacción de autorización con fecha posterior a la publicación que el PROCESADOR hiciere del nuevo CONTRATO en el sitio web www.spug.com.mx.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">Autorizaciones. La EMPRESA autoriza al PROCESADOR a incluir su nombre, denominación o razón social, nombre comercial y domicilio en su directorio de clientes. Asimismo, la EMPRESA autoriza al PROCESADOR a proporcionar su información antes precisada, así como el importe procesado a el Banco Adquirente y a los Titulares de Marca con los fines de evaluación, prevención y análisis.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">Notificaciones y Domicilios. Como domicilio para ser requeridas del cumplimiento de las obligaciones de este CONTRATO, o para cualquier otra cuestión relacionada con el mismo, las PARTES han señalado debidamente sus domicilios en las Declaraciones y Carátula de este CONTRATO. Todos los avisos y notificaciones que deban realizarse al PROCESADOR deberán hacerse en el domicilio señalado o bien a través de la siguiente dirección de correo contacto@mitec.com.mx; dichas notificaciones serán efectivas en la fecha de su entrega o de recepción electrónica.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">Autonomía de Disposiciones. La nulidad, invalidez, ilegalidad o cualquier vicio en cualquiera de las disposiciones del presente CONTRATO, solo afectará a dicha disposición, y por lo tanto no afectará a las demás disposiciones aquí pactadas, las cuales conservarán su fuerza obligatoria.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">Encabezados y Definiciones. Los encabezados o títulos de este CONTRATO se incluyen únicamente para facilitar la referencia, y no limitan o afectan de otra manera los términos y las condiciones de este CONTRATO. Asimismo, las definiciones se incluyen únicamente por conveniencia y economía, por lo que las palabras empleadas en las mismas no tienen significado técnico o jurídico alguno más allá de referirse y/o sustituir a los términos definidos.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">Aceptación electrónica. En este acto el PROCESADOR manifiesta su total y plena aceptación a las cláusulas de este CONTRATO, por lo que señala que su consentimiento se expresa electrónicamente al poner a disposición de la EMPRESA el presente CONTRATO para su aceptación, así como de manera inequívoca al realizar cualquier Servicio para la EMPRESA. Del mismo modo, las PARTES convienen que la aceptación electrónica a los términos y condiciones de este CONTRATO por parte de la EMPRESA supone su consentimiento total y sin lugar a dudas al contenido de cualquier otro fuero presente o futuro que pudiera corresponderle en virtud de sus domicilios presentes o futuros o por cualquier otra causa.</p>
							<p style="font-size:10px;line-height:12px;text-align:justify;margin-bottom:10px;">Derecho Aplicable. Este Contrato se regirá e interpretará por las leyes aplicables de los Estados Unidos Mexicanos.</p>
						</div>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label required>
							<p>{$lang.signature_place}</p>
							<input type="text" name="contract_place">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.signature_date}</p>
							<input type="date" value="' . Functions::get_current_date() . '" disabled>
						</label>
					</div>
				</div>
				<div class="span12">
					<div class="label">
						<label required>
							<p>{$lang.signature}</p>
							<div class="signature" id="contract_signature">
								<canvas></canvas>
								<div>
									<a data-action="clean_contract_signature"><i class="fas fa-trash"></i></a>
								</div>
							</div>
						</label>
					</div>
				</div>';
			}

			$mdl_edit_payment .=
			'					<div class="span12">
									<div class="buttons">
										<a class="delete" button-cancel><i class="fas fa-times"></i></a>
										<button type="submit" class="new"><i class="fas fa-check"></i></button>
									</div>
								</div>
							</div>
						</form>
					</main>
				</div>
			</section>';

			$replace = [
				'{$logotype}' => '{$path.uploads}' . $account['logotype'],
				'{$name}' => $account['name'],
				'{$token}' => strtoupper($account['token']),
				'{$fiscal_name}' => !empty($account['fiscal']['name']) ? $account['fiscal']['name'] : '{$lang.not_fiscal_name}',
				'{$fiscal_id}' => !empty($account['fiscal']['id']) ? $account['fiscal']['id'] : '{$lang.not_fiscal_id}',
				'{$location}' => (!empty($account['location']['lat']) AND !empty($account['location']['lng'])) ? '{$lang.activated}' : '{$lang.deactivated}',
				'{$lat_lng}' => (!empty($account['location']['lat']) AND !empty($account['location']['lng'])) ? $account['location']['lat'] . ' ' . $account['location']['lng'] : '{$lang.not_location}',
				'{$lat}' => !empty($account['location']['lat']) ? $account['location']['lat'] : '',
				'{$lng}' => !empty($account['location']['lng']) ? $account['location']['lng'] : '',
				'{$div_menu}' => $div_menu,
				'{$div_public_requests}' => $div_public_requests,
				'{$div_public_incidents}' => $div_public_incidents,
				'{$div_attention_times}' => $div_attention_times,
				'{$div_siteminder}' => $div_siteminder,
				'{$div_zaviapms}' => $div_zaviapms,
				'{$div_ambit}' => $div_ambit,
				'{$div_rappi}' => $div_rappi,
				'{$div_answer_surveys}' => $div_answer_surveys,
				'{$div_reviews_page}' => $div_reviews_page,
				'{$div_payment}' => $div_payment,
				'{$sms}' => $account['sms'],
				'{$whatsapp}' => $account['whatsapp'],
				'{$opt_countries}' => $opt_countries,
				'{$opt_times_zones}' => $opt_times_zones,
				'{$opt_currencies}' => $opt_currencies,
				'{$opt_languages}' => $opt_languages,
				'{$opt_ladas}' => $opt_ladas,
				'{$mdl_edit_myvox_menu_settings}' => $mdl_edit_myvox_menu_settings,
				'{$mdl_edit_rappi_settings}' => $mdl_edit_rappi_settings,
				'{$mdl_edit_myvox_request_settings}' => $mdl_edit_myvox_request_settings,
				'{$mdl_edit_myvox_incident_settings}' => $mdl_edit_myvox_incident_settings,
				'{$mdl_edit_voxes_attention_times_settings}' => $mdl_edit_voxes_attention_times_settings,
				'{$mdl_edit_myvox_survey_settings}' => $mdl_edit_myvox_survey_settings,
				'{$mdl_edit_reviews_settings}' => $mdl_edit_reviews_settings,
				'{$mdl_edit_payment}' => $mdl_edit_payment
			];

			$template = $this->format->replace($replace, $template);

			echo $template;
		}
	}
}
