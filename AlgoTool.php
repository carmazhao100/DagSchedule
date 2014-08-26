<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor
 */
require_once 'DataStructure/Node.php';
require_once 'DataStructure/Edge.php';
require_once 'DataManager.php';
require_once 'Machine/TimeSegment.php';
require_once 'Global.php';

class AlgoTool {
    //输入DAG 返回HEFT的队列array
    public static function signPriorityByHEFT_Upward($dag) {
        $entry_node = $dag->m_entry_node;
        //标注
        self::countPriorityUpward($entry_node);
        $current_node = $dag->m_exit_node;
        $node_queue = array();
        array_push($node_queue, $current_node);
        while(count($node_queue)) {
            $current_node = array_shift($node_queue);
        }
        
        $value_arr = array_values($dag->m_node_dic);
        usort($value_arr, 'sortByUpwardValue');
        
        return $value_arr;
    }
    //输入DAG 返回CPOP的队列array
    public static function signPriorityByCPOP($dag) {
        $exit_node = $dag->m_exit_node;
        //标注
        self::countPriorityUpward($exit_node);
        self::countPriorityDownward($exit_node);
        $value_arr = array_values($dag->m_node_dic);
        usort($value_arr, 'sortByCPOPValue');
        return $value_arr;
    }


    //递归调用计算upward
    public static function countPriorityUpward($node) {
        if($node->m_up_ward_value >= 0) {
            return $node->m_up_ward_value;
        }
        
        //printf("=======================正在计算 %d\n" , $node->m_index);
        $max_value = 0;
        $succ_edge_arr = $node->m_succ_edge_arr;
        for($i = 0;$i < count($succ_edge_arr);$i++) {
            $succ_edge = $succ_edge_arr[$i];
            $succ_node = $succ_edge->m_succ_node;
            $childe_cost = self::countPriorityUpward($succ_node);
           // printf("xxxxx  孩子 : %d  cost: %d\n" , $succ_node->m_index , $succ_node->m_up_ward_value);
            if($childe_cost + $succ_edge->m_cost > $max_value) {
                $max_value = $childe_cost + $succ_edge->m_cost;
            }
        }
        $node->m_up_ward_value = $max_value + $node->m_cost;
        //printf("现在输出节点 %d 的upward： %d\n" , $node->m_index , $node->m_up_ward_value);
        return $node->m_up_ward_value;
    }
    
    public static function countPriorityDownward($node) {
        if($node->m_down_ward_value >= 0) {
            return $node->m_down_ward_value;
        }
        
        //printf("=======================正在计算 %d\n" , $node->m_index);
        $max_value = 0;
        $pre_edge_arr = $node->m_pre_edge_arr;
        for($i = 0;$i < count($pre_edge_arr);$i++) {
            $pre_edge = $pre_edge_arr[$i];
            $pre_node = $pre_edge->m_succ_node;
            $parent_cost = self::countPriorityDownward($pre_node);
           // printf("xxxxx  孩子 : %d  cost: %d\n" , $succ_node->m_index , $succ_node->m_up_ward_value);
            if($parent_cost + $pre_edge->m_cost > $max_value) {
                $max_value = $parent_cost + $pre_edge->m_cost;
            }
        }
        $node->m_down_ward_value = $max_value + $node->m_cost;
        //printf("现在输出节点 %d 的down： %d\n" , $node->m_index , $node->m_up_ward_value);
        return $node->m_down_ward_value;
    }
    
