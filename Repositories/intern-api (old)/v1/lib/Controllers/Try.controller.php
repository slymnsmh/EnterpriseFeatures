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

class TryController extends SimpleController {
    public function getInfo($name) {
        $redis = $this->sdk()->db("redis");

        if (!$redis->select($name."_name")) {
            $redis->insert($name."_name", $name);
            return "Person with name '".strtoupper($name)."' is added to database.";
        }
        else {
            return "There is a person with name '".strtoupper($name)."' in the database.";
        }
    }
}

?>