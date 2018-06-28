<?php

namespace CoreHelpers;
use \CoreHelpers\User;
use \CoreHelpers\loginLDAPuserUnknownInThisAppException;
use \CoreHelpers\loginCASonlyException;
use \CoreHelpers\loginLDAPonlyException;

class Auth {

    private $flash;

    public $roles;
    public $routesSlim;
    public $permissions;

    public $casUrl;
    public $ldapUrl;
    public $sourceConfig;
    public $allUserRole;
    public $baseAllowedPages;

    function __construct($AuthConfig, $DB) {
        $this->DB = $DB;
        $this->AuthId = 'Auth'.(empty($AuthConfig['id'])?basename(dirname(__DIR__, 2)):'_'.$AuthConfig['id']);
        $this->casUrl = !empty($AuthConfig['casUrl']) ? $AuthConfig['casUrl'] : null;
        $this->ldapUrl = !empty($AuthConfig['ldapUrl']) ? $AuthConfig['ldapUrl'] : null;
        $this->sourceConfig = $AuthConfig['sourceConfig'];
        $this->allUserRole = $AuthConfig['allUserRole'];
        if ($this->sourceConfig == 'file') {
            $roles = $AuthConfig['roles'];
            $this->permissions = $AuthConfig['permissions'];
        } else if (!empty($DB)) { // $this->sourceConfig == 'database'
            // auth_roles: {id, slug, name, created_at, updated_at}
            $roles = $DB->query('SELECT * FROM auth_roles');
            $this->permissions = self::loadPermissionsFromDB();
        } else
            throw new \Exception("Base de données inaccessible, vérifiez la configuration du site web ...", 1);
        $this->baseAllowedPages = !empty($this->permissions['forRole'][$this->allUserRole])
                                ? $this->permissions['forRole'][$this->allUserRole]['allowed']
                                : ['/', 'about', 'login', 'logout', 'account'];
        // consolider les roles de leurs permissions
        $this->roles = [];
        foreach ($roles as $role) {
            $this->roles[$role['slug']] = $role;
            $this->roles[$role['slug']]['permissions'] = (!empty($this->permissions['forRole'][$role['slug']]))? $this->permissions['forRole'][$role['slug']] : null;
        }

        $this->routesSlim = null;
    }
    function setFlashCtrl($flash) {
        $this->flash = $flash;
    }
    public function getUsers() {
        global $settings;
        if (!empty($this->users))
            return $this->users;
        if ($this->sourceConfig == 'database') {
            $this->users = \CoreHelpers\User::getUsersList();
        } else {
            $this->users = $settings['settings']['Auth']['users'];
            foreach ($this->users as $k => $u)
                if (!isset($u['id']))
                    $this->users[$k]['id'] = $k;
        }
        $usrs=[];
        foreach ($this->users as $key => $usr)
            $usrs[$usr['id']] = $usr;
        $this->users = $usrs;
        return $this->users;
    }
    public function userExists($userId) {
        $users = $this->getUsers();
        return !empty($users[$userId]) ? true : false;
    }

    public function fetchUsersInGroups($groups=null) {
        $users = $this->getUsers();
        if (empty($groups))
            $groups = ['superviseur', 'mecano'];
        $retour = [];
        foreach ($users as $usr) {
            foreach ($groups as $group) {
                if (in_array($group, $usr['roles'])) {
                    unset($usr['password']);
                    $retour[] = $usr;
                    break;
                }
            }
        }
        return $retour;
    }
    public function fetchAllUsers() {
        $users = $this->getUsers();
        $retour = [];
        foreach ($users as $usr) {
            unset($usr['password']);
            $retour[] = $usr;
        }
        return $retour;
    }

