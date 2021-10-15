<?php
/**
 * JotForm API - Pragmatic API of jotform.com
 * 
 * @author      Ertuğrul Emre Ertekin <eee@jotform.com>
 * @copyright   2018 JotForm, Inc.
 * @link        http://www.jotform.com
 * @version     1.0
 * @package     JotFormAPI
 */

class ResponseModel {
    public $responseCode = 200;
    public $message = "success";
    public $content = [];
    public $duration;

    public function __construct($constructArray) {
        foreach ( $constructArray as $key => $value) {
            $this->{$key} = $value;
        }
        $this->setDuration();
    }

    private function setDuration() {
        $duration = (microtime(true) - START)*1000;
        $this->duration =  sprintf('%.2fms',$duration);
    }

    public function setStatusHeader() {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest") {
            # Suppress the error code if it's an AJAX request #
            $this->responseCode = 200;
        } else if (isset($_SERVER['HTTP_ORIGIN']) ){
            # Some browsers not sending HTTP_X_REQUESTED_WITH header
            # Suppress the error code if it's an AJAX request #
            $this->responseCode = 200;
        } else if (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] == "Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko") {
            # IE11 not sending neither HTTP_X_REQUESTED_WITH or HTTP_ORIGIN header on CORS Requests
            # Suppress the error code if it's an AJAX request #
            $this->responseCode = 200;
        }
        
        header('HTTP/1.0 ' . $this->responseCode);
        if(is_string($this->content) && strpos($this->content, "Storage Engine is not available") !== FALSE) {
            $this->responseCode = 500;
        }
    }

    public function json() {
        $this->setStatusHeader();
        echo json_encode($this);
    }
}

?>
