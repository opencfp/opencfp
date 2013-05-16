<?php

/**
 * Controller for when a user submits user data
 */
$form = new \OpenCFP\SignupForm($_POST);
$data = array();

if ($form->validateAll()) {
	$sanitizedData = $form->sanitize();

	// Create account using Sentry
	$userData = array(
		'email' => $sanitizedData['email'],
		'password' => $sanitizedData['password']
	);
	$user = Sentry::getUserProvider()->create($userData);

	// Add them to the proper group
	$adminGroup = Sentry::getGroupProvider()->findByName('Speakers');
	$user->addGroup($adminGroup);

	// Create a Speaker record
	$speaker = new \OpenCFP\Speaker($db);
	$speaker->create(array(
	    $user->getId(),
	    $sanitizedData['speaker_info'])
	);

	// Add flash message saying account has been created
	$flashMessage = "Successfully created your account";
	$pageTemplate = "create_user_success.twig";
}

if (!$form->validateAll()) {
	$data['error_message'] = $form->errorMessages;
}

$template = $twig->loadTemplate($pageTemplate);
$template->display($data);