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

class Router extends CoreRouter{

    public function __construct() {
        parent::__construct(...func_get_args());

        $this->middleware([$this,'route']);
    }

    public function serv() {
        try {
            $response = [];
            foreach( $this->middlewares as $middleware) {
                $response = call_user_func($middleware, $response);
                if (is_object($response) && get_class($response) == "ErrorModel") {
                    if(!isset($response->errorDetails)) {
                        $response->errorDetails = NULL;
                    }
                    $response = $this->error = $this->error($response->code,$this->request['path'],$response->errorDetails);
                    break;
                }
            }
            if (!$response) {
                $response = $this->error(404,$this->request['path']);
            }
        } catch (Exception $e) {
            $response = $this->handleException($e);
        }
        $response = new ResponseModel($response);
        try {
            $responseString = $this->outputEngine->getString($response);
        } catch (Exception $e) {
            $responseString = $this->outputEngine->getString($this->handleException($e));
        }

        $response->setStatusHeader();
        $this->CORS();

        if (isset($this->request['callback'])) {
            $responseString = $this->request['callback'].'('.$responseString.');';
        }

        echo $responseString;
    }

    public function sdk($sdk = NULL) {
        if (is_null($sdk)) {
            return $this->jotformSDK;
        }
        $this->jotformSDK = $sdk;
    }
}



?>
