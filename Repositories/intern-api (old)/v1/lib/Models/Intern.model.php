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

class InternModel extends SimpleModel {

    public $username;
    
    protected $primaryKey = ['username'];
    protected $tableName = 'interns';
}