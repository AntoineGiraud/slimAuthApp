<?php

namespace VisuLignes;
use \PDO;

class DB{

    private $host     = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'visulignes';
    private $db;

    public function __construct($host = null, $username = null, $password = null, $database = null){
        if(!empty($host) && !empty($username) && !empty($database)){
            $this->host     = $host;
            $this->username = $username;
            $this->password = $password;
            $this->database = $database;
        }

        try{
            $this->db = new PDO('mysql:host='.$this->host.';dbname='.$this->database, $this->username, $this->password, array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
                PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING
            ));
        }catch(PDOException $e){
            die('<h1>Impossible de se connecter a la base de donnee</h1><p>'.$e->getMessage().'<p>');
        }
    }

    public function query($sql, $data = array(),$fetch = PDO::FETCH_ASSOC){
        try{
            $req = $this->db->prepare($sql);
            $req->execute($data);
            if(!preg_match('/^(INSERT|UPDATE|DELETE|TRUNCATE|DROP|CREATE)/', $sql)){
                if (!empty($fetch))
                    return $req->fetchAll($fetch);
                else
                    return $req->fetchAll();
            }else
                return $this->db->lastInsertId();
        }catch(PDOException $e){
            die('<h3>Erreur : </h3><p>'.$e->getMessage().'</p>');
        }
    }

    public function queryFirst($sql, $data = array(),$fetch = PDO::FETCH_ASSOC){
        try{
            $req = $this->db->prepare($sql);
            $req->execute($data);

            if (!empty($fetch))
                $return = $req->fetch($fetch);
            else 
                $return = $req->fetch();
            if(!empty($return))
                return $return;
            else
                return array();
        }catch(PDOException $e){
            die('<h3>Erreur : </h3><p>'.$e->getMessage().'</p>');
        }
    }

    /**
     * find : fonction de recherche
     * @param string $table Table dans laquelle effectuer la recherche
     * @param $req
     * Permet d'éviter de taper tout ce code :
     * // connexion à la BDD, $DB
     * //récupération de tous les posts
     * $sql = 'SELECT * FROM administrateurs';
     * $req = $DB->prepare($sql); // on prépare la requête SQL à exécuter
     * $req->execute(); // on l'exécute
     * $Users = $req->fetchAll(PDO::FETCH_ASSOC); //permet de voir les données en tableau
     * debug($Users,'brut bdd');
     **/
    public function find($table, $req = null){

        if (str_word_count($table) > 1 && preg_match('/[ ]/i', $table)) {
            $sql = $table;
        }else{
            $sql = 'SELECT ';
            
            // Construciton des champs à récupérer dans la table
            if (isset($req['fields'])) {
                if (is_array($req['fields'])) {
                    $sql.=implode(', ',$req['fields']);
                }else{
                    $sql.=$req['fields'];
                }
            }else{
                $sql .= '*';
            }

            $sql .= ' FROM '.$table.' ';
            
            // Construction du Join
            if (isset($req['join'])) {
                $join = array(
                    'left'=>'LEFT JOIN ',
                    'right'=>'RIGHT JOIN ',
                    'inner'=>'INNER JOIN '
                );
                if (isset($req['join']['conditions'], $req['join']['table'], $join[$req['join']['type']]) && !empty($req['join']['conditions']) && !empty($req['join']['table']) && !empty($join[$req['join']['type']])) {

                    $sql.=$join[$req['join']['type']].$req['join']['table'].' ON ';

                    if (!is_array($req['join']['conditions'])) {
                        $sql.= $req['join']['conditions'];
                    }else{
                        $cond = array();
                        foreach ($req['join']['conditions'] as $k => $v) {
                            if ($k=='cond') {
                                $cond[] = $v;
                            }else{
                                if(!is_numeric($v)){
                                    $v = '"'.htmlspecialchars($v, ENT_QUOTES, "UTF-8").'"';
                                }
                                $cond[] = "$k=$v";
                            }
                        }
                        $sql .= implode(' AND ',$cond);
                    }
                    $sql .= ' ';
                }
            }
            /*LEFT JOIN eleves ON eleves.id = eleves_responsable_assos.eleves_id//*/

            // Construction de la condition
            if (isset($req['conditions']) && !empty($req['conditions'])) {
                $sql.='WHERE ';
                if (!is_array($req['conditions'])) {
                    $sql.= $req['conditions'];
                }else{
                    $cond = array();
                    foreach ($req['conditions'] as $k => $v) {
                        if ($k=='cond') {
                            $cond[] = $v;
                        }else{
                            if(!is_numeric($v)){
                                $v = '"'.htmlspecialchars($v, ENT_QUOTES, "UTF-8").'"';
                            }
                            $cond[] = "$k=$v";
                        }
                    }
                    $sql .= implode(' AND ',$cond);
                }
            }

            // GROUP BY            
            if (isset($req['groupBy'])) {
                $sql.='GROUP BY ';
                if (!is_array($req['groupBy'])) {
                    $sql.= $req['groupBy'];
                }else{
                    $sql .= implode(', ',$req['groupBy']);
                }
            }

            //ORDER BY id DESC
            if (isset($req['orderDesc'])) {
                $sql.=' ORDER BY '.$req['orderDesc'].' DESC';
            }elseif (isset($req['order'])) {
                $sql.=' ORDER BY '.$req['order'];
            }

            // Construction d'une éventuelle limite
            if (isset($req['limit'])) {
                $sql.=' LIMIT '.$req['limit'];
            }
        }
        try {
            $pre = $this->db->prepare($sql);
            $pre->execute();
            //debug($sql,'$sql');
            if (isset($req['groupBy'])) {
                $return = array();
                foreach ($pre->fetchAll(PDO::FETCH_ASSOC) as $v) {
                    $return[] = current($v);
                }
                return $return;
            }else{
                return $pre->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            debug($e->xdebug_message,'Retour Erreur Sql');
            return '';
        }
    }

    /**
     * Récupérer premier élément en date dans la table
     * @param varchar $table
     * @param array $req
     * @return array
     */
    public function findFirst($table, $req){
        return current($this->find($table, $req));
    }

    /**
     * Récupérer le nombre d'éléments dans la bdd satisfaisant la condition
     * @param <type> $table
     * @param <type> $conditions
     * @param <type> $field
     * @return int Nombre d'occurances
     */
    public function findCount($table, $conditions='',$field='id'){
        $res = $this->findFirst($table, array(
            'fields' => 'COUNT('.$field.') as count',
            'conditions' => $conditions
        ));
        return $res['count'];
    }

    /**
     * Permet de supprimer une valeur dans la bdd.
     * @param string $table
     * @param array $data
     * @return boolean
     **/
    public function delete($table, $data=null){
        $fields= array();

        if (is_array($data)) {
            foreach ($data as $k => $v) {
                if(!is_numeric($v)){
                    $v = '"'.htmlspecialchars($v, ENT_QUOTES, "UTF-8").'"';
                }
                $fields[] = "$k=$v";
            }
        }else{
            $fields[] = $data;
        }
        if (!empty($data)) {
            $sql = 'DELETE FROM '.$table.' WHERE '.implode(' AND ', $fields);
        }else{
            $sql = 'DELETE FROM '.$table;
        }
        try{
            $pre = $this->db->query($sql);
            return true;
        }catch (PDOException $e){Functions::setFlash($e,'danger');}
    }

    /**
     * Sauvegarder ou updater des données dans la bdd
     **/
    public function save($table, $data, $action){
        $id       = 0;
        $fields   = array();
        $insert   = array();
        $insert[] = array("fields"=>array());
        $insert[] = array("values"=>array());
        $where    = array();
        $d        = array();
        foreach ($data as $k => $v) {
            $insert['fields'][] = $k;
            $insert['values'][] = ':'.$k;
            $fields[] = "$k=:$k";
            $d["$k"] = $v;
        }
        if (isset($action['update']) && !empty($action['update']) && is_array($action['update'])) {
            foreach ($action['update'] as $k => $v) {
                if(!is_numeric($v)){ $v = '"'.$v.'"'; }
                $where[] = "$k=$v";
                $champs[] = array($k => $v);
            }
            $sql = 'UPDATE '.$table.' SET '.implode(', ', $fields).' WHERE '.implode(' AND ', $where);
            $id = current($champs[0]);
            $act = 'update';
        }else if (isset($action) && $action=='update') {
            $sql = 'UPDATE '.$table.' SET '.implode(', ', $fields);
            $id = -1;
            $act = 'update';
        }else if(isset($action) && $action=='insert'){
            if (!empty($data['fields']) && !empty($data['values']) && (is_array(current($data['values'])) && count($data['fields']) == count(current($data['values'])))) {
                $d = array();
                $values = array();
                foreach ($data['values'] as $v) {
                    foreach ($v as $k => $val) {
                        if (!is_numeric($val)) {
                            $v[$k] = '"'.$val.'"';
                        }
                    }
                    $values[] = '('.implode(', ', $v).')';
                }
                $sql = "INSERT INTO ".$table."(".implode(', ', $data['fields']).") VALUES ".implode(', ', $values)."";
            }else
                $sql = "INSERT INTO ".$table."(".implode(', ', $insert['fields']).") VALUES(".implode(', ', $insert['values']).")";
            $act = 'insert';
        }
        $pre = $this->db->prepare($sql);
        // debug($pre, '$pre');
        // debug($d, '$d');
        $pre->execute($d);
        if($act == 'insert'){
            $id = $this->db->lastInsertId();
        }
        return $id;
    }
    /*
        exemple :
        Functions::save('assos',array(
            'horaires'=> htmlentities($_POST['horaires'], ENT_QUOTES, "UTF-8"),
            'lieu'=> htmlentities($_POST['lieu'], ENT_QUOTES, "UTF-8"),
            'description'=> $_POST['description'],
            'pres_name'=> htmlentities($_POST['pres_name'], ENT_QUOTES, "UTF-8"),
            'nom_as'=> htmlentities($_POST['nom_as'], ENT_QUOTES, "UTF-8")
        ),array('update'=>array('id'=>$this_as_edit)));
    */
}