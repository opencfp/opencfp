<?php

require '../bootstrap.php';

try {
    $user = Sentry::getUser();
} catch (Cartalyst\Sentry\Users\UserNotFoundException $e) {
    header('Location: login.php');
    exit;
}

// Let's look for data being passed in
if ($_POST) {
    require '../controllers/process_edit_talk.php';
}

$talkId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (empty($talkId)) {
    header('Location: dashboard.php');
    exit;
}

$talk = new \OpenCFP\Talk($db);
$talkInfo = $talk->findById($talkId);

// Make sure people can't edit talks that don't belong to them
if ($talkInfo['user_id'] !== $user->getId()) {
    header('Location: dashboard.php');
    exit;
}

$pageTemplate = 'edit_talk.twig';
$template = $twig->loadTemplate($pageTemplate);
$data = array(
    'formAction' => 'edit_talk.php',
    'id' => $talkId,
    'title' => $talkInfo['title'],
    'description' => $talkInfo['description'],
    'type' => $talkInfo['type'],
    'user' => $user,
);
$template->display($data);
