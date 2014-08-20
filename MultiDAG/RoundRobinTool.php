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
    
class RoundRobinTool {
    public static function runDAGArray($dag_arr , $machine_arr) {
        //把所有的DAG里的node排序，输出队列并存储
        $sort_node_arr = array();
        for($i = 0;$i < count($dag_arr);$i++) {
            $node_arr = AlgoTool::signPriorityByHEFT_Upward($dag_arr[$i]);
            array_push($sort_node_arr, $node_arr);
        }
        
        //按照round-robin进行放置
        while(1) {
            $over = true;
            for($j = 0;$j < count($sort_node_arr);$j++) {
                 $current_arr = &$sort_node_arr[$j];
                 if(count($current_arr) > 0) {
                     $node = array_shift($current_arr);
                     AlgoTool::distributeSingleNodeOnMachine($node, $machine_arr , 0);
                     $over = false;
                 }else{
                     array_splice($sort_node_arr, $j, 1);
                 }
            }
            if($over) {
                break;
            }
        }
        
       // MachineManager::getInstance()->showMachineEnvironment();
    }
}