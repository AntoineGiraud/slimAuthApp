<?php

namespace VisuLignes;

class Auth{
    
    private $roles;
    private $flash;
    private $casUrl;

    function __construct($casUrl){
        global $DB;
        $this->casUrl = $casUrl;
        $this->roles = $DB->query('SELECT * FROM roles');
    }
    function setFlashCtrl($flash){
        $this->flash = $flash;
    }

    function login($d){
        global $DB;

        $d = array(
            'email' => $d['email'],
            'password' => md5($d['password'])
        );

        $return = $DB->queryFirst('SELECT administrateurs.id, administrateurs.email,administrateurs.nom,administrateurs.prenom,administrateurs.online,roles.name,roles.slug,roles.level FROM administrateurs LEFT JOIN roles ON administrateurs.role_id=roles.id WHERE email=:email AND password=:password',
            $d);
        if (empty($return)) {
            // $this->flash->addMessage('warning', "Vous n'avez pas les droits d'accéder au site.<br>Faites la demande aux responsables au besoin.");
            return false;
        }else if($return['online'] == 1 && $return['level'] != 0){ // si l'utilisateur est actif dans la BDD
            $_SESSION['Auth'] = array();
            $_SESSION['Auth'] = $return;
            return true;
        }else{
            $this->flash->addMessage('warning', '<strong>Votre compte n\'est pas actif !</strong><br/>Veuillez attendre que les administrateurs activent votre compte ou contactez nous !');
        }
        return false;
    }

    function loginUsingCas($ticket, $service){
        global $DB;
        $CAS = new \VisuLignes\Cas($this->casUrl);

        try {
            $userEmail = $CAS->authenticate($ticket, $service);
        } catch (\Exception $e) {
            $this->flash->addMessage('warning', $e->getMessage());
            return false;
        }

        if (!empty($userEmail) && $DB->findCount('administrateurs',array('email'=>$userEmail),'email') == 1) {
            $return = $DB->queryFirst('SELECT administrateurs.id, administrateurs.email,administrateurs.nom,administrateurs.prenom,administrateurs.online,roles.name,roles.slug,roles.level FROM administrateurs LEFT JOIN roles ON administrateurs.role_id=roles.id WHERE email=:email',array('email'=>$userEmail));
            if (empty($return)) {
                $this->flash->addMessage('warning', "Vous n'avez pas les droits d'accéder au site.<br>Faites la demande aux responsables au besoin.");
                return false;
            }else if($return['online'] == 1 && $return['level'] != 0){ // si l'utilisateur est actif dans la BDD
                $_SESSION['Auth'] = array();
                $_SESSION['Auth'] = $return;
                $_SESSION['Auth']['loggedUsingCas'] = true;
                return true;
            }else{
                $this->flash->addMessage('warning', '<strong>Votre compte n\'est pas actif !</strong><br/>Veuillez attendre que les administrateurs activent votre compte ou contactez nous !');
            }
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
     * */
    function allow($rang){
        $roles = $this->getLevels();
        if(!$this->getUserField('slug')){
            $this->forbidden(); 
        }else{
            if($roles[$rang] > $this->getUserField('level')){
                $this->forbidden(); 
            }else{
                return true;
            }
        }
    }

    function hasRole($rang){
        $roles = $this->getLevels();
        if(!$this->getUserField('slug')){
            return false;
        }else{
            if($roles[$rang] > $this->getUserField('level')){
                return false;
            }else{
                return true;
            }
        }
    }
    
    /**
     * Récupère une info utilisateur
     ***/
    function getUserField($field){
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
    function getUser(){
        return $_SESSION['Auth'];
    }
    
    /**
     * Redirige un utilisateur
     * */
    function forbidden(){
        Functions::setFlash('<strong>Identification requise</strong> Vous ne pouvez accéder à cette page.','danger');
        header('Location:connection.php'.((!empty($_GET['ticket']))?'?ticket='.$_GET['ticket']:''));exit;
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
        if ($this->getUserField('level') !== false && $this->getUserField('level') >= 0)
            return true;
        else
            return false;
    }
    function isAdmin(){ //vérification que l'utilisateur loggué est administrateur
        if ($this->getUserField('role') == 'admin')
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
