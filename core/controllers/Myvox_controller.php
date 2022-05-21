<?php

defined('_EXEC') or die;

require_once 'plugins/nexmo/vendor/autoload.php';
// require_once 'plugins/pusher/vendor/autoload.php';

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

		if (!empty($params[1]))
		{
			$account = $this->model->get_account($params[0]);

			if (!empty($account))
			{
				$owner = null;

				if (($account['type'] == 'hotel' OR $account['type'] == 'restaurant' OR $account['type'] == 'others') AND $params[1] == 'owner' AND !empty($params[2]))
					$owner = $this->model->get_owner($params[2]);

				if ((($account['type'] == 'hotel' OR $account['type'] == 'restaurant' OR $account['type'] == 'others') AND $params[1] == 'owner' AND !empty($owner)) OR (($account['type'] == 'restaurant' OR $account['type'] == 'others') AND $params[1] == 'delivery'))
				{
					if ($account['type'] == 'hotel' AND $params[1] == 'owner')
						$owner['reservation'] = $this->model->get_reservation($owner['number'], $account['zaviapms']);

					$myvox = [
						'account' => $account,
						'owner' => $owner,
						'url' => $params[1]
					];

					Session::set_value('myvox', $myvox);

					$break = false;
				}
			}
		}

		if ($break == false)
		{
			if (($account['type'] == 'hotel' OR $account['type'] == 'restaurant' OR $account['type'] == 'others') AND $params[1] == 'owner')
			{
				$template = $this->view->render($this, 'index');

				define('_title', $account['name'] . ' | {$lang.myvox}');

				$btn_new_menu_order = '';

				if ($account['digital_menu'] == true AND $account['settings']['myvox']['menu']['status'] == true)
					$btn_new_menu_order .= '<a href="/' . $params[0] . '/menu/products">' . $account['settings']['myvox']['menu']['title'][$this->lang1] . '</a>';

				$btn_new_request = '';
				$btn_new_incident = '';

				if ($account['operation'] == true)
				{
					if ($account['settings']['myvox']['request']['status'] == true)
						$btn_new_request .= '<a href="/' . $params[0] . '/request">' . $account['settings']['myvox']['request']['title'][$this->lang1] . '</a>';

					if ($account['settings']['myvox']['incident']['status'] == true)
						$btn_new_incident .= '<a href="/' . $params[0] . '/incident">' . $account['settings']['myvox']['incident']['title'][$this->lang1] . '</a>';
				}

				$btn_new_survey_answer = '';

				$main_survey = $this->model->get_survey('main');

				if ($account['surveys'] == true AND $account['settings']['myvox']['survey']['status'] == true AND !empty($main_survey))
					$btn_new_survey_answer .= '<a href="/' . $params[0] . '/survey">' . $account['settings']['myvox']['survey']['title'][$this->lang1] . '</a>';

				$replace = [
					'{$logotype}' => '{$path.uploads}' . $account['logotype'],
					'{$btn_new_menu_order}' => $btn_new_menu_order,
					'{$btn_new_request}' => $btn_new_request,
					'{$btn_new_incident}' => $btn_new_incident,
					'{$btn_new_survey_answer}' => $btn_new_survey_answer
				];

				$template = $this->format->replace($replace, $template);

				echo $template;
			}
			else if (($account['type'] == 'restaurant' OR $account['type'] == 'others') AND $params[1] == 'delivery' AND $account['digital_menu'] == true AND $account['settings']['myvox']['menu']['status'] == true)
				header('Location: /' . $params[0] . '/menu/products');
		}
		else
			header('Location: /');
    }

	public function menu($params)
    {
		$break = true;

		if (Session::exists_var('myvox') == true AND !empty(Session::get_value('myvox')['account']))
		{
			if (Session::get_value('myvox')['account']['digital_menu'] == true AND Session::get_value('myvox')['account']['settings']['myvox']['menu']['status'] == true)
			{
				if (!empty(Session::get_value('myvox')['url']))
				{
					if ((Session::get_value('myvox')['account']['type'] == 'hotel' OR Session::get_value('myvox')['account']['type'] == 'restaurant' OR Session::get_value('myvox')['account']['type'] == 'others') AND Session::get_value('myvox')['url'] == 'owner' AND !empty(Session::get_value('myvox')['owner']))
						$break = false;
					else if ((Session::get_value('myvox')['account']['type'] == 'restaurant' OR Session::get_value('myvox')['account']['type'] == 'others') AND Session::get_value('myvox')['url'] == 'delivery')
						$break = false;
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

			if (!isset(Session::get_value('myvox')['menu_categories']) OR empty(Session::get_value('myvox')['menu_categories']))
			{
				$myvox = Session::get_value('myvox');

				$myvox['menu_categories'] = [];

				Session::set_value('myvox', $myvox);
			}

			if (!isset(Session::get_value('myvox')['menu_name']) OR empty(Session::get_value('myvox')['menu_name']))
			{
				$myvox = Session::get_value('myvox');

				$myvox['menu_name'] = '';

				Session::set_value('myvox', $myvox);
			}

			if (!isset(Session::get_value('myvox')['menu_price']) OR empty(Session::get_value('myvox')['menu_price']))
			{
				$myvox = Session::get_value('myvox');

				$myvox['menu_price'] = '';

				Session::set_value('myvox', $myvox);
			}

			if (!isset(Session::get_value('myvox')['menu_payment_token']) OR empty(Session::get_value('myvox')['menu_payment_token']))
			{
				$myvox = Session::get_value('myvox');

				$myvox['menu_payment_token'] = strtolower(Functions::get_random(8));

				Session::set_value('myvox', $myvox);
			}

			if (Format::exist_ajax_request() == true)
			{
				if ($_POST['action'] == 'filter_menu_products_by_categories')
				{
					$myvox = Session::get_value('myvox');

					if (array_key_exists($_POST['id'], $myvox['menu_categories']))
						unset($myvox['menu_categories'][$_POST['id']]);
					else
						$myvox['menu_categories'][$_POST['id']] = $_POST['id'];

					Session::set_value('myvox', $myvox);

					Functions::environment([
						'status' => 'success'
					]);
				}

				if ($_POST['action'] == 'preview_menu_product')
				{
					$query = $this->model->get_menu_product($_POST['id']);

					if (!empty($query))
					{
						$html = '<figure class="' . $query['avatar'] . '" ' . (($query['avatar'] == 'icon') ? 'style="background-color:' . $query['icon_color'] . ';"' : '') . '>';

						if ($query['avatar'] == 'image')
							$html .= '<img src="{$path.uploads}' . $query['image'] . '">';
						else if ($query['avatar'] == 'icon')
							$html .= '<img src="{$path.images}icons/' . $query['icon_type'] . '/' . $query['icon_url'] . '">';

						$html .=
						'</figure>
						<h2>' . $query['name'][$this->lang1] . '</h2>
						<p>' . (!empty($query['description'][$this->lang1]) ? $query['description'][$this->lang1] : '') . '</p>
						' . (!empty($query['price']) ? '<span>' . Functions::get_formatted_currency($query['price'], Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency']) . (!empty($query['topics']) ? ' (+ {$lang.topics})' : '') . '</span>' : '');

						if (Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule'][Functions::get_current_day()]['status'] == 'open' AND Functions::get_current_hour() >= Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule'][Functions::get_current_day()]['opening'] AND Functions::get_current_hour() <= Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule'][Functions::get_current_day()]['closing'])
						{
							$html .= '<form name="add_to_menu_order">';

							foreach ($query['topics'] as $key => $value)
							{
								$html .= '<div class="item">';

								foreach ($value as $subkey => $subvalue)
								{
									$html .=
									'<label>
										<input type="' . $subvalue['selection'] . '" name="topics_' . $key . '[]" value="' . $key . '_' . $subkey . '" data-action="update_menu_product_price" data-id="' . $_POST['id'] . '" ' . (($subvalue['selection'] == 'radio' AND $subkey == 0) ? 'checked' : '') . ' />
										<p>' . $subvalue['name'][$this->lang1] . '</p>
										<span>' . (!empty($subvalue['price']) ? Functions::get_formatted_currency($subvalue['price'], Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency']) . ' +' : '') . '</span>
									</label>';
								}

								$html .= '</div>';
							}

							$html .=
							'	<div class="buttons">
									<a class="delete" button-cancel><i class="fas fa-times"></i></a>
									<a data-action="minus_to_menu_order" data-id="' . $_POST['id'] . '"><i class="fas fa-minus"></i></a>
									<input type="text" name="quantity" value="1" min="1">
									<a data-action="plus_to_menu_order" data-id="' . $_POST['id'] . '"><i class="fas fa-plus"></i></a>
									<a class="new" data-action="add_to_menu_order" data-id="' . $_POST['id'] . '"><i class="fas fa-check"></i></a>
								</div>
							</form>';
						}
						else
						{
							$html .=
							'<div class="buttons closed">
								<h6>{$lang.business_closed}</h6>
								<p><strong>{$lang.monday}</strong>: ' . ((Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['monday']['status'] == 'open') ? '{$lang.open} {$lang.from} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['monday']['opening'], '+ hrs') . ' {$lang.a} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['monday']['closing'], '+ hrs') : '{$lang.close}') . '</p>
								<p><strong>{$lang.tuesday}</strong>: ' . ((Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['tuesday']['status'] == 'open') ? '{$lang.open} {$lang.from} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['tuesday']['opening'], '+ hrs') . ' {$lang.a} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['tuesday']['closing'], '+ hrs') : '{$lang.close}') . '</p>
								<p><strong>{$lang.wednesday}</strong>: ' . ((Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['wednesday']['status'] == 'open') ? '{$lang.open} {$lang.from} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['wednesday']['opening'], '+ hrs') . ' {$lang.a} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['wednesday']['closing'], '+ hrs') : '{$lang.close}') . '</p>
								<p><strong>{$lang.thursday}</strong>: ' . ((Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['thursday']['status'] == 'open') ? '{$lang.open} {$lang.from} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['thursday']['opening'], '+ hrs') . ' {$lang.a} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['thursday']['closing'], '+ hrs') : '{$lang.close}') . '</p>
								<p><strong>{$lang.friday}</strong>: ' . ((Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['friday']['status'] == 'open') ? '{$lang.open} {$lang.from} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['friday']['opening'], '+ hrs') . ' {$lang.a} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['friday']['closing'], '+ hrs') : '{$lang.close}') . '</p>
								<p><strong>{$lang.saturday}</strong>: ' . ((Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['saturday']['status'] == 'open') ? '{$lang.open} {$lang.from} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['saturday']['opening'], '+ hrs') . ' {$lang.a} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['saturday']['closing'], '+ hrs') : '{$lang.close}') . '</p>
								<p><strong>{$lang.sunday}</strong>: ' . ((Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['sunday']['status'] == 'open') ? '{$lang.open} {$lang.from} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['sunday']['opening'], '+ hrs') . ' {$lang.a} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['sunday']['closing'], '+ hrs') : '{$lang.close}') . '</p>
								<a class="delete" button-cancel><i class="fas fa-times"></i></a>
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

				if ($_POST['action'] == 'update_menu_product_price')
				{
					$query = $this->model->get_menu_product($_POST['id']);

					if (!empty($query))
					{
						$total = 0;

						foreach ($_POST as $key => $value)
						{
							$key = explode('_', $key);

							if ($key[0] == 'topics')
							{
								foreach ($value as $subkey => $subvalue)
								{
									$subvalue = explode('_', $subvalue);
									$subvalue = $query['topics'][$subvalue[0]][$subvalue[1]];
									$total = ($total + $subvalue['price']);
								}
							}
						}

						$total = (($total + $query['price']) * $_POST['quantity']);
						$total = !empty($total) ? Functions::get_formatted_currency($total, Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency']) . (($total == $query['price'] AND !empty($query['topics'])) ? ' (+ {$lang.topics})' : '') : '';

						Functions::environment([
							'status' => 'success',
							'total' => $total
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

				if ($_POST['action'] == 'add_to_menu_order')
				{
					$query = $this->model->get_menu_product($_POST['id']);

					if (!empty($query))
					{
						$myvox = Session::get_value('myvox');

						$_POST['topics'] = [];

						foreach ($_POST as $key => $value)
						{
							$key = explode('_', $key);

							if ($key[0] == 'topics')
							{
								foreach ($value as $subkey => $subvalue)
								{
									$subvalue = explode('_', $subvalue);

									array_push($_POST['topics'], $query['topics'][$subvalue[0]][$subvalue[1]]);
								}
							}
						}

						$_POST['map'] = (!empty($query['map']) ? $query['map'] : '');

						if (array_key_exists($_POST['id'], $myvox['menu_order']['shopping_cart']))
						{
							array_push($myvox['menu_order']['shopping_cart'][$_POST['id']], [
								'quantity' => $_POST['quantity'],
								'id' => $_POST['id'],
								'name' => $query['name'],
								'topics' => $_POST['topics'],
								'price' => $query['price'],
								'total' => $this->model->get_menu_order_total($query['price'], $_POST['topics'], $_POST['quantity']),
								'map' => $_POST['map']
							]);
						}
						else
						{
							$myvox['menu_order']['shopping_cart'][$_POST['id']] = [
								[
									'quantity' => $_POST['quantity'],
									'id' => $_POST['id'],
									'name' => $query['name'],
									'topics' => $_POST['topics'],
									'price' => $query['price'],
									'total' => $this->model->get_menu_order_total($query['price'], $_POST['topics'], $_POST['quantity']),
									'map' => $_POST['map']
								]
							];
						}

						$myvox['menu_order']['total'] = $this->model->get_menu_order_total($myvox['menu_order']['shopping_cart']);

						Session::set_value('myvox', $myvox);

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

				if ($_POST['action'] == 'remove_to_menu_order')
				{
					$myvox = Session::get_value('myvox');

					unset($myvox['menu_order']['shopping_cart'][$_POST['key']][$_POST['subkey']]);

					if (empty($myvox['menu_order']['shopping_cart'][$_POST['key']]))
						unset($myvox['menu_order']['shopping_cart'][$_POST['key']]);

					$myvox['menu_order']['total'] = $this->model->get_menu_order_total($myvox['menu_order']['shopping_cart']);

					Session::set_value('myvox', $myvox);

					Functions::environment([
						'status' => 'success',
						'message' => '{$lang.operation_success}'
					]);
				}

				if ($_POST['action'] == 'search')
				{
					$labels = [];

					if (!empty($_POST['price']) AND $_POST['price'] < 0)
						array_push($labels, ['price','']);

					if (empty($labels))
					{
						$myvox = Session::get_value('myvox');

						if (isset($_POST['name']) AND !empty($_POST['name']))
							$myvox['menu_name'] = $_POST['name'];

						if (isset($_POST['price']) AND !empty($_POST['price']))
							$myvox['menu_price'] = $_POST['price'];

						Session::set_value('myvox', $myvox);

						Functions::environment([
							'status' => 'success',
							'path' =>  '/' . $params[0] . '/menu/products'
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

				if ($_POST['action'] == 'clear_search')
				{
					$myvox = Session::get_value('myvox');

					$myvox['menu_categories'] = [];
					$myvox['menu_name'] = '';
					$myvox['menu_price'] = '';

					Session::set_value('myvox', $myvox);

					Functions::environment([
						'status' => 'success'
					]);
				}

				if ($_POST['action'] == 'new_menu_order')
				{
					$menu_order_payment_access = true;

					if ($_POST['payment_method'] == 'credit_card' OR $_POST['payment_method'] == 'debit_card')
					{
						$menu_order_payment = $this->model->get_menu_order_payment();
						$menu_order_payment_access = !empty($menu_order_payment) ? true : false;
					}

					if ($menu_order_payment_access == true)
					{
						$labels = [];

						if (Session::get_value('myvox')['url'] == 'delivery')
						{
							if (!isset($_POST['delivery']) OR empty($_POST['delivery']))
								array_push($labels, ['delivery','']);

							if ($_POST['delivery'] == 'home' AND (!isset($_POST['address']) OR empty($_POST['address'])))
								array_push($labels, ['address','']);

							if (!isset($_POST['firstname']) OR empty($_POST['firstname']))
								array_push($labels, ['firstname','']);

							if (!isset($_POST['lastname']) OR empty($_POST['lastname']))
								array_push($labels, ['lastname','']);

							if (!isset($_POST['email']) OR empty($_POST['email']))
								array_push($labels, ['email','']);

							if (!isset($_POST['phone_lada']) OR empty($_POST['phone_lada']))
								array_push($labels, ['phone_lada','']);

							if (!isset($_POST['phone_number']) OR empty($_POST['phone_number']))
								array_push($labels, ['phone_number','']);

							if (!isset($_POST['payment_method']) OR empty($_POST['payment_method']))
								array_push($labels, ['payment_method','']);

							if ($_POST['payment_method'] == 'cash' AND (!isset($_POST['payment_cash']) OR empty($_POST['payment_cash'])))
								array_push($labels, ['payment_cash','']);
						}

						if (!isset($_POST['payment_method']) OR empty($_POST['payment_method']))
							array_push($labels, ['payment_method','']);

						if (empty($labels))
						{
							$_POST['started_date'] = Functions::get_current_date();
							$_POST['started_hour'] = Functions::get_current_hour();

							$query = $this->model->new_menu_order($_POST);

							if (!empty($query))
							{
								if (Session::get_value('myvox')['url'] == 'owner' AND Session::get_value('myvox')['account']['ambit']['status'] == true)
								{
									$ambit_order = $query;

									$ambit = [
										'store_id' => Session::get_value('myvox')['account']['ambit']['store_id'],
										'client' => [
											'name' => '',
											'phone' => '',
											'email' => '',
											'address' => [
												'client_address' => '',
												'client_address_parts' => [
													'city' => '',
													'street' => '',
													'more_address' => ''
												]
											]
										],
										'order' => [
											'id' => $ambit_order,
											'type' => 'Pickup',
											'date' => Functions::get_current_date() . ' ' . Functions::get_current_hour(),
											'note' => '',
											'total' => floatval(Session::get_value('myvox')['menu_order']['total']),
											'store' => '0',
											'origen' => Session::get_value('myvox')['account']['name'],
											'formaPago' => '01',
											'idFormaPago' => 'Efectivo',
											'table' => Session::get_value('myvox')['owner']['name'][$this->lang2]
										],
										'Items' => []
									];

									foreach (Session::get_value('myvox')['menu_order']['shopping_cart'] as $value)
									{
										foreach ($value as $subvalue)
										{
											array_push($ambit['Items'], [
												'price' => floatval($subvalue['price']),
												'type' => !empty($subvalue['map']['type']) ? $subvalue['map']['type'] : '',
												'note' => '',
												'code' => !empty($subvalue['map']['code']) ? $subvalue['map']['code'] : '',
												'descrip' => $subvalue['name'][$this->lang2],
												'cant' => floatval($subvalue['quantity']),
												'modifiers' => []
											]);

											// if (!empty($subvalue['topics']))
											// {
											// 	$note = 'Topics: ';
											//
											// 	foreach($subvalue['topics'] as $chilkey => $childvalue)
											// 	{
											// 		$note .= $childvalue['name'][$this->lang2] . '' . (!empty($childvalue['price']) ? ' $' . $childvalue['price'] . '; ' : '; ');
											// 		$temp['note'] = $note;
											// 	}
											// }
											// else
											// 	$temp['note'] = ';';
										}
									}

									$ambit_order = Functions::api('ambit', $ambit, 'post', '');
								}

								if (Session::get_value('myvox')['url'] == 'delivery')
								{
									$mail1 = new Mailer(true);

									try
									{
										$mail1->setFrom('noreply@guestvox.com', 'Guestvox');
										$mail1->addAddress($_POST['email'], $_POST['firstname'] . ' ' . $_POST['lastname']);
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
															<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:24px;font-weight:600;text-align:center;color:#212121;">' . $mail1->Subject . '</h4>
															<h6 style="width:100%;margin:0px 0px 10px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('token')[$this->lang1] . ': ' . Session::get_value('myvox')['menu_payment_token'] . '</h6>
															<h6 style="width:100%;margin:0px 0px 10px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('total')[$this->lang1] . ': $ ' . Session::get_value('myvox')['menu_order']['total'] . ' ' . Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency'] . '</h6>';

										foreach (Session::get_value('myvox')['menu_order']['shopping_cart'] as $value)
										{
											foreach ($value as $subvalue)
											{
												$mail1->Body .= '<p style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">x' . $subvalue['quantity'] . ' ' . $subvalue['name'][$this->lang2];

												if (!empty($subvalue['topics']))
												{
													$mail1->Body .= ' (';

													foreach ($subvalue['topics'] as $parentvalue)
														$mail1->Body .= $parentvalue['name'][$this->lang2] . ', ';

													$mail1->Body .= ')';
												}

												$mail1->Body .= '</p>';
											}
										}

										$mail1->Body .=
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
										$mail1->send();
									}
									catch (Exception $e) { }

									$sms1 = $this->model->get_sms();

									if ($sms1 > 0)
									{
										$sms1_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
										$sms1_client = new \Nexmo\Client($sms1_basic);
										$sms1_text = Session::get_value('myvox')['account']['name'] . '. ' . Languages::email('thanks_received_menu_order')[$this->lang1] . '. Power by Guestvox.';

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

									if (!empty(Session::get_value('myvox')['account']['settings']['myvox']['menu']['email']))
									{
										$mail2 = new Mailer(true);

										try
										{
											$mail2->setFrom('noreply@guestvox.com', 'Guestvox');
											$mail2->addAddress(Session::get_value('myvox')['account']['settings']['myvox']['menu']['email'], Session::get_value('myvox')['account']['name']);
											$mail2->Subject = Languages::email('new_menu_order')[$this->lang1] . '. ' . Languages::email('token')[$this->lang1] . ': ' . Session::get_value('myvox')['menu_payment_token'];
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
																	<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/uploads/' . Session::get_value('myvox')['account']['logotype'] . '">
																</figure>
															</td>
														</tr>
														<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
															<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
																<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:24px;font-weight:600;text-align:center;color:#212121;">' . $mail2->Subject . '</h4>
																<h6 style="width:100%;margin:0px 0px 10px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('name')[$this->lang1] . ': ' . $_POST['firstname'] . ' ' . $_POST['lastname'] . '</h6>
																<h6 style="width:100%;margin:0px 0px 10px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('email')[$this->lang1] . ': ' . $_POST['email'] . '</h6>
																<h6 style="width:100%;margin:0px 0px 10px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('phone')[$this->lang1] . ': +' . $_POST['phone_lada'] . ' ' . $_POST['phone_number'] . '</h6>
																<h6 style="width:100%;margin:0px 0px 10px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('total')[$this->lang1] . ': $ ' . Session::get_value('myvox')['menu_order']['total'] . ' ' . Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency'] . '</h6>';

											foreach (Session::get_value('myvox')['menu_order']['shopping_cart'] as $value)
											{
												foreach ($value as $subvalue)
												{
													$mail2->Body .= '<p style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">x' . $subvalue['quantity'] . ' ' . $subvalue['name'][$this->lang2];

													if (!empty($subvalue['topics']))
													{
														$mail2->Body .= ' (';

														foreach ($subvalue['topics'] as $parentvalue)
															$mail2->Body .= $parentvalue['name'][$this->lang2] . ', ';

														$mail2->Body .= ')';
													}

													$mail2->Body .= '</p>';
												}
											}

											$mail2->Body .=
											'					<a style="width:100%;display:block;margin:20px 0px 0px 0px;padding:20px 0px;border-radius:40px;box-sizing:border-box;background-color:#00a5ab;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;" href="https://' . Configuration::$domain . '/menu/orders">' . Languages::email('give_follow_up')[$this->lang2] . '</a>
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
											$mail2->send();
										}
										catch (Exception $e) { }
									}

									if (!empty(Session::get_value('myvox')['account']['settings']['myvox']['menu']['phone']['lada']) AND !empty(Session::get_value('myvox')['account']['settings']['myvox']['menu']['phone']['number']))
									{
										$sms2 = $this->model->get_sms();

										if ($sms2 > 0)
										{
											$sms2_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
											$sms2_client = new \Nexmo\Client($sms2_basic);
											$sms2_text = Session::get_value('myvox')['account']['name'] . '. ' . Languages::email('new_menu_order')[$this->lang1] . '. https://' . Configuration::$domain . '/menu/orders. Power by Guestvox.';

											try
											{
												$sms2_client->message()->send([
													'to' => Session::get_value('myvox')['account']['settings']['myvox']['menu']['phone']['lada'] . Session::get_value('myvox')['account']['settings']['myvox']['menu']['phone']['number'],
													'from' => 'Guestvox',
													'text' => $sms2_text
												]);

												$sms2 = $sms2 - 1;
											}
											catch (Exception $e) { }

											$this->model->edit_sms($sms2);
										}

										$whatsapp2 = $this->model->get_whatsapp();

										if ($whatsapp2 > 0)
										{
											$whatsapp2_headers = array(
												'Cache-Control: no-cache',
												'Content-Type: application/x-www-form-urlencoded',
												'Accept: text/plain',
												'Apikey: b70ed8d3c58248a6c8581923e3df00fa'
											);

											$whatsapp2_text = Session::get_value('myvox')['account']['name'] . '. ' . Languages::email('new_menu_order')[$this->lang1] . '. https://' . Configuration::$domain . '/menu/orders. Power by Guestvox.';

											try
											{
												$whatsapp2_ch = curl_init();

												curl_setopt($whatsapp2_ch, CURLOPT_URL, 'https://api.gupshup.io/sm/api/v1/msg');
												curl_setopt($whatsapp2_ch, CURLOPT_RETURNTRANSFER, 1);
												curl_setopt($whatsapp2_ch, CURLOPT_POST, 1);
												curl_setopt($whatsapp2_ch, CURLOPT_POSTFIELDS, 'channel=whatsapp&source=525532012511&destination=' . Session::get_value('myvox')['account']['settings']['myvox']['menu']['phone']['lada'] . Session::get_value('myvox')['account']['settings']['myvox']['menu']['phone']['number'] . '&message=' . urlencode($whatsapp2_text));
												curl_setopt($whatsapp2_ch, CURLOPT_HTTPHEADER, $whatsapp2_headers);

												$whatsapp2_result = curl_exec($whatsapp2_ch);

												curl_close($whatsapp2_ch);

												$whatsapp2 = $whatsapp2 - 1;
											}
											catch (Exception $e) { }

											$this->model->edit_whatsapp($whatsapp2);
										}
									}
								}

								// $options = array(
								// 	'cluster' => 'us2',
								// 	'useTLS' => true
								// );
								//
								// $pusher = new Pusher\Pusher(
								// 	'1907b80d942422da0b8e',
								// 	'e565c25c10f7f68fc3fc',
								// 	'1065298',
								// 	$options
								// );
								//
								// $pusher->trigger('menu-orders', 'new-order', 'Pedido enviado');

								$myvox = Session::get_value('myvox');

								$myvox['menu_order'] = null;
								$myvox['menu_payment_token'] = null;

								Session::set_value('myvox', $myvox);

								Functions::environment([
									'status' => 'success',
									'message' => '{$lang.thanks_received_menu_order}' . ((Session::get_value('myvox')['url'] == 'delivery') ? ' {$lang.thanks_received_myvox_1} <strong>' . $_POST['email'] . '</strong> {$lang.thanks_received_myvox_2}' : ''),
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
								'labels' => $labels
							]);
						}
					}
					else
					{
						Functions::environment([
							'status' => 'error',
							'message' => '{$lang.payment_error}'
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
					$html .= '<section data-menu-categories>';

					foreach ($this->model->get_menu_categories() as $value)
					{
						$html .=
						'<div>
							<figure class="' . (in_array($value['id'], Session::get_value('myvox')['menu_categories']) ? 'selected' : '') . '">
								<img src="{$path.images}icons/' . $value['icon_type'] . '/' . $value['icon_url'] . '">
								<a data-action="filter_menu_products_by_categories" data-id="' . $value['id'] . '"></a>
							</figure>
							<span>' . $value['name'][$this->lang1] . '</span>
						</div>';
					}

					$html .=
				    '</section>
					<section data-menu-products>';

					foreach ($this->model->get_menu_products() as $value)
					{
						if (in_array(Functions::get_current_day(), $value['available']['days']))
						{
							if ((empty($value['available']['start_date']) AND empty($value['available']['end_date'])) OR (!empty($value['available']['start_date']) AND !empty($value['available']['end_date']) AND Functions::get_current_date() >= $value['available']['start_date'] AND Functions::get_current_date() <= $value['available']['end_date']))
							{
								$html .=
								'<div>
									<figure class="' . $value['avatar'] . '" ' . (($value['avatar'] == 'icon') ? 'style="background-color:' . $value['icon_color'] . ';"' : '') . '>';

								if ($value['avatar'] == 'image')
									$html .= '<img src="{$path.uploads}' . $value['image'] . '">';
								else if ($value['avatar'] == 'icon')
									$html .= '<img src="{$path.images}icons/' . $value['icon_type'] . '/' . $value['icon_url'] . '"></figure>';

								$html .=
								'	</figure>
									<div>
										<h2>' . $value['name'][$this->lang1] . '</h2>
										' . (!empty($value['price']) ? '<span>' . Functions::get_formatted_currency($value['price'], Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency']) . (!empty($value['topics']) ? ' (+ {$lang.topics})' : '') . '</span>' : '') . '
										<a data-action="preview_menu_product" data-id="' . $value['id'] . '"></a>
									</div>
								</div>';
							}
						}
					}

					$html .=
					'</section>
					<section class="modal fullscreen" data-modal="preview_menu_product">
					    <div class="content">
					        <main></main>
					    </div>
					</section>';
				}
				else if ($params[1] == 'search')
				{
					$html .=
					'<section data-search>
						<form name="search">
							<div class="row">
								<div class="span12">
									<div class="label">
										<label unrequired>
											<p>{$lang.what_do_you_want_to_eat}</p>
											<input type="text" name="name" value="' . Session::get_value('myvox')['menu_name'] . '">
										</label>
									</div>
								</div>
								<div class="span12">
									<div class="label">
										<label unrequired>
											<p>{$lang.how_much_is_your_budget}</p>
											<input type="number" name="price" value="' . Session::get_value('myvox')['menu_price'] . '">
										</label>
									</div>
								</div>
								<div class="span12">
									<div data-menu-categories>
										<p>{$lang.choose_your_categories}</p>';

					foreach ($this->model->get_menu_categories() as $value)
					{
						$html .=
						'<div>
							<figure class="' . (in_array($value['id'], Session::get_value('myvox')['menu_categories']) ? 'selected' : '') . '">
								<img src="{$path.images}icons/' . $value['icon_type'] . '/' . $value['icon_url'] . '">
								<a data-action="filter_menu_products_by_categories" data-id="' . $value['id'] . '"></a>
							</figure>
							<span>' . $value['name'][$this->lang1] . '</span>
						</div>';
					}

					$html .=
					'				</div>
								</div>
								<div class="span12">
									<div class="buttons">
										<a data-action="clear_search"><i class="fas fa-sync-alt"></i></a>
										<button class="new" type="submit"><i class="fas fa-search"></i></button>
									</div>
								</div>
							</div>
						</form>
					</section>';
				}
				else if ($params[1] == 'order')
				{
					$html .=
					'<section data-total>
						<span>' . Functions::get_formatted_currency(Session::get_value('myvox')['menu_order']['total'], Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency']) . '</span>
					</section>
					<section data-shopping-cart>';

					foreach (Session::get_value('myvox')['menu_order']['shopping_cart'] as $key => $value)
					{
						foreach ($value as $subkey => $subvalue)
						{
							$html .=
							'<div>
								<h2>x' . $subvalue['quantity'] . ' ' . $subvalue['name'][$this->lang1] . '</h2>';

							foreach ($subvalue['topics'] as $parentvalue)
								$html .= '<h3><i class="fas fa-check"></i>' . $parentvalue['name'][$this->lang1] . '</h3>';

							$html .=
								(!empty($subvalue['total']) ? '<span>' . Functions::get_formatted_currency($subvalue['total'], Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency']) . '</span>' : '') . '
								<a class="delete" data-action="remove_to_menu_order" data-key="' . $key . '" data-subkey="' . $subkey . '"><i class="fas fa-trash"></i></a>
							</div>';
						}
					}

					if (((Session::get_value('myvox')['url'] == 'owner' AND Session::get_value('myvox')['account']['settings']['myvox']['menu']['requests'] == true) OR (Session::get_value('myvox')['url'] == 'delivery' AND Session::get_value('myvox')['account']['settings']['myvox']['menu']['delivery'] == true)) AND !empty(Session::get_value('myvox')['menu_order']['shopping_cart']))
					{
						$html .=
						'<form name="new_menu_order">
							<div class="row">';

						if (Session::get_value('myvox')['url'] == 'delivery')
						{
							$html .=
							'<div class="span12">
								<div class="label">
									<label required>
										<p>{$lang.where_do_we_deliver_you}</p>
										<select name="delivery">
											<option value="bring">{$lang.my_home}</option>
											<option value="collect">{$lang.i_want_pick_up_' . Session::get_value('myvox')['account']['type'] . '}</option>
										</select>
									</label>
								</div>
							</div>
							<div class="span12">
								<div class="label">
									<label required>
										<p>{$lang.address} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
										<input type="text" name="address">
									</label>
								</div>
							</div>
							<div class="span12">
								<div id="delivery_map" data-lat="' . Session::get_value('myvox')['account']['location']['lat'] . '" data-lng="' . Session::get_value('myvox')['account']['location']['lng'] . '"></div>
								<input id="delivery_lat" type="text" class="hidden" name="delivery_lat">
								<input id="delivery_lng" type="text" class="hidden" name="delivery_lng">
							</div>
							<div class="span6">
								<div class="label">
									<label required>
										<p>{$lang.firstname}</p>
										<input type="text" name="firstname">
									</label>
								</div>
							</div>
							<div class="span6">
								<div class="label">
									<label required>
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
									<label required>
										<p>{$lang.lada}</p>
										<select name="phone_lada">
											<option value="" hidden>{$lang.choose}</option>';

							foreach ($this->model->get_countries() as $value)
								$html .= '<option value="' . $value['lada'] . '">' . $value['name'][$this->lang1] . ' (+' . $value['lada'] . ')</option>';

							$html .=
							'			</select>
									</label>
								</div>
							</div>
							<div class="span3">
								<div class="label">
									<label required>
										<p>{$lang.phone}</p>
										<input type="number" name="phone_number">
									</label>
								</div>
							</div>';
						}

						$html .=
						'<div class="span12">
							<div class="label">
								<label required>
									<p>{$lang.payment_method}</p>
									<select name="payment_method">
										<option value="cash">{$lang.cash}</option>
										' . ((Session::get_value('myvox')['account']['payment']['status'] == true) ? '<option value="credit_card">{$lang.credit_card}</option>' : '') . '
										' . ((Session::get_value('myvox')['account']['payment']['status'] == true) ? '<option value="debit_card">{$lang.debit_card}</option>' : '') . '
									</select>
								</label>
							</div>
						</div>
						<div class="span12">
							<div class="label">
								<label required>
									<p>{$lang.how_much_to_pay}</p>
									<input type="number" name="payment_cash">
								</label>
							</div>
						</div>';

						if (Session::get_value('myvox')['account']['payment']['status'] == true)
						{
							if (Session::get_value('myvox')['account']['payment']['type'] == 'mit')
							{
								if (Session::get_value('myvox')['menu_order']['total'] > 0)
								{
									$mit = Functions::api('mit', null, 'get', null, [
										'amount' => Session::get_value('myvox')['menu_order']['total'],
										'mit' => Session::get_value('myvox')['account']['payment']['mit']['code'],
										'currency' => Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency'],
										'types' => Session::get_value('myvox')['account']['payment']['mit']['types']
									]);

									$html .= '<div id="payment_with_card" class="span12 hidden">';

									if (!empty($mit['code']) AND $mit['code'] == 'success')
										$html .= '<iframe src="' . $mit['url'] . '" width="100%" height="645px"></iframe>';
									else
										$html .= '<p>' . $mit['message'] . '</p>';

									$html .= '</div>';
								}
							}
						}

						$html .=
						'		<div class="span12">
									<div class="buttons">
										<button class="new" type="submit">{$lang.generate_order}</button>
									</div>
								</div>
							</div>
						</form>';
					}

					$html .= '</section>';
				}

				$html .=
				'<section class="buttons">
					<a href="/' . $params[0] . '/menu/products" ' . (($params[1] == 'products') ? 'class="active"' : '') . '><i class="fas fa-igloo"></i><span>{$lang.home}</span></a>
					<a href="/' . $params[0] . '/menu/search" ' . (($params[1] == 'search') ? 'class="active"' : '') . '><i class="fas fa-search"></i><span>{$lang.search}</span></a>
					<a ' . ((Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule'][Functions::get_current_day()]['status'] == 'open' AND Functions::get_current_hour() >= Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule'][Functions::get_current_day()]['opening'] AND Functions::get_current_hour() <= Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule'][Functions::get_current_day()]['closing']) ? 'href="/' . $params[0] . '/menu/order" ' . (($params[1] == 'order') ? 'class="active"' : '') . ' data-total' : '') . '><i class="fas fa-shopping-cart"></i><span>' . ((Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule'][Functions::get_current_day()]['status'] == 'open' AND Functions::get_current_hour() >= Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule'][Functions::get_current_day()]['opening'] AND Functions::get_current_hour() <= Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule'][Functions::get_current_day()]['closing']) ? Functions::get_formatted_currency(Session::get_value('myvox')['menu_order']['total'], Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency']) : '{$lang.business_closed}') . '</span></a>
				</section>';

				$replace = [
					'{$logotype}' => '{$path.uploads}' . Session::get_value('myvox')['account']['logotype'],
					'{$btn_home}' => (Session::get_value('myvox')['url'] == 'delivery') ? '<a data-action="share" data-title="' . Session::get_value('myvox')['account']['name'] . '" data-text="{$lang.share_delivery}" data-url="https://' . Configuration::$domain . '/' . $params[0] . '/myvox/delivery"><i class="fas fa-share-alt"></i></a>' : '<a href="/' . $params[0] . '/myvox/owner/' . Session::get_value('myvox')['owner']['token'] . '"><i class="fas fa-house-user"></i></a>',
					'{$html}' => $html
				];

				$template = $this->format->replace($replace, $template);

				echo $template;
			}
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
					if (Session::get_value('myvox')['url'] == 'owner' AND !empty(Session::get_value('myvox')['owner']))
						$break = false;
				}
			}
		}

		if ($break == false)
		{
			if (Format::exist_ajax_request() == true)
			{
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

					if (empty($labels))
					{
						$_POST['type'] = 'request';
						$_POST['token'] = strtolower(Functions::get_random(8));

						$query = $this->model->new_vox($_POST);

						if (!empty($query))
						{
							// $_POST['opportunity_area'] = $this->model->get_opportunity_area($_POST['opportunity_area']);
							// $_POST['opportunity_type'] = $this->model->get_opportunity_type($_POST['opportunity_type']);
							// $_POST['location'] = $this->model->get_location($_POST['location']);
							// $_POST['assigned_users'] = $this->model->get_assigned_users($_POST['opportunity_area']['id']);
							//
							// $mail2 = new Mailer(true);
							//
							// try
							// {
							// 	$mail2->setFrom('noreply@guestvox.com', 'Guestvox');
							//
							// 	foreach ($_POST['assigned_users'] as $value)
							// 		$mail2->addAddress($value['email'], $value['firstname'] . ' ' . $value['lastname']);
							//
							// 	$mail2->Subject = Languages::email('new', 'request')[$this->lang2];
							// 	$mail2->Body =
							// 	'<html>
							// 		<head>
							// 			<title>' . $mail2->Subject . '</title>
							// 		</head>
							// 		<body>
							// 			<table style="width:600px;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#eee">
							// 				<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
							// 					<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
							// 						<figure style="width:100%;margin:0px;padding:0px;text-align:center;">
							// 							<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/images/logotype_color.png">
							// 						</figure>
							// 					</td>
							// 				</tr>
							// 				<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
							// 					<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
							// 						<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:18px;font-weight:600;text-align:center;color:#212121;">' . $mail2->Subject . '</h4>
							// 						<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('token')[$this->lang2] . ': ' . $_POST['token'] . '</h6>
							// 						<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('owner')[$this->lang2] . ': ' . Session::get_value('myvox')['owner']['name'][$this->lang2] . (!empty(Session::get_value('myvox')['owner']['number']) ? ' #' . Session::get_value('myvox')['owner']['number'] : '') . '</h6>
							// 						<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('opportunity_area')[$this->lang2] . ': ' . $_POST['opportunity_area']['name'][$this->lang2] . '</h6>
							// 						<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('opportunity_type')[$this->lang2] . ': ' . $_POST['opportunity_type']['name'][$this->lang2] . '</h6>
							// 						<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('started_date')[$this->lang2] . ': ' . Functions::get_formatted_date($_POST['started_date'], 'd.m.Y') . '</h6>
							// 						<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('started_hour')[$this->lang2] . ': ' . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '</h6>
							// 						<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('location')[$this->lang2] . ': ' . $_POST['location']['name'][$this->lang2] . '</h6>
							// 						<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('urgency')[$this->lang2] . ': ' . Languages::email('medium')[$this->lang2] . '</h6>
							// 						<p style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('observations')[$this->lang2] . ': ' . (!empty($_POST['observations']) ? $_POST['observations'] : Languages::email('not_observations')[$this->lang2]) . '</p>
							// 						<a style="width:100%;display:block;margin:0px;padding:20px 0px;border-radius:40px;box-sizing:border-box;background-color:#00a5ab;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;" href="https://' . Configuration::$domain . '/voxes/details/' . $_POST['token'] . '">' . Languages::email('give_follow_up')[$this->lang2] . '</a>
							// 					</td>
							// 				</tr>
							// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
							// 					<td style="width:100%;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#fff;">
							// 						<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://' . Configuration::$domain . '">' . Configuration::$domain . '</a>
							// 					</td>
							// 				</tr>
							// 			</table>
							// 		</body>
							// 	</html>';
							// 	$mail2->send();
							// }
							// catch (Exception $e) { }
							//
							// $sms2 = $this->model->get_sms();
							//
							// if ($sms2 > 0)
							// {
							// 	$sms2_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
							// 	$sms2_client = new \Nexmo\Client($sms2_basic);
							// 	$sms2_text = 'Guestvox. ' . Languages::email('new', 'request')[$this->lang2] . '. ';
							// 	$sms2_text .= Languages::email('token')[$this->lang2] . ': ' . $_POST['token'] . '. ';
							// 	$sms2_text .= Languages::email('owner')[$this->lang2] . ': ' . Session::get_value('myvox')['owner']['name'][$this->lang2] . (!empty(Session::get_value('myvox')['owner']['number']) ? ' #' . Session::get_value('myvox')['owner']['number'] : '') . '. ';
							// 	$sms2_text .= Languages::email('opportunity_area')[$this->lang2] . ': ' . $_POST['opportunity_area']['name'][$this->lang2] . '. ';
							// 	$sms2_text .= Languages::email('opportunity_type')[$this->lang2] . ': ' . $_POST['opportunity_type']['name'][$this->lang2] . '. ';
							// 	$sms2_text .= Languages::email('started_date')[$this->lang2] . ': ' . Functions::get_formatted_date($_POST['started_date'], 'd M y') . '. ';
							// 	$sms2_text .= Languages::email('started_hour')[$this->lang2] . ': ' . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '. ';
							// 	$sms2_text .= Languages::email('location')[$this->lang2] . ': ' . $_POST['location']['name'][$this->lang2] . '. ';
							// 	$sms2_text .= Languages::email('urgency')[$this->lang2] . ': ' . Languages::email('medium')[$this->lang2] . '. ';
							// 	$sms2_text .= Languages::email('observations')[$this->lang2] . ': ' . (!empty($_POST['observations']) ? $_POST['observations'] : Languages::email('not_observations')[$this->lang2]) . '. ';
							// 	$sms2_text .= 'https://' . Configuration::$domain . '/voxes/details/' . $_POST['token'];
							//
							// 	foreach ($_POST['assigned_users'] as $value)
							// 	{
							// 		if ($sms2 > 0)
							// 		{
							// 			try
							// 			{
							// 				$sms2_client->message()->send([
							// 					'to' => $value['phone']['lada'] . $value['phone']['number'],
							// 					'from' => 'Guestvox',
							// 					'text' => $sms2_text
							// 				]);
							//
							// 				$sms2 = $sms2 - 1;
							// 			}
							// 			catch (Exception $e) { }
							// 		}
							// 	}
							//
							// 	$this->model->edit_sms($sms2);
							// }

							Functions::environment([
								'status' => 'success',
								'message' => '{$lang.thanks_received_request}',
								'path' => '/' . $params[0] . '/myvox/owner/' . Session::get_value('myvox')['owner']['token']
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

				define('_title', Session::get_value('myvox')['account']['name'] . ' | {$lang.request}');

				$html =
				'<form name="new_request">
					<div class="row">
						<div class="span6">
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
				'					</select>
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
						<div class="span12">
							<div class="buttons">
								<a href="/' . $params[0] . '/myvox/owner/' . Session::get_value('myvox')['owner']['token'] . '" class="delete"><i class="fas fa-times"></i></a>
								<button type="submit" class="new"><i class="fas fa-check"></i></button>
							</div>
						</div>
					</div>
				</form>';

				$replace = [
					'{$logotype}' => '{$path.uploads}' . Session::get_value('myvox')['account']['logotype'],
					'{$btn_home}' => '<a href="/' . $params[0] . '/myvox/owner/' . Session::get_value('myvox')['owner']['token'] . '"><i class="fas fa-house-user"></i></a>',
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
					if (Session::get_value('myvox')['url'] == 'owner' AND !empty(Session::get_value('myvox')['owner']))
						$break = false;
				}
			}
		}

		if ($break == false)
		{
			if (Format::exist_ajax_request() == true)
			{
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

					if (empty($labels))
					{
						$_POST['type'] = 'incident';
						$_POST['token'] = strtolower(Functions::get_random(8));

						$query = $this->model->new_vox($_POST);

						if (!empty($query))
						{
							// $_POST['opportunity_area'] = $this->model->get_opportunity_area($_POST['opportunity_area']);
							// $_POST['opportunity_type'] = $this->model->get_opportunity_type($_POST['opportunity_type']);
							// $_POST['location'] = $this->model->get_location($_POST['location']);
							// $_POST['assigned_users'] = $this->model->get_assigned_users($_POST['opportunity_area']['id']);
							//
							// $mail2 = new Mailer(true);
							//
							// try
							// {
							// 	$mail2->setFrom('noreply@guestvox.com', 'Guestvox');
							//
							// 	foreach ($_POST['assigned_users'] as $value)
							// 		$mail2->addAddress($value['email'], $value['firstname'] . ' ' . $value['lastname']);
							//
							// 	$mail2->Subject = Languages::email('new', 'incident')[$this->lang2];
							// 	$mail2->Body =
							// 	'<html>
							// 		<head>
							// 			<title>' . $mail2->Subject . '</title>
							// 		</head>
							// 		<body>
							// 			<table style="width:600px;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#eee">
							// 				<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
							// 					<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
							// 						<figure style="width:100%;margin:0px;padding:0px;text-align:center;">
							// 							<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/images/logotype_color.png">
							// 						</figure>
							// 					</td>
							// 				</tr>
							// 				<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
							// 					<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
							// 						<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:18px;font-weight:600;text-align:center;color:#212121;">' . $mail2->Subject . '</h4>
							// 						<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('token')[$this->lang2] . ': ' . $_POST['token'] . '</h6>
							// 						<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('owner')[$this->lang2] . ': ' . Session::get_value('myvox')['owner']['name'][$this->lang2] . (!empty(Session::get_value('myvox')['owner']['number']) ? ' #' . Session::get_value('myvox')['owner']['number'] : '') . '</h6>
							// 						<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('opportunity_area')[$this->lang2] . ': ' . $_POST['opportunity_area']['name'][$this->lang2] . '</h6>
							// 						<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('opportunity_type')[$this->lang2] . ': ' . $_POST['opportunity_type']['name'][$this->lang2] . '</h6>
							// 						<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('started_date')[$this->lang2] . ': ' . Functions::get_formatted_date($_POST['started_date'], 'd.m.Y') . '</h6>
							// 						<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('started_hour')[$this->lang2] . ': ' . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '</h6>
							// 						<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('location')[$this->lang2] . ': ' . $_POST['location']['name'][$this->lang2] . '</h6>
							// 						<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('urgency')[$this->lang2] . ': ' . Languages::email('medium')[$this->lang2] . '</h6>
							// 						<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('confidentiality')[$this->lang2] . ': ' . Languages::email('not')[$this->lang2] . '</h6>
							// 						<p style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('description')[$this->lang2] . ': ' . (!empty($_POST['description']) ? $_POST['description'] : Languages::email('not_description')[$this->lang2]) . '</p>
							// 						<a style="width:100%;display:block;margin:0px;padding:20px 0px;border-radius:40px;box-sizing:border-box;background-color:#00a5ab;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;" href="https://' . Configuration::$domain . '/voxes/details/' . $_POST['token'] . '">' . Languages::email('give_follow_up')[$this->lang2] . '</a>
							// 					</td>
							// 				</tr>
							// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
							// 					<td style="width:100%;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#fff;">
							// 						<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://' . Configuration::$domain . '">' . Configuration::$domain . '</a>
							// 					</td>
							// 				</tr>
							// 			</table>
							// 		</body>
							// 	</html>';
							// 	$mail2->send();
							// }
							// catch (Exception $e) { }
							//
							// $sms2 = $this->model->get_sms();
							//
							// if ($sms2 > 0)
							// {
							// 	$sms2_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
							// 	$sms2_client = new \Nexmo\Client($sms2_basic);
							// 	$sms2_text = 'Guestvox. ' . Languages::email('new', 'incident')[$this->lang2] . '. ';
							// 	$sms2_text .= Languages::email('token')[$this->lang2] . ': ' . $_POST['token'] . '. ';
							// 	$sms2_text .= Languages::email('owner')[$this->lang2] . ': ' . Session::get_value('myvox')['owner']['name'][$this->lang2] . (!empty(Session::get_value('myvox')['owner']['number']) ? ' #' . Session::get_value('myvox')['owner']['number'] : '') . '. ';
							// 	$sms2_text .= Languages::email('opportunity_area')[$this->lang2] . ': ' . $_POST['opportunity_area']['name'][$this->lang2] . '. ';
							// 	$sms2_text .= Languages::email('opportunity_type')[$this->lang2] . ': ' . $_POST['opportunity_type']['name'][$this->lang2] . '. ';
							// 	$sms2_text .= Languages::email('started_date')[$this->lang2] . ': ' . Functions::get_formatted_date($_POST['started_date'], 'd M y') . '. ';
							// 	$sms2_text .= Languages::email('started_hour')[$this->lang2] . ': ' . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '. ';
							// 	$sms2_text .= Languages::email('location')[$this->lang2] . ': ' . $_POST['location']['name'][$this->lang2] . '. ';
							// 	$sms2_text .= Languages::email('urgency')[$this->lang2] . ': ' . Languages::email('medium')[$this->lang2] . '. ';
							// 	$sms2_text .= Languages::email('confidentiality')[$this->lang2] . ': ' . Languages::email('not')[$this->lang2] . '. ';
							// 	$sms2_text .= Languages::email('description')[$this->lang2] . ': ' . (!empty($_POST['description']) ? $_POST['description'] : Languages::email('not_description')[$this->lang2]) . '. ';
							// 	$sms2_text .= 'https://' . Configuration::$domain . '/voxes/details/' . $_POST['token'];
							//
							// 	foreach ($_POST['assigned_users'] as $value)
							// 	{
							// 		if ($sms2 > 0)
							// 		{
							// 			try
							// 			{
							// 				$sms2_client->message()->send([
							// 					'to' => $value['phone']['lada'] . $value['phone']['number'],
							// 					'from' => 'Guestvox',
							// 					'text' => $sms2_text
							// 				]);
							//
							// 				$sms2 = $sms2 - 1;
							// 			}
							// 			catch (Exception $e) { }
							// 		}
							// 	}
							//
							// 	$this->model->edit_sms($sms2);
							// }

							Functions::environment([
								'status' => 'success',
								'message' => '{$lang.thanks_received_incident}',
								'path' => '/' . $params[0] . '/myvox/owner/' . Session::get_value('myvox')['owner']['token']
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

				define('_title', Session::get_value('myvox')['account']['name'] . ' | {$lang.incident}');

				$html =
				'<form name="new_incident">
					<div class="row">
						<div class="span6">
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
				'					</select>
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
						<div class="span12">
							<div class="buttons">
								<a href="/' . $params[0] . '/myvox/owner/' . Session::get_value('myvox')['owner']['token'] . '" class="delete"><i class="fas fa-times"></i></a>
								<button type="submit" class="new"><i class="fas fa-check"></i></button>
							</div>
						</div>
					</div>
				</form>';

				$replace = [
					'{$logotype}' => '{$path.uploads}' . Session::get_value('myvox')['account']['logotype'],
					'{$btn_home}' => '<a href="/' . $params[0] . '/myvox/owner/' . Session::get_value('myvox')['owner']['token'] . '"><i class="fas fa-house-user"></i></a>',
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

		if (!empty($params[1]))
		{
			$account = $this->model->get_account($params[0]);

			if (!empty($account))
			{
				$survey = $this->model->get_survey($params[1], $account['id']);

				if (!empty($survey))
				{
					if ($account['surveys'] == true AND $account['settings']['myvox']['survey']['status'] == true)
					{
						Session::set_value('myvox', [
							'account' => $account,
							'owner' => null,
							'url' => 'survey',
							'survey' => $survey
						]);

						$break = false;
					}
				}
			}
		}
		else
		{
			$survey = $this->model->get_survey('main');

			if (!empty($survey))
			{
				if (Session::exists_var('myvox') == true AND !empty(Session::get_value('myvox')['account']))
				{
					if (Session::get_value('myvox')['account']['surveys'] == true AND Session::get_value('myvox')['account']['settings']['myvox']['survey']['status'] == true)
					{
						$myvox = Session::get_value('myvox');

						$myvox['survey'] = $survey;

						Session::set_value('myvox', $myvox);

						if (!empty(Session::get_value('myvox')['url']))
						{
							if (Session::get_value('myvox')['url'] == 'owner' AND !empty(Session::get_value('myvox')['owner']))
								$break = false;
						}
					}
				}
			}
		}

		if ($break == false)
		{
			if (Format::exist_ajax_request() == true)
			{
				if ($_POST['action'] == 'new_survey_answer')
				{
					$labels = [];

					if (Session::get_value('myvox')['url'] == 'survey')
					{
						if (!isset($_POST['firstname']) OR empty($_POST['firstname']))
							array_push($labels, ['firstname','']);

						if (!isset($_POST['lastname']) OR empty($_POST['lastname']))
							array_push($labels, ['lastname','']);

						if (!isset($_POST['email']) OR empty($_POST['email']) OR Functions::check_email($_POST['email']) == false)
							array_push($labels, ['email','']);
					}

					if (empty($labels))
					{
						if (Session::get_value('myvox')['survey']['signature'] == true AND (!isset($_POST['signature']) OR empty($_POST['signature'])))
						{
							Functions::environment([
								'status' => 'error',
								'message' => 'Porfavor ingrese su firma'
							]);
						}
						else if ((Session::get_value('myvox')['survey']['signature'] == true AND !empty($_POST['signature'])) OR Session::get_value('myvox')['survey']['signature'] == false)
						{
							$_POST['token'] = strtolower(Functions::get_random(8));

							$query = $this->model->new_survey_answer($_POST);

							if (!empty($query))
							{
								$mail1_subject = Session::get_value('myvox')['account']['settings']['myvox']['survey']['mail']['subject'][$this->lang1];
								$mail1_description = Session::get_value('myvox')['account']['settings']['myvox']['survey']['mail']['description'][$this->lang1];

								if (!empty($mail1_subject) AND !empty($mail1_description))
								{
									$mail1 = new Mailer(true);
								
									try
									{
										$mail1->setFrom('noreply@guestvox.com', 'Guestvox');
										$mail1->addAddress($_POST['email'], $_POST['firstname'] . ' ' . $_POST['lastname']);
										$mail1->Subject = $mail1_subject;
										$mail1->Body =
										'<html>
											<head>
												<title>' . $mail1_subject . '</title>
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
															<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:18px;font-weight:600;text-align:center;color:#212121;">' . $mail1_subject . '</h4>
															<p style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . $mail1_description . '</p>
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
										$mail1->send();
									}
									catch (Exception $e) { }
									
									// $sms2 = $this->model->get_sms();
									//
									// if ($sms2 > 0)
									// {
									// 	$sms2_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
									// 	$sms2_client = new \Nexmo\Client($sms2_basic);
									// 	$sms2_text = 'Guestvox. ' . Languages::email('new', 'request')[$this->lang2] . '. ';
									// 	$sms2_text .= Languages::email('token')[$this->lang2] . ': ' . $_POST['token'] . '. ';
									// 	$sms2_text .= Languages::email('owner')[$this->lang2] . ': ' . Session::get_value('myvox')['owner']['name'][$this->lang2] . (!empty(Session::get_value('myvox')['owner']['number']) ? ' #' . Session::get_value('myvox')['owner']['number'] : '') . '. ';
									// 	$sms2_text .= Languages::email('opportunity_area')[$this->lang2] . ': ' . $_POST['opportunity_area']['name'][$this->lang2] . '. ';
									// 	$sms2_text .= Languages::email('opportunity_type')[$this->lang2] . ': ' . $_POST['opportunity_type']['name'][$this->lang2] . '. ';
									// 	$sms2_text .= Languages::email('started_date')[$this->lang2] . ': ' . Functions::get_formatted_date($_POST['started_date'], 'd M y') . '. ';
									// 	$sms2_text .= Languages::email('started_hour')[$this->lang2] . ': ' . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '. ';
									// 	$sms2_text .= Languages::email('location')[$this->lang2] . ': ' . $_POST['location']['name'][$this->lang2] . '. ';
									// 	$sms2_text .= Languages::email('urgency')[$this->lang2] . ': ' . Languages::email('medium')[$this->lang2] . '. ';
									// 	$sms2_text .= Languages::email('observations')[$this->lang2] . ': ' . (!empty($_POST['observations']) ? $_POST['observations'] : Languages::email('not_observations')[$this->lang2]) . '. ';
									// 	$sms2_text .= 'https://' . Configuration::$domain . '/voxes/details/' . $_POST['token'];
									//
									// 	foreach ($_POST['assigned_users'] as $value)
									// 	{
									// 		if ($sms2 > 0)
									// 		{
									// 			try
									// 			{
									// 				$sms2_client->message()->send([
									// 					'to' => $value['phone']['lada'] . $value['phone']['number'],
									// 					'from' => 'Guestvox',
									// 					'text' => $sms2_text
									// 				]);
									//
									// 				$sms2 = $sms2 - 1;
									// 			}
									// 			catch (Exception $e) { }
									// 		}
									// 	}
									//
									// 	$this->model->edit_sms($sms2);
									// }
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
									'message' => '{$lang.thanks_answering_survey}',
									'widget' => $widget,
									// 'path' => '/' . $params[0] . '/' . ((Session::get_value('myvox')['url'] == 'survey') ? 'survey/' . Session::get_value('myvox')['survey']['token'] : 'myvox/owner/' . Session::get_value('myvox')['owner']['token'])
									'path' => 'https://guestvox.com'
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

				define('_title', Session::get_value('myvox')['account']['name'] . ' | {$lang.survey}');

				$html =
				'<form name="new_survey_answer">
					<div class="row">
						<div class="span12">
							<div class="tbl_stl_5" data-table>';

				foreach ($this->model->get_surveys_questions(Session::get_value('myvox')['survey']) as $value)
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
						'<label><i class="fas fa-thumbs-up"></i><input type="radio" name="' . $value['id'] . '" value="yes"></label>
						<label><i class="fas fa-thumbs-down"></i><input type="radio" name="' . $value['id'] . '" value="not"></label>';
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

					foreach ($this->model->get_surveys_questions(Session::get_value('myvox')['survey'], $value['id']) as $subvalue)
					{
						if ($subvalue['type'] == 'open' OR $subvalue['type'] == 'rate' OR $subvalue['type'] == 'twin' OR $subvalue['type'] == 'check')
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

							foreach ($this->model->get_surveys_questions(Session::get_value('myvox')['survey'], $subvalue['id']) as $parentvalue)
							{
								if ($parentvalue['type'] == 'open' OR $parentvalue['type'] == 'rate' OR $parentvalue['type'] == 'twin' OR $parentvalue['type'] == 'check')
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
						}
					}

					$html .= '</div>';
				}

				$html .=
				'	</div>
				</div>';

				if (Session::get_value('myvox')['url'] == 'survey')
				{
					$html .=
					'<div class="span6">
						<div class="label">
							<label required>
								<p>{$lang.firstname}</p>
								<input type="text" name="firstname" />
							</label>
						</div>
					</div>
					<div class="span6">
						<div class="label">
							<label required>
								<p>{$lang.lastname}</p>
								<input type="text" name="lastname" />
							</label>
						</div>
					</div>
					<div class="span12">
						<div class="label">
							<label required>
								<p>{$lang.email}</p>
								<input type="email" name="email" />
							</label>
						</div>
					</div>';
				}

				$html .=
				'<div class="span12">
					<div class="label">
						<label unrequired>
							<p>{$lang.commentary}</p>
							<textarea name="comment"></textarea>
						</label>
					</div>
				</div>';

				if (!empty(Session::get_value('myvox')['survey']['text'][$this->lang1]))
				{
					$html .=
					'<div class="span12">
						<p style="padding:20px;box-sizing:border-box;text-align:justify;background-color:#eee;">' . Session::get_value('myvox')['survey']['text'][$this->lang1] . '</p>
					</div>';
				}

				if (Session::get_value('myvox')['survey']['signature'] == true)
				{
					$html .=
					'<div class="span12">
						<div class="label">
							<label required>
								<p>{$lang.signature}</p>
								<div class="signature" id="signature">
									<canvas></canvas>
									<div>
										<a data-action="clean_signature"><i class="fas fa-trash"></i></a>
									</div>
								</div>
							</label>
						</div>
					</div>';
				}

				$html .=
				'		<div class="span12">
							<div class="buttons">
								' . ((Session::get_value('myvox')['url'] == 'survey') ? '' : '<a href="/' . $params[0] . '/myvox/owner/' . Session::get_value('myvox')['owner']['token'] . '" class="delete"><i class="fas fa-times"></i></a>') . '
								<button type="submit" class="new"><i class="fas fa-check"></i></button>
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
									<a class="new" button-close><i class="fas fa-check"></i></a>
								</div>
							</main>
						</div>
					</section>';
				}

				$replace = [
					'{$logotype}' => '{$path.uploads}' . Session::get_value('myvox')['account']['logotype'],
					'{$btn_home}' => (Session::get_value('myvox')['url'] == 'survey') ? '' : '<a href="/' . $params[0] . '/myvox/owner/' . Session::get_value('myvox')['owner']['token'] . '"><i class="fas fa-house-user"></i></a>',
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
