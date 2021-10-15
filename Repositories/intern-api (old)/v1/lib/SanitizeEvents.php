<?php
/**
 * JotForm API - Pragmatic API of jotform.com
 * 
 * @author      Ertuğrul Emre Ertekin <eee@jotform.com>
 * @copyright   2020 JotForm, Inc.
 * @link        http://www.jotform.com
 * @version     1.0
 * @package     JotFormAPI
 */

 class SanitizeEvents {
    static private $events = [];
    static private $replaceTo = [];

    static private $jsEvents = [
        'click', 
        'ctextmenu',
        'contextmenu',
        'dblclick',
        'mousedown',
        'mouseenter',
        'mouseleave',
        'mousemove',
        'mouseover',
        'mouseout',
        'mouseup',
        'mousewheel',
        'offline',
        'popstate',
        'toggle',
        'touchcancel',
        'touchend',
        'touchmove',
        'touchstart',
        'abort',
        'beforeunload',
        'error',
        'hashchange',
        'load',
        'pageshow',
        'pagehide',
        'resize',
        'scroll',
        'unload',
        'blur',
        'change',
        'focus',
        'focusin',
        'focusout',
        'input',
        'invalid',
        'reset',
        'search',
        'submit',
        'start',
        'keypress',
        'keyup',
        'keydown',
    ];

    static function filter($raw) {
        if ($raw == 'couponChange' || $raw == 'couponInvalid') {
            return $raw;
        }

        if (!self::$events) {
            self::generateList();
        }

        return str_ireplace(self::$events, self::$replaceTo, $raw);
    }

    static private function generateList() {
        $events = [];
        foreach (self::$jsEvents as $disable) {
            $events["on{$disable}="] = "onDISABLED{$disable}=";
        }
        
        $events['<svg xmlns=']  = '<svg xmlns=DISABLED:';
        $events['xlink:href']   = 'xlink:hrefDISABLED';
        $events['xlink:type']   = 'xlink:typeDISABLED';
        $events['xlink:show']   = 'xlink:showDISABLED';

        $events['javascript:']  = 'javascriptDISABLED:';
        $events['javascript&colon;'] = 'javascriptDISABLED:';
        
        $events['data:text/html;base64'] = 'data:text/html;base64DISABLED:';

        self::$events = array_keys($events);
        self::$replaceTo = array_values($events);
        unset($events);
    }
}