<?php
require 'bootstrap.php';

// Set a few options

// Load our template and RENDER
$template = $twig->loadTemplate('home.twig');
$template->display(array());
