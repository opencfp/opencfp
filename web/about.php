<?php
require '../bootstrap.php';

// Set a few options

// Load our template and RENDER
$template = $twig->loadTemplate('about.twig');
$template->display(array());

