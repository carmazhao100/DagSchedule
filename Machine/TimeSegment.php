<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Global.php';
class TimeSegment {
    public $m_start_time;
    public $m_finish_time;
    //构造+初始化
    public function __construct() {
        $this->m_finish_time = MAX_NUMBER;
        $this->m_start_time = 0;
    }
}