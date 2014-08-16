<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Edge.php';
require_once 'Node.php';

class DAG {
    public function __construct() {
        $this->m_entry_node = null;
        $this->m_exit_node = null;
        $this->m_node_dic = array();//其实是个dic
    }
    
    //出入口节点
    public  $m_entry_node;
    public  $m_exit_node;
    //顶点集合
    public  $m_node_dic;
    public function getNodeByIndex($index) {
        $node = $this->m_node_dic[$index];
        if(is_null($node)) {
            trigger_error('node for this index is null' , E_USER_ERROR);
        }

        return $node;
    }
    public function addNode($index , $node) {
        if(is_null($this->m_node_dic)) {
            trigger_error('Node dic is null' , E_USER_ERROR);
        }

        $this->m_node_dic[$index] = $node;
    }
    
    public function showMe() {
        printf("start at %d\n", $this->m_entry_node->m_index
                );
        
        $current_node = $this->m_entry_node;
        
        $node_queue = array();
        array_push($node_queue, $current_node);
        while(true) {
            if(count($node_queue) == 0) {
                break;
            }
            $top_node = array_shift($node_queue);
            
            $child_arr = $top_node->m_succ_node_arr;
            printf('----------Current Node is %d\n' , $top_node->m_index);
            for($i = 0;$i < count($child_arr);$i++) {
                $child_node = $child_arr[$i];
                printf('Child Node is %d\n' , $child_node->m_index);
            }
        }
    }
}