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

class SimpleController {
    public $sdkInstance;

    public function __construct($sdkInstance){
        $this->sdkInstance = $sdkInstance;
    }

    public function sdk() {
        return $this->sdkInstance;
    }

    public function syntax() {
        return 1;
    }
}

?>