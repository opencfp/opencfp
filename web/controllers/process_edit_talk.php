<?php
$form = new \OpenCFP\TalkForm($_POST);
$pageTemplate = 'create_talk.twig';
$valid = $form->validateAll();

if (!$valid && empty($data['error_message'])) {
    $data['error_messages'] = $form->errorMessages;
}

if ($valid) {
    $sanitizedData = $form->sanitize();
    $data = array(
        'id' => (int)$sanitizedData['id'],
        'title' => $sanitizedData['title'],
        'description' => $sanitizedData['description'],
        'type' => $sanitizedData['type'],
        'user_id' => (int)$user->getId()
    );
    $talk = new \OpenCFP\Talk($db);
    $talk->update($data);
    header('Location: dashboard.php');
    exit();
}