    /**
     * fonction pour charger les permissions comme dans le fichier de configuration
     * @param  Array $permissions chaque ligne:
     *   {id, user_id, role_id, permission, type_id, created_at, updated_at, allowed, user_email, role_slug}
     * @return Array Les permissions rangées comme suit:
     * $permissions = ['forRole' => ['member' => ['allowed' => ['login'], 'not_allowed' => [] ], 'forUser' => [...]
     */
    public function loadPermissionsFromDB() {
        // permission_types ['role'=>1, 'user'=>2, 'user_has_role'=>3]
        $res = $this->DB->query('SELECT p.*, u.email user_email, r.slug role_slug
                    FROM `auth_permissions` p
                        LEFT JOIN auth_roles r ON r.id = p.role_id
                        LEFT JOIN auth_users u ON u.id = p.user_id
                    WHERE type_id < 3');
        // id, user_id, role_id, permission, type_id, created_at, updated_at, allowed, user_email, role_slug
        $permissions = [ 'forRole' => [], 'forUser' => [] ];
        foreach ($res as $p) {
            if ($p['type_id'] == 1) { // 1 = role (permission_type)
                $cat = 'forRole';
                $name = $p['role_slug'];
            } else {
                $cat = 'forUser';
                $name = $p['user_email'];
            }
            if (empty($permissions[$cat][$name]))
                $permissions[$cat][$name] = [ 'allowed' => [], 'not_allowed' => [] ];

            $allowed = (empty($p['can_access']))? 'not_allowed' : 'allowed';
            $permissions[$cat][$name][$allowed][] = $p['permission'];
        }
        return $permissions;
    }

    /////////////////////////////
    // Partie Authentification //
    /////////////////////////////

    function login($d) {
        try {
            $user = User::getUser($this, $d['email'], $d['password']);
        } catch (\CoreHelpers\loginLDAPuserUnknownInThisAppException $e) {
            $this->flash->addMessage('warning', "Vos identifiants sont corrects mais vous n'avez pas les droits pour accéder au site.<br>Faites la demande aux responsables au besoin.");
            return false;
        } catch (\CoreHelpers\loginCASonlyException $e) {
            $this->flash->addMessage('warning', "Veuillez utiliser la connexion via le CAS");
            return false;
        } catch (\CoreHelpers\loginLDAPonlyException $e) {
            $this->flash->addMessage('warning', "Pensez à utiliser vos identifiants entreprise");
            return false;
        }
        if (empty($user)) {
            return false;
        } else if ($user['is_active'] == 1) { // si l'utilisateur est actif dans la BDD
            $_SESSION[$this->AuthId] = array();
            $_SESSION[$this->AuthId] = $user;
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
            if($user['is_active'] == 1) { // si l'utilisateur est actif dans la BDD
                $_SESSION[$this->AuthId] = array();
                $_SESSION[$this->AuthId] = $user;
                $_SESSION[$this->AuthId]['loggedUsingCas'] = true;
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
    function fetchUserAuthLastDbVal($mail=null) {
        $mail = empty($mail)? $this->getSessionUserField('email') : $mail ;
        $user = User::getUser($this, $mail, null, true);
        if (empty($user))
            return false;
        foreach ($user as $key => $value)
            $_SESSION[$this->AuthId][$key] = $value;
        return true;
    }
    function getSessionUserField($field) {
        if (isset($_SESSION[$this->AuthId][$field]))
            return $_SESSION[$this->AuthId][$field];
        else
            return false;
    }
    /** Récupère une info utilisateur */
    function getSessionUser() {
        return !empty($_SESSION[$this->AuthId])? $_SESSION[$this->AuthId] : null;
    }
    /** Redirige un utilisateur */
    function forbidden($response, $router, $pageRenvoi="home", $msg='', $cat='danger') {
        if (empty($msg))
            $msg = "<strong>Attention !</strong> Vous n'avez pas les droits pour accéder à cette page.";
        $this->flash->addMessage($cat, $msg);
        return $response->withHeader('Location', $router->pathFor($pageRenvoi))/*->withStatus(401)*/;
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
    function hasRole($role, $trueCheck=false) {
        $user = $this->getSessionUser();
        if ($this->isSuperAdmin() && !$trueCheck)
            return true;
        else if (!empty($user['roles']) && array_key_exists($role, $user['roles']))
            return true;
        else
            return false;
    }
    /**
     * Est ce que l'utilisateur a tous les roles passés ? Si superadmin, on bypass !
     * @param  Array    $roles     tableau de roles
     * @return boolean
     */
    function hasRoles($roles) {
        $user = $this->getSessionUser();
        if ($this->isSuperAdmin())
            return true;
        else if (!empty($user['roles'])) {
            foreach ($roles as $role)
                if (!array_key_exists($role, $user['roles']))
                    return false;
            return true;
        } else
            return false;
    }

    /**
     * est ce que la page est dans le tableau de page passé ?
     * @param  String $page
     * @param  Array $array
     * @return Boolean
     * pageInArray('auth/user', ['auth/user']) > true
     * pageInArray('auth/user', ['auth/user/edit']) > false
     * pageInArray('auth/user', ['auth/*']) > true
     */
    public static function pageInArray($page, $array) {
        if (empty($array) || empty($page))
            return false;
        foreach ($array as $val) {
            if ($val == $page)
                return true;
            else if ( substr($val, -1) == '*' && strpos($page, substr($val, 0, -1)) === 0 )
                return true;
        }
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
        else if (empty($user['permissions']))
            return false;
        else
            foreach ($pages as $page)
                if (self::pageInArray($page, $user['permissions']))
                    return true;
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
        else if (empty($user['permissions']))
            return false;
        else
            foreach ($pages as $page)
                if (!self::pageInArray($page, $user['permissions']))
                    return false;
        return true;
    }
    /**
     * Est ce que l'utilisateur ne peut pas accéder à au moins une des pages ?
     * @return Array  Liste de pages à valider
     */
    function memberCanNOTAccessPages() {
        $pages = func_get_args();
        $user = $this->getSessionUser();
        if ($this->isSuperAdmin() || empty($user['restrictions']))
            return false;
        else
            foreach ($pages as $page)
                if (self::pageInArray($page, $user['restrictions']))
                    return true;
        return false;
    }
    /**
     * Est ce que l'utilisateur ne peut pas accéder à absolument toutes les pages ?
     * @return Array  Liste de pages à valider
     */
    function memberCanNOTAccessAllPages() {
        $pages = func_get_args();
        $user = $this->getSessionUser();
        if ($this->isSuperAdmin() || empty($user['restrictions']))
            return false;
        else
            foreach ($pages as $page)
                if (!self::pageInArray($page, $user['restrictions']))
                    return false;
        return true;
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
    public function getTokenSlimCsrf($app, $request) {
        $token = [
            'nameKey' => $app->csrf->getTokenNameKey(),
            'valueKey' => $app->csrf->getTokenValueKey()
        ];
        $token['name'] = $request->getAttribute($token['nameKey']);
        $token['value'] = $request->getAttribute($token['valueKey']);
        return $token;
    }

    public static function generateToken($name = '') {
        $token = md5(uniqid(rand(147,1753), true));
        $_SESSION['tokens'][$name.'_token'] = [
            'token' => $token,
            'time' => time()
        ];
        return $token;
    }

    public static function validateToken($token, $name = '', $temps = 600, $referer = '') {
        global $settings;
        if (empty($referer))
            $referer = $settings['settings']['public_url'].basename($_SERVER['REQUEST_URI']);
        if (!empty($_SESSION['tokens'][$name.'_token']['token']) && !empty($token)) {
            $curToken = $_SESSION['tokens'][$name.'_token'];
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
