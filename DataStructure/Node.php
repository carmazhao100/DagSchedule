<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Node {
    //每个节点的花费
    public  $m_cost;
    //cost字典,在不同机器上的cost
    public $m_cost_arr;
    //每个节点编号
    public $m_index;
    //前向节点
    public  $m_pre_node_arr;
    //后续节点
    public $m_succ_node_arr;
    //入边
    public  $m_pre_edge_arr;
    //出边
    public $m_succ_edge_arr;
    //优先级值
    public $m_up_ward_value;
    public $m_down_ward_value;
    
    
    /*==============机器相关=================*/
    //所在的机器id
    public $m_machine_id;
    //开始时间
    public $m_start_time;
    //结束时间
    public $m_finish_time;
    
    //构造+初始化
    public function __construct() {
        $this->m_cost = 0;
        $this->m_cost_arr = array();
        $this->m_index = 0;
        //节点数组
        $this->m_pre_node_arr = array();
        $this->m_succ_node_arr = array();
        //边数组
        $this->m_pre_edge_arr = array();
        $this->m_succ_edge_arr = array();
        //优先级
        $this->m_up_ward_value = -1;
        $this->m_down_ward_value = -1;
        //时间
        $this->m_start_time = -1;
        $this->m_finish_time = -1;
        $this->m_machine_id = -1;
    }
    
    public function showMe() {
        echo '***********************************';
        printf("MyIndex is %d\n" , $this->m_index);
    }
    
    public function countAverageCost() {
        $sum_cost = 0;
        for($i = 0;$i < count($this->m_cost_arr);$i++) {
            $sum_cost += $this->m_cost_arr[$i];
        }
        
        $avg_cost = $sum_cost/count($this->m_cost_arr);
        $this->m_cost = $avg_cost;
    }
    public function resetAllProperties() {
        //时间
        $this->m_start_time = -1;
        $this->m_finish_time = -1;
        $this->m_machine_id = -1;
    }
}