    //将工作分配到处理机上，并且返回最早的ready时间,单个dag使用
    public static function distributeNodesOnMachine($node_arr , $machine_arr , $dag_reach_time) {
        $ready_time = MAX_NUMBER;
        for($n = 0;$n < count($node_arr);$n++){
            $node = $node_arr[$n];
            $tmp_ready_time = self::distributeSingleNodeOnMachine($node, $machine_arr , $dag_reach_time);
            if($tmp_ready_time < $ready_time) {
                $ready_time = $tmp_ready_time;
            }
        }
       
        //检测代码
        //MachineManager::getInstance()->showMachineEnvironment();
        return $ready_time;
    }
    //node属于不通dag使用
    public static function distributeNodesFromMultiDAGsOnMachine($node_arr , $machine_arr) {
        $ready_time = MAX_NUMBER;
        for($n = 0;$n < count($node_arr);$n++){
            $node = $node_arr[$n];
            $tmp_ready_time = self::distributeSingleNodeOnMachine($node, $machine_arr , $node->m_dag->m_reach_time);
            if($tmp_ready_time < $ready_time) {
                $ready_time = $tmp_ready_time;
            }
        }
       
        //检测代码
        //MachineManager::getInstance()->showMachineEnvironment();
        return $ready_time;
    }
    //把工作分配到机器上，返回本台机器的下次ready时间
    public static function distributeSingleNodeOnMachine($node , $machine_arr , $dag_reach_time) {
        //找到合适的缝隙
        $target_machine_id = 0;
        $target_segment_id = 0;
        $finish_time = MAX_NUMBER;
        for ($i = 0;$i < count($machine_arr);$i++) {
            $machine = $machine_arr[$i];
            //找到父节点们最晚结束时间，不在同一台机器上就加上传输边====================
            $pre_node_finish_time = $dag_reach_time;
            for($e = 0;$e < count($node->m_pre_edge_arr);$e++) {
                $pre_edge = $node->m_pre_edge_arr[$e];
                $f_t = 0;
                $pre_node = $pre_edge->m_pre_node;
                if($pre_node->m_machine_id == $machine->m_index) {
                    $f_t = $pre_node->m_finish_time;
                }else{
                    $f_t = $pre_node->m_finish_time + $pre_edge->m_cost;
                }
                if($f_t > $pre_node_finish_time) {
                    $pre_node_finish_time = $f_t;
                }
            }
                
            //寻找本台机器上的每一个时间片段=======================
            for ($j = 0;$j < count($machine->m_time_seg_arr);$j++) {
                $segment = $machine->m_time_seg_arr[$j];
                 //看本片段内可否装入
                if($segment->m_finish_time > $pre_node_finish_time) {
                    $start_time = $segment->m_start_time > $pre_node_finish_time?$segment->m_start_time:$pre_node_finish_time;
                    $time_slot = $segment->m_finish_time - $start_time;
                        
                   // echo "在机器 " , $machine->m_index , "上执行start = " , $start_time , " slot = " ,$time_slot , "\n"; 
                    //如果在时间缝隙之内 说明可以塞进去
                    if($time_slot < $node->m_cost_arr[$i]) {
                        continue; 
                    }else{
                        //如果找到了更小的 那么久记录下来
                        $tmp_finish_time = $start_time+$node->m_cost_arr[$i];
                        if($tmp_finish_time < $finish_time) {
                            $finish_time = $tmp_finish_time;
                            $target_machine_id = $machine->m_index;
                            $target_segment_id = $j;
                            //在node里进行记录
                            $node->m_start_time = $start_time;
                            $node->m_finish_time = $finish_time;
                        }
                    }
                }
            }
        }
        //找到合适的机器之后就塞进去
        $node->m_machine_id = $target_machine_id;
            //更改机器配置
        $machine = MachineManager::getInstance()->m_machine_arr[$target_machine_id];
        array_push($machine->m_node_arr , $node);
            //对时间片操作
        $target_seg = $machine->m_time_seg_arr[$target_segment_id];
       // echo "当前要删除     " , $target_segment_id , "\n";
        //echo "====数目是", count($machine->m_time_seg_arr) , "\n";
        array_splice($machine->m_time_seg_arr, $target_segment_id, 1);//删除target segment
        //echo "====数目是", count($machine->m_time_seg_arr) , "\n";
            //对时间片拆分,扣掉中间的时间消耗
        $current_place = $target_segment_id;
        if($node->m_start_time > $target_seg->m_start_time) {
            $new_seg = new TimeSegment();
            $new_seg->m_start_time = $target_seg->m_start_time;
            $new_seg->m_finish_time = $node->m_start_time;
            //echo"第一当前 start是 ", $new_seg->m_start_time , " " , $new_seg->m_finish_time , "\n";
            array_splice($machine->m_time_seg_arr, $current_place, 0 , array($new_seg));
            $current_place++;
        }
            
        if($node->m_finish_time < $target_seg->m_finish_time) {
            $new_seg = new TimeSegment();
            $new_seg->m_start_time = $node->m_finish_time;
            $new_seg->m_finish_time = $target_seg->m_finish_time;
            array_splice($machine->m_time_seg_arr, $current_place, 0 , array($new_seg));
           // echo"第二当前 start是 ", $new_seg->m_start_time , " " , $new_seg->m_finish_time , "\n";
        }
        //echo "finish time is " , $node->m_finish_time , "\n";
        return $node->m_finish_time;
    }
}  


function sortByUpwardValue($node_1 , $node_2) {
    return $node_1->m_up_ward_value > $node_2->m_up_ward_value?-1:1;
}
function sortByCPOPValue($node_1 , $node_2) {
    return ($node_1->m_up_ward_value + $node_1->m_down_ward_value) > ($node_2->m_up_ward_value + $node_2->m_down_ward_value)?-1:1;
}