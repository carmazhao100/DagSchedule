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
require_once 'MultiDAG/SingleJoinTool.php';
require_once 'MultiDAG/RoundRobinTool.php';
require_once 'MultiDAGDynamic/FIFOTool.php';
require_once 'MultiDAGDynamic/MyAlgoTool.php';
require_once 'MultiDAGDynamic/EFairnessTool.php';

$time_inter_base = 20;
function showDagResult($dag_arr) {
    $sum = 0;
    for($i = 0;$i < count($dag_arr);$i++) {
        printf("====DAG  %d 的执行时间是： %d\n" , $i , $dag_arr[$i]->m_exit_node->m_finish_time);
        $sum += ($dag_arr[$i]->m_exit_node->m_finish_time - $dag_arr[$i]->m_reach_time);
    }
    echo "平均值 : ",$sum/count($dag_arr) , "\n";
}
//创造dag
$dag_arr = array();
for($i = 0;$i < 7;$i++) {
    $dag = DataManager::getInstance()->createOneDagWithParam(8,MIDDLE_WIDE , MACHINE_NUMBER);
    $dag->m_index = $i;
    $dag_arr[$i] = $dag;
    $dag->m_reach_time = $i * $time_inter_base;
    //$dag->showMe();
    echo "一共有 " , count($dag->m_node_dic) , "\n";
}

//设定机器数目
MachineManager::getInstance()->createMachineWithCount(MACHINE_NUMBER);

//==========EFAIRNESS=============
EFairnessTool::runDAGArray($dag_arr, MachineManager::getInstance()->m_machine_arr);
echo "EFairness 的结果\n";
showDagResult($dag_arr);
//MachineManager::getInstance()->showMachineEnvironment();

//===========FIFO==================
MachineManager::getInstance()->resetAllMachines();
for($i = 0;$i < count($dag_arr);$i++) {
    $dag_arr[$i]->resetAllNodes();
    $dag_arr[$i]->m_reach_time = $i * $time_inter_base;
}
FIFOTool::runDAGArray($dag_arr, MachineManager::getInstance()->m_machine_arr);
echo "FIFO 的结果\n";
showDagResult($dag_arr);

//===========MYALGO==================
MachineManager::getInstance()->resetAllMachines();
for($i = 0;$i < count($dag_arr);$i++) {
    $dag_arr[$i]->resetAllNodes();
    $dag_arr[$i]->m_reach_time = $i * $time_inter_base;
}
MyAlgoTool::runDAGArray($dag_arr, MachineManager::getInstance()->m_machine_arr);
echo "My 的结果\n";
showDagResult($dag_arr);

//正常的
echo "正常的结果\n";

for($i = 0;$i < count($dag_arr);$i++) {
    MachineManager::getInstance()->resetAllMachines();
    $dag_arr[$i]->resetAllNodes();
    $dag_arr[$i]->m_reach_time = $i * $time_inter_base;
    $node_arr = AlgoTool::signPriorityByHEFT_Upward($dag_arr[$i]);
    AlgoTool::distributeNodesOnMachine($node_arr, MachineManager::getInstance()->m_machine_arr, $dag_arr[$i]->m_reach_time);
    //echo "===finish time is ",$dag_arr[$i]->m_exit_node->m_finish_time,"reach time is ",$dag_arr[$i]->m_reach_time , "\n";
    printf("====DAG  %d 的执行时间是： %d\n" , $i , ($dag_arr[$i]->m_exit_node->m_finish_time - $dag_arr[$i]->m_reach_time));
}

//MachineManager::getInstance()->showMachineEnvironment();
//RoundRobinTool::runDAGArray($dag_arr, MachineManager::getInstance()->m_machine_arr);
//printf("测试   %d" , $dag_2->m_node_dic[18]->m_start_time);
//$dag = SingleJoinTool::joinDAGs_SimpleJoin($dag_arr , 5);
//标定优先级
//$node_arr = AlgoTool::signPriorityByHEFT_Upward($dag);
//运行分配机器
//AlgoTool::distributeNodesOnMachine($node_arr, MachineManager::getInstance()->m_machine_arr , 0);


/*for($i = 0;$i < count($node_arr);$i++) {
    $node = $node_arr[$i];
    printf("排序node %d  value = %d\n" , $node->m_index , $node->m_up_ward_value);
}
*/
//$dag->showMe();


