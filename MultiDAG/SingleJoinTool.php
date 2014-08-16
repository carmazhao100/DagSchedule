<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../DataStructure/DAG.php';
require_once '../DataStructure/Node.php';
require_once '../DataStructure/Edge.php';

class SingleJoinTool {
    //输入多个DAG输出一个连接DAG
    public static function JoinDAGs_SimpleJoin($dag_arr) {
        $new_dag = new DAG();
        
        $entry_node = new Node();
        $entry_node->m_index = 0;
        $entry_node->m_down_ward_value = 0;
        
        $exit_node = new Node();
        $exit_node->m_up_ward_value = 0;
        
        //cost都清零
        for($a = 0 ; $a < $mnumber;$a++) {
            $cost = 0;
            array_push($entry_node->m_cost_arr, $cost);
            array_push($exit_node->m_cost_arr, $cost);
        }
        
        $new_dag->m_entry_node = $entry_node;
        $new_dag->m_exit_node = $exit_node;
        //将所有的连接到虚拟头节点上
        $index = 1;
        for($i = 0;$i < count($dag_arr);$i++) {
            $dag = $dag_arr[$i];
            //连接头
            $old_head = $dag->m_entry_node;
            
            $new_edge = new Edge();
            $new_edge->m_cost = 0;
            $new_edge->m_pre_node = $entry_node;
            $new_edge->m_succ_node = $old_head;
            
            //设置关系
            array_push($old_head->m_pre_node_arr , $entry_node);
            array_push($old_head->m_pre_edge_arr , $new_edge);

            array_push($entry_node->m_succ_node_arr , $old_head);
            array_push($entry_node->m_succ_edge_arr , $new_edge);
            
            //连接尾
            $old_tail = $dag->m_exit_node;
            
            $new_edge = new Edge();
            $new_edge->m_cost = 0;
            $new_edge->m_pre_node = $old_tail;
            $new_edge->m_succ_node = $exit_node;
            
            //设置关系
            array_push($exit_node->m_pre_node_arr , $old_tail);
            array_push($exit_node->m_pre_edge_arr , $new_edge);

            array_push($old_tail->m_succ_node_arr , $exit_node);
            array_push($old_tail->m_succ_edge_arr , $new_edge);
            
            //重新编号加入新DAG中
            $node_arr = array_values($dag->m_node_dic);
            for($m = 0;$m < count($node_arr);$m++) {
                $tmp_node = $node_arr[$m];
                $tmp_node->m_index = $index;
                array_push($dag->m_node_arr, $tmp_node);
                $new_dag->m_node_dic[$index] = $tmp_node;
                $index++;
            }
        }
        $exit_node->m_index = $index;
        $new_dag->m_node_dic[$index] = $exit_node;
        $new_dag->m_node_dic[0] = $entry_node;
        
        return $new_dag;
    }
}
