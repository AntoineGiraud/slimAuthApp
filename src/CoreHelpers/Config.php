<?php

class Config {
    protected static $config = array();
    
    public static function initFromArray($arr) {
        static::$config = $arr;
    }
    
    public static function get($name, $default = null) {
        if (array_key_exists($name, static::$config))
            return static::$config[$name];
        else
            return $default;
    }
    
    public static function set($name, $value) {
        static::$config[$name] = $value;
    }

    public static function getDbConfig($name){
        global $DB;
        $conf = $DB->findFirst('configs',array(
            'fields' => 'value',
            'conditions' => array('name'=>$name)));
        if (empty($conf)) {
            $DB->query("INSERT INTO configs VALUES(:name, '')",array('name'=>$name));
            return "";
        }else return current($conf);
    }

    public static function setDbConfig($name,$value){
        global $DB;
        return $DB->save('configs',array('value'=> $value),array('update'=>array('name'=>$name)));
    }
}