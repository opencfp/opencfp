<?php
/**
 * Make sure the user is logged in, kicking them out to the login page
 * if they are not
 */
try {
    $user = Sentry::getUser();
} catch (Cartalyst\Sentry\Users\UserNotFoundException $e) {
    $user = null;
}
if (is_null($user)) {
    header('Location: login.php');
    exit;
}
return $user;