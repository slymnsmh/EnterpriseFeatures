<?php
/**
 * JotForm API - Pragmatic API of jotform.com
 * 
 * @author      Ertuğrul Emre Ertekin <eee@jotform.com>
 * @copyright   2012 JotForm, Inc.
 * @link        http://www.jotform.com
 * @version     1.0
 * @package     JotFormAPI
 */

class SimpleModel {
    public function __construct($constructArray = array(), $normalized = false) {
        $XSS = false;

        foreach ($constructArray as $key => $value) {
            if (is_string($value)) {
                $value = str_replace(chr(0), '', $value);
            }
            if(empty($key)) {
                continue;
            }
            if ($normalized) {
                $this->{$key} = $this->denormalize($value, $XSS);
            } else {
                $this->{$key} = $this->normalize($value, $XSS);
            }
        }
    }

    public function normalizeBool($value) {
        switch ($value) {
            case 'true':
                $value = true;
                break;
            case 'false':
                $value = false;
                break;
        }
        return $value;
    }

    public function denormalizeBool($value) {
        if (!is_bool($value)) {
            return $value;
        }
        return $value ? "1" : "0";
    }

    public function normalize($value,$XSS=false) {
        if (is_string($value)) {
            $value = SanitizeEvents::filter($value);
            if ($XSS) {
                $value = $this->sanitize($value);
            }
        }
        $value = $this->denormalizeBool($value);
        return $value;
    }

    public function denormalize($value,$XSS=false) {
        if (is_string($value)) {
            $value = SanitizeEvents::filter($value);
            if ($XSS) {
                $value = $this->desanitize($value);
            }
            $value = $this->normalizeBool($value);
        }
        return $value;
    }

    # Sanitize the string against to XSS
    public function sanitize($str) {
        if(json_decode($str)) {
            return $str;
        }
        
        if(strlen($str) > 1){ // prevent '<' to be strippped
            $str = strip_tags($str);
        }

        $str = htmlspecialchars($str,ENT_QUOTES);
        return $str;
    }

    public function desanitize($str) {
        if(json_decode($str)) {
            return $str;
        }
        $str = htmlspecialchars_decode($str,ENT_QUOTES);
        return $str;
    }

    public function getClassDetails(){
        return ['classDetails', 'primaryKey','tableName','limits','fetchColumns','ORDER_BY','GROUP_BY','COLUMNS','CUSTOM_WHERE','NOESCAPE','all','cachable', 'USE_INDEX'];
    }
    
    public function set($key,$value) {
        if(is_array($key)) {
            foreach ($key as $var) {
                $this->$var = $value;
            }
        }
        else {
            $this->$key = $value;
        }
    }

     public function get($key) {
        return $this->$key ?? null;
    }

    public function getVars() {
        $vars = get_object_vars($this);
        
        foreach ($vars as $k => $v) {
            if (in_array($k, $this->getClassDetails())) {
                unset($vars[$k]);
            } else if (is_null($v)) {
                unset($vars[$k]);
            }
        }

        return $vars;
    }

    public function getClassVars() {
        return array_keys(get_class_vars(get_class($this)));
    }

    public function getModelVars() {
        $allVars = get_object_vars($this);
        return array_filter($allVars, function($key) {
            return !in_array($key, ['limits', 'fetchColumns', 'classDetails', 'tableName', 'primaryKey']);
        }, ARRAY_FILTER_USE_KEY);
    }

    public function getArray() {
        $modelArray = $this->getVars();
        foreach($modelArray as $key => $val) {
            if (is_null($val) || in_array($key, $this->getClassDetails())) {
                unset($modelArray[$key]);
            }
        }
        return $modelArray;
    }

    public function isEmpty() {
        $vars = get_object_vars($this);
        $vars = array_diff(array_keys($vars), $this->getClassDetails());

        foreach ($vars as $key) {
            if ($this->{$key}) {
                return false;
            }
        }
        return true;
    }

    public function getMappedTable() {
        return $this->tableName;
    }

    public function setMappedTable($tableName) {
        $this->tableName = $tableName;
    }

    public function getFetchColumns() {
        return $this->fetchColumns ?? ['*'];
    }

    public function setFetchColumns($fetchColumns) {
        $this->fetchColumns = $fetchColumns;
    }

    public function getLimits() {
        return $this->limits ?? ['offset'=>0, 'limit'=>10000];
    }

    public function where($condition) {
        if(!isset($this->CUSTOM_WHERE)) {
            $this->CUSTOM_WHERE = array();
        } else if (!is_array($this->CUSTOM_WHERE)) {
            $this->CUSTOM_WHERE = array($this->CUSTOM_WHERE);
        }
        $this->CUSTOM_WHERE[] = $condition;
        return $this;
    }

    public function setLimits($limitArray) {
        if (!isset($this->limits)) {
            $this->limits = [
                'offset' => 0, 
                'limit'  => 10000
            ];    
        }
        
        foreach(['offset','limit','deleteLimit'] as $op) {
            if (isset($limitArray[$op])) {
                $this->limits[$op] = $limitArray[$op];
            }
        }
    }

    public function orderBy($column, $direction = 'DESC') {
        if (!in_array($direction, ['DESC','ASC'])) {
            $direction = 'DESC';
        }
        
        if ($column) {
            $column = trim($column, '`');
            $this->ORDER_BY = sprintf('`%s` %s', $this->escape($column), $this->escape($direction));
        }
        return $this;
    }

    public function escape($var) {
        // mysql_escape_string Removed on PHP 7.x
        // return mysql_escape_string($var);
        return str_replace(
            ["\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a"],
            ["\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z"],
            $var
        );
    }

    public function cache($status = true) {
        $this->cachable = $status;
    }

    public function columns($columns = []) {
        foreach($columns as $i => $column) {
            $column = trim($column, '`');
            $columns[$i] = sprintf("`%s`", $this->escape($column));
        }
        $this->setFetchColumns($columns);
        return $this;
    }

    public function limit($offset = 0, $limit = 1) {
        $offset = intval($offset);
        $limit = intval($limit);

        if ($limit < 1) { 
            $limit = 1; 
        }
        if ($offset < 0) { 
            $offset = 0; 
        }
        
        $this->setLimits([
            "offset" => $offset,
            "limit"  => $limit,
        ]);
        return $this;
    }

    public function deleteLimit($limit = 1) {
        $this->setLimits(["deleteLimit" => $limit]);
        return $this;
    }

    public function groupBy($column) {
        if ($column) {
            if (is_array($column)) {
                foreach($column as $k => $v) {
                    $column[$k] = sprintf('`%s`', $this->escape(trim($v, '`')));
                }
                $this->GROUP_BY = implode(', ', $column);
            } else {
                $this->GROUP_BY = sprintf('`%s`', $this->escape(trim($column, '`')));
            }
        }
        return $this;
    }
}

?>
