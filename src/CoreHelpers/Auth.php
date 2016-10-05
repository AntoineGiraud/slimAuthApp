<?php

namespace CoreHelpers;
use \Exception;
use \CoreHelpers\User;

class Auth {

    private $flash;

    public $roles;
    public $routesSlim;
    public $permissions;

    public $casUrl;
    public $sourceConfig;
    public $allUserRole;
    public $baseAllowedPages;

    function __construct($AuthConfig) {
        global $DB, $settings;
        $this->casUrl = !empty($AuthConfig['casUrl']) ? $AuthConfig['casUrl'] : null;
        $this->sourceConfig = $AuthConfig['sourceConfig'];
        $this->allUserRole = $AuthConfig['allUserRole'];
        if ($this->sourceConfig == 'file') {
            $roles = $AuthConfig['roles'];
            $this->permissions = $AuthConfig['permissions'];
        } else if(!empty($DB)) { // $this->sourceConfig == 'database'
            // auth_roles: {id, slug, name, created_at, updated_at}
            $roles = $DB->query('SELECT * FROM auth_roles');
            $this->permissions = self::loadPermissionsFromDB();
        } else
            throw new Exception("On ne va pas réussi à vous authentifier ... vérifiez la configuration du site web ...", 1);
        $this->baseAllowedPages = !empty($Auth->permissions['forRole'][$this->allUserRole])
                                ? $Auth->permissions['forRole'][$this->allUserRole]['allowed']
                                : ['/', 'about', 'login', 'logout', 'account'];
        // consolider les roles de leurs permissions
        $this->roles = [];
        foreach ($roles as $role) {
            $this->roles[$role['slug']] = $role;
            $this->roles[$role['slug']]['permissions'] = (!empty($this->permissions['forRole'][$role['slug']]))? $this->permissions['forRole'][$role['slug']] : null;
        }

        $this->routesSlim = null;
        // var_dump($this->sourceConfig);
        // var_dump($this->roles);
        // var_dump($this->permissions);
        // var_dump($this->permissions['forRole']);
        // var_dump($this->baseAllowedPages);
        // $moi = User::getUser('user1@operations', 'motdepasse', false);
        // var_dump($moi);
        // $moi = User::getUser('user2@entreprise', 'motdepasse', false);
        // var_dump($moi);
        // $moi = User::getUser('antoine.giraud@2015.icam.fr', 'motdepasse', false);
        // var_dump($moi);
        // die();
    }
    function setFlashCtrl($flash) {
        $this->flash = $flash;
    }
    public function getUsers() {
        global $settings;
        if ($this->sourceConfig == 'database') {
            return \CoreHelpers\User::getUsersList();
        } else {
            $users = $settings['settings']['Auth']['users'];
            foreach ($users as $k => $u)
                if (!isset($u['id']))
                    $users[$k]['id'] = $k;
            return $users;
        }
    }

