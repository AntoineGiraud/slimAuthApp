<?php

//////////////////////////////
// Routes pour la connexion //
//////////////////////////////
$app->get('/login', function ($request, $response, $args) {
    global $Auth;
    $Auth->setFlashCtrl($this->flash);
    $RouteHelper = new \VisuLignes\RouteHelper($this, $request, 'Login');
    $service = $RouteHelper->curPageBaseUrl. '/login';

    // Connexion via le CAS
    if (!empty($request->getParam('ticket'))) {
        if ($Auth->loginUsingCas($request->getParam('ticket'), $service)) {
            $this->flash->addMessage('success', "Vous avez bien été authentifié avec le serveur CAS");
            return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('home'));
        }else
            return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('login'));
    }

    if ($Auth->isLogged())
        $this->flash->addMessage('info', 'Vous êtes déjà authentifés ! <a class="btn btn-sm btn-primary" href="'.$this->router->pathFor('home').'">Retour à l\'accueil</a>');

    $flash = $this->flash;
    $tokenForm = \VisuLignes\Auth::generateToken();
    $casUrl = $this->get('settings')['casUrl']."login?service=".urlencode($service);
    return $this->renderer->render($response, 'connexion.php', compact('RouteHelper', 'flash', 'Auth', 'tokenForm', 'casUrl', $args));
})->setName('login');

$app->post('/login', function ($request, $response, $args) {
    global $Auth;
    $Auth->setFlashCtrl($this->flash);
    $RouteHelper = new \VisuLignes\RouteHelper($this, $request, 'Login');

    if(!empty($_POST['email']) && !empty($_POST['password'])){
        if($Auth->loginUsingConf($_POST)){
        // if($Auth->login($_POST)){
            $this->flash->addMessage('success', 'Vous êtes maintenant connecté');
            return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('home'));
        } else {
            $this->flash->addMessage('danger', 'Identifiants incorects');
            return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('login')."?errorLogin=1");
        }
    }else if (!empty($_POST)) { // Si l'utilisateur n'a pas rempli tous les champs demandés
        $this->flash->addMessage('danger', 'Veuillez remplir tous vos champs');
        return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('login')."?errorLogin=1");
    }

    return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('home'));
});

$app->get('/logout', function ($request, $response, $args) {
    global $Auth, $payutcClient;
    $RouteHelper = new \VisuLignes\RouteHelper($this, $request, 'Login');

    if($Auth->isLoggedUsingCas()) {
        $service = $RouteHelper->curPageBaseUrl. '/login';
        $casUrl = $this->get('settings')['casUrl']."logout?url=".urlencode($service);
        session_destroy();
        return $response->withStatus(303)->withHeader('Location', $casUrl);
    } else {
        session_destroy();
        return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('home'));
    }
})->setName('logout');