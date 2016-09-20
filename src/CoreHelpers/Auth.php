<?php

namespace CoreHelpers;
use \Exception;

class Auth{

    private $flash;
    public $casUrl;
    private $roles;

    function __construct($AuthConfig){
        global $DB, $settings;
        $this->casUrl = !empty($AuthConfig['casUrl']) ? $AuthConfig['casUrl'] : null;
        if (!empty($AuthConfig['roles'])){
            $this->sourceConfig = "conf file";
            $this->roles = $AuthConfig['roles'];
        } else if(!empty($DB)){
            $this->sourceConfig = "database";
            $this->roles = $DB->query('SELECT * FROM roles');
        } else
            throw new Exception("On ne va pas réussi à vous authentifier ... vérifiez la configuration du site web ...", 1);
    }
    function setFlashCtrl($flash){
        $this->flash = $flash;
    }

    function fetchUser($mail, $pswd=null, $ignorePswd=false){
        if ($this->sourceConfig == "conf file") {
            global $settings;
            foreach ($settings['settings']['Auth']['users'] as $user) {
                if ($user['email'] == $mail && ( $user['password'] == $pswd || password_verify($pswd, $user['password']) || $ignorePswd))
                    return $user;
            }
        }else if($this->sourceConfig = "database"){
            global $DB;
            if ($ignorePswd)
                return $DB->queryFirst('SELECT administrateurs.id, administrateurs.email,administrateurs.nom,administrateurs.prenom,administrateurs.online,roles.name,roles.slug,roles.level FROM administrateurs LEFT JOIN roles ON administrateurs.role_id=roles.id WHERE email=:email', ['email'=>$mail]);
            else
                return $DB->queryFirst('SELECT administrateurs.id, administrateurs.email,administrateurs.nom,administrateurs.prenom,administrateurs.online,roles.name,roles.slug,roles.level FROM administrateurs LEFT JOIN roles ON administrateurs.role_id=roles.id WHERE email=:email AND password=:password', ['email'=>$mail, 'password'=>md5($pswd)]);
        }
    }

    function login($d){
        global $DB;

        $user = $this->fetchUser($d['email'], $d['password']);
        if (empty($user)) {
            // $this->flash->addMessage('warning', "Vous n'avez pas les droits d'accéder au site.<br>Faites la demande aux responsables au besoin.");
            return false;
        }else if($user['online'] == 1 && $user['level'] != 0){ // si l'utilisateur est actif dans la BDD
            $_SESSION['Auth'] = array();
            $_SESSION['Auth'] = $user;
            return true;
        }else{
            $this->flash->addMessage('warning', '<strong>Votre compte n\'est pas actif !</strong><br/>Veuillez attendre que les administrateurs activent votre compte ou contactez nous !');
        }
        return false;
    }

    function loginUsingCas($ticket, $service){
        $CAS = new \CoreHelpers\Cas($this->casUrl);
        try {
            $userEmail = $CAS->authenticate($ticket, $service);
        } catch (\Exception $e) {
            $this->flash->addMessage('warning', $e->getMessage());
            return false;
        }
        $user = (!empty($userEmail))? $this->fetchUser($userEmail, null, true) : null;
        if (!empty($user)) {
            if($user['online'] == 1 && $user['level'] != 0){ // si l'utilisateur est actif dans la BDD
                $_SESSION['Auth'] = array();
                $_SESSION['Auth'] = $user;
                $_SESSION['Auth']['loggedUsingCas'] = true;
                return true;
            }else
                $this->flash->addMessage('warning', '<strong>Votre compte n\'est pas actif !</strong><br/>Veuillez attendre que les administrateurs activent votre compte ou contactez nous !');
        }else if ($userEmail == 'AuthenticationFailure' || $userEmail == "Cas return is weird" || $userEmail == "Return cannot be parsed") {
            $this->flash->addMessage('danger', $userEmail);
            return false;
        }else if(!empty($userEmail)){
            $this->flash->addMessage('warning', "Vous n'avez pas les droits d'accéder au site.<br>Faites la demande aux responsables au besoin.");
        }
        return false;
    }

    /**
     * Autorise un rang à accéder à une page, redirige vers forbidden sinon
     **/
    function allow($rang){
        $roles = $this->getLevels();
        if (!$this->getSessionUserField('slug')) {
            $this->forbidden();
        } else {
            if($roles[$rang] > $this->getSessionUserField('level'))
                $this->forbidden();
            else
                return true;
        }
        return false;
    }
    function hasRole($rang){
        $roles = $this->getLevels();
        if(!$this->getSessionUserField('slug')){
            return false;
        }else{
            if($roles[$rang] > $this->getSessionUserField('level')){
                return false;
            }else{
                return true;
            }
        }
    }

