<?php
require '../bootstrap.php';

// Set a few options

// Load our template and RENDER
$template = $twig->loadTemplate('dashboard.twig');
$template->display(array());
