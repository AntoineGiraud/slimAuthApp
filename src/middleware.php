<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

// sécuriser l'application
$app->add(function ($request, $response, $next) {
    global $Auth;

    if (!in_array($request->getUri()->getPath(), ['login'])) { // Toute autre page que login on check si la personne est bien authentifiée
        if(!$Auth->isLogged()) {
            // Il n'était pas encore connecté en tant qu'icam.
            $this->flash->addMessage('info', "Vous devez être connecté pour accéder au reste de l'application");
            return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('login'));
        }
    }
    // On a vérifié que l'utilisateur est connecté !
    // On va s'assurer qu'il a le droit d'accéder aux pages

    if (in_array($request->getUri()->getPath(), ['/', 'about', 'login', 'logout'])) {
        // On ne fait rien de spécial, mais ce sont les pages de base auquel tout user a le droit
    } else if (!$Auth->hasRole('admin')){
        // checker les droits
        if (!$Auth->memberCanAccessPages($request->getUri()->getPath())) {
            return $Auth->forbidden($response, $this->router, 'home');
        }
    }

    // $response->getBody()->write('BEFORE');
    $response = $next($request, $response);
    // $response->getBody()->write('AFTER');

    return $response;
});