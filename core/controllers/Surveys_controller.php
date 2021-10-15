<?php

defined('_EXEC') or die;

require_once 'vendor/autoload.php';

use Spipu\Html2Pdf\Html2Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Surveys_controller extends Controller
{
	private $lang;

	public function __construct()
	{
		parent::__construct();

		$this->lang = Session::get_value('account')['language'];
	}

	public function index()
	{
        if (Format::exist_ajax_request() == true)
		{
			if ($_POST['action'] == 'get_survey')
			{
				$query = $this->model->get_survey($_POST['id']);

                if (!empty($query))
                {
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

			if ($_POST['action'] == 'new_survey' OR $_POST['action'] == 'edit_survey')
			{
				$labels = [];

				if (!isset($_POST['name_es']) OR empty($_POST['name_es']))
					array_push($labels, ['name_es','']);

				if (!isset($_POST['name_en']) OR empty($_POST['name_en']))
					array_push($labels, ['name_en','']);

				if (!empty($_POST['report_status']))
				{
					if (!isset($_POST['report_days']) OR empty($_POST['report_days']))
						array_push($labels, ['report_days','']);

					if (!isset($_POST['report_time']) OR empty($_POST['report_time']))
						array_push($labels, ['report_time','']);

					if (!isset($_POST['report_email_1']) OR empty($_POST['report_email_1']))
						array_push($labels, ['report_email_1','']);
				}

				if (empty($labels))
				{
					if ($_POST['action'] == 'new_survey')
						$query = $this->model->new_survey($_POST);
					else if ($_POST['action'] == 'edit_survey')
						$query = $this->model->edit_survey($_POST);

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

			if ($_POST['action'] == 'deactivate_survey' OR $_POST['action'] == 'activate_survey' OR $_POST['action'] == 'delete_survey')
			{
				if ($_POST['action'] == 'deactivate_survey')
					$query = $this->model->deactivate_survey($_POST['id']);
				else if ($_POST['action'] == 'activate_survey')
					$query = $this->model->activate_survey($_POST['id']);
				else if ($_POST['action'] == 'delete_survey')
					$query = $this->model->delete_survey($_POST['id']);

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
		}
		else
		{
			$template = $this->view->render($this, 'index');

			define('_title', 'Guestvox | {$lang.surveys}');

			$tbl_surveys = '';

			foreach ($this->model->get_surveys() as $value)
			{
				$tbl_surveys .=
				'<div>
					<div class="datas">
						<div class="itm_1">
							' . (($value['main'] == true) ? '<h2>Encuesta predeterminada</h2>' : '') . '
							<h2>' . $value['token'] . ' | ' . $value['name'][$this->lang] . '</h2>
							<span>Solicitud de NPS: ' . (($value['nps'] == true) ? 'Si' : 'No') . '</span>
							<span>Solicitud de firma: ' . (($value['signature'] == true) ? 'Si' : 'No') . '</span>
							<span>Envío de reporte: ' . (($value['report']['status'] == true) ? 'Automático' : 'No') . '</span>
						</div>
						<div class="itm_2">
							<figure>
								<a href="{$path.uploads}' . $value['qr'] . '" download="' . $value['qr'] . '"><img src="{$path.uploads}' . $value['qr'] . '"></a>
							</figure>
						</div>
					</div>
					<div class="buttons">
						' . ((Functions::check_user_access(['{surveys_reports_print}']) == true) ? '<a class="big" href="/surveys/reports/print/' . $value['id'] . '"><i class="fas fa-bug"></i><span>Reportes</span></a>' : '') . '
						' . ((Functions::check_user_access(['{surveys_stats_view}']) == true) ? '<a class="big" href="/surveys/stats/' . $value['id'] . '"><i class="fas fa-chart-pie"></i><span>{$lang.stats}</span></a>' : '') . '
						' . ((Functions::check_user_access(['{surveys_answers_view}']) == true) ? '<a class="big" href="/surveys/answers/raters/' . $value['id'] . '"><i class="fas fa-star"></i><span>{$lang.answers}</span></a>' : '') . '
						' . ((Functions::check_user_access(['{surveys_answers_view}']) == true) ? '<a class="big" href="/surveys/answers/comments/' . $value['id'] . '"><i class="fas fa-comment-alt"></i><span>{$lang.comments}</span></a>' : '') . '
						<a class="big" data-action="copy_to_clipboard" data-copy="https://guestvox.com/' . Session::get_value('account')['path'] . '/survey/' . $value['token'] . '"><i class="fas fa-copy"></i><span>Copiar QR</span></a>
						' . ((Functions::check_user_access(['{surveys_questions_create}','{surveys_questions_update}','{surveys_questions_deactivate}','{surveys_questions_activate}','{surveys_questions_delete}']) == true) ? '<a class="big" href="/surveys/questions/' . $value['id'] . '"><i class="fas fa-ghost"></i><span>{$lang.questions}</span></a>' : '') . '
						' . ((Functions::check_user_access(['{surveys_questions_update}']) == true) ? '<a class="edit" data-action="edit_survey" data-id="' . $value['id'] . '"><i class="fas fa-pen"></i></a>' : '') . '
						' . ((Functions::check_user_access(['{surveys_questions_delete}']) == true) ? '<a class="delete" data-action="delete_survey" data-id="' . $value['id'] . '"><i class="fas fa-trash"></i></a>' : '') . '
						' . ((Functions::check_user_access(['{surveys_questions_deactivate}','{surveys_questions_activate}']) == true) ? '<a data-action="' . (($value['status'] == true) ? 'deactivate_survey' : 'activate_survey') . '" data-id="' . $value['id'] . '">' . (($value['status'] == true) ? '<i class="fas fa-ban"></i>' : '<i class="fas fa-check"></i>') . '</a>' : '') . '
					</div>
				</div>';
			}

			$replace = [
				'{$tbl_surveys}' => $tbl_surveys
			];

			$template = $this->format->replace($replace, $template);

			echo $template;
		}
	}

	public function reports($params)
	{
		$survey = $this->model->get_survey($params[1]);
		$average = $this->model->get_surveys_average($params[1]);
		$nps = $this->model->get_chart_data($params[1], 's2_chart');
		$answers = $this->model->get_surveys_answers($params[1], 'raters');

		if (Session::get_value('settings')['surveys']['reports']['filter']['channels'] == true AND Session::get_value('account')['type'] == 'hotel' AND Session::get_value('account')['zaviapms']['status'] == true)
		{
			$nationalities = $this->model->get_chart_data($params[1], 's4_chart');
			$nationalities_labels = explode(',', $nationalities['labels']);
			$nationalities_data = explode(',', $nationalities['datasets']['data']);

			$input_channels = $this->model->get_chart_data($params[1], 's5_chart');
			$input_channels_labels = explode(',', $input_channels['labels']);
			$input_channels_data = explode(',', $input_channels['datasets']['data']);

			$traveler_types = $this->model->get_chart_data($params[1], 's6_chart');
			$traveler_types_labels = explode(',', $traveler_types['labels']);
			$traveler_types_data = explode(',', $traveler_types['datasets']['data']);

			$age_groups = $this->model->get_chart_data($params[1], 's7_chart');
			$age_groups_labels = explode(',', $age_groups['labels']);
			$age_groups_data = explode(',', $age_groups['datasets']['data']);
		}

		if (Format::exist_ajax_request() == true)
		{
			if ($_POST['action'] == 'filter_surveys_reports')
			{
				$settings = Session::get_value('settings');

				$settings['surveys']['reports']['filter']['search'] = $_POST['search'];
				$settings['surveys']['reports']['filter']['period_type'] = $_POST['period_type'];
				$settings['surveys']['reports']['filter']['period_number'] = $_POST['period_number'];
				$settings['surveys']['reports']['filter']['started_date'] = ($_POST['search'] == 'period') ? Functions::get_past_date(Functions::get_current_date(), $_POST['period_number'], $_POST['period_type']) : $_POST['started_date'];
				$settings['surveys']['reports']['filter']['end_date'] = ($_POST['search'] == 'period') ? Functions::get_current_date() : $_POST['end_date'];
				$settings['surveys']['reports']['filter']['owner'] = $_POST['owner'];
				$settings['surveys']['reports']['filter']['rating'] = $_POST['rating'];
				$settings['surveys']['reports']['filter']['general'] = !empty($_POST['general']) ? true : false;
				$settings['surveys']['reports']['filter']['channels'] = !empty($_POST['channels']) ? true : false;
				$settings['surveys']['reports']['filter']['comments'] = !empty($_POST['comments']) ? true : false;

				Session::set_value('settings', $settings);

				Functions::environment([
					'status' => 'success'
				]);
			}

			if ($_POST['action'] == 'send_survey_report')
			{
				$labels = [];

				if (!isset($_POST['emails']) OR empty($_POST['emails']))
					array_push($labels, ['emails','']);

				if (empty($labels))
				{
					set_time_limit(100000000);

					$_POST['pdf'] = Session::get_value('account')['path'] . '_report_' . Functions::get_random(8) . '.pdf';
					$_POST['xlsx'] = Session::get_value('account')['path'] . '_report_' . Functions::get_random(8) . '.xlsx';

					$html2pdf = new Html2Pdf('P', 'A4', 'es', true, 'UTF-8', [0,0,0,0]);
					$writing =
					'<table style="width:100%;margin:0px;padding:20px 40px;border:0px;border-top:20px;border-color:#00a5ab;box-sizing:border-box;background-color:#fff;">
						<tr style="width:100%;margin:0px;padding:0px;border:0px;">
							<td style="width:20%;margin:0px;padding:0px;border:0px;vertical-align:middle;">
								<img style="width:100%;" src="' . PATH_UPLOADS .  Session::get_value('account')['logotype'] . '">
							</td>
							<td style="width:80%;margin:0px;padding:0px;border:0px;vertical-align:middle;">
								<table style="width:100%;margin:0px;padding:0px;border:0px;">
									<tr style="width:100%;margin:0px;padding:0px;border:0px;">
										<td style="width:100%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:24px;font-weight:600;text-transform:uppercase;text-align:right;color:#00a5ab;">' . Session::get_value('account')['name'] . '</td>
									</tr>
									<tr style="width:100%;margin:0px;padding:0px;border:0px;">
										<td style="width:100%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:18px;font-weight:400;text-transform:uppercase;text-align:right;color:#00a5ab;">Periodo del reporte: ' .  Session::get_value('settings')['surveys']['reports']['filter']['started_date'] . ' ' . Session::get_value('settings')['surveys']['reports']['filter']['end_date'] . '</td>
									</tr>
									<tr style="width:100%;margin:0px;padding:0px;border:0px;">
										<td style="width:100%;margin:0px;padding:0px 0px 5px 0px;border:0px;font-size:14px;font-weight:400;text-align:right;color:#00a5ab;">Reporte generado el ' . Functions::get_current_date()  . '</td>
									</tr>
									<tr style="width:100%;margin:0px;padding:0px;border:0px;">
										<td style="width:100%;margin:0px;padding:0px;border:0px;vertical-align:middle;text-align:right;">
											<img style="height:60px;" src="' . PATH_UPLOADS .  $survey['qr'] . '">
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>';

					if (Session::get_value('settings')['surveys']['reports']['filter']['general'] == true)
					{
						$writing .=
						'<table style="width:100%;margin:0px;padding:0px 40px 5px 40px;border:0px;box-sizing:border-box;background-color:#fff;">
							<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								<td style="width:100%;margin:0px;padding:0px 0px 0px 10px;border:0px;border-left:5px;border-color:#00a5ab;box-sizing:border-box;font-size:18px;font-weight:600;text-transform:uppercase;text-align:left;color:#24383f;">Información general</td>
							</tr>
						</table>
						<table style="width:100%;margin:0px;padding:0px 40px 20px 40px;border:0px;box-sizing:border-box;background-color:#fff;">
							<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								<td style="width:5%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">ID</td>
								<td style="width:10%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">Folio</td>
								<td style="width:40%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">Nombre</td>
								<td style="width:15%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">Tipo</td>
								<td style="width:10%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">Promedio</td>
								<td style="width:10%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">Respuestas</td>
								<td style="width:10%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">NPS</td>
							</tr>
							<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								<td style="width:5%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . $survey['id'] . '</td>
								<td style="width:10%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . $survey['token'] . '</td>
								<td style="width:40%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . $survey['name']['es'] . '</td>
								<td style="width:15%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . (($survey['main'] == true) ? 'Predeterminada' : 'Independiente') . '</td>
								<td style="width:10%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . $average . ' pts</td>
								<td style="width:10%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . count($answers) . '</td>
								<td style="width:10%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . (($survey['nps'] == true AND isset($nps['nps']) AND !empty($nps['nps'])) ? $nps['nps'] . '%' : 'No aplica') . '</td>
							</tr>
						</table>';
					}

					if (Session::get_value('settings')['surveys']['reports']['filter']['channels'] == true AND Session::get_value('account')['type'] == 'hotel' AND Session::get_value('account')['zaviapms']['status'] == true)
					{
						$writing .=
						'<table style="width:100%;margin:0px;padding:0px 40px 5px 40px;border:0px;box-sizing:border-box;background-color:#fff;">
							<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								<td style="width:100%;margin:0px;padding:0px 0px 0px 10px;border:0px;border-left:5px;border-color:#00a5ab;box-sizing:border-box;font-size:18px;font-weight:600;text-transform:uppercase;text-align:left;color:#24383f;">Canales</td>
							</tr>
						</table>
						<table style="width:100%;margin:0px;padding:0px 40px 20px 40px;border:0px;box-sizing:border-box;background-color:#fff;">
							<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								<td style="width:25%;margin:0px;padding:0px;border:0px;vertical-align:middle;">
									<table style="width:100%;margin:0px;padding:0px;border:0px;background-color:#fff;">
										<tr style="width:100%;margin:0px;padding:0px;border:0px;">
											<td style="width:60%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">Nacionalidades</td>
											<td style="width:40%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;"></td>
										</tr>';

						foreach ($nationalities_labels as $key => $value)
						{
							$writing .=
							'<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								<td style="width:60%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . str_replace("'", "", trim($value)) . '</td>
								<td style="width:40%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . $nationalities_data[$key] . ' Registros</td>
							</tr>';
						}

						$writing .=
						'			</table>
								</td>
								<td style="width:25%;margin:0px;padding:0px;border:0px;vertical-align:middle;">
									<table style="width:100%;margin:0px;padding:0px;border:0px;background-color:#fff;">
										<tr style="width:100%;margin:0px;padding:0px;border:0px;">
											<td style="width:60%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">Canales de entrada</td>
											<td style="width:40%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;"></td>
										</tr>';

						foreach ($input_channels_labels as $key => $value)
						{
							$writing .=
							'<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								<td style="width:60%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . str_replace("'", "", trim($value)) . '</td>
								<td style="width:40%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . $input_channels_data[$key] . ' Registros</td>
							</tr>';
						}

						$writing .=
						'			</table>
								</td>
								<td style="width:25%;margin:0px;padding:0px;border:0px;vertical-align:middle;">
									<table style="width:100%;margin:0px;padding:0px;border:0px;background-color:#fff;">
										<tr style="width:100%;margin:0px;padding:0px;border:0px;">
											<td style="width:60%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">Tipos de viajero</td>
											<td style="width:40%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;"></td>
										</tr>';

						foreach ($traveler_types_labels as $key => $value)
						{
							$writing .=
							'<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								<td style="width:60%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . str_replace("'", "", trim($value)) . '</td>
								<td style="width:40%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . $traveler_types_data[$key] . ' Registros</td>
							</tr>';
						}

						$writing .=
						'			</table>
								</td>
								<td style="width:25%;margin:0px;padding:0px;border:0px;vertical-align:middle;">
									<table style="width:100%;margin:0px;padding:0px;border:0px;background-color:#fff;">
										<tr style="width:100%;margin:0px;padding:0px;border:0px;">
											<td style="width:60%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">Grupos de edad</td>
											<td style="width:40%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;"></td>
										</tr>';

						foreach ($age_groups_labels as $key => $value)
						{
							$writing .=
							'<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								<td style="width:60%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . str_replace("'", "", trim($value)) . '</td>
								<td style="width:40%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . $age_groups_data[$key] . ' Registros</td>
							</tr>';
						}

						$writing .=
						'			</table>
								</td>
							</tr>
						</table>';
					}

					$writing .=
					'<table style="width:100%;margin:0px;padding:0px 40px 5px 40px;border:0px;box-sizing:border-box;background-color:#fff;">
						<tr style="width:100%;margin:0px;padding:0px;border:0px;">
							<td style="width:100%;margin:0px;padding:0px 0px 0px 10px;border:0px;border-left:5px;border-color:#00a5ab;box-sizing:border-box;font-size:18px;font-weight:600;text-transform:uppercase;text-align:left;color:#24383f;">Respuestas</td>
						</tr>
					</table>
					<table style="width:100%;margin:0px;padding:0px 40px 20px 40px;border:0px;box-sizing:border-box;background-color:#fff;">
						<tr style="width:100%;margin:0px;padding:0px;border:0px;">
							<td style="width:5%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">ID</td>
							<td style="width:10%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">Folio</td>
							<td style="width:35%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">Nombre</td>
							<td style="width:20%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">Fecha</td>
							<td style="width:20%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">Propietario</td>
							<td style="width:10%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">Promedio</td>
						</tr>';

					foreach ($answers as $value)
					{
						$writing .=
						'<tr style="width:100%;margin:0px;padding:0px;border:0px;">
							<td style="width:5%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . $value['id'] . '</td>
							<td style="width:10%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . $value['token'] . '</td>
							<td style="width:35%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . (!empty($value['owner']) ? ((Session::get_value('account')['type'] == 'hotel' AND !empty($query['reservation']['firstname']) AND !empty($query['reservation']['lastname'])) ? $query['reservation']['firstname'] . ' ' . $query['reservation']['lastname'] : 'Anónimo') : $value['firstname'] . ' ' . $value['lastname']) . '</td>
							<td style="width:20%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . Functions::get_formatted_date($value['date'], 'd.m.Y') . ' ' . Functions::get_formatted_hour($value['hour'], '+ hrs') . '</td>
							<td style="width:20%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . (!empty($value['owner']) ? $value['owner_name'][$this->lang] . (!empty($value['owner_number']) ? ' #' . $value['owner_number'] : '') : 'Sin propietario') . '</td>
							<td style="width:10%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . $value['average'] . ' pts</td>
						</tr>';

						if (Session::get_value('settings')['surveys']['reports']['filter']['comments'] == true AND !empty($value['comment']))
						{
							$writing .=
							'<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								<td style="width:5%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;"></td>
								<td style="width:10%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;"></td>
								<td style="width:35%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . $value['comment'] . '</td>
								<td style="width:20%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;"></td>
								<td style="width:20%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;"></td>
								<td style="width:10%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;"></td>
							</tr>';
						}

						$writing .=
						'<tr style="width:100%;margin:0px;padding:0px;border:0px;">
							<td style="width:5%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;"></td>
							<td style="width:10%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;"></td>
							<td style="width:35%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;">' . (!empty($value['email']) ? $value['email'] : 'Sin correo electrónico') . '</td>
							<td style="width:20%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;"></td>
							<td style="width:20%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;"></td>
							<td style="width:10%;margin:0px;padding:0px 0px 5px 0px;border:0px;box-sizing:border-box;font-size:10px;font-weight:400;text-align:left;color:#757575;"></td>
						</tr>';
					}

					$writing .=
					'</table>
					<table style="width:100%;margin:0px;padding:0px 40px;border:0px;box-sizing:border-box;background-color:#fff;">
						<tr style="width:100%;margin:0px;padding:0px;border:0px;">
							<td style="width:100%;margin:0px;padding:0px;border:0px;vertical-align:middle;font-size:12px;text-align:center;">
								Powered by <img style="height:13px;" src="' . PATH_IMAGES . '/logotype_color.png">
							</td>
						</tr>
					</table>';
					$html2pdf->writeHTML($writing);
					$html2pdf->output(PATH_UPLOADS . $_POST['pdf'], 'F');

					$spreadsheet = new Spreadsheet();

					$sheet_1 = $spreadsheet->getActiveSheet();

					$sheet_1->setTitle('Respuestas');

					$sheet_1->getColumnDimension('A')->setAutoSize(true);
					$sheet_1->getColumnDimension('B')->setAutoSize(true);
					$sheet_1->getColumnDimension('C')->setAutoSize(true);
					$sheet_1->getColumnDimension('D')->setAutoSize(true);
					$sheet_1->getColumnDimension('E')->setAutoSize(true);
					$sheet_1->getColumnDimension('F')->setAutoSize(true);
					$sheet_1->getColumnDimension('G')->setAutoSize(true);
					$sheet_1->getColumnDimension('H')->setAutoSize(true);

					$sheet_1->setCellValue('A1', 'ID');
					$sheet_1->setCellValue('B1', 'Folio');
					$sheet_1->setCellValue('C1', 'Nombre');
					$sheet_1->setCellValue('D1', 'Fecha');
					$sheet_1->setCellValue('E1', 'Hora');
					$sheet_1->setCellValue('F1', 'Propietario');
					$sheet_1->setCellValue('G1', 'Promedio');
					$sheet_1->setCellValue('H1', 'Comentarios');

					$sheet_1_count = 2;

					foreach ($answers as $key => $value)
					{
						$sheet_1->setCellValue('A' . $sheet_1_count, $value['id']);
						$sheet_1->setCellValue('B' . $sheet_1_count, $value['token']);
						$sheet_1->setCellValue('C' . $sheet_1_count, (!empty($value['owner']) ? ((Session::get_value('account')['type'] == 'hotel' AND !empty($query['reservation']['firstname']) AND !empty($query['reservation']['lastname'])) ? $query['reservation']['firstname'] . ' ' . $query['reservation']['lastname'] : 'Anónimo') : $value['firstname'] . ' ' . $value['lastname']));
						$sheet_1->setCellValue('D' . $sheet_1_count, Functions::get_formatted_date($value['date'], 'd.m.Y'));
						$sheet_1->setCellValue('E' . $sheet_1_count, Functions::get_formatted_hour($value['hour'], '+ hrs'));
						$sheet_1->setCellValue('F' . $sheet_1_count, (!empty($value['owner']) ? $value['owner_name'][$this->lang] . (!empty($value['owner_number']) ? ' #' . $value['owner_number'] : '') : 'Sin propietario'));
						$sheet_1->setCellValue('G' . $sheet_1_count, $value['average']);
						$sheet_1->setCellValue('H' . $sheet_1_count, ((Session::get_value('settings')['surveys']['reports']['filter']['comments'] == true AND !empty($value['comment'])) ? $value['comment'] : ''));

						$sheet_1_count = $sheet_1_count + 1;
					}

					if (Session::get_value('settings')['surveys']['reports']['filter']['general'] == true)
					{
						$sheet_2 = $spreadsheet->createSheet();

						$sheet_2->setTitle('Información general');

						$sheet_2->getColumnDimension('A')->setAutoSize(true);
						$sheet_2->getColumnDimension('B')->setAutoSize(true);
						$sheet_2->getColumnDimension('C')->setAutoSize(true);
						$sheet_2->getColumnDimension('D')->setAutoSize(true);
						$sheet_2->getColumnDimension('E')->setAutoSize(true);
						$sheet_2->getColumnDimension('F')->setAutoSize(true);
						$sheet_2->getColumnDimension('G')->setAutoSize(true);

						$sheet_2->setCellValue('A1', 'ID');
						$sheet_2->setCellValue('B1', 'Folio');
						$sheet_2->setCellValue('C1', 'Nombre');
						$sheet_2->setCellValue('D1', 'Tipo');
						$sheet_2->setCellValue('E1', 'Promedio');
						$sheet_2->setCellValue('F1', 'Respuestas');
						$sheet_2->setCellValue('G1', 'NPS');

						$sheet_2->setCellValue('A2', $survey['id']);
						$sheet_2->setCellValue('B2', $survey['token']);
						$sheet_2->setCellValue('C2', $survey['name']['es']);
						$sheet_2->setCellValue('D2', (($survey['main'] == true) ? 'Predeterminada' : 'Independiente'));
						$sheet_2->setCellValue('E2', $average . ' pts');
						$sheet_2->setCellValue('F2', count($answers));
						$sheet_2->setCellValue('G2', (($survey['nps'] == true AND isset($nps['nps']) AND !empty($nps['nps'])) ? $nps['nps'] . '%' : 'No aplica'));
					}

					if (Session::get_value('settings')['surveys']['reports']['filter']['channels'] == true AND Session::get_value('account')['type'] == 'hotel' AND Session::get_value('account')['zaviapms']['status'] == true)
					{
						$sheet_3 = $spreadsheet->createSheet();

						$sheet_3->setTitle('Nacionalidades');

						$sheet_3->getColumnDimension('A')->setAutoSize(true);
						$sheet_3->getColumnDimension('B')->setAutoSize(true);

						$sheet_3->setCellValue('A1', 'Nacionalidades');
						$sheet_3->setCellValue('B1', '');

						$sheet_3_count = 2;

						foreach ($nationalities_labels as $key => $value)
						{
							$sheet_3->setCellValue('A' . $sheet_3_count, str_replace("'", "", trim($value)));
							$sheet_3->setCellValue('B' . $sheet_3_count, $nationalities_data[$key] . ' Registros');

							$sheet_3_count = $sheet_3_count + 1;
						}

						// ---

						$sheet_4 = $spreadsheet->createSheet();

						$sheet_4->setTitle('Canales de entrada');

						$sheet_4->getColumnDimension('A')->setAutoSize(true);
						$sheet_4->getColumnDimension('B')->setAutoSize(true);

						$sheet_4->setCellValue('A1', 'Canales de entrada');
						$sheet_4->setCellValue('B1', '');

						$sheet_4_count = 2;

						foreach ($input_channels_labels as $key => $value)
						{
							$sheet_4->setCellValue('A' . $sheet_4_count, str_replace("'", "", trim($value)));
							$sheet_4->setCellValue('B' . $sheet_4_count, $input_channels_data[$key] . ' Registros');

							$sheet_4_count = $sheet_4_count + 1;
						}

						// ---

						$sheet_5 = $spreadsheet->createSheet();

						$sheet_5->setTitle('Tipos de viajero');

						$sheet_5->getColumnDimension('A')->setAutoSize(true);
						$sheet_5->getColumnDimension('B')->setAutoSize(true);

						$sheet_5->setCellValue('A1', 'Tipos de viajero');
						$sheet_5->setCellValue('B1', '');

						$sheet_5_count = 2;

						foreach ($traveler_types_labels as $key => $value)
						{
							$sheet_5->setCellValue('A' . $sheet_5_count, str_replace("'", "", trim($value)));
							$sheet_5->setCellValue('B' . $sheet_5_count, $traveler_types_data[$key] . ' Registros');

							$sheet_5_count = $sheet_5_count + 1;
						}

						// ---

						$sheet_6 = $spreadsheet->createSheet();

						$sheet_6->setTitle('Grupos de edad');

						$sheet_6->getColumnDimension('A')->setAutoSize(true);
						$sheet_6->getColumnDimension('B')->setAutoSize(true);

						$sheet_6->setCellValue('A1', 'Grupos de edad');
						$sheet_6->setCellValue('B1', '');

						$sheet_6_count = 2;

						foreach ($age_groups_labels as $key => $value)
						{
							$sheet_6->setCellValue('A' . $sheet_6_count, str_replace("'", "", trim($value)));
							$sheet_6->setCellValue('B' . $sheet_6_count, $age_groups_data[$key] . ' Registros');

							$sheet_6_count = $sheet_6_count + 1;
						}
					}

					// header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					// header('Content-Disposition: attachment; filename="' . $_POST['xlsx'] . '"');
					// header('Cache-Control: max-age=0');
					// header('Cache-Control: max-age=1');
					// header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
					// header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
					// header('Cache-Control: cache, must-revalidate');
					// header('Pragma: public');

					$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
					$writer->save('uploads/' . $_POST['xlsx']);
					// $writer->save('php://output');

					$mail = new Mailer(true);

	                try
	                {
	                    $mail->setFrom('noreply@guestvox.com', 'Guestvox');

						$_POST['emails'] = explode(',', $_POST['emails']);

						foreach ($_POST['emails'] as $value)
							$mail->addAddress(trim($value), Session::get_value('account')['name']);

						$mail->addAttachment(PATH_UPLOADS . $_POST['pdf']);
						$mail->addAttachment(PATH_UPLOADS . $_POST['xlsx']);
	                    $mail->Subject = 'Reporte de encuesta | ' . $survey['name']['es'] . ' | ' . Functions::get_current_date();
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
	                                            <img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/uploads/' . Session::get_value('account')['logotype'] . '">
	                                        </figure>
	                                    </td>
	                                </tr>
	                                <tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
	                                    <td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
	                                        <h4 style="width:100%;margin:0px 0px 10px 0px;padding:0px;font-size:18px;font-weight:600;text-align:center;color:#212121;">' . $survey['name']['es'] . '</h4>
	                                        <h6 style="width:100%;margin:0px;padding:0px;font-size:18px;font-weight:400;text-align:center;color:#757575;">Periodo del reporte: ' . Session::get_value('settings')['surveys']['reports']['filter']['started_date'] . ' al ' . Session::get_value('settings')['surveys']['reports']['filter']['end_date'] . '</h6>
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
	                    $mail->send();
	                }
	                catch (Exception $e) { }

					Functions::undoloader($_POST['pdf']);
					Functions::undoloader($_POST['xlsx']);

					Functions::environment([
						'status' => 'success',
						'message' => '{$lang.operation_success}'
					]);
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
			$template = $this->view->render($this, 'reports');

			define('_title', 'Guestvox | Encuestas | Reportes');

			$tbl_report = '';

			if (Session::get_value('settings')['surveys']['reports']['filter']['general'] == true)
			{
				$tbl_report .=
				'<table style="width:100%;margin-bottom:60px;">
					<thead>
						<tr>
							<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;">ID</th>
							<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;">Folio</th>
							<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;">Nombre</th>
							<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;">Tipo</th>
							<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;">Promedio general</th>
							<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;">Respuestas</th>
							<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;">NPS</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#757575;">' . $survey['id'] . '</td>
							<td style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#757575;">' . $survey['token'] . '</td>
							<td style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#757575;">' . $survey['name']['es'] . '</td>
							<td style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#757575;">' . (($survey['main'] == true) ? 'Predeterminada' : 'Independiente') . '</td>
							<td style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#757575;">' . $average . ' pts</td>
							<td style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#757575;">' . count($answers) . ' respuestas</td>
							<td style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#757575;">' . (($survey['nps'] == true AND isset($nps['nps']) AND !empty($nps['nps'])) ? $nps['nps'] . '%' : 'No aplica') . '</td>
						<tr>
					</tbody>
				</table>';
			}

			if (Session::get_value('settings')['surveys']['reports']['filter']['channels'] == true AND Session::get_value('account')['type'] == 'hotel' AND Session::get_value('account')['zaviapms']['status'] == true)
			{
				$tbl_report .=
				'<div class="row">
					<div class="span3" style="padding-right:20px;">
						<table style="width:100%;margin-bottom:60px;">
							<thead>
								<tr>
									<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;">Nacionalidades</th>
									<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;"></th>
								</tr>
							</thead>
							<tbody>';

				foreach ($nationalities_labels as $key => $value)
				{
					$tbl_report .=
					'<tr>
						<td style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#757575;">' . str_replace("'", "", trim($value)) . '</td>
						<td style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#757575;">' . $nationalities_data[$key] . ' registros</td>
					<tr>';
				}

				$tbl_report .=
				'			</tbody>
						</table>
					</div>
					<div class="span3" style="padding-right:20px;">
						<table style="width:100%;margin-bottom:60px;">
							<thead>
								<tr>
									<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;">Canales de entrada</th>
									<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;"></th>
								</tr>
							</thead>
							<tbody>';

				foreach ($input_channels_labels as $key => $value)
				{
					$tbl_report .=
					'<tr>
						<td style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#757575;">' . str_replace("'", "", trim($value)) . '</td>
						<td style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#757575;">' . $input_channels_data[$key] . ' registros</td>
					<tr>';
				}

				$tbl_report .=
				'			</tbody>
						</table>
					</div>
					<div class="span3" style="padding-right:20px;">
						<table style="width:100%;margin-bottom:60px;">
							<thead>
								<tr>
									<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;">Tipos de viajero</th>
									<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;"></th>
								</tr>
							</thead>
							<tbody>';

				foreach ($traveler_types_labels as $key => $value)
				{
					$tbl_report .=
					'<tr>
						<td style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#757575;">' . str_replace("'", "", trim($value)) . '</td>
						<td style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#757575;">' . $traveler_types_data[$key] . ' registros</td>
					<tr>';
				}

				$tbl_report .=
				'			</tbody>
						</table>
					</div>
					<div class="span3" style="padding-right:20px;">
						<table style="width:100%;margin-bottom:60px;">
							<thead>
								<tr>
									<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;">Grupos de edad</th>
									<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;"></th>
								</tr>
							</thead>
							<tbody>';

				foreach ($age_groups_labels as $key => $value)
				{
					$tbl_report .=
					'<tr>
						<td style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#757575;">' . str_replace("'", "", trim($value)) . '</td>
						<td style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#757575;">' . $age_groups_data[$key] . ' registros</td>
					<tr>';
				}

				$tbl_report .=
				'			</tbody>
						</table>
					</div>
				</div>';
			}

			$tbl_report .=
			'<table style="width:100%;">
				<thead>
					<tr>
						<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;">ID</th>
						<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;">Folio</th>
						<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;">Nombre</th>
						<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;">Fecha</th>
						<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;">Propietario</th>
						<th style="padding:10px;border-bottom:1px dashed #e0e0e0;text-align:left;color:#212121;">Promedio</th>
					</tr>
				</thead>
	            <tbody>';

			foreach ($answers as $value)
			{
				$tbl_report .=
				'<tr>
					<td style="padding:10px;' . (empty($value['comment']) ? 'border-bottom:1px dashed #e0e0e0;' : '') . 'text-align:left;color:#757575;">' . $value['id'] . '</td>
					<td style="padding:10px;' . (empty($value['comment']) ? 'border-bottom:1px dashed #e0e0e0;' : '') . 'text-align:left;color:#757575;">' . $value['token'] . '</td>
					<td style="max-width:200px;padding:10px;' . (empty($value['comment']) ? 'border-bottom:1px dashed #e0e0e0;' : '') . 'text-align:left;color:#757575;">' . (!empty($value['owner']) ? ((Session::get_value('account')['type'] == 'hotel' AND !empty($query['reservation']['firstname']) AND !empty($query['reservation']['lastname'])) ? $query['reservation']['firstname'] . ' ' . $query['reservation']['lastname'] : '{$lang.not_name}') : $value['firstname'] . ' ' . $value['lastname']) . '</td>
					<td style="padding:10px;' . (empty($value['comment']) ? 'border-bottom:1px dashed #e0e0e0;' : '') . 'text-align:left;color:#757575;">' . Functions::get_formatted_date($value['date'], 'd.m.Y') . ' ' . Functions::get_formatted_hour($value['hour'], '+ hrs') . '</td>
					<td style="padding:10px;' . (empty($value['comment']) ? 'border-bottom:1px dashed #e0e0e0;' : '') . 'text-align:left;color:#757575;">' . (!empty($value['owner']) ? $value['owner_name'][$this->lang] . (!empty($value['owner_number']) ? ' #' . $value['owner_number'] : '') : 'Sin propietario') . '</td>
					<td style="padding:10px;' . (empty($value['comment']) ? 'border-bottom:1px dashed #e0e0e0;' : '') . 'text-align:left;color:#757575;">' . $value['average'] . ' pts</td>
				<tr>';

				if (Session::get_value('settings')['surveys']['reports']['filter']['comments'] == true AND !empty($value['comment']))
				{
					$tbl_report .=
					'<tr>
						<td style="padding:0px 10px 10px 10px;' . (!empty($value['comment']) ? 'border-bottom:1px dashed #e0e0e0;' : '') . 'text-align:left;color:#bdbdbd;"></td>
						<td style="padding:0px 10px 10px 10px;' . (!empty($value['comment']) ? 'border-bottom:1px dashed #e0e0e0;' : '') . 'text-align:left;color:#bdbdbd;"></td>
						<td style="max-width:200px;padding:0px 10px 10px 10px;' . (!empty($value['comment']) ? 'border-bottom:1px dashed #e0e0e0;' : '') . 'text-align:left;color:#bdbdbd;">' . $value['comment'] . '</td>
						<td style="padding:0px 10px 10px 10px;' . (!empty($value['comment']) ? 'border-bottom:1px dashed #e0e0e0;' : '') . 'text-align:left;color:#bdbdbd;"></td>
						<td style="padding:0px 10px 10px 10px;' . (!empty($value['comment']) ? 'border-bottom:1px dashed #e0e0e0;' : '') . 'text-align:left;color:#bdbdbd;"></td>
						<td style="padding:0px 10px 10px 10px;' . (!empty($value['comment']) ? 'border-bottom:1px dashed #e0e0e0;' : '') . 'text-align:left;color:#bdbdbd;"></td>
					<tr>';
				}

				$tbl_report .=
				'<tr>
					<td style="padding:0px 10px 10px 10px;' . (!empty($value['comment']) ? 'border-bottom:1px dashed #e0e0e0;' : '') . 'text-align:left;color:#bdbdbd;"></td>
					<td style="padding:0px 10px 10px 10px;' . (!empty($value['comment']) ? 'border-bottom:1px dashed #e0e0e0;' : '') . 'text-align:left;color:#bdbdbd;"></td>
					<td style="max-width:200px;padding:0px 10px 10px 10px;' . (!empty($value['email']) ? 'border-bottom:1px dashed #e0e0e0;' : '') . 'text-align:left;color:#bdbdbd;">' . (!empty($value['email']) ? $value['email'] : 'Sin correo electrónico') . '</td>
					<td style="padding:0px 10px 10px 10px;' . (!empty($value['comment']) ? 'border-bottom:1px dashed #e0e0e0;' : '') . 'text-align:left;color:#bdbdbd;"></td>
					<td style="padding:0px 10px 10px 10px;' . (!empty($value['comment']) ? 'border-bottom:1px dashed #e0e0e0;' : '') . 'text-align:left;color:#bdbdbd;"></td>
					<td style="padding:0px 10px 10px 10px;' . (!empty($value['comment']) ? 'border-bottom:1px dashed #e0e0e0;' : '') . 'text-align:left;color:#bdbdbd;"></td>
				<tr>';
			}

			$tbl_report .=
	        '    </tbody>
	        </table>';

			$opt_owners = '';

			foreach ($this->model->get_owners('survey') as $value)
				$opt_owners .= '<option value="' . $value['id'] . '" ' . ((Session::get_value('settings')['surveys']['reports']['filter']['owner'] == $value['id']) ? 'selected' : '') . '>' . $value['name'][$this->lang] . (!empty($value['number']) ? ' #' . $value['number'] : '') . '</option>';

			$replace = [
				'{$menu_focus}' => $params[0],
				'{$started_date}' => Session::get_value('settings')['surveys']['reports']['filter']['started_date'],
				'{$end_date}' => Session::get_value('settings')['surveys']['reports']['filter']['end_date'],
				'{$qr}' => $survey['qr'],
				'{$tbl_report}' => $tbl_report,
				'{$opt_owners}' => $opt_owners
			];

			$template = $this->format->replace($replace, $template);

			echo $template;
		}
	}

	public function stats($params)
	{
		if (Format::exist_ajax_request() == true)
		{
			if ($_POST['action'] == 'filter_surveys_stats')
			{
				$settings = Session::get_value('settings');

				$settings['surveys']['reports']['filter']['started_date'] = $_POST['started_date'];
				$settings['surveys']['reports']['filter']['end_date'] = $_POST['end_date'];
				$settings['surveys']['reports']['filter']['owner'] = $_POST['owner'];

				Session::set_value('settings', $settings);

				Functions::environment([
					'status' => 'success'
				]);
			}
		}
		else
		{
			define('_title', 'Guestvox | {$lang.survey} | {$lang.stats}');

			$template = $this->view->render($this, 'stats');

			$surveys_average = $this->model->get_surveys_average($params[0]);

			$h2_surveys_average = '';

			if ($surveys_average >= 0 AND $surveys_average < 1.8)
				$h2_surveys_average = '<h2 class="one">' . $surveys_average . '</h2>';
			else if ($surveys_average >= 1.8 AND $surveys_average < 2.8)
				$h2_surveys_average = '<h2 class="two">' . $surveys_average . '</h2>';
			else if ($surveys_average >= 2.8 AND $surveys_average < 3.8)
				$h2_surveys_average = '<h2 class="three">' . $surveys_average . '</h2>';
			else if ($surveys_average >= 3.8 AND $surveys_average < 4.8)
				$h2_surveys_average = '<h2 class="four">' . $surveys_average . '</h2>';
			else if ($surveys_average >= 4.8 AND $surveys_average <= 5)
				$h2_surveys_average = '<h2 class="five">' . $surveys_average . '</h2>';

			$spn_surveys_average =
			'<span>
				' . (($surveys_average >= 0 AND $surveys_average < 1.8) ? '<i class="fas fa-sad-cry one"></i>' : '<i class="far fa-sad-cry"></i>') . '
				' . (($surveys_average >= 1.8 AND $surveys_average < 2.8) ? '<i class="fas fa-frown two"></i>' : '<i class="far fa-frown"></i>') . '
				' . (($surveys_average >= 2.8 AND $surveys_average < 3.8) ? '<i class="fas fa-meh-rolling-eyes three"></i>' : '<i class="far fa-meh-rolling-eyes"></i>') . '
				' . (($surveys_average >= 3.8 AND $surveys_average < 4.8) ? '<i class="fas fa-smile four"></i>' : '<i class="far fa-smile"></i>') . '
				' . (($surveys_average >= 4.8 AND $surveys_average <= 5) ? '<i class="fas fa-grin-stars five"></i>' : '<i class="far fa-grin-stars"></i>') . '
			</span>';

			$opt_owners = '';

			foreach ($this->model->get_owners('survey') as $value)
				$opt_owners .= '<option value="' . $value['id'] . '" ' . ((Session::get_value('settings')['surveys']['reports']['filter']['owner'] == $value['id']) ? 'selected' : '') . '>' . $value['name'][$this->lang] . (!empty($value['number']) ? ' #' . $value['number'] : '') . '</option>';

			$replace = [
				'{$h2_surveys_average}' => $h2_surveys_average,
				'{$spn_surveys_average}' => $spn_surveys_average,
				'{$surveys_porcentage_one}' => $this->model->get_surveys_percentage($params[0], 'one'),
				'{$surveys_porcentage_two}' => $this->model->get_surveys_percentage($params[0], 'two'),
				'{$surveys_porcentage_tree}' => $this->model->get_surveys_percentage($params[0], 'tree'),
				'{$surveys_porcentage_four}' => $this->model->get_surveys_percentage($params[0], 'four'),
				'{$surveys_porcentage_five}' => $this->model->get_surveys_percentage($params[0], 'five'),
				'{$surveys_count_total}' => $this->model->get_surveys_count($params[0], 'total'),
				'{$surveys_count_today}' => $this->model->get_surveys_count($params[0], 'today'),
				'{$surveys_count_week}' => $this->model->get_surveys_count($params[0], 'week'),
				'{$surveys_count_month}' => $this->model->get_surveys_count($params[0], 'month'),
				'{$surveys_count_year}' => $this->model->get_surveys_count($params[0], 'year'),
				'{$opt_owners}' => $opt_owners,
				'{$return_btn}' => !empty($params) ? '<a href="/surveys" class="big delete"><i class="fas fa-times"></i></a>' : '',
				'{$chart_params}' => !empty($params) ? '/' . $params[0] : ''
			];

			$template = $this->format->replace($replace, $template);

			echo $template;
		}
	}

	public function charts($params)
	{
		header('Content-Type: application/javascript');

		$s1_chart_data = $this->model->get_chart_data($params[0], 's1_chart');
		$s2_chart_data = $this->model->get_chart_data($params[0], 's2_chart');

		if (Session::get_value('account')['type'] == 'hotel' AND Session::get_value('account')['zaviapms']['status'] == true)
		{
			$s4_chart_data = $this->model->get_chart_data($params[0], 's4_chart');
			$s5_chart_data = $this->model->get_chart_data($params[0], 's5_chart');
			$s6_chart_data = $this->model->get_chart_data($params[0], 's6_chart');
			$s7_chart_data = $this->model->get_chart_data($params[0], 's7_chart');
		}

		$js =
		"'use strict';

		var s1_chart = {
	        type: 'pie',
	        data: {
				labels: [
	                " . $s1_chart_data['labels'] . "
	            ],
				datasets: [{
					data: [
	                    " . $s1_chart_data['datasets']['data'] . "
	                ],
	                backgroundColor: [
	                    " . $s1_chart_data['datasets']['colors'] . "
	                ]
	            }]
	        },
	        options: {
				title: {
					display: true,
					text: '" . Languages::charts('s1_chart')[$this->lang] . "'
				},
				legend: {
					display: false,
					position: 'left'
				},
	            responsive: true
            }
        };

		var s2_chart = {
	        type: 'doughnut',
	        data: {
				labels: [
	                " . $s2_chart_data['labels'] . "
	            ],
				datasets: [{
					data: [
	                    " . $s2_chart_data['datasets']['data'] . "
	                ],
	                backgroundColor: [
	                    " . $s2_chart_data['datasets']['colors'] . "
	                ]
	            }]
	        },
	        options: {
				title: {
					display: true,
					text: '" . Languages::charts('s2_chart')[$this->lang] . "'
				},
				legend: {
					display: true,
					position: 'left'
				},
	            responsive: true
            }
        };

		document.getElementById('nps').innerHTML  = '" . $s2_chart_data['nps'] . "%';";

		if (Session::get_value('account')['type'] == 'hotel' AND Session::get_value('account')['zaviapms']['status'] == true)
		{
			$js .=
			"var s4_chart = {
		        type: 'pie',
		        data: {
					labels: [
		                " . $s4_chart_data['labels'] . "
		            ],
					datasets: [{
						data: [
		                    " . $s4_chart_data['datasets']['data'] . "
		                ],
		                backgroundColor: [
		                    " . $s4_chart_data['datasets']['colors'] . "
		                ]
		            }]
		        },
		        options: {
					title: {
						display: true,
						text: '" . Languages::charts('s4_chart')[$this->lang] . "'
					},
					legend: {
						display: true,
						position: 'left'
					},
		            responsive: true
	            }
	        };

			var s5_chart = {
		        type: 'pie',
		        data: {
					labels: [
		                " . $s5_chart_data['labels'] . "
		            ],
					datasets: [{
						data: [
		                    " . $s5_chart_data['datasets']['data'] . "
		                ],
		                backgroundColor: [
		                    " . $s5_chart_data['datasets']['colors'] . "
		                ]
		            }]
		        },
		        options: {
					title: {
						display: true,
						text: '" . Languages::charts('s5_chart')[$this->lang] . "'
					},
					legend: {
						display: true,
						position: 'left'
					},
		            responsive: true
	            }
	        };

			var s6_chart = {
		        type: 'pie',
		        data: {
					labels: [
		                " . $s6_chart_data['labels'] . "
		            ],
					datasets: [{
						data: [
		                    " . $s6_chart_data['datasets']['data'] . "
		                ],
		                backgroundColor: [
		                    " . $s6_chart_data['datasets']['colors'] . "
		                ]
		            }]
		        },
		        options: {
					title: {
						display: true,
						text: '" . Languages::charts('s6_chart')[$this->lang] . "'
					},
					legend: {
						display: true,
						position: 'left'
					},
		            responsive: true
	            }
	        };

			var s7_chart = {
		        type: 'pie',
		        data: {
					labels: [
		                " . $s7_chart_data['labels'] . "
		            ],
					datasets: [{
						data: [
		                    " . $s7_chart_data['datasets']['data'] . "
		                ],
		                backgroundColor: [
		                    " . $s7_chart_data['datasets']['colors'] . "
		                ]
		            }]
		        },
		        options: {
					title: {
						display: true,
						text: '" . Languages::charts('s7_chart')[$this->lang] . "'
					},
					legend: {
						display: true,
						position: 'left'
					},
		            responsive: true
	            }
	        };";
		}

		$js .=
		"window.onload = function()
		{
			s1_chart = new Chart(document.getElementById('s1_chart').getContext('2d'), s1_chart);
			s2_chart = new Chart(document.getElementById('s2_chart').getContext('2d'), s2_chart);";

		if (Session::get_value('account')['type'] == 'hotel' AND Session::get_value('account')['zaviapms']['status'] == true)
		{
			$js .=
			"s4_chart = new Chart(document.getElementById('s4_chart').getContext('2d'), s4_chart);
			s5_chart = new Chart(document.getElementById('s5_chart').getContext('2d'), s5_chart);
			s6_chart = new Chart(document.getElementById('s6_chart').getContext('2d'), s6_chart);
			s7_chart = new Chart(document.getElementById('s7_chart').getContext('2d'), s7_chart);";
		}

		$js .= "};";

		$js = trim(str_replace(array("\t\t\t"), '', $js));

		echo $js;
	}

	public function answers($params)
	{
		$survey = $this->model->get_survey($params[1]);

		if (!empty($survey))
		{
			if (Format::exist_ajax_request() == true)
			{
				if ($_POST['action'] == 'filter_surveys_answers')
				{
					$settings = Session::get_value('settings');

					$settings['surveys']['reports']['filter']['started_date'] = $_POST['started_date'];
					$settings['surveys']['reports']['filter']['end_date'] = $_POST['end_date'];
					$settings['surveys']['reports']['filter']['started_hour'] = $_POST['started_hour'];
					$settings['surveys']['reports']['filter']['end_hour'] = $_POST['end_hour'];
					$settings['surveys']['reports']['filter']['owner'] = $_POST['owner'];
					$settings['surveys']['reports']['filter']['rating'] = $_POST['rating'];

					Session::set_value('settings', $settings);

					Functions::environment([
						'status' => 'success'
					]);
				}

				if ($_POST['action'] == 'preview_survey_answer')
				{
					$query = $this->model->get_survey_answer($_POST['id']);

					if (!empty($query))
					{
						$html = '<div class="rating">';

						if ($query['average'] < 2)
							$html .= '<span class="bad"><i class="fas fa-star"></i>' . $query['average'] . '</span>';
						else if ($query['average'] >= 2 AND $query['average'] < 4)
							$html .= '<span class="medium"><i class="fas fa-star"></i>' . $query['average'] . '</span>';
						else if ($query['average'] >= 4)
							$html .= '<span class="good"><i class="fas fa-star"></i>' . $query['average'] . '</span>';

						$html .=
						'</div>
						<div class="datas">
							<span>' . $query['token'] . '</span>
							<h2>' . (!empty($query['owner']) ? ((Session::get_value('account')['type'] == 'hotel' AND !empty($query['reservation']['firstname']) AND !empty($query['reservation']['lastname'])) ? $query['reservation']['firstname'] . ' ' . $query['reservation']['lastname'] : '{$lang.not_name}') : $query['firstname'] . ' ' . $query['lastname']) . '</h2>
							<span><i class="fas fa-shapes"></i>' . (!empty($query['owner']) ? $query['owner_name'][$this->lang] . (!empty($query['owner_number']) ? ' #' . $query['owner_number'] : '') : 'Sin propietario') . '</span>
							<span><i class="fas fa-calendar-alt"></i>' . Functions::get_formatted_date($query['date'], 'd.m.Y') . ' ' . Functions::get_formatted_hour($query['hour'], '+ hrs') . '</span>
						</div>';

						if (Session::get_value('account')['type'] == 'hotel' AND !empty($query['owner']))
						{
							$html .=
							'<div class="reservation">
								<span><strong>{$lang.room}:</strong> ' . $query['owner_name'][$this->lang] . (!empty($query['owner_number']) ? ' #' . $query['owner_number'] : '') . '</span>
								<span><strong>{$lang.guest}:</strong> ' . ((!empty($query['reservation']['firstname']) AND !empty($query['reservation']['lastname'])) ? $query['reservation']['firstname'] . ' ' . $query['reservation']['lastname'] : '{$lang.not_name}') . '</span>
								<span><strong>{$lang.reservation_number}:</strong> ' . (!empty($query['reservation']['reservation_number']) ? $query['reservation']['reservation_number'] : '{$lang.not_reservation_number}') . '</span>
								<span><strong>{$lang.check_in}:</strong> ' . (!empty($query['reservation']['check_in']) ? $query['reservation']['check_in'] : '{$lang.not_check_in}') . '</span>
								<span><strong>{$lang.check_out}:</strong> ' . (!empty($query['reservation']['check_out']) ? $query['reservation']['check_out'] : '{$lang.not_check_out}') . '</span>
								<span><strong>{$lang.nationality}:</strong> ' . (!empty($query['reservation']['nationality']) ? $query['reservation']['nationality'] : '{$lang.not_nationality}') . '</span>
								<span><strong>{$lang.input_channel}:</strong> ' . (!empty($query['reservation']['input_channel']) ? $query['reservation']['input_channel'] : '{$lang.not_input_channel}') . '</span>
								<span><strong>{$lang.traveler_type}:</strong> ' . (!empty($query['reservation']['traveler_type']) ? $query['reservation']['traveler_type'] : '{$lang.not_traveler_type}') . '</span>
								<span><strong>{$lang.age_group}:</strong> ' . (!empty($query['reservation']['age_group']) ? $query['reservation']['age_group'] : '{$lang.not_age_group}') . '</span>
							</div>';
						}

						$html .=
						'<div class="comment">
							<p>' . (!empty($query['comment']) ? '<i class="fas fa-quote-left"></i>' . $query['comment'] . '<i class="fas fa-quote-right"></i>' : '{$lang.not_commentary}') . '</p>
						</div>
						<div class="tbl_stl_5">';

						foreach ($this->model->get_surveys_questions($params[1]) as $value)
						{
							$html .=
							'<div>
								<div data-level="1">
									<h2>' . $value['name'][$this->lang] . '</h2>
									<div class="' . $value['type'] . '">';

							if ($value['type'] == 'nps')
							{
								$html .=
								'<div>
									<label class="' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '1') ? 'focus' : '') . '"><i>1</i><input type="radio" disabled></label>
									<label class="' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '2') ? 'focus' : '') . '"><i>2</i><input type="radio" disabled></label>
									<label class="' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '3') ? 'focus' : '') . '"><i>3</i><input type="radio" disabled></label>
									<label class="' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '4') ? 'focus' : '') . '"><i>4</i><input type="radio" disabled></label>
									<label class="' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '5') ? 'focus' : '') . '"><i>5</i><input type="radio" disabled></label>
								</div>
								<div>
									<label class="' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '6') ? 'focus' : '') . '"><i>6</i><input type="radio" disabled></label>
									<label class="' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '7') ? 'focus' : '') . '"><i>7</i><input type="radio" disabled></label>
									<label class="' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '8') ? 'focus' : '') . '"><i>8</i><input type="radio" disabled></label>
									<label class="' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '9') ? 'focus' : '') . '"><i>9</i><input type="radio" disabled></label>
									<label class="' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '10') ? 'focus' : '') . '"><i>10</i><input type="radio" disabled></label>
								</div>';
							}
							else if ($value['type'] == 'open')
								$html .= '<input type="text" value="' . (array_key_exists($value['id'], $query['values']) ? $query['values'][$value['id']] : '') . '" disabled>';
							else if ($value['type'] == 'rate')
							{
								$html .=
								'<label class="' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '1') ? 'focus' : '') . '"><i class="fas fa-sad-cry"></i><input type="radio" disabled></label>
								<label class="' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '2') ? 'focus' : '') . '"><i class="fas fa-frown"></i><input type="radio" disabled></label>
								<label class="' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '3') ? 'focus' : '') . '"><i class="fas fa-meh-rolling-eyes"></i><input type="radio" disabled></label>
								<label class="' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '4') ? 'focus' : '') . '"><i class="fas fa-smile"></i><input type="radio" disabled></label>
								<label class="' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '5') ? 'focus' : '') . '"><i class="fas fa-grin-stars"></i><input type="radio" disabled></label>';
							}
							else if ($value['type'] == 'twin')
							{
								$html .=
								'<label class="' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == 'yes') ? 'focus' : '') . '"><i class="fas fa-thumbs-up"></i><input type="radio" disabled></label>
								<label class="' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == 'not') ? 'focus' : '') . '"><i class="fas fa-thumbs-down"></i><input type="radio" disabled></label>';
							}
							else if ($value['type'] == 'check')
							{
								$html .= '<div class="checkboxes stl_3">';

								foreach ($value['values'] as $subvalue)
								{
									$html .=
									'<div>
										<input type="checkbox" ' . ((array_key_exists($value['id'], $query['values']) AND in_array($subvalue['token'], $query['values'][$value['id']])) ? 'checked' : '') . ' disabled>
										<span>' . $subvalue[$this->lang] . '</span>
									</div>';
								}

								$html .= '</div>';
							}

							$html .=
							'	</div>
							</div>';

							foreach ($this->model->get_surveys_questions($params[1], $value['id']) as $subvalue)
							{
								if ($subvalue['type'] == 'open' OR $subvalue['type'] == 'rate' OR $subvalue['type'] == 'twin' OR $subvalue['type'] == 'check')
								{
									$html .=
									'<div data-level="2">
										<h2>' . $subvalue['name'][$this->lang] . '</h2>
										<div class="' . $subvalue['type'] . '">';

									if ($subvalue['type'] == 'open')
										$html .= '<input type="text" value="' . (array_key_exists($subvalue['id'], $query['values']) ? $query['values'][$subvalue['id']] : '') . '" disabled>';
									else if ($subvalue['type'] == 'rate')
									{
										$html .=
										'<label class="' . ((array_key_exists($subvalue['id'], $query['values']) AND $query['values'][$subvalue['id']] == '1') ? 'focus' : '') . '"><i class="fas fa-sad-cry"></i><input type="radio" disabled></label>
										<label class="' . ((array_key_exists($subvalue['id'], $query['values']) AND $query['values'][$subvalue['id']] == '2') ? 'focus' : '') . '"><i class="fas fa-frown"></i><input type="radio" disabled></label>
										<label class="' . ((array_key_exists($subvalue['id'], $query['values']) AND $query['values'][$subvalue['id']] == '3') ? 'focus' : '') . '"><i class="fas fa-meh-rolling-eyes"></i><input type="radio" disabled></label>
										<label class="' . ((array_key_exists($subvalue['id'], $query['values']) AND $query['values'][$subvalue['id']] == '4') ? 'focus' : '') . '"><i class="fas fa-smile"></i><input type="radio" disabled></label>
										<label class="' . ((array_key_exists($subvalue['id'], $query['values']) AND $query['values'][$subvalue['id']] == '5') ? 'focus' : '') . '"><i class="fas fa-grin-stars"></i><input type="radio" disabled></label>';
									}
									else if ($subvalue['type'] == 'twin')
									{
										$html .=
										'<label class="' . ((array_key_exists($subvalue['id'], $query['values']) AND $query['values'][$subvalue['id']] == 'yes') ? 'focus' : '') . '"><i class="fas fa-thumbs-up"></i><input type="radio" disabled></label>
										<label class="' . ((array_key_exists($subvalue['id'], $query['values']) AND $query['values'][$subvalue['id']] == 'not') ? 'focus' : '') . '"><i class="fas fa-thumbs-down"></i><input type="radio" disabled></label>';
									}
									else if ($subvalue['type'] == 'check')
									{
										$html .= '<div class="checkboxes stl_3">';

										foreach ($subvalue['values'] as $parentvalue)
										{
											$html .=
											'<div>
												<input type="checkbox" ' . ((array_key_exists($subvalue['id'], $query['values']) AND in_array($parentvalue['token'], $query['values'][$subvalue['id']])) ? 'checked' : '') . ' disabled>
												<span>' . $parentvalue[$this->lang] . '</span>
											</div>';
										}

										$html .= '</div>';
									}

									$html .=
									'	</div>
									</div>';

									foreach ($this->model->get_surveys_questions($params[1], $subvalue['id']) as $parentvalue)
									{
										if ($parentvalue['type'] == 'open' OR $parentvalue['type'] == 'rate' OR $parentvalue['type'] == 'twin' OR $parentvalue['type'] == 'check')
										{
											$html .=
											'<div data-level="3">
												<h2>' . $parentvalue['name'][$this->lang] . '</h2>
												<div class="' . $parentvalue['type'] . '">';

											if ($parentvalue['type'] == 'open')
												$html .= '<input type="text" value="' . (array_key_exists($parentvalue['id'], $query['values']) ? $query['values'][$parentvalue['id']] : '') . '" disabled>';
											else if ($parentvalue['type'] == 'rate')
											{
												$html .=
												'<label class="' . ((array_key_exists($parentvalue['id'], $query['values']) AND $query['values'][$parentvalue['id']] == '1') ? 'focus' : '') . '"><i class="fas fa-sad-cry"></i><input type="radio" disabled></label>
												<label class="' . ((array_key_exists($parentvalue['id'], $query['values']) AND $query['values'][$parentvalue['id']] == '2') ? 'focus' : '') . '"><i class="fas fa-frown"></i><input type="radio" disabled></label>
												<label class="' . ((array_key_exists($parentvalue['id'], $query['values']) AND $query['values'][$parentvalue['id']] == '3') ? 'focus' : '') . '"><i class="fas fa-meh-rolling-eyes"></i><input type="radio" disabled></label>
												<label class="' . ((array_key_exists($parentvalue['id'], $query['values']) AND $query['values'][$parentvalue['id']] == '4') ? 'focus' : '') . '"><i class="fas fa-smile"></i><input type="radio" disabled></label>
												<label class="' . ((array_key_exists($parentvalue['id'], $query['values']) AND $query['values'][$parentvalue['id']] == '5') ? 'focus' : '') . '"><i class="fas fa-grin-stars"></i><input type="radio" disabled></label>';
											}
											else if ($parentvalue['type'] == 'twin')
											{
												$html .=
												'<label class="' . ((array_key_exists($parentvalue['id'], $query['values']) AND $query['values'][$parentvalue['id']] == 'yes') ? 'focus' : '') . '"><i class="fas fa-thumbs-up"></i><input type="radio" disabled></label>
												<label class="' . ((array_key_exists($parentvalue['id'], $query['values']) AND $query['values'][$parentvalue['id']] == 'not') ? 'focus' : '') . '"><i class="fas fa-thumbs-down"></i><input type="radio" disabled></label>';
											}
											else if ($parentvalue['type'] == 'check')
											{
												$html .= '<div class="checkboxes stl_3">';

												foreach ($parentvalue['values'] as $childvalue)
												{
													$html .=
													'<div>
														<input type="checkbox" ' . ((array_key_exists($parentvalue['id'], $query['values']) AND in_array($childvalue['token'], $query['values'][$parentvalue['id']])) ? 'checked' : '') . ' disabled>
														<span>' . $childvalue[$this->lang] . '</span>
													</div>';
												}

												$html .= '</div>';
											}

											$html .=
											'	</div>
											</div>';
										}
									}
								}
							}

							$html .= '</div>';
						}

						if (!empty($query['signature']))
						{
							$html .=
							'<figure style="width:100%;">
								<img src="{$path.uploads}' . $query['signature'] . '" style="width:100%;" />
							</figure>';
						}

						$html .= '</div>';

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

				if ($_POST['action'] == 'get_survey_reservation')
				{
					$query = $this->model->get_survey_answer($_POST['id']);

					if (!empty($query))
					{
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

				if ($_POST['action'] == 'edit_reservation')
				{
					$query = $this->model->edit_survey_reservation($_POST);

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

				if ($_POST['action'] == 'print_survey_answer')
				{
					$query = $this->model->get_survey_answer($_POST['id']);

					if (!empty($query))
					{
						$html = '<div>';

						foreach ($this->model->get_surveys_questions($params[1]) as $value)
						{
							$html .=
							'<div style="margin-bottom:20px;padding:20px;border: 1px dashed #000;box-sizing:border-box;">
								<div>
									<h2 style="font-family:arial;font-size:16px;font-weight;600;color:#000;">' . $value['name'][$this->lang] . '</h2>
									<div>';

							if ($value['type'] == 'nps')
							{
								$html .=
								'<div style="display:flex;align-items:center;justify-content:flex-start;">
									<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '1') ? 'border:1px dashed #000;' : '') . '">1</h4>
									<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '2') ? 'border:1px dashed #000;' : '') . '">2</h4>
									<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '3') ? 'border:1px dashed #000;' : '') . '">3</h4>
									<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '4') ? 'border:1px dashed #000;' : '') . '">4</h4>
									<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '5') ? 'border:1px dashed #000;' : '') . '">5</h4>
									<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '6') ? 'border:1px dashed #000;' : '') . '">6</h4>
									<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '7') ? 'border:1px dashed #000;' : '') . '">7</h4>
									<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '8') ? 'border:1px dashed #000;' : '') . '">8</h4>
									<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '9') ? 'border:1px dashed #000;' : '') . '">9</h4>
									<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '10') ? 'border:1px dashed #000;' : '') . '">10</h4>
								</div>';
							}
							else if ($value['type'] == 'open')
								$html .= '<h4 style="font-family:arial;font-size:14px;font-weight;400;color:#000;">' . (array_key_exists($value['id'], $query['values']) ? $query['values'][$value['id']] : '') . '</h4>';
							else if ($value['type'] == 'rate')
							{
								$html .=
								'<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '1') ? 'border:1px dashed #000;' : '') . '">1</h4>
								<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '2') ? 'border:1px dashed #000;' : '') . '">2</h4>
								<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '3') ? 'border:1px dashed #000;' : '') . '">3</h4>
								<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '4') ? 'border:1px dashed #000;' : '') . '">4</h4>
								<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == '5') ? 'border:1px dashed #000;' : '') . '">5</h4>';
							}
							else if ($value['type'] == 'twin')
							{
								$html .=
								'<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == 'yes') ? 'border:1px dashed #000;' : '') . '">Si</h4>
								<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($value['id'], $query['values']) AND $query['values'][$value['id']] == 'not') ? 'border:1px dashed #000;' : '') . '">No</h4>';
							}
							else if ($value['type'] == 'check')
							{
								$html .= '<div style="display:flex;align-items:center;justify-content:flex-start;">';

								foreach ($value['values'] as $subvalue)
									$html .= '<h4 style="width:auto;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;padding:0px 10px;border-radius:30px;box-sizing:border-box;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($value['id'], $query['values']) AND in_array($subvalue['token'], $query['values'][$value['id']])) ? 'border:1px dashed #000;' : '') . '">' . $subvalue[$this->lang] . '</h4>';

								$html .= '</div>';
							}

							$html .=
							'	</div>
							</div>';

							foreach ($this->model->get_surveys_questions($params[1], $value['id']) as $subvalue)
							{
								if ($subvalue['type'] == 'open' OR $subvalue['type'] == 'rate' OR $subvalue['type'] == 'twin' OR $subvalue['type'] == 'check')
								{
									$html .=
									'<div style="margin-bottom:20px;padding:20px;border: 1px dashed #000;box-sizing:border-box;">
										<h2 style="font-family:arial;font-size:16px;font-weight;600;color:#000;">' . $subvalue['name'][$this->lang] . '</h2>
										<div>';

									if ($subvalue['type'] == 'open')
										$html .= '<h4 style="font-family:arial;font-size:14px;font-weight;400;color:#000;">' . (array_key_exists($subvalue['id'], $query['values']) ? $query['values'][$subvalue['id']] : '') . '</h4>';
									else if ($subvalue['type'] == 'rate')
									{
										$html .=
										'<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($subvalue['id'], $query['values']) AND $query['values'][$subvalue['id']] == '1') ? 'border:1px dashed #000;' : '') . '">1</h4>
										<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($subvalue['id'], $query['values']) AND $query['values'][$subvalue['id']] == '2') ? 'border:1px dashed #000;' : '') . '">2</h4>
										<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($subvalue['id'], $query['values']) AND $query['values'][$subvalue['id']] == '3') ? 'border:1px dashed #000;' : '') . '">3</h4>
										<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($subvalue['id'], $query['values']) AND $query['values'][$subvalue['id']] == '4') ? 'border:1px dashed #000;' : '') . '">4</h4>
										<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($subvalue['id'], $query['values']) AND $query['values'][$subvalue['id']] == '5') ? 'border:1px dashed #000;' : '') . '">5</h4>';
									}
									else if ($subvalue['type'] == 'twin')
									{
										$html .=
										'<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($subvalue['id'], $query['values']) AND $query['values'][$subvalue['id']] == 'yes') ? 'border:1px dashed #000;' : '') . '">Si</h4>
										<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($subvalue['id'], $query['values']) AND $query['values'][$subvalue['id']] == 'not') ? 'border:1px dashed #000;' : '') . '">No</h4>';
									}
									else if ($subvalue['type'] == 'check')
									{
										$html .= '<div style="display:flex;align-items:center;justify-content:flex-start;">';

										foreach ($subvalue['values'] as $parentvalue)
											$html .= '<h4 style="width:auto;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;padding:0px 10px;border-radius:30px;box-sizing:border-box;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($subvalue['id'], $query['values']) AND in_array($parentvalue['token'], $query['values'][$subvalue['id']])) ? 'border:1px dashed #000;' : '') . '">' . $parentvalue[$this->lang] . '</h4>';

										$html .= '</div>';
									}

									$html .=
									'	</div>
									</div>';

									foreach ($this->model->get_surveys_questions($params[1], $subvalue['id']) as $parentvalue)
									{
										if ($parentvalue['type'] == 'open' OR $parentvalue['type'] == 'rate' OR $parentvalue['type'] == 'twin' OR $parentvalue['type'] == 'check')
										{
											$html .=
											'<div style="margin-bottom:20px;padding:20px;border: 1px dashed #000;box-sizing:border-box;">
												<h2 style="font-family:arial;font-size:16px;font-weight;600;color:#000;">' . $parentvalue['name'][$this->lang] . '</h2>
												<div>';

											if ($parentvalue['type'] == 'open')
												$html .= '<h4 style="font-family:arial;font-size:14px;font-weight;400;color:#000;">' . (array_key_exists($parentvalue['id'], $query['values']) ? $query['values'][$parentvalue['id']] : '') . '</h4>';
											else if ($parentvalue['type'] == 'rate')
											{
												$html .=
												'<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($parentvalue['id'], $query['values']) AND $query['values'][$parentvalue['id']] == '1') ? 'border:1px dashed #000;' : '') . '">1</h4>
												<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($parentvalue['id'], $query['values']) AND $query['values'][$parentvalue['id']] == '2') ? 'border:1px dashed #000;' : '') . '">2</h4>
												<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($parentvalue['id'], $query['values']) AND $query['values'][$parentvalue['id']] == '3') ? 'border:1px dashed #000;' : '') . '">3</h4>
												<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($parentvalue['id'], $query['values']) AND $query['values'][$parentvalue['id']] == '4') ? 'border:1px dashed #000;' : '') . '">4</h4>
												<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($parentvalue['id'], $query['values']) AND $query['values'][$parentvalue['id']] == '5') ? 'border:1px dashed #000;' : '') . '">5</h4>';
											}
											else if ($parentvalue['type'] == 'twin')
											{
												$html .=
												'<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($parentvalue['id'], $query['values']) AND $query['values'][$parentvalue['id']] == 'yes') ? 'border:1px dashed #000;' : '') . '">Si</h4>
												<h4 style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;border-radius:50%;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($parentvalue['id'], $query['values']) AND $query['values'][$parentvalue['id']] == 'not') ? 'border:1px dashed #000;' : '') . '">No</h4>';
											}
											else if ($parentvalue['type'] == 'check')
											{
												$html .= '<div style="display:flex;align-items:center;justify-content:flex-start;">';

												foreach ($parentvalue['values'] as $childvalue)
													$html .= '<h4 style="width:auto;height:30px;display:flex;align-items:center;justify-content:center;margin-right:5px;padding:0px 10px;border-radius:30px;box-sizing:border-box;font-family:arial;font-size:14px;font-weight;400;color:#000;' . ((array_key_exists($parentvalue['id'], $query['values']) AND in_array($childvalue['token'], $query['values'][$parentvalue['id']])) ? 'border:1px dashed #000;' : '') . '">' . $childvalue[$this->lang] . '</h4>';

												$html .= '</div>';
											}

											$html .=
											'	</div>
											</div>';
										}
									}
								}
							}

							$html .= '</div>';
						}

						$html .= '</div>';

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

				if ($_POST['action'] == 'public_survey_comment' OR $_POST['action'] == 'unpublic_survey_comment')
				{
					if ($_POST['action'] == 'public_survey_comment')
						$query = $this->model->public_survey_comment($_POST['id']);
					else if ($_POST['action'] == 'unpublic_survey_comment')
						$query = $this->model->unpublic_survey_comment($_POST['id']);

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
			}
			else
			{
				define('_title', 'Guestvox | {$lang.survey} | {$lang.answers}');

				$template = $this->view->render($this, 'answers');

				$tbl_surveys_raters = '';
				$tbl_surveys_comments = '';
				$mdl_public_survey_comment = '';
				$mdl_unpublic_survey_comment = '';

				if ($params[0] == 'raters')
				{
					$tbl_surveys_raters .= '<div class="tbl_stl_8" data-table>';

					foreach ($this->model->get_surveys_answers($params[1], 'raters') as $value)
					{
						$tbl_surveys_raters .=
						'<div>
							<div class="counter">
								<h6>' . $value['id'] . '</h6>
							</div>
							<div class="rating">';

						if ($value['average'] < 2)
							$tbl_surveys_raters .= '<span class="bad"><i class="fas fa-star"></i>' . $value['average'] . '</span>';
						else if ($value['average'] >= 2 AND $value['average'] < 4)
							$tbl_surveys_raters .= '<span class="medium"><i class="fas fa-star"></i>' . $value['average'] . '</span>';
						else if ($value['average'] >= 4)
							$tbl_surveys_raters .= '<span class="good"><i class="fas fa-star"></i>' . $value['average'] . '</span>';

						$tbl_surveys_raters .=
						'	</div>
							<div class="datas">
								<span>' . $value['token'] . '</span>
								<h2>' . (!empty($value['owner']) ? ((Session::get_value('account')['type'] == 'hotel' AND !empty($query['reservation']['firstname']) AND !empty($query['reservation']['lastname'])) ? $query['reservation']['firstname'] . ' ' . $query['reservation']['lastname'] : '{$lang.not_name}') : $value['firstname'] . ' ' . $value['lastname']) . '</h2>
								<span><i class="fas fa-calendar-alt"></i>' . Functions::get_formatted_date($value['date'], 'd.m.Y') . ' ' . Functions::get_formatted_hour($value['hour'], '+ hrs') . '</span>
								<span><i class="fas fa-shapes"></i>' . (!empty($value['owner']) ? $value['owner_name'][$this->lang] . (!empty($value['owner_number']) ? ' #' . $value['owner_number'] : '') : 'Sin propietario') . '</span>
							</div>
							<div class="buttons">
								<a class="big" data-action="preview_survey_answer" data-id="' . $value['id'] . '"><i class="fas fa-ghost"></i><span>{$lang.survey}</span></a>
								<a data-action="print_survey_answer" data-id="' . $value['id'] . '"><i class="fas fa-print"></i></a>
								<a class="edit" data-action="edit_reservation" data-id="' . $value['id'] . '"><i class="fas fa-pen"></i></a>
							</div>
						</div>';
					}

					$tbl_surveys_raters .= '</div>';
				}
				else if ($params[0] == 'comments')
				{
					$tbl_surveys_comments .= '<div class="tbl_stl_2" data-table>';

					foreach ($this->model->get_surveys_answers($params[1], 'comments') as $value)
					{
						if (!empty($value['comment']))
						{
							$tbl_surveys_comments .=
							'<div>
								<div class="datas">
									<h2>' . (!empty($value['owner']) ? ((Session::get_value('account')['type'] == 'hotel' AND !empty($value['reservation']['firstname']) AND !empty($value['reservation']['lastname'])) ? $value['reservation']['firstname'] . ' ' . $value['reservation']['lastname'] : '{$lang.not_name}') : $value['firstname'] . ' ' . $value['lastname']) . '</h2>
									<span><i class="fas fa-star"></i>' . $value['token'] . '</span>
									<span><i class="fas fa-calendar-alt"></i>' . Functions::get_formatted_date($value['date'], 'd.m.Y') . ' ' . Functions::get_formatted_hour($value['hour'], '+ hrs') . '</span>
									<span><i class="fas fa-shapes"></i>' . (!empty($value['owner']) ? $value['owner_name'][$this->lang] . (!empty($value['owner_number']) ? ' #' . $value['owner_number'] : '') : 'Sin propietario') . '</span>
									<p><i class="fas fa-comment-alt"></i>' . $value['comment'] . '</p>
								</div>
								<div class="buttons flex_right">
									<a class="' . (($value['public'] == true) ? 'delete' : 'new') . ' big" data-action="' . (($value['public'] == true) ? 'unpublic_survey_comment' : 'public_survey_comment') . '" data-id="' . $value['id'] . '">' . (($value['public'] == true) ? '{$lang.unpublic_survey_comment}' : '{$lang.public_survey_comment}') . '</a>
									<a class="big" data-action="preview_survey_answer" data-id="' . $value['id'] . '"><i class="fas fa-ghost"></i><span>{$lang.survey}</span></a>
								</div>
							</div>';
						}
					}

					$tbl_surveys_comments .= '</div>';

					$mdl_public_survey_comment .=
					'<section class="modal edit" data-modal="public_survey_comment">
						<div class="content">
							<footer>
								<a button-close><i class="fas fa-times"></i></a>
								<a button-success><i class="fas fa-check"></i></a>
							</footer>
						</div>
					</section>';

					$mdl_unpublic_survey_comment .=
					'<section class="modal edit" data-modal="unpublic_survey_comment">
						<div class="content">
							<footer>
								<a button-close><i class="fas fa-times"></i></a>
								<a button-success><i class="fas fa-check"></i></a>
							</footer>
						</div>
					</section>';
				}

				$opt_owners = '';

				foreach ($this->model->get_owners('survey') as $value)
					$opt_owners .= '<option value="' . $value['id'] . '" ' . ((Session::get_value('settings')['surveys']['reports']['filter']['owner'] == $value['id']) ? 'selected' : '') . '>' . $value['name'][$this->lang] . (!empty($value['number']) ? ' #' . $value['number'] : '') . '</option>';

				$opt_ladas = '';

				foreach ($this->model->get_countries() as $value)
					$opt_ladas .= '<option value="' . $value['lada'] . '">' . $value['name'][$this->lang] . ' (+' . $value['lada'] . ')</option>';

				$replace = [
					'{$menu_focus}' => $params[0],
					'{$tbl_surveys_raters}' => $tbl_surveys_raters,
					'{$tbl_surveys_comments}' => $tbl_surveys_comments,
					'{$mdl_public_survey_comment}' => $mdl_public_survey_comment,
					'{$mdl_unpublic_survey_comment}' => $mdl_unpublic_survey_comment,
					'{$opt_owners}' => $opt_owners,
					'{$opt_ladas}' => $opt_ladas
				];

				$template = $this->format->replace($replace, $template);

				echo $template;
			}
		}
		else
			header('Location: /surveys');
	}

	public function questions($params)
	{
		$survey = $this->model->get_survey($params[0]);

		if (!empty($survey))
		{
			if (Format::exist_ajax_request() == true)
			{
				if ($_POST['action'] == 'get_survey_question')
				{
					$query = $this->model->get_survey_question($_POST['id']);

					if (!empty($query))
					{
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

				if ($_POST['action'] == 'new_survey_question' OR $_POST['action'] == 'edit_survey_question')
				{
					$labels = [];

					if (!isset($_POST['name_es']) OR empty($_POST['name_es']))
						array_push($labels, ['name_es','']);

					if (!isset($_POST['name_en']) OR empty($_POST['name_en']))
						array_push($labels, ['name_en','']);

					if (!isset($_POST['type']) OR empty($_POST['type']))
						array_push($labels, ['type','']);

					if ($_POST['type'] == 'check')
					{
						$_POST['values'] = json_decode($_POST['values'], true);

						if (!isset($_POST['values']) OR empty($_POST['values']))
						{
							array_push($labels, ['value_es','']);
							array_push($labels, ['value_en','']);
						}
					}

					if (empty($labels))
					{
						if ($_POST['action'] == 'new_survey_question')
						{
							$_POST['survey'] = $params[0];

							$query = $this->model->new_survey_question($_POST);
						}
						else if ($_POST['action'] == 'edit_survey_question')
							$query = $this->model->edit_survey_question($_POST);

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

				if ($_POST['action'] == 'deactivate_survey_question' OR $_POST['action'] == 'activate_survey_question' OR $_POST['action'] == 'delete_survey_question')
				{
					if ($_POST['action'] == 'deactivate_survey_question')
						$query = $this->model->deactivate_survey_question($_POST['id']);
					else if ($_POST['action'] == 'activate_survey_question')
						$query = $this->model->activate_survey_question($_POST['id']);
					else if ($_POST['action'] == 'delete_survey_question')
						$query = $this->model->delete_survey_question($_POST['id']);

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
			}
			else
			{
				$template = $this->view->render($this, 'questions');

				define('_title', 'Guestvox | {$lang.survey} | {$lang.questions}');

				$tbl_surveys_questions = '';

				foreach ($this->model->get_surveys_questions($params[0]) as $value)
				{
					$tbl_surveys_questions .=
					'<div>
						<div data-level="1">
							<h2>' . $value['name'][$this->lang] . '</h2>
							<div class="' . $value['type'] . '">';

					if ($value['type'] == 'nps')
					{
						$tbl_surveys_questions .=
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
						$tbl_surveys_questions .= '<input type="text" disabled>';
					else if ($value['type'] == 'rate')
					{
						$tbl_surveys_questions .=
						'<label><i class="fas fa-sad-cry"></i><input type="radio" disabled></label>
						<label><i class="fas fa-frown"></i><input type="radio" disabled></label>
						<label><i class="fas fa-meh-rolling-eyes"></i><input type="radio" disabled></label>
						<label><i class="fas fa-smile"></i><input type="radio" disabled></label>
						<label><i class="fas fa-grin-stars"></i><input type="radio" disabled></label>';
					}
					else if ($value['type'] == 'twin')
					{
						$tbl_surveys_questions .=
						'<label><i class="fas fa-thumbs-up"></i><input type="radio" disabled></label>
						<label><i class="fas fa-thumbs-down"></i><input type="radio" disabled></label>';
					}
					else if ($value['type'] == 'check')
					{
						$tbl_surveys_questions .= '<div class="checkboxes stl_3">';

						foreach ($value['values'] as $subvalue)
						{
							$tbl_surveys_questions .=
							'<div>
								<input type="checkbox" disabled>
								<span>' . $subvalue[$this->lang] . '</span>
							</div>';
						}

						$tbl_surveys_questions .= '</div>';
					}

					$tbl_surveys_questions .= '</div>';

					if ($value['system'] == false)
					{
						$tbl_surveys_questions .=
						'<div class="buttons">
							' . ((Functions::check_user_access(['{surveys_questions_deactivate}','{surveys_questions_activate}']) == true) ? '<a class="big" data-action="' . (($value['status'] == true) ? 'deactivate_survey_question' : 'activate_survey_question') . '" data-id="' . $value['id'] . '">' . (($value['status'] == true) ? '<i class="fas fa-ban"></i><span>{$lang.deactivate}</span>' : '<i class="fas fa-check"></i><span>{$lang.activate}</span>') . '</a>' : '') . '
							' . ((Functions::check_user_access(['{surveys_questions_update}']) == true) ? '<a class="edit" data-action="edit_survey_question" data-id="' . $value['id'] . '"><i class="fas fa-pen"></i></a>' : '') . '
							' . ((Functions::check_user_access(['{surveys_questions_delete}']) == true) ? '<a class="delete" data-action="delete_survey_question" data-id="' . $value['id'] . '"><i class="fas fa-trash"></i></a>' : '') . '
						</div>';
					}

					$tbl_surveys_questions .= '</div>';

					foreach ($this->model->get_surveys_questions($params[0], $value['id']) as $subvalue)
					{
						if ($subvalue['type'] == 'open' OR $subvalue['type'] == 'rate' OR $subvalue['type'] == 'twin' OR $subvalue['type'] == 'check')
						{
							$tbl_surveys_questions .=
							'<div data-level="2">
								<h2>' . $subvalue['name'][$this->lang] . '</h2>
								<div class="' . $subvalue['type'] . '">';

							if ($subvalue['type'] == 'open')
								$tbl_surveys_questions .= '<input type="text" disabled>';
							else if ($subvalue['type'] == 'rate')
							{
								$tbl_surveys_questions .=
								'<label><i class="fas fa-sad-cry"></i><input type="radio" disabled></label>
								<label><i class="fas fa-frown"></i><input type="radio" disabled></label>
								<label><i class="fas fa-meh-rolling-eyes"></i><input type="radio" disabled></label>
								<label><i class="fas fa-smile"></i><input type="radio" disabled></label>
								<label><i class="fas fa-grin-stars"></i><input type="radio" disabled></label>';
							}
							else if ($subvalue['type'] == 'twin')
							{
								$tbl_surveys_questions .=
								'<label><i class="fas fa-thumbs-up"></i><input type="radio" disabled></label>
								<label><i class="fas fa-thumbs-down"></i><input type="radio" disabled></label>';
							}
							else if ($subvalue['type'] == 'check')
							{
								$tbl_surveys_questions .= '<div class="checkboxes stl_3">';

								foreach ($subvalue['values'] as $parentvalue)
								{
									$tbl_surveys_questions .=
									'<div>
										<input type="checkbox" disabled>
										<span>' . $parentvalue[$this->lang] . '</span>
									</div>';
								}

								$tbl_surveys_questions .= '</div>';
							}

							$tbl_surveys_questions .= '</div>';

							if ($subvalue['system'] == false)
							{
								$tbl_surveys_questions .=
								'<div class="buttons">
									' . ((Functions::check_user_access(['{surveys_questions_deactivate}','{surveys_questions_activate}']) == true) ? '<a data-action="' . (($subvalue['status'] == true) ? 'deactivate_survey_question' : 'activate_survey_question') . '" data-id="' . $subvalue['id'] . '">' . (($subvalue['status'] == true) ? '<i class="fas fa-ban"></i>' : '<i class="fas fa-check"></i>') . '</a>' : '') . '
									' . ((Functions::check_user_access(['{surveys_questions_update}']) == true) ? '<a class="edit" data-action="edit_survey_question" data-id="' . $subvalue['id'] . '"><i class="fas fa-pen"></i></a>' : '') . '
									' . ((Functions::check_user_access(['{surveys_questions_delete}']) == true) ? '<a class="delete" data-action="delete_survey_question" data-id="' . $subvalue['id'] . '"><i class="fas fa-trash"></i></a>' : '') . '
								</div>';
							}

							$tbl_surveys_questions .= '</div>';

							foreach ($this->model->get_surveys_questions($params[0], $subvalue['id']) as $parentvalue)
							{
								if ($parentvalue['type'] == 'open' OR $parentvalue['type'] == 'rate' OR $parentvalue['type'] == 'twin' OR $parentvalue['type'] == 'check')
								{
									$tbl_surveys_questions .=
									'<div data-level="3">
										<h2>' . $parentvalue['name'][$this->lang] . '</h2>
										<div class="' . $parentvalue['type'] . '">';

									if ($parentvalue['type'] == 'open')
										$tbl_surveys_questions .= '<input type="text" disabled>';
									else if ($parentvalue['type'] == 'rate')
									{
										$tbl_surveys_questions .=
										'<label><i class="fas fa-sad-cry"></i><input type="radio" disabled></label>
										<label><i class="fas fa-frown"></i><input type="radio" disabled></label>
										<label><i class="fas fa-meh-rolling-eyes"></i><input type="radio" disabled></label>
										<label><i class="fas fa-smile"></i><input type="radio" disabled></label>
										<label><i class="fas fa-grin-stars"></i><input type="radio" disabled></label>';
									}
									else if ($parentvalue['type'] == 'twin')
									{
										$tbl_surveys_questions .=
										'<label><i class="fas fa-thumbs-up"></i><input type="radio" disabled></label>
										<label><i class="fas fa-thumbs-down"></i><input type="radio" disabled></label>';
									}
									else if ($parentvalue['type'] == 'check')
									{
										$tbl_surveys_questions .= '<div class="checkboxes stl_3">';

										foreach ($parentvalue['values'] as $childvalue)
										{
											$tbl_surveys_questions .=
											'<div>
												<input type="checkbox" disabled>
												<span>' . $childvalue[$this->lang] . '</span>
											</div>';
										}

										$tbl_surveys_questions .= '</div>';
									}

									$tbl_surveys_questions .= '</div>';

									if ($parentvalue['system'] == false)
									{
										$tbl_surveys_questions .=
										'<div class="buttons">
											' . ((Functions::check_user_access(['{surveys_questions_deactivate}','{surveys_questions_activate}']) == true) ? '<a data-action="' . (($parentvalue['status'] == true) ? 'deactivate_survey_question' : 'activate_survey_question') . '" data-id="' . $parentvalue['id'] . '">' . (($parentvalue['status'] == true) ? '<i class="fas fa-ban"></i>' : '<i class="fas fa-check"></i>') . '</a>' : '') . '
											' . ((Functions::check_user_access(['{surveys_questions_update}']) == true) ? '<a class="edit" data-action="edit_survey_question" data-id="' . $parentvalue['id'] . '"><i class="fas fa-pen"></i></a>' : '') . '
											' . ((Functions::check_user_access(['{surveys_questions_delete}']) == true) ? '<a class="delete" data-action="delete_survey_question" data-id="' . $parentvalue['id'] . '"><i class="fas fa-trash"></i></a>' : '') . '
										</div>';
									}

									$tbl_surveys_questions .= '</div>';
								}
							}
						}
					}

					$tbl_surveys_questions .= '</div>';
				}

				$opt_surveys_questions = '';

				foreach ($this->model->get_surveys_questions($params[0]) as $value)
				{
					if ($value['type'] == 'open' OR $value['type'] == 'rate' OR $value['type'] == 'twin' OR $value['type'] == 'check')
					{
						$opt_surveys_questions .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang] . '</option>';

						foreach ($this->model->get_surveys_questions($params[0], $value['id']) as $subvalue)
						{
							if ($subvalue['type'] == 'open' OR $subvalue['type'] == 'rate' OR $subvalue['type'] == 'twin' OR $subvalue['type'] == 'check')
								$opt_surveys_questions .= '<option value="' . $subvalue['id'] . '">- ' . $subvalue['name'][$this->lang] . '</option>';
						}
					}
				}

				$replace = [
					'{$tbl_surveys_questions}' => $tbl_surveys_questions,
					'{$opt_surveys_questions}' => $opt_surveys_questions
				];

				$template = $this->format->replace($replace, $template);

				echo $template;
			}
		}
		else
			header('Location: /surveys');
	}
}
