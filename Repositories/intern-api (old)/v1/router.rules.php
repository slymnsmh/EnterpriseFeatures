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



$router->get('getinfo/#name', function($name) use (&$sdk) {
    $data = $sdk->controller('Try')->getInfo($name);
    
    if ($sdk->isError($data)) {
        return $data;
    }
    return ['message'=> 'success', 'content' => $data];
});

$router->get('hello', function() use (&$sdk) {
    $data = $sdk->controller('Intern')->sayHi();
    
    if ($sdk->isError($data)) {
        return $data;
    }

    return ['message'=> 'success', 'content' => $data];
});

$router->get('redis/hello', function() use (&$sdk) {
    $data = $sdk->controller('Intern')->sayHiFromRedis();
    
    if ($sdk->isError($data)) {
        return $data;
    }

    return ['message'=> 'success', 'content' => $data];
});

$router->get('hello/#username', function($username) use (&$sdk) {
    $data = $sdk->controller('Intern')->get($username);
    
    if ($sdk->isError($data)) {
        return $data;
    }

    $data->say = 'Hi!';

    return ['responseCode'=> 200, 'message'=> 'success', 'content' => $data];
});


$router->get('hello-id/:id', function($id) use (&$sdk) {
    $data = $sdk->controller('Intern')->getByID($id);
    
    if ($sdk->isError($data)) {
        return $data;
    }

    $data->say = 'Hi!';

    return ['responseCode'=> 200, 'message'=> 'success', 'content' => $data];
});