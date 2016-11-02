<?php

namespace CoreHelpers;

class ErrorsController {

    public $validations;
    public $errors;
    public $hasError;

    /**
     * Initialisation de notre objet de vérification des erreurs
     * @param Array $validations Tableau des règles à faire valider lors d'une soumission d'un formulaire
     * Il est possible d'envoyer différents types de règles:
     * * notEmpty
     *   * $validate['first_name'] = array('rule'=>'notEmpty', 'msg' => 'Entrez votre prénom')
     * * email
     *   * $validate['email'] = array('rule'=>'email', 'msg' => 'Entrez un email valide')
     * * password
     *   * Permet de gérer la logique de vérifier si les nouveaux mots de passe concordent
     *   * Possibilité de passer outre la vérification du mot de passe si les deux champs sont vide: canBeSkiped
     *   * Possibilité de valider l'ancien mot de passe si on le passe en paramêtre avec le champs du pswd à vérifier dans les données passées
     *      * Sinon mettre field à null ou la valeur entière à null :
     *          * validate['password']['fields']['ancien'] = null.
     *          * validate['password']['fields']['ancien']['field'].
     *   * Il faut passer les champs du nouveau & de la confirmation du pswd dans le tableau de données passé
     *   * $validate['password'] = array('rule'=>'password', 'msg' => 'Les mots de passe ne concordent pas !',
     *        'fields'=>[
     *            'nouveau'=>'pass_new',
     *            'confirmation'=>'pass_new2',
     *            'ancien'=>['field'=>null, 'hash'=>$curUser['password']]],
     *        'canBeSkiped'=>($page=='ajout')?true:false )
     * * RegExp: Expression régulière
     *   * $validate['date'] = array('rule'=>'^[0-9]{4}-[0-1]{1}[0-9]{1}-[0-3]{1}[0-9]{1}$', 'msg' => 'Entrez une date valide')
     */
    function __construct($validations=[]) {
        $this->hasError = 0;
        $this->validations = $validations;
        $this->errors = [];
    }

    /**
     * Fonction pour valider que les champs du formulaire passé respectent les bonne règles
     * @param  Array $data Données à valider
     * @return Boolean Est ce qu'absolument tous les champs respectent les règles passées lors de l'initialisation de l'objet
     */
    public function validate($data) {
        foreach ($this->validations as $key => $validate) {
            if ($key == "password") {
                // $validate = array('rule'=>'password', 'msg' => 'Les mots de passe ne concordent pas !',
                //       'fields'=>['nouveau'=>'pass_new', 'confirmation'=>'pass_new2', 'ancien'=>['field'=>'ancienMdp', 'hash'=>'ABCXYZ']],
                //       'canBeSkiped'=>!empty($post['id']))
                $nouveau = (!empty($data[$validate['fields']['nouveau']])) ? $data[$validate['fields']['nouveau']]:'';
                $confirmation = (!empty($data[$validate['fields']['confirmation']])) ? $data[$validate['fields']['confirmation']]:'';
                $ancien = (!empty($data[$validate['fields']['ancien']['field']])) ? $data[$validate['fields']['ancien']['field']]:'';
                if (!$validate['canBeSkiped'] && (empty($nouveau) || empty($confirmation)
                        || ( !empty($validate['fields']['ancien']) && empty($ancien) )
                )) {
                    $this->addError('password', $validate['msg']);
                } else if (!empty($nouveau) || !empty($confirmation)) {
                    if ($nouveau != $confirmation) {
                        $this->addError('password', $validate['msg']);
                    }
                    if (!empty($validate['fields']['ancien']['field']) && ( empty($ancien)
                            || (!empty($ancien) && $ancien != $validate['fields']['ancien']['hash'] && !password_verify($ancien, $validate['fields']['ancien']['hash']))
                    )) {
                        $this->addError('password_ancien', "Merci de donner aussi l'ancien mot de passe");
                    }
                }
            } else if (!isset($data[$key])) {
                $this->addError($key, 'Champ manquant');
            } else {
                if ($validate['rule'] == 'notEmpty') {
                    if (empty($data[$key]))
                        $this->addError($key, $validate['msg']);
                } else if ($validate['rule'] == 'email'){
                    if ((empty($data[$key]) || !filter_var($data[$key], FILTER_VALIDATE_EMAIL)))
                        $this->addError($key, $validate['msg'].' : '.$data[$key]);
                }  else if ($validate['rule'] == 'integer'){
                    if (!is_numeric($data[$key]))
                        $this->addError($key, $validate['msg'].' : '.$data[$key]);
                } else if ($validate['rule'] == 'date'){
                    if (!preg_match('/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/', $data[$key]))
                        $this->addError($key, $validate['msg'].' : '.$data[$key]);
                } else if ($validate['rule'] == 'hour'){
                    if (!preg_match('/^[0-2][0-9]:[0-5][0-9](:[0-5][0-9]([.][0-9]{3})?)?$/', $data[$key]))
                        $this->addError($key, $validate['msg'].' : '.$data[$key]);
                } else if (!preg_match('/'.$validate['rule'].'/', $data[$key]))
                    $this->addError($key, $validate['msg']);
            }
        }
        return $this->hasError == 0;
    }

    /**
     * Pour ajouter une nouvelle erreur - peut être accédé de l'extérieur de la classe
     * @param String $key Clé de l'erreur
     * @param String $msg Message de l'erreur
     */
    public function addError($key, $msg='') {
        $this->hasError ++;
        $this->errors[$key] = ['hasError' => true, 'msg' => $msg];
    }

    public function hasError($key) {
        return !empty($this->errors[$key]);
    }
    public function getError($key) {
        return $this->errors[$key];
    }
}