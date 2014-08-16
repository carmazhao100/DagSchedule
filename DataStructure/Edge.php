<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Edge {
    public function __construct() {
        $this->m_cost = 0;
        $this->m_pre_node = null;
        $this->m_succ_node = null;
    }
   
    //边的cost
    public  $m_cost;
    //子节点
    public $m_succ_node;
    public $m_pre_node;
}