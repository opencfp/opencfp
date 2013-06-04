<?php
require '../bootstrap.php';

Sentry::logout();
header('Location: index.php');