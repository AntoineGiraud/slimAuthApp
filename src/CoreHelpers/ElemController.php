<?php

namespace CoreHelpers;

abstract class ElemController {
    abstract public static function tableName();
    abstract public static function codeField();

    abstract public static function getEmptyElem();
    abstract public static function parse($d);
    abstract public static function validate($id, &$d, $DB);
    abstract public static function insert($d, $DB);
    abstract public static function getInsertSql($d);
    abstract public static function getUpdateSql($d, $cur, $id);

    public static function update($d, $cur, $id, $DB) {
        $update = static::getUpdateSql($d, $cur, $id);
        if (!empty($update)) {
            $DB->query($update);
            return true;
        }
        return false;
    }
    public static function delete($id, $DB) { $table = static::tableName();
        $DB->query("DELETE FROM $table WHERE `id` = :id", ['id'=>$id]);
    }

    abstract public static function getCsvHead();
    abstract public static function rowToCsv($row);

    public function has($key) { return !empty($this->list[$key]); }
    public function get($key) { return $this->list[$key]; }
    public function hasId($id) { return !empty($this->listId[$id]); }
    public function getId($id) { return $this->listId[$id]; }

    public static function cleanUpText($text) { return str_replace(['"', '\\', ';'], ["''", '/', ','], $text); }
    public static function cleanUpCode($text) { return str_replace(['"', '\\', ';', ' '], ["''", '/', ',', ''], $text); }
    public static function emptySqlInt($val) { return empty($val)? 'null' : 1*$val; }
    public static function emptySqlText($val) { return empty($val)? 'null' : '"'.$val.'"'; }
    public static function sp_pun($word, $count) {$s_or_no_s = $count>1 ? 's' : ''; return $word . $s_or_no_s; }

    ///////////////////////////////////
    // fetch & check up DB functions //
    ///////////////////////////////////
    public static function fetchRow($code) {
        if (empty($code)) return null;
        global $DB;
        $table = static::tableName();
        $field = static::codeField();
        $elem = $DB->queryFirst("SELECT * FROM $table
                                 WHERE UPPER($field) = UPPER( :$field )", [$field => $code]);
        return empty($elem) ? null : $elem;
    }
    public static function fetchRowById($id) {
        if (empty($id)) return null;
        global $DB;
        $table = static::tableName();
        $elem = $DB->queryFirst("SELECT * FROM $table
                                 WHERE id = :id", ['id' => $id]);
        return empty($elem) ? null : $elem;
    }
    public static function rowIdExists($id) {
        if (empty($id)) return null;
        if (!is_numeric($id))
            return false;
        global $DB;
        $table = static::tableName();
        $code = static::codeField();
        $res = $DB->queryFirst("SELECT $code FROM $table WHERE id = :id", ['id' => $id]);
        return empty($res) ? false : current($res);
    }
    public static function rowCodeExists($code) {
        if (empty($code)) return null;
        global $DB;
        $table = static::tableName();
        $field = static::codeField();
        $res = $DB->queryFirst("SELECT id FROM $table WHERE $field = :$field", [$field => $code]);
        return empty($res) ? false : (int)current($res);
    }
    public static function getFileExtension($file) {
        return pathinfo($file, PATHINFO_EXTENSION);
    }
}