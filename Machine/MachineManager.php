<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Machine.php';

class MachineManager {
    //静态变量
    private  static  $_instance;
    public static  function  getInstance() {
        if(is_null(self::$_instance)) {
            self::$_instance = new MachineManager();
        }

        return self::$_instance;
    }
    public function __construct() {
        $this->m_machine_arr = array();
    }
    
    //机器数组
    public $m_machine_arr;
    
    public function createMachineWithCount($m_number) {
        for($i = 0;$i < $m_number;$i++) {
            $machine = new Machine();
            $machine->m_index = $i;
            array_push($this->m_machine_arr, $machine);
        }
    }
    
    //把每个机器的任务分配情况输出
    public function showMachineEnvironment() {
        //检测代码
        for($s = 0 ; $s < count($this->m_machine_arr);$s++) {
            $machine = $this->m_machine_arr[$s];
            printf("================= 机器  %d 总共有%d个任务\n" , $s , count($machine->m_node_arr));
            /*for($t = 0;$t < count($machine->m_node_arr);$t++) {
                printf("拥有节点： %d\n" , $machine->m_node_arr[$t]->m_index);
            }*/
            
            for($i = 0;$i < count($machine->m_time_seg_arr);$i++) {
                printf("时间片 %d : %d-----%d\n" , $i ,$machine->m_time_seg_arr[$i]->m_start_time , $machine->m_time_seg_arr[$i]->m_finish_time );
            }
        }
    }
    
    public function resetAllMachines() {
        for($i = 0;$i < count($this->m_machine_arr);$i++) {
            $this->m_machine_arr[$i]->resetMachine();
        }
    }
}