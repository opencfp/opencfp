<?php
$pageTemplate = 'activate_failure.twig';
$data = array();

if (isset($_REQUEST['code'])) {
    $code = $_REQUEST['code'];
    try {
        $user = Sentry::getUserProvider()->findByActivationCode($code);
        if ($user->attemptActivation($code)) {
            $pageTemplate = 'activate_success.twig';
        }
    }
    catch (Cartalyst\Sentry\Users\UserNotFoundException $e) {
        //Ignore user not found as an invalid activation
    }
}

$template = $twig->loadTemplate($pageTemplate);
$template->display($data);
