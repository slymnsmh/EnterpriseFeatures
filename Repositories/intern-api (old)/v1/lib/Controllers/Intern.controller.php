<?php

/**
 * JotForm API - Pragmatic API of jotform.com
 *
 * @author      Ertugrul Emre Ertekin <eee@jotform.com>
 * @copyright   2021 JotForm, Inc.
 * @link        http://www.jotform.com
 * @version     1.0
 * @package     JotFormAPI
 */

class InternController extends SimpleController {

    public function sayHi() {
        return [
            'say' => 'Hi!',
        ];
    }

    public function sayHiFromRedis() {
        $redis = $this->sdk()->db("redis");
        
        // Just in case...
        if (!$redis->select("say")) {
            $redis->insert("say", "Hi!!" . time());
        }
        
        return [
            'say' => $redis->select("say"),
        ];
    }

    public function get($username) {
        if ($username == 'intern1') {
            return new InternModel(['username' => $username]);
        }

        return new ErrorModel([
            'code' => '404',
            'errorDetails' => 'User not found',
        ]);
    }

    public function getByID($id) {
        if ($id == 1) {
            return new InternModel(['username' => 'intern'. $id]);
        }

        return new ErrorModel([
            'code' => '404',
            'errorDetails' => 'User not found',
        ]);
    }
}