<?php

//////////////////////////////
// Routes pour la connexion //
//////////////////////////////
$app->get('/login', function ($request, $response, $args) {
    global $Auth;
    $Auth->setFlashCtrl($this->flash);
    $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, 'Login');
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
    $tokenForm = \CoreHelpers\Auth::generateToken();
    $casUrl = $this->get('settings')['Auth']['casUrl']."login?service=".urlencode($service);
    return $this->renderer->render($response, 'auth/connexion.php', compact('RouteHelper', 'flash', 'Auth', 'tokenForm', 'casUrl', $args));
})->setName('login');

$app->post('/login', function ($request, $response, $args) {
    global $Auth;
    $Auth->setFlashCtrl($this->flash);
    $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, 'Login');

    if(!empty($_POST['email']) && !empty($_POST['password'])){
        if($Auth->login($_POST)){
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
    $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, 'Login');

    if($Auth->isLoggedUsingCas()) {
        $service = $RouteHelper->curPageBaseUrl. '/login';
        $casUrl = $this->get('settings')['Auth']['casUrl']."logout?url=".urlencode($service);
        session_destroy();
        return $response->withStatus(303)->withHeader('Location', $casUrl);
    } else {
        session_destroy();
        return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('home'));
    }
})->setName('logout');

$app->get('/auth/list_droits', function ($request, $response, $args) {
    global $Auth, $settings;

    $flash = $this->flash;
    $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, 'A propos');

    $SettingsAuth = $settings['settings']['Auth'];

    $this->renderer->render($response, 'header.php', compact('Auth', 'flash', 'RouteHelper', 'settings', $args));
    $this->renderer->render($response, 'auth/list_droits.php', compact('Auth', 'RouteHelper', 'SettingsAuth', $args));
    return $this->renderer->render($response, 'footer.php', compact('Auth', 'RouteHelper', $args));
})->setName('auth/list_droits');

$app->get('/account', function ($request, $response, $args) {
    global $Auth, $DB;
    $flash = $this->flash;
    $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, 'vue compte');

    $this->renderer->render($response, 'header.php', compact('Auth', 'flash', 'RouteHelper', $args));
    $this->renderer->render($response, 'auth/account.php', compact('Auth', $args));
    return $this->renderer->render($response, 'footer.php', compact('Auth', 'RouteHelper', $args));
})->setName('account');