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

class CoreRouter {
    protected $request = null;
    protected $session = null;
    protected $env = null;
    protected $routingTable;
    protected $outputEngine;
    protected $middlewares = [];
    protected $rateStatus = 0;
    protected $rateEngine = NULL;
    public $jotformSDK = NULL;
    protected $allowedOutputEngines = array("xml","json","store");
    protected $overridedMethods = array("DELETE");
    protected $overridedParam = "_method";

    public function __construct($jotformSDK = null) {
        if ($jotformSDK) {
            $this->jotformSDK = $jotformSDK;
        }
        $this->request = $_REQUEST;
        $this->env = $_ENV;
        $this->env["REQUEST_FORMAT"] = "JSON";
    
        if (!isset($this->request['path'])) {
            $this->request['path'] = "index";
        }

        $pathMap = explode("/", $this->request['path']);
        $requestFormat = explode(".", end($pathMap));
        if(count($requestFormat) > 1) {
            if (in_array(end($requestFormat), $this->allowedOutputEngines )) {
                $this->env["REQUEST_FORMAT"] = end($requestFormat);
                $this->request['path'] = substr($this->request['path'], 0, strrpos($this->request['path'],".".$this->env["REQUEST_FORMAT"]));
            }
        }
    
        $outputEngineClass = "OutputEngines_".strtoupper($this->env["REQUEST_FORMAT"]);
        $this->outputEngine = new $outputEngineClass($this->request);
    
        if($this->env['REQUEST_METHOD'] === "OPTIONS") {
            header('HTTP/1.0 200');
            header('Access-Control-Allow-Headers:Content-Type');
            $this->CORS();
            echo $this->outputEngine->getString(array('responseCode'=>200, 'message'=>'success', 'content'=>'OPTIONS method is reserved for CORS requests'));
            exit();
        }
    
        if (isset($_SESSION)) {
             $this->session = $_SESSION;
        }
    }

    public function bind($rawURI, $callback, $requestType="GET") {
        $routingType = 'RoutingRules_Static'; // Default

        $paths = explode('/', $rawURI);
        $patterns = array(
            ':' => '(?P<{variable}>\d+)',
            '#' => '(?P<{variable}>[^\/]*)',
        );

        for($i=0; $i < count($paths); $i++) {
            $operator = substr($paths[$i], 0, 1);
            if ( in_array($operator, array_keys($patterns))) {
                $routingType = 'RoutingRules_Dynamic';
                $variableName = substr($paths[$i],1,strlen($paths[$i]));
                $paths[$i] = str_replace('{variable}', $variableName, $patterns[$operator]);
            }
        }

        $divider = ($routingType == 'RoutingRules_Dynamic') ? '\/' : '/';
        $URI = implode($divider, $paths);

        $routingRule = new $routingType();
        $routingRule->setCallBack($callback);
        $routingRule->setMethod($requestType);
        $routingRule->setURI($URI);
        $routingRule->setRawURI($rawURI);

        $this->routingTable[$requestType][$URI] = $routingRule;
    }

    public function bulkBind($URINodes,$callback,$requestType="GET") {
        if (is_array($URINodes)) {
            foreach ($URINodes as $URINode) {
                $this->bind($URINode,$callback,$requestType);
            }
        }
        else {
            $this->bind($URINodes,$callback,$requestType);
        }
    }

    /*
    Shortcut for binding function with GET/POST request
    */
    public function any($URI,$callback) {
        $this->bulkBind($URI,$callback,'GET');
        $this->bulkBind($URI,$callback,'POST');
    }

    /*
    Shortcut for binding function with GET request
    */
    public function get($URI,$callback) {
        $this->bulkBind($URI,$callback,'GET');
    }

    /*
    Shortcut for binding function with POST request
    */
    public function post($URI,$callback) {
        $this->bulkBind($URI,$callback,'POST');
    }

    /*
    Shortcut for binding function with PUT request
    */
    public function put($URI,$callback) {
        $this->bulkBind($URI,$callback,'PUT');
    }
    /*
    Shortcut for binding function with DELETE request
    */
    public function delete($URI,$callback) {
        $this->bulkBind($URI,$callback,'DELETE');
    }

    public function bindError($errorCode, $errorFunction) {
        $this->routingTable['errors'][$errorCode] = $errorFunction;
    }

    public function error($errorCode, $path, $message = NULL) {
        if (isset($this->routingTable['errors'])) {
            return call_user_func($this->routingTable['errors'][$errorCode], $path, $message);
        }
    }

    public function getResources(){
        foreach ($this->routingTable as $method => $rules) {
            if(in_array($method,array("GET","POST"))) {
                foreach ($rules as $rule => $value) {
                    $rule = "/".str_replace(array("\/","(?P<",">\w+)",">\d+)"), array("/","{","}","}"), $rule);
                    $resources[$method][] = $rule;
                }
            }
            if(isset($resources[$method]) && $resources[$method]) {
                sort($resources[$method]);
            }
        }
        return $resources;
    }

    public function route() {

        $request_type = $this->env['REQUEST_METHOD'];
        if(isset($_REQUEST[$this->overridedParam]) && in_array(strtoupper($_REQUEST[$this->overridedParam]), $this->overridedMethods)) {
            $request_type = strtoupper($_REQUEST[$this->overridedParam]);
        }

        $response = new ErrorModel(['code' => 404]);

        // Check if it's a static routing
        if(isset($this->routingTable[$request_type][$this->request['path']])) {
            $routingObject = $this->routingTable[$request_type][$this->request['path']];

            $response = call_user_func($routingObject->getCallBack());
        }

        // if it's a dynamic routing
        else if (isset($this->routingTable[$request_type])){
            
            foreach ($this->routingTable[$request_type] as $routingPattern => $routingObject) {
                if (($routingPattern != 'errors') && (get_class($routingObject) == 'RoutingRules_Dynamic')) {
                    preg_match('/^'.$routingPattern.'/s', $this->request['path'], $bindedArgs);
                    if ($bindedArgs && ($bindedArgs[0] == $this->request['path'])) {
                        $refFunc = new ReflectionFunction($routingObject->getCallBack());
                        $args = array();
                        foreach ($refFunc->getParameters() as $refParameter) {
                            if (isset($bindedArgs[$refParameter->getName()])) {
                                $passArgs[] = $bindedArgs[$refParameter->getName()];
                            }
                            else {
                                $passArgs[] = NULL;
                            }
                        }
                        $response = call_user_func_array($routingObject->getCallBack(), $passArgs);
                        break;
                    }
                }
            }
        }

        return $response;
    }

    public function middleware($middlewareFunc) {
        $this->middlewares[] = $middlewareFunc;
    }

    public function handleException($e) {
        $content = $e->getMessage();

        $isJSON = json_decode($content);
        if ($isJSON) {
            $content = $isJSON;
        }

        return ["responseCode" => 500, "message" => "error", "content" => $content];
    }

    public function CORS() {
        if (!isset($_SERVER['HTTP_ORIGIN'])) {
            $_SERVER['HTTP_ORIGIN'] = '*';
        }

        header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
        header('Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS, DELETE');

        if (preg_match('/jotform.dev/', $_SERVER['HTTP_ORIGIN'])) {
            header('Access-Control-Allow-Credentials: true');
        }
    }
}

?>
