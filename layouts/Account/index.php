<?php

defined('_EXEC') or die;

$this->dependencies->add(['css', '{$path.plugins}signature_pad/signature_pad.css']);
$this->dependencies->add(['js', '{$path.plugins}signature_pad/signature_pad.js']);
$this->dependencies->add(['js', '{$path.js}Account/index.js']);
$this->dependencies->add(['other', '<script>menu_focus("account");</script>']);
$this->dependencies->add(['other', '<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBLCea8Q6BtcTHwY3YFCiB0EoHE5KnsMUE&callback=map"></script>']);

?>

%{header}%
<main class="dashboard">
    <section class="workspace">
        <div class="account">
            <div class="stl_1">
                <div class="stl_1" data-uploader="fast">
                    <figure data-preview>
                        <img src="{$logotype}">
                        <a data-action="edit_logotype" data-select><i class="fas fa-upload"></i></a>
                        <input type="file" name="logotype" accept="image/*" data-upload>
                    </figure>
                </div>
            </div>
            <div class="stl_5">
                <i class="fas fa-bowling-ball"></i>
                <h2>{$name}</h2>
                <span>{$token}</span>
                <a class="edit" data-action="edit_account"><i class="fas fa-pen"></i></a>
            </div>
            <div class="stl_5">
                <i class="fas fa-heart"></i>
                <h2>{$fiscal_name}</h2>
                <span>{$fiscal_id}</span>
                <a class="edit" data-action="edit_billing"><i class="fas fa-pen"></i></a>
            </div>
            <div class="stl_5">
                <i class="fas fa-map-marked-alt"></i>
                <h2>{$location}</h2>
                <span>{$lat_lng}</span>
                <a class="edit" data-action="edit_location"><i class="fas fa-pen"></i></a>
            </div>
            {$div_menu}
            {$div_public_requests}
            {$div_public_incidents}
            <!-- {$div_attention_times} -->
            <!-- {$div_siteminder} -->
            {$div_zaviapms}
            {$div_ambit}
            {$div_rappi}
            {$div_answer_surveys}
            {$div_reviews_page}
            {$div_payment}
            <div class="stl_5">
                <i class="fas fa-comment-alt"></i>
                <h2>{$lang.sms_credit}</h2>
                <span>{$sms} {$lang.sms}</span>
            </div>
            <div class="stl_5">
                <i class="fab fa-whatsapp"></i>
                <h2>{$lang.whatsapp_credit}</h2>
                <span>{$whatsapp} {$lang.sms}</span>
            </div>
        </div>
    </section>
</main>
<section class="modal fullscreen" data-modal="edit_account">
    <div class="content">
        <main>
            <form name="edit_account">
                <div class="row">
                    <div class="span12">
                        <div class="label">
                            <label required>
                                <p>{$lang.name}</p>
                                <input type="text" name="name">
                            </label>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="label">
                            <label required>
                                <p>{$lang.country}</p>
                                <select name="country">
                                    {$opt_countries}
                                </select>
                            </label>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="label">
                            <label required>
                                <p>{$lang.city}</p>
                                <input type="text" name="city">
                            </label>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="label">
                            <label required>
                                <p>{$lang.zip_code}</p>
                                <input type="text" name="zip_code">
                            </label>
                        </div>
                    </div>
                    <div class="span12">
                        <div class="label">
                            <label required>
                                <p>{$lang.address}</p>
                                <input type="text" name="address">
                            </label>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="label">
                            <label required>
                                <p>{$lang.time_zone}</p>
                                <select name="time_zone">
                                    {$opt_times_zones}
                                </select>
                            </label>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="label">
                            <label required>
                                <p>{$lang.currency}</p>
                                <select name="currency">
                                    {$opt_currencies}
                                </select>
                            </label>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="label">
                            <label required>
                                <p>{$lang.language}</p>
                                <select name="language">
                                    {$opt_languages}
                                </select>
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
</section>
<section class="modal fullscreen" data-modal="edit_billing">
    <div class="content">
        <main>
            <form name="edit_billing">
                <div class="row">
                    <div class="span6">
                        <div class="label">
                            <label required>
                                <p>{$lang.fiscal_id}</p>
                                <input type="text" name="fiscal_id">
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label required>
                                <p>{$lang.fiscal_name}</p>
                                <input type="text" name="fiscal_name">
                            </label>
                        </div>
                    </div>
                    <div class="span12">
                        <div class="label">
                            <label required>
                                <p>{$lang.fiscal_address}</p>
                                <input type="text" name="fiscal_address">
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label required>
                                <p>{$lang.firstname}</p>
                                <input type="text" name="contact_firstname">
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label required>
                                <p>{$lang.lastname}</p>
                                <input type="text" name="contact_lastname">
                            </label>
                        </div>
                    </div>
                    <div class="span12">
                        <div class="label">
                            <label required>
                                <p>{$lang.department}</p>
                                <input type="text" name="contact_department">
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label required>
                                <p>{$lang.email}</p>
                                <input type="text" name="contact_email">
                            </label>
                        </div>
                    </div>
                    <div class="span3">
                        <div class="label">
                            <label required>
                                <p>{$lang.lada}</p>
                                <select name="contact_phone_lada">
                                    {$opt_ladas}
                                </select>
                            </label>
                        </div>
                    </div>
                    <div class="span3">
                        <div class="label">
                            <label required>
                                <p>{$lang.phone}</p>
                                <input type="text" name="contact_phone_number">
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
</section>
<section class="modal fullscreen" data-modal="edit_location">
    <div class="content">
        <main>
            <form name="edit_location">
                <div class="row">
                    <div class="span12">
                        <div id="location_map" data-lat="{$lat}" data-lng="{$lng}"></div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label required>
                                <p>{$lang.lat}</p>
                                <input id="location_lat" type="text" name="lat">
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label required>
                                <p>{$lang.lng}</p>
                                <input id="location_lng" type="text" name="lng">
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
</section>
{$mdl_edit_myvox_menu_settings}
{$mdl_edit_rappi_settings}
{$mdl_edit_myvox_request_settings}
{$mdl_edit_myvox_incident_settings}
{$mdl_edit_myvox_survey_settings}
{$mdl_edit_reviews_settings}
<!-- {$mdl_edit_voxes_attention_times_settings} -->
{$mdl_edit_payment}
