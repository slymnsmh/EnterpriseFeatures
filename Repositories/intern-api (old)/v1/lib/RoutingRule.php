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

class RoutingRule {

    private $requestURI = null;
    private $rawURI = null;
    private $callback = null;
    private $requestMethod = "GET";

    public function setCallBack($callbackFunction) {
        $this->callback = $callbackFunction;
    }

    public function setMethod($method) {
        $this->requestMethod = $method;
    }

    public function setURI($uri) {
        $this->requestURI = $uri;
    }

    public function getCallBack() {
        return $this->callback;
    }

    public function getMethod() {
        return $this->requestMethod;
    }

    public function getURI() {
        return $this->requestURI;
    }

    public function setRawURI($uri) {
        $this->rawURI = $uri;
    }

    public function getRawURI() {
        return $this->rawURI;
    }

}

?>
