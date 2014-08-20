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

//创造dag
$dag_1 = DataManager::getInstance()->createOneDagWithParam(10,TOP_WIDE , 5);
$dag_1->m_reach_time = 0;
$dag_1->m_index = 0;
$dag_2 = DataManager::getInstance()->createOneDagWithParam(10,TOP_WIDE , 5);
$dag_2->m_reach_time = 40;
$dag_2->m_index = 1;
/*$dag_3 = DataManager::getInstance()->createOneDagWithParam(10,TOP_WIDE , 5);
$dag_4 = DataManager::getInstance()->createOneDagWithParam(10,TOP_WIDE , 5);
$dag_5 = DataManager::getInstance()->createOneDagWithParam(10,TOP_WIDE , 5);*/
//设定机器数目
MachineManager::getInstance()->createMachineWithCount(5);
$dag_arr = array("0"=>$dag_1 , "1"=>$dag_2 );
        //, "2"=>$dag_3 , "3"=>$dag_4 , "4"=>$dag_5);
FIFOTool::runDAGArray($dag_arr, MachineManager::getInstance()->m_machine_arr);

printf("DAG  %d 的执行时间是： %d\n" , $dag_1->m_index , ($dag_1->m_exit_node->m_finish_time - $dag_1->m_reach_time));
printf("DAG  %d 的执行时间是： %d\n" , $dag_2->m_index , ($dag_2->m_exit_node->m_finish_time - $dag_2->m_reach_time));



//正常的
MachineManager::getInstance()->resetAllMachines();
$dag_1->resetAllNodes();
$node_arr = AlgoTool::signPriorityByHEFT_Upward($dag_1);
AlgoTool::distributeNodesOnMachine($node_arr, MachineManager::getInstance()->m_machine_arr, 0);
printf("DAG  %d 的执行时间是： %d\n" , $dag_1->m_index , ($dag_1->m_exit_node->m_finish_time - $dag_1->m_reach_time));

MachineManager::getInstance()->resetAllMachines();
$dag_2->resetAllNodes();
$node_arr = AlgoTool::signPriorityByHEFT_Upward($dag_2);
AlgoTool::distributeNodesOnMachine($node_arr, MachineManager::getInstance()->m_machine_arr, 0);
printf("DAG  %d 的执行时间是： %d\n" , $dag_2->m_index , ($dag_2->m_exit_node->m_finish_time - $dag_2->m_reach_time));

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


