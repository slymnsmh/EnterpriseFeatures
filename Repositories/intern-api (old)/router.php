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

define('DEVELOPER', $_SERVER['DEVELOPER']);
define('START',microtime(true));

try {
    require_once "/www/intern-api/".DEVELOPER."/v1/lib/init.php";

    $router = new Router();
    require_once "v1/router.errors.php";
    require_once "v1/router.rules.php";

    $sdk = new JotFormSDK();
    $router->sdk($jotformSDK);
    $router->serv();
} catch (Exception $e) {
    $response = new ResponseModel($router->handleException($e));
    $response->json();
}