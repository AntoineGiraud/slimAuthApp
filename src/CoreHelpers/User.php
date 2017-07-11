<?php

namespace CoreHelpers;

class User {

    function __construct() {

    }

    public static function getBlankFields($val="pasDeValeurParDefaut") {
        $retour = [
            'id' => '',
            'email' => '',
            'last_login' => '',
            'is_active' => 1,
            'first_name' => '',
            'last_name' => '',
            'created_at' => '',
            'updated_at' => '',
            'roles' => [],
            'userPermissions' => '',
            'restrictions' => [],
            'permissions' => []
        ];
        if ($val != "pasDeValeurParDefaut")
            foreach ($retour as $key => $value)
                $retour[$key] = $val;
        return $retour;
    }

    public static function deleteUser($id) {
        global $DB;
        $DB->query('DELETE FROM auth_permissions WHERE user_id = :id', ['id' => $id]);
        $DB->query('DELETE FROM auth_users WHERE id = :id', ['id' => $id]);
    }

    public static function insert($post) {
        global $DB;
        if (isset($post['roles'])) {
            $userRoles = $post['roles'];
            unset($post['roles']);
        }
        $keysSql = implode(',', array_keys($post));
        $valuesSql = ':'.implode(', :', array_keys($post));
        $usrId = $DB->query("INSERT INTO auth_users ($keysSql) VALUES ($valuesSql)", $post);
        // insert des roles si existants
        $valuesRole = [];
        foreach ($userRoles as $role)
            $valuesRole[] = '('.(int)$usrId.', '.(int)$role['id'].', 3)'; // user_has_role = 3
        if (!empty($valuesRole))
            $DB->query("INSERT INTO `auth_permissions` (`user_id`, `role_id`, `type_id`) VALUES ".implode(', ', $valuesRole));

        return $usrId;
    }
    public static function update($id, $post, $curUsr=[]) {
        global $DB;
        if (isset($post['roles'])) {
            $userRoles = $post['roles'];
            unset($post['roles']);
        }
        $setSql = [];
        $data = [];
        foreach ($post as $key => $value) {
            if (isset($curUsr[$key]) && $curUsr[$key] == $value)
                continue;
            $setSql[] = $key.' = :'.$key;
            $data[$key] = $value;
        }
        $setSql = implode(', ', $setSql);
        if (!empty($setSql))
            $DB->query("UPDATE `auth_users` SET $setSql WHERE `auth_users`.`id` = ".(int)$id, $data);

        // Maj des roles: on supprime tout et on rajoute les nouveaux
        $valuesRole = [];
        $update = count($userRoles) != count($curUsr['roles']);
        foreach ($userRoles as $role) {
            if (isset($curUsr['roles'][$role['slug']]))
                continue;
            $valuesRole[] = '('.(int)$id.', '.(int)$role['id'].', 3)'; // user_has_role = 3
            $update = true;
        }

        if ($update)
            $DB->query('DELETE FROM auth_permissions WHERE type_id = 3 AND user_id = :id', ['id' => $id]);
        if (!empty($valuesRole))
            $DB->query("INSERT INTO auth_permissions (user_id, role_id, type_id) VALUES ".implode(', ', $valuesRole));
    }
    public static function replaceRoles($id, $roles) {
        global $DB;

        $sql = "INSERT INTO auth_users () VALUES ()";
    }

