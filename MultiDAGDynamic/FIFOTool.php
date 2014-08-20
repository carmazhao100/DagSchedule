<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'DataStructure/DAG.php';
require_once 'DataStructure/Node.php';
require_once 'DataStructure/Edge.php';
require_once 'Machine/MachineManager.php';
require_once 'AlgoTool.php';

class FIFOTool {
    public static function runDAGArray($dag_arr , $machine_arr) {
        //先按到达时间给DAG排序
        usort($dag_arr, 'sortByReachTimeValue');
        //把所有的DAG里的node排序，输出队列并存储
        $sort_node_arr = array();
        for($i = 0;$i < count($dag_arr);$i++) {
            $node_arr = AlgoTool::signPriorityByHEFT_Upward($dag_arr[$i]);
            $sort_node_arr[$i] = $node_arr;
            AlgoTool::distributeNodesOnMachine($node_arr, MachineManager::getInstance()->m_machine_arr, $dag_arr[$i]->m_reach_time);
        }
    } 
}

function sortByReachTimeValue($dag_1 , $dag_2) {
    return $dag_1->m_reach_time > $dag_2->m_reach_time?1:-1;
}