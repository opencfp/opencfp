<?php
require '../bootstrap.php';
$user = require_once '../controllers/process_authenticate.php';

// Let's see if our logged in user has any talk submissions
$talk = new \OpenCFP\Talk($db);
$myTalks = $talk->findByUserId($user->getId());

// Load our template and RENDER
$template = $twig->loadTemplate('dashboard.twig');
$templateData = array(
    'myTalks' => $myTalks,
    'user' => $user,
);
$template->display($templateData);