    public static function getUsersList() {
        global $DB;
        $users = $DB->query('SELECT * FROM auth_users');
        $res = $DB->query('SELECT ur.user_id, ur.role_id, u.email, r.slug
                FROM auth_permissions ur
                    LEFT JOIN auth_users u ON u.id = ur.user_id
                    LEFT JOIN auth_roles r ON r.id = ur.role_id
                WHERE type_id = 3'); // user_has_role = 3
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

    public static function checkLdapPswd($mail, $pswd, $ldapUrl, $ldapPort=389) {
        if (empty($ldapUrl) || empty($mail) || empty($pswd))
            return false;
        $ds = ldap_connect($ldapUrl, $ldapPort);
        if (!$ds)
            return false;

        $user = substr($mail, 0, strpos($mail, '@')).'@'.$ldapUrl;

        $ldapbind = ldap_bind($ds, $user, $pswd) ;
        ldap_close($ds);

        if ($ldapbind)
            return true;
        else
            return false;
    }

    /**
     * Récupération des informations d'un utilisateur
     * @param  String  $mail       email user
     * @param  String  $pswd       mot de passe
     * @param  boolean $ignorePswd est ce que l'on saute la validation du mot de passe
     * @return [type]              retourne les informations de l'utilisateur (champs de base, roles & permissions)
     */
    public static function getUser($Auth, $mail, $pswd=null, $ignorePswd=false, $removePswd=true) {
        $ldapAuthValid = ($ignorePswd)? false : self::checkLdapPswd($mail, $pswd, $Auth->ldapUrl);
        if ($Auth->sourceConfig == "file") {
            global $settings;
            $user = null;
            foreach ($settings['settings']['Auth']['users'] as $u) {
                if ($u['email'] == $mail && ($ldapAuthValid || $u['password'] == $pswd || password_verify($pswd, $u['password']) || $ignorePswd)) {
                    $user = $u;
                    $user['roles'] = $Auth->getRoles($user['roles']);
                    break;
                }
            }
            if (empty($user)) {
                if ($ldapAuthValid)
                    throw new loginLDAPuserUnknownInThisAppException("Utilisateur LDAP inconnu", 1);
                return null;
            } else if (!$ignorePswd) {
                if ($user['password'] == "ldap_only" && !$ldapAuthValid)
                    throw new \CoreHelpers\loginLDAPonlyException("ldap_only", 1);
                else if ($user['password'] == "cas_only")
                    throw new \CoreHelpers\loginCASonlyException("cas_only", 1);
            }
        } else { // database
            global $DB;
            // id, email, password, last_login, is_active, first_name, last_name, created_at, updated_at
            $user = $DB->queryFirst('SELECT * FROM auth_users WHERE email = :email', ['email'=>$mail]);
            if (!empty($user) && !$ignorePswd) {
                if ($user['password'] == "ldap_only" && !$ldapAuthValid)
                    throw new \CoreHelpers\loginLDAPonlyException("ldap_only", 1);
                else if ($user['password'] == "cas_only")
                    throw new \CoreHelpers\loginCASonlyException("cas_only", 1);
            }
            if (empty($user) || !$ignorePswd && ($user['password'] != $pswd && !password_verify($pswd, $user['password']) && !$ldapAuthValid)) {
                if ($ldapAuthValid)
                    throw new loginLDAPuserUnknownInThisAppException("Utilisateur LDAP inconnu", 1);
                return null; // On a pas le bon mot de passe
            }
            // auth_permissions: {user_id, role_id, created_at, updated_at, type_id=user_has_role}
            $res = $DB->query('SELECT ur.*, r.slug role_slug
                    FROM auth_permissions ur
                        LEFT JOIN auth_roles r ON r.id = ur.role_id
                    WHERE type_id = 3 AND user_id = :user_id', ['user_id'=>$user['id']]); // user_has_role = 3
            $user['roles'] = [];
            foreach ($res as $role)
                $user['roles'][$role['role_slug']] = $Auth->roles[$role['role_slug']];
        }
        // Récupérer les permissions de l'utilisateur
        $user['userPermissions'] = !empty($Auth->permissions['forUser'][$user['email']]) ? $Auth->permissions['forUser'][$user['email']] : null;
        $user['restrictions'] = [];
        $user['permissions'] = $Auth->baseAllowedPages;
        if (!empty($user['userPermissions']) && !empty($user['userPermissions']['allowed']))
            foreach ($user['userPermissions']['allowed'] as $ok)
                if (!in_array($ok, $user['permissions']))
                    $user['permissions'][] = $ok;
        if (!empty($user['userPermissions']) && !empty($user['userPermissions']['not_allowed']))
            foreach ($role['userPermissions']['not_allowed'] as $ok)
                if (!in_array($ok, $user['permissions']) && !in_array($ok, $user['restrictions']))
                    $user['restrictions'][] = $ok;
        if (!empty($user['roles'])) {
            foreach ($user['roles'] as $role) {
                if (!empty($role['permissions']['allowed']))
                    foreach ($role['permissions']['allowed'] as $ok)
                        if (!in_array($ok, $user['permissions']))
                            $user['permissions'][] = $ok;
                if (!empty($role['permissions']['not_allowed']))
                    foreach ($role['permissions']['not_allowed'] as $ok)
                        if (!in_array($ok, $user['permissions']) && !in_array($ok, $user['restrictions']))
                            $user['restrictions'][] = $ok;
            }
        }
        if ($removePswd)
            unset($user['password']);
        if ($ldapAuthValid)
            $user['used_ldap_auth'] = true;
        return $user;
    }

    public static function fetchAllUsers() {
        global $DB;
        return $DB->query('SELECT id, first_name, last_name FROM auth_users');
    }
}