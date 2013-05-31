<?php
require '../bootstrap.php';

/**
 * Make sure the user is logged in, kicking them out to the login page
 * if they are not
 */
try {
    $user = Sentry::getUser();
} catch (Cartalyst\Sentry\Users\UserNotFoundException $e) {
    header('Location: login.php');
    exit;
}

// Load our template and RENDER
$template = $twig->loadTemplate('dashboard.twig');
$template->display(array());