    /**
     * fonction pour charger les permissions comme dans le fichier de configuration
     * @param  Array $permissions chaque ligne:
     *   {id, user_id, role_id, permission, category, created_at, updated_at, allowed, user_email, role_slug}
     * @return Array Les permissions rangées comme suit:
     * $permissions = ['forRole' => ['member' => ['allowed' => ['login'], 'not_allowed' => [] ], 'forUser' => [...]
     */
    public function loadPermissionsFromDB() {
        global $DB;
        $res = $DB->query('SELECT p.*, u.email user_email, r.slug role_slug
                    FROM `auth_permissions` p
                        LEFT JOIN auth_roles r ON r.id = p.role_id
                        LEFT JOIN auth_users u ON u.id = p.user_id');
        // id, user_id, role_id, permission, category, created_at, updated_at, allowed, user_email, role_slug
        $permissions = [ 'forRole' => [], 'forUser' => [] ];
        foreach ($res as $p) {
            if ($p['category'] == 'role') {
                $cat = 'forRole';
                $name = $p['role_slug'];
            } else {
                $cat = 'forUser';
                $name = $p['user_email'];
            }
            if (empty($permissions[$cat][$name]))
                $permissions[$cat][$name] = [ 'allowed' => [], 'not_allowed' => [] ];

            $allowed = (empty($p['allowed']))? 'not_allowed' : 'allowed';
            $permissions[$cat][$name][$allowed][] = $p['permission'];
        }
        return $permissions;
    }

    /////////////////////////////
    // Partie Authentification //
    /////////////////////////////

    function login($d) {
        global $DB;
        $user = User::getUser($this, $d['email'], $d['password']);
        if (empty($user)) {
            // $this->flash->addMessage('warning', "Vous n'avez pas les droits d'accéder au site.<br>Faites la demande aux responsables au besoin.");
            return false;
        } else if ($user['online'] == 1) { // si l'utilisateur est actif dans la BDD
            $_SESSION['Auth'] = array();
            $_SESSION['Auth'] = $user;
            return true;
        } else {
            $this->flash->addMessage('warning', '<strong>Votre compte n\'est pas actif !</strong><br/>Veuillez attendre que les administrateurs activent votre compte ou contactez nous !');
        }
        return false;
    }

    function loginUsingCas($ticket, $service) {
        $CAS = new \CoreHelpers\Cas($this->casUrl);
        try {
            $userEmail = $CAS->authenticate($ticket, $service);
        } catch (\Exception $e) {
            $this->flash->addMessage('warning', $e->getMessage());
            return false;
        }
        $user = (!empty($userEmail))? User::getUser($this, $userEmail, null, true) : null;
        if (!empty($user)) {
            if($user['online'] == 1) { // si l'utilisateur est actif dans la BDD
                $_SESSION['Auth'] = array();
                $_SESSION['Auth'] = $user;
                $_SESSION['Auth']['loggedUsingCas'] = true;
                return true;
            } else
                $this->flash->addMessage('warning', '<strong>Votre compte n\'est pas actif !</strong><br/>Veuillez attendre que les administrateurs activent votre compte ou contactez nous !');
        } else if ($userEmail == 'AuthenticationFailure' || $userEmail == "Cas return is weird" || $userEmail == "Return cannot be parsed") {
            $this->flash->addMessage('danger', $userEmail);
            return false;
        } else if(!empty($userEmail)) {
            $this->flash->addMessage('warning', "Vous n'avez pas les droits d'accéder au site.<br>Faites la demande aux responsables au besoin.");
        }
        return false;
    }

    // --------------------  -------------------- //
    /** Récupère un champ de l'utilisateur */
    function getSessionUserField($field) {
        if (isset($_SESSION['Auth'][$field]))
            return $_SESSION['Auth'][$field];
        else
            return false;
    }
    /** Récupère une info utilisateur */
    function getSessionUser() {
        return !empty($_SESSION['Auth'])? $_SESSION['Auth'] : null;
    }
    /** Redirige un utilisateur */
    function forbidden($response, $router, $pageRenvoi="home") {
        $this->flash->addMessage('danger', "<strong>Attention !</strong> Vous n'avez pas les droits pour accéder à cette page.");
        return $response->withStatus(401)->withHeader('Location', $router->pathFor($pageRenvoi));
    }

    //////////////////////////////
    // Partie validation droits //
    //////////////////////////////
    public function isLogged() { // vérification de de l'existence d'une session "Auth", d'une session ouverte
        $user = $this->getSessionUser();
        if (!empty($user['email']))
            return true;
        else
            return false;
    }
    public function isLoggedUsingCas() { // vérification de de l'existence d'une session "Auth", d'une session ouverte
        $user = $this->getSessionUser();
        if (!empty($user['loggedUsingCas']))
            return true;
        else
            return false;
    }
    public function isSuperAdmin($user=null) {
        if (empty($user))
            $user = $this->getSessionUser();
        if (!empty($user['userPermissions']['allowed']) && in_array('superadmin', $user['userPermissions']['allowed']))
            return true;
        else if (!empty($user['userPermissions']['not_allowed']) && in_array('superadmin', $user['userPermissions']['not_allowed']))
            return false; // On veut que le droit personnel à la personne ai plus de poids que le role.
        else if (!empty($user['roles']) && array_key_exists('superadmin', $user['roles']))
            return true;
        else
            return false;
    }
    /**
     * Est ce que l'utilisateur a le role passé ? Si superadmin, on bypass !
     * @param  String    $role     Un rôle
     * @return boolean
     */
    function hasRole($role) {
        $user = $this->getSessionUser();
        if ($this->isSuperAdmin())
            return true;
        else if (!empty($user['roles']) && array_key_exists($role, $user['roles']))
            return true;
        else
            return false;
    }

    /**
     * Est ce que l'utilisateur peut accéder au moins une des pages ?
     * @return Array  Liste de pages à valider
     */
    function memberCanAccessPages() {
        $pages = func_get_args();
        $user = $this->getSessionUser();
        if ($this->isSuperAdmin())
            return true;
        else {
            foreach ($pages as $page) {
                if (!empty($user['userPermissions']['allowed']) && in_array($page, $user['userPermissions']['allowed']))
                    return true;
                // else if (!empty($user['userPermissions']['not_allowed']) && in_array($page, $user['userPermissions']['not_allowed']))
                //     return false;
                else if (!empty($user['permissions']) && in_array($page, $user['permissions']))
                    return true;
            }
        }
        return false;
    }
    /**
     * Est ce que l'utilisateur peut accéder à absolument toutes les pages ?
     * @return Array  Liste de pages à valider
     */
    function memberCanAccessAllPages() {
        $pages = func_get_args();
        $user = $this->getSessionUser();
        if ($this->isSuperAdmin())
            return true;
        else {
            foreach ($pages as $page) {
                if (!empty($user['userPermissions']['allowed']) && in_array($page, $user['userPermissions']['allowed']))
                    return true;
                else if (!empty($user['userPermissions']['not_allowed']) && in_array($page, $user['userPermissions']['not_allowed']))
                    return false;
                else if (!empty($user['permissions']) && in_array($page, $user['permissions']))
                    return true;
                else
                    return false;
            }
        }
        return false;
    }

    public function setSlimRoutes($slimApp) {
        $this->routesSlim = [];
        foreach ($slimApp->router->getRoutes() as $key => $val) {
            $this->routesSlim[] = [
                'identifier' => $val->getIdentifier(),
                'name' => $val->getName(),
                'pattern' => $val->getPattern(),
                'methods' => $val->getMethods(),
                'groups' => $val->getGroups()
            ];
        }
        return $this->routesSlim;
    }

    // --------------------  -------------------- //
    public function getRoles($rolesSlug=[]) {
        if (empty($rolesSlug))
            return $this->roles;
        else {
            $retour = [];
            foreach ($rolesSlug as $role) {
                if (isset($this->roles[$role]))
                    $retour[$role] = $this->roles[$role];
            }
            return $retour;
        }
    }

    // -------------------- Security & Token functions -------------------- //
    public static function generateToken($nom = '') {
        $token = md5(uniqid(rand(147,1753), true));
        $_SESSION['tokens'][$nom.'_token'] = [
            'token' => $token,
            'time' => time()
        ];
        return $token;
    }

    public static function validateToken($token, $nom = '', $temps = 600, $referer = '') {
        global $DB, $settings;
        if (empty($referer))
            $referer = $settings['settings']['public_url'].basename($_SERVER['REQUEST_URI']);
        if (!empty($_SESSION['tokens'][$nom.'_token']['token']) && !empty($token)) {
            $curToken = $_SESSION['tokens'][$nom.'_token'];
            if ($curToken['token'] == $token)
                if ($curToken['time'] >= (time() - $temps)) {
                    if (!empty($_SERVER['HTTP_REFERER']) && dirname($_SERVER['HTTP_REFERER']) == dirname($referer))
                        return true;
                    else if(empty($_SERVER['HTTP_REFERER']))
                        return true;
                }
        } else
            return false;
    }
}
