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

class MyAlgoTool {
     public static function runDAGArray($dag_arr , $machine_arr) {
        usort($dag_arr, 'sortByReachTimeValue');
        
        //当前操作的dag
        $current_dag = $dag_arr[0];
        $current_node_list = AlgoTool::signPriorityByHEFT_Upward($dag_arr[0]);
        
        //记录当前合并到第几个DAG了
        $current_target_dag_id = 1;
        //记录所有的结束时间
        $time_arr = array();
        
        $current_time = 0;
        array_push($time_arr , $current_time);
        while(true) {
            //排序时间
            usort($time_arr, 'sortTime');
            for($i = 0;$i < count($time_arr);$i++) {
               // echo "时间是 " , $time_arr[$i] , "\n";
            }
            echo "时间完毕\n";
            //如果一直到上一个DAG执行完都没有接续的话 那么直接跳到下一个DAG上执行
            if(count($time_arr) > 0) {
                $current_time = $time_arr[0];
                array_splice($time_arr, 0 , 1);//删掉头时间
                
                if($current_target_dag_id < count($dag_arr)) {
                    if($dag_arr[$current_target_dag_id]->m_reach_time <= $current_time) {
                    //进行合并
                      //  echo "要合并了**** 目标DAG  ： " , $dag_arr[$current_target_dag_id]->m_index , "\n";
                        $current_dag->clearAllNodesValues();
                        AlgoTool::countPriorityUpward($current_dag->m_entry_node);
                        $current_dag = self::combineTwoDAGs($current_node_list, $current_dag, $dag_arr[$current_target_dag_id]);
                        
                        $current_dag->clearAllNodesValues();
                        echo "马上了 \n";
                        $current_node_list = AlgoTool::signPriorityByHEFT_Upward($current_dag);
                        echo "合并完之后================================\n";
                        for($i = 0;$i <count($current_node_list) ; $i++) {
                            echo "node " , $current_node_list[$i]->m_index ,"隶属于 DAG ",$current_node_list[$i]->m_dag->m_index , "拥有EFT ",$current_node_list[$i]->m_up_ward_value , "\n";
                        }
                        echo "可以了\n";
                        
                        for($s = 0;$s < count($current_node_list);$s++) {
                            //echo "===当前为 " , $current_node_list[$s]->m_up_ward_value , "  属于 " ,$current_node_list[$s]->m_dag->m_index , "  ID = " ,$current_node_list[$s]->m_index  , "\n";
                            $tmp_node = $current_node_list[$s];
                            $tmp_node->m_up_ward_value = $tmp_node->m_up_ward_value - $tmp_node->m_dag->m_index * 40000;
                        }
                        //echo "得到了新的nodelist ,长度: " , count($current_node_list) , "\n";
                        $current_target_dag_id++;
                    }
                }
                
            }else{
            //否则如果时间都用完了，那么直接跳到下一个DAG去
                if($current_target_dag_id < count($dag_arr)) {
                    echo "------------------------------跳到了下一个DAG \n";
                    $current_dag = $dag_arr[$current_target_dag_id];
                    $current_node_list = AlgoTool::signPriorityByHEFT_Upward($current_dag);
                    $current_target_dag_id++;
                    $current_time = $current_dag->m_reach_time;
                }else{
                    break;
                }
            }
            
           //找到可以执行的node
            $active_node_arr = array();
            for($i = 0;$i < count($current_node_list);$i++) {
               //检查父节点完备性
               $ok_to_active = true;
               $pre_node_arr = $current_node_list[$i]->m_pre_node_arr;
               for($j = 0;$j < count($pre_node_arr);$j++) {
                   if($pre_node_arr[$j]->m_machine_id < 0) {
                       $ok_to_active = false;
                       break;
                   }
               }
               
               if($ok_to_active) {
                   array_push($active_node_arr, $current_node_list[$i]);
                   //echo "选中了 est = " , $current_node_list[$i]->m_up_ward_value ,"\n";
                   array_splice($current_node_list, $i,1);
                   $i--;
               }
            } 
           
            //对执行的node 进行分配
           // echo "这次的激活节点有 " , count($active_node_arr) , "\n";
            for($a = 0;$a < count($active_node_arr);$a++) {
               echo "分配了节点 :  " , $active_node_arr[$a]->m_index , "隶属DAG " , $active_node_arr[$a]->m_dag->m_index,"\n";
              //  echo "=========基准时间 " , $current_time , "\n";
               $finish_time = AlgoTool::distributeSingleNodeOnMachine($active_node_arr[$a], $machine_arr, $current_time);
              // echo "========结束时间 :" , $finish_time , "\n" ;
               array_push($time_arr, $finish_time);
              // echo "-----其eft : " , $active_node_arr[$a]->m_up_ward_value , "\n";
               $active_node_arr[$a]->m_active = false;
              // echo "此时分配了的节点是 : " , $active_node_arr[$a]->m_index , "\n";
            }
        }
    }  
    
