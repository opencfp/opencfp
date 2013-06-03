<?php

require '../bootstrap.php';
$user = require_once '../controllers/process_authenticate.php';

try {
    $user = Sentry::getUser();
} catch (Cartalyst\Sentry\Users\UserNotFoundException $e) {
    header('Location: login.php');
    exit;
}

if ($_POST) {
    require '../controllers/process_submit_talk.php';
}

if (empty($_POST)) {
    $pageTemplate = 'submit_talk.twig';
    $template = $twig->loadTemplate($pageTemplate);
    $data = array('formAction' => 'submit_talk.php');
    $template->display($data);
}
