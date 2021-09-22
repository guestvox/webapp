<?php

defined('_EXEC') or die;

$this->dependencies->add(['css', '{$path.plugins}signature_pad/signature_pad.css']);
$this->dependencies->add(['js', '{$path.plugins}signature_pad/signature_pad.js']);
$this->dependencies->add(['css', '{$path.css}Myvox/survey.css']);
$this->dependencies->add(['js', '{$path.js}Myvox/survey.js?v=1.4']);

$this->dependencies->add(['css', '{$path.plugins}sweet-alert2/sweetalert2.min.css']);
$this->dependencies->add(['js', '{$path.plugins}sweet-alert2/sweetalert2.min.js']);

?>

<header class="survey">
    <figure>
        <img src="{$logotype}" alt="Client">
    </figure>
    <nav>
        <ul>
            <li>{$btn_home}</li>
            <li><a href="?<?php echo Language::get_lang_url('es'); ?>"><img src="{$path.images}es.png"></a></li>
            <li><a href="?<?php echo Language::get_lang_url('en'); ?>"><img src="{$path.images}en.png"></a></li>
        </ul>
    </nav>
</header>
<main class="survey">
    {$html}
</main>
{$mdl_widget}
<footer class="survey">
    <a href="/">Powered by <img src="{$path.images}logotype_color.png" alt="Guestvox"></a>
    <p>Copyright<i class="far fa-copyright"></i>2021 | Guestvox S.A.P.I. de C.V.</p>
</footer>
