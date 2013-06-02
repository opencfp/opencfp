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

// Let's see if our logged in user has any talk submissions
$talk = new \OpenCFP\Talk($db);
$myTalks = $talk->findByUserId($user->getId());

// Load our template and RENDER
$template = $twig->loadTemplate('dashboard.twig');
$templateData = array(
    'myTalks' => $myTalks
);
$template->display($templateData);
