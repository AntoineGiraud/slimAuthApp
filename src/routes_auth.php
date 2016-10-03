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
        } else
            return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('login'));
    }

    if ($Auth->isLogged())
        $this->flash->addMessage('info', 'Vous êtes déjà authentifés ! <a class="btn btn-sm btn-primary" href="'.$this->router->pathFor('home').'">Retour à l\'accueil</a>');

    $flash = $this->flash;
    $token = [
        'nameKey' => $this->csrf->getTokenNameKey(),
        'valueKey' => $this->csrf->getTokenValueKey()
    ];
    $token['name'] = $request->getAttribute($token['nameKey']);
    $token['value'] = $request->getAttribute($token['valueKey']);
    $casUrl = $this->get('settings')['Auth']['casUrl']."login?service=".urlencode($service);
    return $this->renderer->render($response, 'auth/connexion.php', compact('RouteHelper', 'flash', 'Auth', 'token', 'casUrl', $args));
})->add($container->get('csrf'))->setName('login');

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
})->add($container->get('csrf'));

$app->get('/logout', function ($request, $response, $args) {
    global $Auth, $payutcClient;
    $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, 'logout');

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

$app->get('/account', function ($request, $response, $args) {
    global $Auth, $DB;
    $flash = $this->flash;
    $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, 'Vue compte');

    $this->renderer->render($response, 'header.php', compact('Auth', 'flash', 'RouteHelper', $args));
    $this->renderer->render($response, 'auth/account.php', compact('Auth', $args));
    return $this->renderer->render($response, 'footer.php', compact('Auth', 'RouteHelper', $args));
})->setName('account');