    public static function combineTwoDAGs($old_dag_list ,$old_dag , $new_dag) {
        echo "+++++++++++合并前  entrynode 为 " , $new_dag->m_entry_node->m_index , " 隶属于 DAG = " , $new_dag->m_entry_node->m_dag->m_index , "\n";;
        
        AlgoTool::countPriorityDownward($new_dag->m_exit_node);
        $new_node_list = array_values($new_dag->m_node_dic);
        usort($new_node_list, 'sortByDownwardValue');
        //寻找eft
        $max_eft = 0;
        $old_start_index = 0;
        for($i = 0;$i < count($old_dag_list);$i++) {
            $old_node = $old_dag_list[$i];
             echo "===index = " , $old_node->m_index , " 隶属于 DAG = " , $old_node->m_dag->m_index , "大小为" ,$old_node->m_up_ward_value, "\n";
            // 一旦找到了最高eft就记录下来
            if($old_node->m_active == true) {
               // echo "===此时找到了最高EFT ，index = " , $old_node->m_index , " 隶属于 DAG = " , $old_node->m_dag->m_index , "\n";
                $max_eft = $old_node->m_up_ward_value;
                $old_start_index = $i;
                break;
            }
            echo "这里 i = ",$i," 没激活\n";
        }
        
        //寻找
        $min_inter = PHP_INT_MAX;
        $new_target_index = 0;
        for($j = 0;$j < count($new_node_list);$j++) {
            echo "这个看看昂昂 :" , $new_node_list[$j]->m_down_ward_value ," MAX_EFT = ",$max_eft, "\n";
            $inter = abs($max_eft - $new_node_list[$j]->m_down_ward_value);
            if($inter < $min_inter) {
                $new_target_index = $j;
                $min_inter = $inter;
            }
            echo "********当前的INTER = " , $inter ," min = ", $min_inter ,  "\n";
        }
        
        echo "选择了 " , $new_target_index , "\n";
        echo "粗来了 \n";
       // echo "…………此时找到了最高EST index = " , $new_node_list[$new_target_index]->m_index , " 隶属于 DAG = " , $new_node_list[$new_target_index]->m_dag->m_index , "\n";
        //old的尾节点并入target上面 
        //创建一条边
        $new_edge = new Edge();
        $new_edge->m_cost = 0;
        $new_edge->m_pre_node = $old_dag->m_exit_node;
        $new_edge->m_succ_node = $new_node_list[$new_target_index];
        //设置关系
        array_push($new_node_list[$new_target_index]->m_pre_node_arr , $old_dag->m_exit_node);
        array_push($new_node_list[$new_target_index]->m_pre_edge_arr , $new_edge);

        array_push($old_dag->m_exit_node->m_succ_node_arr , $new_node_list[$new_target_index]);
        array_push($old_dag->m_exit_node->m_succ_edge_arr , $new_edge);
        
        //old加入到new的头结点中去
        for($i = 0;$i < count($old_dag_list);$i++) {
            if($old_dag_list[$i]->m_active == true) {
                //创建一条边
                $new_edge = new Edge();
                $new_edge->m_cost = 0;
                $new_edge->m_pre_node = $new_dag->m_entry_node;
                $new_edge->m_succ_node = $old_dag_list[$i];
                //设置关系
                array_push($old_dag_list[$i]->m_pre_node_arr , $new_dag->m_entry_node);
                array_push($old_dag_list[$i]->m_pre_edge_arr , $new_edge);

                array_push($new_dag->m_entry_node->m_succ_node_arr , $old_dag_list[$i]);
                array_push($new_dag->m_entry_node->m_succ_edge_arr , $new_edge);
                
                array_push($new_dag->m_node_dic, $old_dag_list[$i]);
            }
        }
        echo "合并完毕\n";
        //echo "+++++++++++合并后  entrynode 为 " , $new_dag->m_entry_node->m_index , " 隶属于 DAG = " , $new_dag->m_entry_node->m_dag->m_index , "\n";;
        return $new_dag;
    }
}

function sortTime($time_1 , $time_2) {
    return $time_1 > $time_2?1:-1;
}

function sortByDownwardValue($node_1 , $node_2) {
    return $node_1->m_down_ward_value > $node_2->m_down_ward_value?-1:1;
}