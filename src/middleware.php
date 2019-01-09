<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

// valider la connexion a la base de données
$app->add(function ($request, $response, $next) {

    if (!empty($this->DB)) {
        // $response->getBody()->write('BEFORE');
        $response = $next($request, $response);
        // $response->getBody()->write('AFTER');
        return $response;
    }

    // On short cut l'application ! on affiche le message d'erreur
    $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, $response, 'Erreur connexion BDD');
    $this->renderer->render($response, 'header.php', compact('RouteHelper'));
    $response->getBody()->write('<h3>Erreur de connexion <abbr title="Base de données">BDD</abbr> <code>'.$c->get('settings')['confSQL']['sql_host'].'</code></h3>');
    return $this->renderer->render($response, 'footer.php', compact('RouteHelper'));
});
// sécuriser l'application à l'aide d'une authentification
$app->add(function ($request, $response, $next) {
    $curPagePath = $request->getUri()->getPath();
    if ($curPagePath != '/' && substr($curPagePath, 0, 1) == "/")
        $curPagePath = substr($curPagePath, 1);
    if (!in_array($curPagePath, ['login'])) { // Toute autre page que login on check si la personne est bien authentifiée
        if(!$this->Auth->isLogged()) {
            // Il n'était pas encore connecté en tant qu'icam.
            $this->flash->addMessage('info', "Vous devez être connecté pour accéder au reste de l'application");
            return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('login'));
        }
    }
    // On a vérifié que l'utilisateur est connecté !
    // On va s'assurer qu'il a le droit d'accéder aux pages
    if (in_array($curPagePath, $this->Auth->baseAllowedPages)) {
        // On ne fait rien de spécial, mais ce sont les pages de base auquel tout user a le droit d'accèder
    } else if (!$this->Auth->isSuperAdmin()){
        // checker les droits
        if (!$this->Auth->memberCanAccessPages($curPagePath)) {
            return $this->Auth->forbidden($response, $this->router, 'home');
        }
    }

    // $response->getBody()->write('BEFORE');
    $response = $next($request, $response);
    // $response->getBody()->write('AFTER');

    return $response;
});

// Sécuriser l'application contre les failles csrf
// Si on l'ajoute ici, toutes les pages utilisant un formulaire seront coupées si les jetons ne sont pas bon ou pas envoyés
// $app->add($container->get('csrf'));