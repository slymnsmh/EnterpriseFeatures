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

class JotFormSDK {
    private $engines = [];

    public function __construct() {
    }

    public function __destruct() {
        foreach ($this->engines as $engine) {
            if ($engine) {
                $engine->disconnect();
            }
        }
    }

    public function db($engine) {
        if (!$engine) {
            throw new Exception("Empty storage engine is not allowed!");
        }

        $engineModel = 'Storage_'. ucwords($engine);
        if (!class_exists($engineModel)) {
            throw new Exception($engine . " storage engine is not available!");
        }

        if (!isset($this->engines[$engine])) {
            $this->engines[$engine] = new $engineModel;
            $this->engines[$engine]->connect();
        }
        return $this->engines[$engine];
    }

    public function isError($data) {
        return is_object($data) && (get_class($data) == "ErrorModel");
    }

    public function controller($moduleName) {
        if (strpos($moduleName, "Controller") === FALSE) {
            $moduleName = $moduleName."Controller";
        }

        if(!isset( $this->{$moduleName} )) {
            $this->{$moduleName} = new $moduleName($this);
        }

        return $this->{$moduleName};
    }
}
