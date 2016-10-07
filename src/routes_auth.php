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
    } else if (!empty($_POST)) { // Si l'utilisateur n'a pas rempli tous les champs demandés
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

        $users = $Auth->getUsers();

        $Auth->setSlimRoutes($this);

        $this->renderer->render($response, 'header.php', compact('Auth', 'flash', 'RouteHelper', 'settings', $args));
        $this->renderer->render($response, 'auth/list_droits.php', compact('Auth', 'RouteHelper', 'users', $args));
        return $this->renderer->render($response, 'footer.php', compact('Auth', 'RouteHelper', $args));
    })->setName('auth/list_droits');
  $this->group('/users', function () {

    $this->get('/list', function ($request, $response, $args) {
        global $Auth, $settings, $DB;
        $flash = $this->flash;
        $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, 'Liste des utilisateurs');

        $users = $Auth->getUsers();
        $token = $Auth->getTokenSlimCsrf($this, $request);

        $this->renderer->render($response, 'header.php', compact('Auth', 'flash', 'RouteHelper', 'settings', $args));
        $this->renderer->render($response, 'auth/users/list.php', compact('Auth', 'RouteHelper', 'token', 'users', $args));
        return $this->renderer->render($response, 'footer.php', compact('Auth', 'RouteHelper', $args));
    })->setName('auth/users/list');

    $this->get('/export', function ($request, $response, $args) {
        global $Auth, $settings, $DB;
        $flash = $this->flash;
        $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, 'Liste des utilisateurs');

        $users = $Auth->getUsers();

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
        if ($Auth->sourceConfig != 'database')
            return $Auth->forbidden($response, $this->router, 'auth/users/list', "Il n'est pas possible d'éditer les membres avec une configuration fichier. Migrez vers une configuration base de données.", 'danger');
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
                $msg = "Suppression utilisateur #".$v." : ".$userMail;
                $this->logger->addInfo($msg);
                $this->flash->addMessage('success', $msg);
                return $response->withHeader('Location', $this->router->pathFor('auth/users/list'));
            }
        }
    })->setName('auth/users/delete');

    $this->get('/add', function ($request, $response, $args) {
        global $Auth, $settings, $DB;

        $flash = $this->flash;
        $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, 'Ajout nouvel utilisateur');

        $user = \CoreHelpers\User::getBlankFields();
        $token = $Auth->getTokenSlimCsrf($this, $request);

        $ErrorsCtrl = new \CoreHelpers\ErrorsController([]);
        if (!empty($_SESSION['errorForm'])) {
            $ErrorsCtrl = $_SESSION['errorForm']['Errors'];
            $user = array_merge($user, $_SESSION['errorForm']['curForm']);
            unset($_SESSION['errorForm']);
        }

        $Auth->setSlimRoutes($this);

        $this->renderer->render($response, 'header.php', compact('Auth', 'flash', 'RouteHelper', 'settings', $args));
        $this->renderer->render($response, 'auth/users/edit.php', compact('Auth', 'RouteHelper', 'routesSlim', 'user', 'ErrorsCtrl', 'token', $args));
        return $this->renderer->render($response, 'footer.php', compact('Auth', 'RouteHelper', $args));
    })->setName('auth/users/add');

    $this->get('/edit', function ($request, $response, $args) {
        return $response->withHeader('Location', $this->router->pathFor('auth/users/list'));
    });
    $this->get('/edit/{id}', function ($request, $response, $args) {
        global $Auth, $settings, $DB;
        if ($Auth->sourceConfig != 'database')
            return $Auth->forbidden($response, $this->router, 'auth/users/list', "Il n'est pas possible d'éditer les membres avec une configuration fichier. Migrez vers une configuration base de données.", 'danger');

        $flash = $this->flash;
        $id = (int)$args['id'];
        $userMail = \CoreHelpers\User::getMailFromId($id);
        if (empty($userMail)) {
            $this->flash->addMessage('warning', "Utilisateur #$id inconnu.");
            return $response->withHeader('Location', $this->router->pathFor('auth/users/list'));
        }

        $user = \CoreHelpers\User::getUser($Auth, $userMail, null, true);
        $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, 'Editer utilisateur <small>#'.$id.'</small>');

        $token = $Auth->getTokenSlimCsrf($this, $request);

        $ErrorsCtrl = new \CoreHelpers\ErrorsController([]);
        if (!empty($_SESSION['errorForm'])) {
            $ErrorsCtrl = $_SESSION['errorForm']['Errors'];
            $user = array_merge($user, $_SESSION['errorForm']['curForm']);
            unset($_SESSION['errorForm']);
        }

        $Auth->setSlimRoutes($this);

        $this->renderer->render($response, 'header.php', compact('Auth', 'flash', 'RouteHelper', 'settings', $args));
        $this->renderer->render($response, 'auth/users/edit.php', compact('Auth', 'RouteHelper', 'token', 'ErrorsCtrl', 'user', $args));
        return $this->renderer->render($response, 'footer.php', compact('Auth', 'RouteHelper', $args));
    })->setName('auth/users/edit');

    $this->post('/edit', function ($request, $response, $args) {
        global $Auth, $settings, $DB;
        $flash = $this->flash;
        $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, 'Edition utilisateur');

        $post = $request->getParsedBody();
        var_dump($post);

        $userMail = (empty($post['id'])) ? '' : \CoreHelpers\User::getMailFromId($post['id']);
        if (!empty($post['id']) && empty($userMail)) {
            $this->flash->addMessage('warning', "Utilisateur #".$post['id']." inconnu.");
            return $response->withHeader('Location', $this->router->pathFor('auth/users/list'));
        }

        // Validation formulaire (ajoute ou mise à jour)
        // $userFields = array_keys(\CoreHelpers\User::getBlankFields());
        if (!empty($userMail)) {
            $curUser = \CoreHelpers\User::getUser($Auth, $userMail, null, true, false);
            $pswdAncienValidate = ['field'=>null, 'hash'=>$curUser['password']];
        } else {
            $curUser = null;
            $pswdAncienValidate = null;
        }
        $validate = [
            'first_name'=> array('rule'=>'notEmpty', 'msg' => 'Entrez votre prénom'),
            'last_name' => array('rule'=>'notEmpty', 'msg' => 'Entrez votre nom'),
            'email'     => array('rule'=>'email',    'msg' => 'Entrez un email valide'),
            'password'  => array('rule'=>'password', 'msg' => 'Les mots de passe ne concordent pas !',
                                 'fields'=>['nouveau'=>'password', 'confirmation'=>'password_confirm', 'ancien'=>$pswdAncienValidate],
                                 'canBeSkiped'=>!empty($post['id']))
        ];

        $ErrorsCtrl = new \CoreHelpers\ErrorsController($validate);
        if ($ErrorsCtrl->validate($post)) {
            if ($Auth->sourceConfig != 'database')
                // On a laissé faire les autres vérifications, mais si jamais on a pas accès à la bdd, on coupe !!
                return $Auth->forbidden($response, $this->router, 'auth/users/list', "Il n'est pas possible d'éditer les membres avec une configuration fichier. Migrez vers une configuration base de données.", 'danger');
            if (!empty($userMail) && $userMail != $post['email'] || empty($userMail)) {
                // Si jamais on a un ajout de user: on check le mail
                // Si jamais on une MAJ de user, il faut checker si le mail est modifié que ce dernier n'existe pas déjà
                if (\CoreHelpers\User::emailExist($post['email']))
                    $ErrorsCtrl->addError('email', 'Email déjà existant');
            }
            $post['roles'] = $Auth->getRoles($post['roles']);
        }

        if ($ErrorsCtrl->hasError) { // On a trouvé une erreur, on redirige !
            $_SESSION['errorForm'] = ['Errors' => $ErrorsCtrl, 'curForm' => $post];
            if (empty($post['id'])) { // Si on était en train d'ajouter une personne
                $this->flash->addMessage('warning', $ErrorsCtrl->hasError." erreur".($ErrorsCtrl->hasError==1?'':'s')." dans le formlaire");
                return $response->withHeader('Location', $RouteHelper->getPathFor('auth/users/add'));
            } else {
                $this->flash->addMessage('warning', $ErrorsCtrl->hasError." erreur".($ErrorsCtrl->hasError==1?'':'s')." dans le formulaire");
                return $response->withHeader('Location', $RouteHelper->getPathFor('auth/users/edit').'/'.$post['id']);
            }
        } else { // On a effectué toutes les vérifications
            if (empty($post['password']))
                unset($post['password']);
            else
                $post['password'] = password_hash($post['password'], PASSWORD_BCRYPT);
            var_dump($post);
            $usrId = $post['id'];
            $rolesSlug = array_keys($post['roles']);
            unset($post['id'], $post['password_confirm'], $post['csrf_name'], $post['csrf_value']);
            if (empty($usrId)) {
                $usrId = \CoreHelpers\User::insert($post);
                $msg = "Ajout d'un utilisateur #".$usrId." : ".$post['email'].' - '.json_encode($rolesSlug);
                $this->logger->addInfo($msg);
                $this->flash->addMessage('success', $msg);
                echo $msg;
            } else {
                $msg = "MAJ utilisateur #".$usrId." : ".$post['email'].' - '.json_encode($rolesSlug);
                \CoreHelpers\User::update($usrId, $post, $curUser);
                $this->logger->addInfo($msg);
                $this->flash->addMessage('success', $msg);
                echo $msg;
            }
        }
        return $response->withHeader('Location', $this->router->pathFor('auth/users/list'));
    })->setName('auth/users/commit');

  });
})->add($container->get('csrf'));