$app->group('/auth', function () {
    $this->get('/list_droits', function ($request, $response, $args) {
        global $Auth, $settings, $DB;
        $flash = $this->flash;
        $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, 'Liste des droits');
        $SettingsAuth = $settings['settings']['Auth'];

        if ($Auth->sourceConfig == 'file')
            $users = $SettingsAuth['users'];
        else
            $users = \CoreHelpers\User::getUsersList();

        $Auth->setSlimRoutes($this);

        $this->renderer->render($response, 'header.php', compact('Auth', 'flash', 'RouteHelper', 'settings', $args));
        $this->renderer->render($response, 'auth/list_droits.php', compact('Auth', 'RouteHelper', 'SettingsAuth', 'users', $args));
        return $this->renderer->render($response, 'footer.php', compact('Auth', 'RouteHelper', $args));
    })->setName('auth/list_droits');
  $this->group('/users', function () {

    $this->get('/list', function ($request, $response, $args) {
        global $Auth, $settings, $DB;
        $flash = $this->flash;
        $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, 'Liste des utilisateurs');

        if ($Auth->sourceConfig == 'database') {
            $users = \CoreHelpers\User::getUsersList();
        } else {
            $users = $SettingsAuth['users'];
        }

        $token = [
            'nameKey' => $this->csrf->getTokenNameKey(),
            'valueKey' => $this->csrf->getTokenValueKey()
        ];
        $token['name'] = $request->getAttribute($token['nameKey']);
        $token['value'] = $request->getAttribute($token['valueKey']);

        $this->renderer->render($response, 'header.php', compact('Auth', 'flash', 'RouteHelper', 'settings', $args));
        $this->renderer->render($response, 'auth/users/list.php', compact('Auth', 'RouteHelper', 'token', 'users', $args));
        return $this->renderer->render($response, 'footer.php', compact('Auth', 'RouteHelper', $args));
    })->setName('auth/users/list');

    $this->get('/export', function ($request, $response, $args) {
        global $Auth, $settings, $DB;
        $flash = $this->flash;
        $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, 'Liste des utilisateurs');

        if ($Auth->sourceConfig == 'database') {
            $users = \CoreHelpers\User::getUsersList();
        } else {
            $users = $SettingsAuth['users'];
        }

        $response->getBody()->write('online;email;prenom;nom;roles'."\n");
        foreach ($users as $usr) {
            $response->getBody()->write(
                $usr['online'].';'.
                $usr['email'].';'.
                $usr['first_name'].';'.
                $usr['last_name'].';'.
                '['.implode(', ', $usr['roles']).']'.
                "\n"
            );
        }
        return $response->withHeader('Content-Type', 'text/csv; charset=utf-8')
                    ->withHeader('Content-Disposition', 'text/csv; attachment; filename=export_users.csv');
    })->setName('auth/users/export');

    $this->get('/delete/{id}/{token_name}/{token_value}', function ($request, $response, $args) {
        global $Auth, $settings, $DB;
        $flash = $this->flash;
        $id = (int)$args['id'];
        $token_name = $args['token_name'];
        $token_value = $args['token_value'];

        if (!$this->csrf->validateToken($token_name, $token_value)) {
            // var_dump($request->getAttribute('csrf_status'));
            $this->flash->addMessage('danger', "<strong>Attention !</strong> Mauvais ticket pour supprimer cet utilisateur.");
            return $response->withHeader('Location', $this->router->pathFor('auth/users/list'));
        } else {
            $userMail = \CoreHelpers\User::getMailFromId($id);
            if (empty($userMail)) {
                $this->flash->addMessage('warning', "Utilisateur #$id inconnu.");
                return $response->withHeader('Location', $this->router->pathFor('auth/users/list'));
            } else {
                \CoreHelpers\User::deleteUser($id);
                $this->flash->addMessage('success', "Utilisateur #$id supprimé avec succès.");
                return $response->withHeader('Location', $this->router->pathFor('auth/users/list'));
            }
        }
    })->setName('auth/users/delete');

    $this->get('/add', function ($request, $response, $args) {
        global $Auth, $settings, $DB;
        $flash = $this->flash;
        $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, 'Ajout nouvel utilisateur');

        $token = [
            'nameKey' => $this->csrf->getTokenNameKey(),
            'valueKey' => $this->csrf->getTokenValueKey()
        ];
        $token['name'] = $request->getAttribute($token['nameKey']);
        $token['value'] = $request->getAttribute($token['valueKey']);
        $user = \CoreHelpers\User::getBlankFields();

        $Auth->setSlimRoutes($this);

        $this->renderer->render($response, 'header.php', compact('Auth', 'flash', 'RouteHelper', 'settings', $args));
        $this->renderer->render($response, 'auth/users/edit.php', compact('Auth', 'RouteHelper', 'routesSlim', 'user', 'token', $args));
        return $this->renderer->render($response, 'footer.php', compact('Auth', 'RouteHelper', $args));
    })->setName('auth/users/add');

    $this->get('/edit/{id}', function ($request, $response, $args) {
        global $Auth, $settings, $DB;
        $flash = $this->flash;
        $id = (int)$args['id'];
        $userMail = \CoreHelpers\User::getMailFromId($id);
        if (empty($userMail)) {
            $this->flash->addMessage('warning', "Utilisateur #$id inconnu.");
            return $response->withHeader('Location', $this->router->pathFor('auth/users/list'));
        }

        $user = \CoreHelpers\User::getUser($Auth, $userMail, null, true);
        $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, 'Editer utilisateur <small>#'.$id.'</small>');

        $token = [
            'nameKey' => $this->csrf->getTokenNameKey(),
            'valueKey' => $this->csrf->getTokenValueKey()
        ];
        $token['name'] = $request->getAttribute($token['nameKey']);
        $token['value'] = $request->getAttribute($token['valueKey']);

        $Auth->setSlimRoutes($this);

        $this->renderer->render($response, 'header.php', compact('Auth', 'flash', 'RouteHelper', 'settings', $args));
        $this->renderer->render($response, 'auth/users/edit.php', compact('Auth', 'RouteHelper', 'token', 'user', $args));
        return $this->renderer->render($response, 'footer.php', compact('Auth', 'RouteHelper', $args));
    })->setName('auth/users/edit');

    $this->post('/edit', function ($request, $response, $args) {
        global $Auth, $settings, $DB;
        $flash = $this->flash;

        $post = $request->getParsedBody();
        var_dump($post);

        if (empty($post['id'])) {
            $this->flash->addMessage('success', "Ajout d'un utilisateur");
            echo "Ajout d'un utilisateur";
        } else {
            $userMail = \CoreHelpers\User::getMailFromId($post['id']);
            if (empty($userMail)) {
                $this->flash->addMessage('warning', "Utilisateur #".$post['id']." inconnu.");
                return $response->withHeader('Location', $this->router->pathFor('auth/users/list'));
            } else {
                $this->flash->addMessage('success', "MAJ utilisateur ".$post['id']);
                echo "MAJ utilisateur ".$post['id'];
            }
        }
    })->setName('auth/users/edit');

  });
})->add($container->get('csrf'));