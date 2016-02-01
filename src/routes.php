<?php

////////////
// Routes //
////////////

// Page ouverte à tous
$app->get('/about', function ($request, $response, $args) {
    global $Auth;

    $flash = $this->flash;
    $RouteHelper = new \VisuLignes\RouteHelper($this, $request, 'A propos');

    $this->renderer->render($response, 'header.php', compact('Auth', 'flash', 'RouteHelper', $args));
    $this->renderer->render($response, 'about.php', compact('Auth', $args));
    return $this->renderer->render($response, 'footer.php', compact('Auth', 'RouteHelper', $args));
})->setName('about');

/////////////////
// Espace Icam //
/////////////////

$app->get('/', function ($request, $response, $args) {
    global $Auth, $DB;

    $flash = $this->flash;
    $RouteHelper = new \VisuLignes\RouteHelper($this, $request, 'Accueil');
    
    // Sample log message
    // $this->logger->info("Slim-Skeleton '/' index");
    
    // Render index view
    $this->renderer->render($response, 'header.php', compact('flash', 'RouteHelper', 'Auth', $args));
    $this->renderer->render($response, 'home.php', compact('RouteHelper', 'Auth', $args));
    return $this->renderer->render($response, 'footer.php', compact('RouteHelper', 'Auth', $args));
})->setName('home');
