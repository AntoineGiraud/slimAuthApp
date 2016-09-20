<?php

// Page ouverte à tous
$app->get('/list_droits', function ($request, $response, $args) {
    global $Auth, $settings;

    $flash = $this->flash;
    $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, 'A propos');

    $SettingsAuth = $settings['settings']['Auth'];

    $this->renderer->render($response, 'header.php', compact('Auth', 'flash', 'RouteHelper', 'settings', $args));
    $this->renderer->render($response, 'list_droits.php', compact('Auth', 'RouteHelper', 'SettingsAuth', $args));
    return $this->renderer->render($response, 'footer.php', compact('Auth', 'RouteHelper', $args));
})->setName('list_droits');
