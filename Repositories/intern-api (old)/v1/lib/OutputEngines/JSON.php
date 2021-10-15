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

class OutputEngines_JSON {
    // http://stackoverflow.com/questions/10199017/how-to-solve-json-error-utf8-error-in-php-json-decode/26760943#26760943
    public static function utf8ize($mixed) {

        if (is_string($mixed)) {
            return self::checkUTF8($mixed);
            // return utf8_encode($mixed);
        } else if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                if(is_string($key) && self::utf8ize($key) !== $key) {
                    unset($mixed[$key]);
                    $key = self::utf8ize($key);
                }
                $mixed[$key] = self::utf8ize($value);
            }
        } else if (is_object($mixed)) {
            $keys = array_keys(get_object_vars($mixed));
            foreach ($keys as $key) {
                $key = trim($key);
                if(is_string($key) && self::utf8ize($key) !== $key) {
                    unset($mixed->{$key});
                    $key = self::utf8ize($key);
                }
                $mixed->{$key} = self::utf8ize($mixed->{$key});
            }
        }

        return $mixed;
    }

    public function getString($responseArray) {
        header('Content-Type: application/json');
        $json = json_encode($responseArray);
        
        switch (json_last_error()) {
            case JSON_ERROR_UTF8:
                $json = json_encode(self::utf8ize($responseArray));
                break;
            default:
                # noop
                break;
        }

        if (!$json) {
            throw new Exception("JSON Engine failed. ERRCODE: #". json_last_error_msg());
        }
        return $json;
    }
    
    public static function checkUTF8($string = '') {
        $encoding = mb_detect_encoding($string, "UTF-8");
        if ($encoding === "UTF-8" && mb_check_encoding($string, "UTF-8")) {
            $string = $string;
        } else {
            $string = utf8_encode($string);
        }
        return $string;
    }
}

?>