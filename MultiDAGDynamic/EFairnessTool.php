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
  //***********************初始化所有必须的数据结构*************************
        //构建ready Pool,为每一个DAG创建一个属于自己的ready序列
        $ready_pool = array();
        //存储所有已分配的node
        $allocated_node_arr = array();
        //保存每一个排好序的dag序列
        $dag_list_arr = array();
        for($i = 0;$i < count($dag_arr);$i++) {
            $arr = array();
            $ready_pool[$i] = $arr;
            //预处理，得到每个DAG的执行序列
            $list = AlgoTool::signPriorityByHEFT_Upward($dag_arr[$i]);
            $dag_list_arr[$i] = $list;
        }
        //打印信息
        echo "===============DAG LIST  的信息==================\n";
        for($i = 0;$i < count($dag_list_arr);$i++) {
            echo "DAG LIST  " , $i , " 数目是" , count($dag_list_arr[$i]) , "\n";
        }
        //用来计时
        $next_free_time = 0;
        $target_list_arr = array();
        while(true) {
            echo "\n\n\n======================新的一轮==========\n";
            if(count(self::combineAllPool($dag_list_arr)) == 0) {
                break;
            }
      //寻找可以解放的node list=====================================
            unset($target_list_arr);
            $target_list_arr = array();
            echo "------------现在开始审查 ALLOCATED NODE  ARRAY\n";
            echo "   Allocated node array 数目是 : " , count($allocated_node_arr) , "\n";
            if(count($allocated_node_arr)) {
                //排序
                usort($allocated_node_arr, 'sortByFinishTime');
                $next_free_time = $allocated_node_arr[0]->m_finish_time;
                echo "最早结束时间 " , $next_free_time , "\n";
                
                echo "开始构造 target list array,初始size = ",count($target_list_arr) , "\n";
                for($a = 0;$a < count($allocated_node_arr);$a++) {
                    $node = $allocated_node_arr[$a];
                    
                    if($node->m_finish_time <= $next_free_time) {
                        $dag_index = $node->m_dag->m_index;
                        if(in_array($dag_list_arr[$dag_index], $target_list_arr)) {
                            continue;
                        }
                        $target_list = &$dag_list_arr[$node->m_dag->m_index];
                        if(count($target_list) != 0) {
                            array_push($target_list_arr, $target_list);
                            echo "________DAG index ：" , $dag_index , "被选入 , 长度为" , count($target_list) , "\n";
                            array_splice($allocated_node_arr, $a , 1);
                            $a--;
                        }
                    }else{
                        break;
                    }
                    //如果一个都没选到，比如说目标DAG已经空了
                    
                    if(count(self::combineAllPool($target_list_arr) == 0)) {
                        echo "启动了============================";
                        for($i = 0;$i < count($dag_list_arr);$i++) {
                            if($dag_arr[$i]->m_reach_time <= $next_free_time) {
                                array_push($target_list_arr, $dag_list_arr[$i]);
                                echo "长度 " , count($dag_list_arr[$i]) , "\n";
                            }
                        }
                        echo "结束了============================";
                    }else {
                        echo "target array list 里面是有东西的！\n";
                    }
                    
                   // echo "xxxx现在的target_list_arr 为 " , count($target_list_arr) , "\n";
                }
            }else{
                for($i = 0;$i < count($dag_list_arr);$i++) {
                    if($dag_arr[$i]->m_reach_time <= $next_free_time) {
                        array_push($target_list_arr, $dag_list_arr[$i]);
                    }
                }
            }
            
            
            echo "\n++++++++++++开始执行算法，搜索ReadyNodes\n";
            $current_target_list_index = 0;
            echo "targetlist里 共有list " , count($target_list) , "\n";
            for($i = 0;$i < count($target_list_arr);$i++) {
                $sort_list = $target_list_arr[$i];
                if(count($sort_list) == 0) {
                    echo "本sort list = 0\n";
                    continue;
                }
                $current_target_list_index = $sort_list[0]->m_dag->m_index;
                //至少检查下所属的DAG是否到达了该执行的时间
                if(count($sort_list)) {
                    if($sort_list[0]->m_dag->m_reach_time > $next_free_time) {
                        continue;
                    }
                }
                
      //找到一个node 然后检查是否激活，激活了就放进去。否则中断
                $break_it = false;
                for($j = 0;$j < count($sort_list);$j++) {
                    $node = $sort_list[$j];  
                    //检查父节点有没有完备
                    echo "我是" , $node->m_dag->m_index , "DAG 的 " , $node->m_index , "号节点\n";
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
                        array_push($ready_pool[$node->m_dag->m_index] , $node);
                        echo "READY POOL index = " , $node->m_dag->m_index , "加入节点" , $node->m_index,"\n";
                        //删除掉
                        array_splice($dag_list_arr[$node->m_dag->m_index], $j, 1);
                        array_splice($sort_list , $j , 1);
                        $j--;
                        echo "     从sort list中删除 , 剩余长度: " ,count($dag_list_arr[$node->m_dag->m_index]) , "\n";
                    }
                }
                echo "选择完毕之后  sortlist = " , count($sort_list) , "\n";
                //如果队列空了 就删掉
                if(count($sort_list) == 0) {
                    //不可直接删除，因为后面会有用dag的index来决定一些操作
                    $dag_list_arr[$current_target_list_index] = array();
                }
            }
            //把现在pool里面的任务都清空掉
            $ready_node_list = self::combineAllPool($ready_pool);
            echo "现在的ready pool 数目是 " , count($ready_node_list) , "\n";
            while(count($ready_node_list)) {
               if(self::checkAllTheSame($ready_pool)) {
                   usort($ready_node_list, 'sortBigFirst');
                   //echo "big\n";
               }else{
                  usort($ready_node_list, 'sortSmallFirst');
                  //echo "small\n";
               }
               AlgoTool::distributeSingleNodeOnMachine($ready_node_list[0], $machine_arr,$next_free_time);
               //存储已分配的node
               array_push($allocated_node_arr, $ready_node_list[0]);  
               echo "   执行了一个节点:" , $ready_node_list[0]->m_index;
               //去除头元素
               array_splice($ready_node_list, 0, 1);
            }
            //重置ready pool
            $ready_pool = array();
            for($i = 0;$i < count($dag_arr);$i++) {
                $arr = array();
                $ready_pool[$i] = $arr;
            }
           // echo "qqqqq现在daglistarry大小为 " , count($dag_list_arr) , "\n";
        }
    }
    
    public static function combineAllPool($pool) {
        $arr = array();
        for($i = 0;$i < count($pool);$i++) {
            //echo "Pool 数组" , $i , " 拥有任务" , count($pool[$i]) , "\n";
            $arr = array_merge($arr , $pool[$i]);
        }
        //echo "=====================\n";
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
    if($node_1->m_up_ward_value == $node_2->m_up_ward_value) {
        return 0;
    }
    return $node_1->m_up_ward_value > $node_2->m_up_ward_value?-1:1;
}
function sortSmallFirst($node_1 , $node_2) {
    if($node_1->m_up_ward_value == $node_2->m_up_ward_value) {
        return 0;
    }   
    return $node_1->m_up_ward_value < $node_2->m_up_ward_value?-1:1;
}
function sortByFinishTime($node_1 , $node_2) {
    return $node_1->m_finish_time < $node_2->m_finish_time?-1:1;
}