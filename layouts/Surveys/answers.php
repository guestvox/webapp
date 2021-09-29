<?php

defined('_EXEC') or die;

$this->dependencies->add(['js', '{$path.js}Surveys/answers.js?v=1.1']);
$this->dependencies->add(['other', '<script>menu_focus("surveys_{$menu_focus}");</script>']);

?>

%{header}%
<main class="dashboard">
    <section class="workspace">
        {$tbl_surveys_raters}
        {$tbl_surveys_comments}
    </section>
    <section class="buttons">
        <div>
            <a href="/surveys" class="big delete"><i class="fas fa-times"></i></a>
            <?php if (Functions::check_user_access(['{surveys_answers_view}']) == true) : ?>
            <a class="big new" data-button-modal="filter_surveys_answers"><i class="fas fa-stream"></i><span>{$lang.filter}</span></a>
            <?php endif; ?>
        </div>
    </section>
</main>
<section class="modal fullscreen" data-modal="preview_survey_answer">
    <div class="content">
        <main>
            <div class="survey_answer_preview"></div>
            <div class="buttons">
                <a class="new" button-cancel><i class="fas fa-check"></i></a>
            </div>
        </main>
    </div>
</section>
<section class="modal fullscreen" data-modal="filter_surveys_answers">
    <div class="content">
        <main>
            <form name="filter_surveys_answers">
                <div class="row">
                    <div class="span6">
                        <div class="label">
                            <label required>
                                <p>{$lang.started_date}</p>
                                <input type="date" name="started_date" value="<?php echo Session::get_value('settings')['surveys']['reports']['filter']['started_date']; ?>">
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label required>
                                <p>{$lang.end_date}</p>
                                <input type="date" name="end_date" value="<?php echo Session::get_value('settings')['surveys']['reports']['filter']['end_date']; ?>">
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label required>
                                <p>Hora de inicio</p>
                                <input type="time" name="started_hour" value="<?php echo Session::get_value('settings')['surveys']['reports']['filter']['started_hour']; ?>">
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label required>
                                <p>Hora de termino</p>
                                <input type="time" name="end_hour" value="<?php echo Session::get_value('settings')['surveys']['reports']['filter']['end_hour']; ?>">
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label required>
                                <p>{$lang.owner}</p>
                                <select name="owner">
                                    <option value="all" <?php echo ((Session::get_value('settings')['surveys']['reports']['filter']['owner'] == 'all') ? 'selected' : ''); ?>>{$lang.all}</option>
                                    <option value="not_owner" <?php echo ((Session::get_value('settings')['surveys']['reports']['filter']['owner'] == 'not_owner') ? 'selected' : ''); ?>>Sin propietarios</option>
                                    {$opt_owners}
                                </select>
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label required>
                                <p>{$lang.rating}</p>
                                <select name="rating">
                                    <option value="all" <?php echo ((Session::get_value('settings')['surveys']['reports']['filter']['rating'] == 'all') ? 'selected' : ''); ?>>{$lang.all}</option>
                                    <option value="1" <?php echo ((Session::get_value('settings')['surveys']['reports']['filter']['rating'] == '1') ? 'selected' : ''); ?>>1</option>
                                    <option value="2" <?php echo ((Session::get_value('settings')['surveys']['reports']['filter']['rating'] == '2') ? 'selected' : ''); ?>>2</option>
                                    <option value="3" <?php echo ((Session::get_value('settings')['surveys']['reports']['filter']['rating'] == '3') ? 'selected' : ''); ?>>3</option>
                                    <option value="4" <?php echo ((Session::get_value('settings')['surveys']['reports']['filter']['rating'] == '4') ? 'selected' : ''); ?>>4</option>
                                    <option value="5" <?php echo ((Session::get_value('settings')['surveys']['reports']['filter']['rating'] == '5') ? 'selected' : ''); ?>>5</option>
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
<section class="modal fullscreen" data-modal="edit_reservation">
    <div class="content">
        <main>
            <form name="edit_reservation">
                <div class="row">
                    <div class="span6">
                        <div class="label">
                            <label>
                                <p>{$lang.name}</p>
                                <input type="text" name="firstname">
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label>
                                <p>{$lang.lastname}</p>
                                <input type="text" name="lastname">
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label>
                                <p>{$lang.guest}</p>
                                <input type="number" name="guest_id">
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label>
                                <p>{$lang.reservation_number}</p>
                                <input type="number" name="reservation_number">
                            </label>
                        </div>
                    </div>
                    <div class="span3">
                        <div class="label">
                            <label required>
                                <p>{$lang.check_in}</p>
                                <input type="date" name="check_in">
                            </label>
                        </div>
                    </div>
                    <div class="span3">
                        <div class="label">
                            <label required>
                                <p>{$lang.check_out}</p>
                                <input type="date" name="check_out">
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label>
                                <p>{$lang.nationality}</p>
                                <input type="text" name="nationality">
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label>
                                <p>{$lang.input_channel}</p>
                                <input type="text" name="input_channel">
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label>
                                <p>{$lang.traveler_type}</p>
                                <input type="text" name="traveler_type">
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label>
                                <p>{$lang.age_group}</p>
                                <input type="text" name="age_group">
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
{$mdl_public_survey_comment}
{$mdl_unpublic_survey_comment}
