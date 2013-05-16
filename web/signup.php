<?php
require 'bootstrap.php';

// If we received our posted information, then create a user
if ($_POST) {
    require APP_DIR . '/controllers/process_create_user.php';
}

if (empty($_POST)) {
    $pageTemplate = 'create_user.twig';
    $template = $twig->loadTemplate($pageTemplate); 
    $template->display(array());
    $data = array();
}
