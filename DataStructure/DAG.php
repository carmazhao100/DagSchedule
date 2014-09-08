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
        $this->m_reach_time = 0;
        $this->m_index = 0;
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
    
    //到达的时间
    public $m_reach_time;
    //编号
    public $m_index;
    
    public function showMe() {
        printf("start at %d\n", $this->m_entry_node->m_index
                );
        
       /* $current_node = $this->m_entry_node;
        
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
        }*/
        for($i = 0;$i < count($this->m_node_dic);$i++) {
            $node = $this->m_node_dic[$i];
            echo "====NODE " , $node->m_index , "\n";
            echo "拥有孩子 : \n";
            for($e = 0;$e < count($node->m_succ_edge_arr);$e++) {
                $succ_node = $node->m_succ_edge_arr[$e]->m_succ_node;
                echo "   " , $succ_node->m_index ,"  边长 :",$node->m_succ_edge_arr[$e]->m_cost,"\n";
            }
        }
    }
    
    public function resetAllNodes() {
        $this->m_reach_time = 0;
        $node_arr = array_values($this->m_node_dic);
        for($i = 0;$i < count($node_arr);$i++) {
            $node_arr[$i]->resetAllProperties();
        }
    }
    
    public function clearAllNodesValues() {
        $node_arr = array_values($this->m_node_dic);
        for($i = 0;$i < count($node_arr);$i++) {
            $node_arr[$i]->m_down_ward_value = -1;
            $node_arr[$i]->m_up_ward_value = -1;
        }
        $this->m_entry_node->m_down_ward_value = 0;
        $this->m_exit_node->m_up_ward_value = 0;
    }
    
    public function removeNode($node) {
        //从之前的里面磨灭他
        $pre_edge_arr = $node->m_pre_edge_arr;
        for($i = 0;$i < count($pre_edge_arr);$i++) {
            $edge = $pre_edge_arr[$i];
            $pre_node = $edge->m_pre_node;
            $pre_succ_edge_arr = $pre_node->m_succ_edge_arr;
            for($j = 0;$j < count($pre_succ_edge_arr);$j++) {
                if($pre_succ_edge_arr[$j]->m_succ_node == $node) {
                    array_splice($pre_node->m_succ_edge_arr, $j , 1);
                }
            }
        }
        //从之后的里面磨灭他
        $succ_edge_arr = $node->m_succ_edge_arr;
        for($i = 0;$i < count($succ_edge_arr);$i++) {
            $edge = $succ_edge_arr[$i];
            $succ_node = $edge->m_succ_node;
            $succ_pre_edge_arr = $succ_node->m_pre_edge_arr;
            for($j = 0;$j < count($succ_pre_edge_arr);$j++) {
                if($succ_pre_edge_arr[$j]->m_pre_node == $node) {
                    array_splice($succ_node->m_pre_edge_arr, $j , 1);
                }
            }
        }
    }
}