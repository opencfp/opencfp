<?php
namespace OpenCFP;

$hasher = new \Cartalyst\Sentry\Hashing\NativeHasher();
$userProvider = new \Cartalyst\Sentry\Users\Eloquent\Provider($hasher);
$groupProvider = new \Cartalyst\Sentry\Groups\Eloquent\Provider();
$throttleProvider = new \Cartalyst\Sentry\Throttling\Eloquent\Provider($userProvider);
$session = new \Cartalyst\Sentry\Sessions\NativeSession;
$cookie = new \Cartalyst\Sentry\Cookies\NativeCookie(array());

$sentry = new \Cartalyst\Sentry\Sentry(
    $userProvider,
    $groupProvider,
    $throttleProvider,
    $session,
    $cookie
);

$page = new Login($sentry);
$template = $twig->loadTemplate('login.twig');
$variables = $page->getViewVariables();
if (isset($variables['redirect'])) {
    header('Location: ' . $variables['redirect']);
    exit;
}
$template->display($variables);
