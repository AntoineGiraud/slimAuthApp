<?php

////////////
// Routes //
////////////

// Page ouverte à tous
$app->get('/about', function ($request, $response, $args) {
    $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, $response, 'A propos');

    $this->renderer->render($response, 'header.php', compact('RouteHelper', $args));
    $this->renderer->render($response, 'about.php', compact('RouteHelper', $args));
    return $this->renderer->render($response, 'footer.php', compact('RouteHelper', $args));
})->setName('about');

/////////////////
// Espace Icam //
/////////////////

$app->get('/', function ($request, $response, $args) {
    $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, $response, 'Accueil');

    // Sample log message
    // $this->logger->info("Slim-Skeleton '/' index");

    // Render index view
    $this->renderer->render($response, 'header.php', compact('RouteHelper', $args));
    $this->renderer->render($response, 'home.php', compact('RouteHelper', $args));
    return $this->renderer->render($response, 'footer.php', compact('RouteHelper', $args));
})->setName('home');
