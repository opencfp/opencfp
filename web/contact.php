<?php
require '../bootstrap.php';

// Set a few options

// Load our template and RENDER
$template = $twig->loadTemplate('contact.twig');
$template->display(array());

