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
            array_push($this->m_machine_arr, $machine);
        }
    }
    
    public function resetAllMachines() {
        for($i = 0;$i < count($this->m_machine_arr);$i++) {
            $this->m_machine_arr[$i]->resetMachine();
        }
    }
}