<?php

namespace CoreHelpers;

/**
*
*/
class User {

    function __construct() {

    }

    public static function getBlankFields($val="pasDeValeurParDefaut") {
        $retour = [
            'id' => '',
            'email' => '',
            'last_login' => '',
            'online' => 1,
            'first_name' => '',
            'last_name' => '',
            'created_at' => '',
            'updated_at' => '',
            'roles' => [],
            'userPermissions' => '',
            'permissions' => []
        ];
        if ($val != "pasDeValeurParDefaut")
            foreach ($retour as $key => $value)
                $retour[$key] = $val;
        return $retour;
    }

    public static function deleteUser($id) {
        global $DB;
        $users = $DB->query('DELETE FROM auth_users WHERE id = :id', ['id' => $id]);
    }

    public static function getUsersList() {
        global $DB;
        $users = $DB->query('SELECT * FROM auth_users');
        $res = $DB->query('SELECT ur.user_id, ur.role_id, u.email, r.slug
                FROM auth_user_has_role ur
                    LEFT JOIN auth_users u ON u.id = ur.user_id
                    LEFT JOIN auth_roles r ON r.id = ur.role_id');
        $usersHasRole = [];
        foreach ($res as $userRole) {
            if (!isset($usersHasRole[$userRole['email']]))
                $usersHasRole[$userRole['email']] = [];
            $usersHasRole[$userRole['email']][] = $userRole['slug'];
        }
        foreach ($users as $k => $u) {
            if (!empty($usersHasRole[$u['email']]))
                $users[$k]['roles'] = $usersHasRole[$u['email']];
            else
                $users[$k]['roles'] = [];
        }
        return $users;
    }

    public static function getMailFromId($id) {
        global $DB;
        $res = $DB->queryFirst('SELECT email FROM auth_users WHERE id = :id', ['id'=>$id]);
        return !empty($res)? current($res) : null;
    }
    public static function emailExist($email) {
        global $DB;
        $res = $DB->queryFirst('SELECT email FROM auth_users WHERE email = :email', ['email'=>$email]);
        return !empty($res)? true : false;
    }

    /**
     * Récupération des informations d'un utilisateur
     * @param  String  $mail       email user
     * @param  String  $pswd       mot de passe
     * @param  boolean $ignorePswd est ce que l'on saute la validation du mot de passe
     * @return [type]              retourne les informations de l'utilisateur (champs de base, roles & permissions)
     */
    public static function getUser($Auth, $mail, $pswd=null, $ignorePswd=false, $removePswd=true) {
        if ($Auth->sourceConfig == "file") {
            global $settings;
            $user = null;
            foreach ($settings['settings']['Auth']['users'] as $u) {
                if ($u['email'] == $mail && ( $u['password'] == $pswd || password_verify($pswd, $u['password']) || $ignorePswd)) {
                    $user = $u;
                    $user['roles'] = $Auth->getRoles($user['roles']);
                    break;
                }
            }
            if (empty($user))
                return null;
        } else { // database
            global $DB;
            // id, email, password, last_login, online, first_name, last_name, created_at, updated_at
            $user = $DB->queryFirst('SELECT * FROM auth_users WHERE email = :email', ['email'=>$mail]);
            if (empty($user) || !$ignorePswd && ($user['password'] != $pswd && !password_verify($pswd, $user['password'])))
                return null; // On a pas le bon mot de passe
            // auth_user_has_role: {user_id, role_id, created_at, updated_at}
            $res = $DB->query('SELECT ur.*, r.slug role_slug
                    FROM auth_user_has_role ur
                        LEFT JOIN auth_roles r ON r.id = ur.role_id
                    WHERE user_id = :user_id', ['user_id'=>$user['id']]);
            $user['roles'] = [];
            foreach ($res as $role)
                $user['roles'][$role['role_slug']] = $Auth->roles[$role['role_slug']];
        }
        // Récupérer les permissions de l'utilisateur
        $user['userPermissions'] = !empty($Auth->permissions['forUser'][$user['email']]) ? $Auth->permissions['forUser'][$user['email']] : null;
        $user['permissions'] = [];
        if (!empty($user['userPermissions']))
            $user['permissions'] = $user['userPermissions']['allowed'];
        if (!empty($user['roles'])) {
            foreach ($user['roles'] as $role) {
                $rolePermissions = $role['permissions']['allowed'];
                if (empty($rolePermissions))
                    continue;
                foreach ($rolePermissions as $ok)
                    if (!in_array($ok, $user['permissions']))
                        $user['permissions'][] = $ok;
            }
        }
        if ($removePswd)
            unset($user['password']);
        return $user;
    }
}