    function memberCanAccessPages(){
        $pages = func_get_args();
        global $settings;
        if ($this->hasRole('admin'))
            return true;
        else {
            foreach ($pages as $page) {
                if (isset($settings['settings']['Auth']['allowedRoutes']['forRole'][$this->getSessionUserField('slug')])
                      && in_array($page, $settings['settings']['Auth']['allowedRoutes']['forRole'][$this->getSessionUserField('slug')]))
                    return true;
                else if (isset($settings['settings']['Auth']['allowedRoutes']['forUser'][$this->getSessionUserField('email')])
                      && in_array($page, $settings['settings']['Auth']['allowedRoutes']['forUser'][$this->getSessionUserField('email')]))
                    return true;
            }
        }
        return false;
    }
    /**
     * Récupère une info utilisateur
     ***/
    function getSessionUserField($field){
        if($field == 'role') $field = 'slug';
        if(isset($_SESSION['Auth'][$field])){
            return $_SESSION['Auth'][$field];
        }else{
            return false;
        }
    }
    /**
     * Récupère une info utilisateur
     ***/
    function getSessionUser(){
        return $_SESSION['Auth'];
    }

    /**
     * Redirige un utilisateur
     * */
    function forbidden($response, $router, $pageRenvoi="home"){
        $this->flash->addMessage('danger', "<strong>Attention !</strong> Vous n'avez pas les droits pour accéder à cette page.");
        return $response->withStatus(401)->withHeader('Location', $router->pathFor($pageRenvoi));
    }

    // -------------------- Security & Token functions -------------------- //
    public static function generateToken($nom = ''){
        $token = md5(uniqid(rand(147,1753), true));
        $_SESSION['tokens'][$nom.'_token'] = $token;
        $_SESSION['tokens'][$nom.'_token_time'] = time();
        return $token;
    }

    public static function validateToken($token, $nom = '', $temps = 600, $referer = ''){
        if (empty($referer)){
            $referer = Config::get('accueil-payicam').basename($_SERVER['REQUEST_URI']);
        }
        if(isset($_SESSION['tokens'][$nom.'_token']) && isset($_SESSION['tokens'][$nom.'_token_time']) && !empty($token))
            if($_SESSION['tokens'][$nom.'_token'] == $token)
                if($_SESSION['tokens'][$nom.'_token_time'] >= (time() - $temps)){
                    if(!empty($_SERVER['HTTP_REFERER']) && dirname($_SERVER['HTTP_REFERER']) == dirname($referer))
                        return true;
                    elseif(empty($_SERVER['HTTP_REFERER']))
                        return true;
                }
        return false;
    }

    // -------------------- isXXX functions -------------------- //
    function isLoggedUsingCas(){ // vérification de de l'existence d'une session "Auth", d'une session ouverte
        if (!empty($_SESSION['Auth']['loggedUsingCas']))
            return true;
        else
            return false;
    }
    function isLogged(){ // vérification de de l'existence d'une session "Auth", d'une session ouverte
        if ($this->getSessionUserField('level') !== false && $this->getSessionUserField('level') >= 0)
            return true;
        else
            return false;
    }
    function isAdmin(){ //vérification que l'utilisateur loggué est administrateur
        if ($this->getSessionUserField('role') == 'admin')
            return true;
        else
            return false;
    }

    // -------------------- Getters -------------------- //
    public function getLevels($key = 'slug'){
        global $DB;
        if ($key != 'slug' || $key != 'id')
            $key = 'slug';

        $roles = array();
        foreach($this->roles as $d){
            $roles[$d[$key]] = $d['level'];
        }
        return $roles;
    }
    public function getRoles($key = 'id'){
        global $DB;
        if ($key != 'slug' || $key != 'id')
            $key = 'id';

        $roles = array();
        foreach($this->roles as $d){
            $roles[$d[$key]] = $d['name'];
        }
        return $roles;
    }
    public function getRole($key){
        if (isset($this->roles[$key])) {
            return $this->roles[$key];
        }else{ // C'est surement son slug
            foreach($this->roles as $d){
                if ($d['slug'] == $key) {
                    return $d;
                }
            }
            return null;
        }
    }
    public function getRoleName($id){
        $role = $this->getRole($id);
        return $role['name'];
    }
}
