<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'DataManager.php';
require_once 'AlgoTool.php';
require_once 'Global.php';
require_once 'Machine/MachineManager.php';

//创造dag
$dag = DataManager::getInstance()->createOneDagWithParam(5,TOP_WIDE , 5);
//设定机器数目
MachineManager::getInstance()->createMachineWithCount(5);
//标定优先级
$node_arr = AlgoTool::signPriorityByHEFT_Upward($dag);
//运行分配机器
AlgoTool::distributeNodesOnMachine($node_arr, MachineManager::getInstance()->m_machine_arr);


/*for($i = 0;$i < count($node_arr);$i++) {
    $node = $node_arr[$i];
    printf("排序node %d  value = %d\n" , $node->m_index , $node->m_up_ward_value);
}
*/
$dag->showMe();


