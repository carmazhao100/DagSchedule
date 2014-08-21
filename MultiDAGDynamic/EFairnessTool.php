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

class EFairnessTool {
    public static function runDAGArray($dag_arr , $machine_arr) {
        //构建ready Pool,为每一个DAG创建一个属于自己的ready序列
        $ready_pool = array();
        //保存每一个排好序的dag序列
        $dag_list_arr = array();
        for($i = 0;$i < count($dag_arr);$i++) {
            $arr = array();
            $ready_pool[$i] = $arr;
            //预处理，得到每个DAG的执行序列
            $sort_list = AlgoTool::signPriorityByHEFT_Upward($dag_arr[$i]);
            $dag_list_arr[$i] = $sort_list;
        }
        
        //用来计时
        $next_free_time = 0;
        while(true) {
            if(count($dag_list_arr) == 0) {
                break;
            }
      //寻找可以解放的node=====================================
            for($i = 0;$i < count($dag_list_arr);$i++) {
                $sort_list = &$dag_list_arr[$i];
                //还没到时间
               // echo "reach时间是 ", $dag_arr[$i]->m_reach_time, "现在时间是 ",$next_free_time , "\n";
                if($dag_arr[$i]->m_reach_time > $next_free_time) {
                    //echo "reach时间是 ", $dag_arr[$i]->m_reach_time, "现在时间是 ",$next_free_time , "\n";
                    continue;
                }
                
      //找到一个node 然后检查是否激活，激活了就放进去。否则中断
                $break_it = false;
                for($j = 0;$j < count($sort_list);$j++) {
                    $node = $sort_list[$j];  
                    //检查父节点有没有完备
                    for($p = 0;$p < count($node->m_pre_edge_arr);$p++) {
                        $pre_edge = $node->m_pre_edge_arr[$p];
                        $pre_node = $pre_edge->m_pre_node;
                        //如果仍未分配
                        if($pre_node->m_machine_id < 0) {
                            $break_it = true;
                            break;
                        }
                    }
                    //一旦本节点不行了 那后面的都不用看了
                    if($break_it) {
                        break;
                    }else{
                        //看来本node还是可以激活的，把它放到激活队列里
                        array_push($ready_pool[$i] , $node);
                        //删除掉
                        array_splice($sort_list, $j, 1);
                    }
                }
                //如果队列空了 就删掉
                if(count($sort_list) == 0) {
                    array_splice($dag_list_arr, $i, 1);
                    $i--;
                }
            }
            
            //把现在pool里面的任务都清空掉
            $ready_node_list = self::combineAllPool($ready_pool);
            if(self::checkAllTheSame($ready_pool)) {
                usort($ready_node_list, 'sortBigFirst');
            }else{
                usort($ready_node_list, 'sortSmallFirst');
            }
            $next_free_time = AlgoTool::distributeNodesFromMultiDAGsOnMachine($ready_node_list, $machine_arr);
            //重置ready pool
            $ready_pool = array();
            for($i = 0;$i < count($dag_arr);$i++) {
                $arr = array();
                $ready_pool[$i] = $arr;
            }
        }
    }
    
    public static function combineAllPool($pool) {
        $arr = array();
        for($i = 0;$i < count($pool);$i++) {
            $arr = array_merge($arr , $pool[$i]);
        }
        return $arr;
    }
    
    public static function checkAllTheSame($pool) {
        $result = false;
        $dag_count = 0;//记录有多少个dag
        for($i = 0;$i < count($pool);$i++) {
            if(count($pool[$i])) {
                $dag_count++;
            }
        }
        if($dag_count <= 1) {
            $result = true;
        }
        
        return $result;
    }
}

function sortBigFirst($node_1 , $node_2) {
    return $node_1->m_up_ward_value > $node_2->m_up_ward_value?-1:1;
}
function sortSmallFirst($node_1 , $node_2) {
    return $node_1->m_up_ward_value < $node_2->m_up_ward_value?-1:1;
}