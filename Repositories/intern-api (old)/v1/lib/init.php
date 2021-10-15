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

$autoloader = new AutoClassLoader();

ini_set('display_errors', '1');
error_reporting(E_ALL);

function checkErrors() { 
    $error = error_get_last();
    if ($error && !headers_sent()) {
        header('HTTP/1.0 500');
        var_dump($error);
    }
}
register_shutdown_function('checkErrors');

if(!isset($_SERVER["HTTP_USER_AGENT"])) {
    $_SERVER["HTTP_USER_AGENT"] = "User-Agent-Not-Defined";
}

if (php_sapi_name() !== "cli") {
    header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');
    header("Expires: Tue, 03 Jul 1970 06:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}

class AutoClassLoader {
    private $rootPath = "/www/intern-api";
    private $basePath = "lib";

    public function __construct() {
        spl_autoload_register([$this, 'loader']);
    }

    private function loader($className) {
        $classDirectory = '';
        if ($className == 'QueryOperatorProps') {
            $className = 'QueryOperator';
        }

        if (strpos($className, "_") !== FALSE) {
            $className = str_replace("_", "/", $className);
        }
        # Example : className = Router
        # Try to include lib/Router.class.php
        if (strpos($className,'_') !== FALSE) {
            $className = str_replace('_', '/', $className);
        }

        $classTypes = ['Model','View','Controller'];
        foreach ($classTypes as $key => $extension) {
            $classExtensions[$key] = "." . strtolower($extension);
        }

        $className = str_replace($classTypes, $classExtensions, $className);

        if (strpos($className, ".") !== FALSE ) {
            $classType = explode(".", $className);
            if (isset($classType[1])) {
                $classDirectory = ucfirst($classType[1])."s";
            }
        }

        $classFile = $className;
        if ($classDirectory != '') {
            $classFile = $classDirectory .  '/' . $classFile;
        }

        $classFile = sprintf('%s/%s/%s/%s/%s.php',
            $this->rootPath,
            DEVELOPER,
            'v1',
            $this->basePath,
            $classFile
        );

        if (!file_exists($classFile)) { 
            echo "File does not exists $classFile <br />";
            exit();
        }
        
        require_once $classFile;
        return true;
    }
}

?>
