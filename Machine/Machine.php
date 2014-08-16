<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'TimeSegment.php';

class Machine {
    //用来装任务
    public $m_node_arr;
    //用来装时间碎片
    public $m_time_seg_arr;
    
    //构造+初始化
    public function __construct() {
        $this->m_node_arr = array();
        $this->m_time_seg_arr = array();
        //放入大的时间片
        $seg = new TimeSegment();
        array_push($this->m_time_seg_arr, $seg);
    }
}
