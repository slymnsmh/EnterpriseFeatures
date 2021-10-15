<?php
/**
 * JotForm API - Pragmatic API of jotform.com
 *
 * @author      Ertuğrul Emre Ertekin <eee@jotform.com>
 * @copyright   2021 JotForm, Inc.
 * @link        http://www.jotform.com
 * @version     1.0
 * @package     JotFormAPI
 */

$router->bindError(400, function ($path, $message) {
    return ["responseCode" => 400, "message" => $message];
});

$router->bindError(401, function ($path,$message) use (&$jotformSDK) {
    if (!$message) {
        $message = "You're not authorized to use (/$path) ";
    }
    return ["responseCode" => 401, "message" => $message];
});

$router->bindError(403, function ($path, $message) {
    if (!$message) {
        $message = "Forbidden to use /$path ";
    }
    return ["responseCode" => 403, "message" => $message];
});

$router->bindError(404, function ($path, $message) {
    if (!$message) {
        $message = "Requested URL (/$path) is not available!";
    }
    return ["responseCode" => 404, "message" => $message];
});

$router->bindError(500, function ($path, $message) {
    if (!$message) {
        $message = "The server encountered an internal error.";
    }
    return ["responseCode" => 400, "message" => $message];
});