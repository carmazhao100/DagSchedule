<?php
/**
 * Created by PhpStorm.
 * User: carmazhao
 * Date: 14-7-19
 * Time: 下午1:03
 */
require_once 'Global.php';
require_once 'DataStructure/Node.php';
require_once 'DataStructure/Edge.php';
require_once 'DataStructure/DAG.php';

class DataManager {
    //静态变量
    private  static  $_instance;

    //数据结构
    private $m_dag_arr;
    //构造
    private  function __construct() {
         $this->m_dag_arr = array();
    }
    
    //防止克隆
    public  function  __clone() {
        trigger_error('Clone is forbbiden' , E_USER_ERROR);
    }

    public static  function  getInstance() {
        if(is_null(self::$_instance)) {
            self::$_instance = new DataManager();
        }

        return self::$_instance;
    }

    /*输入：
    level：一共有多少层（5---15）
    Type：三种形状（上粗，中粗，下粗）
    */
    public  function createOneDagWithParam($level , $type , $mnumber) {
        $new_dag = new DAG();
        //开始节点
        $entry_node = new Node();
        $entry_node->m_index = 0;
        $entry_node->m_down_ward_value = 0;
        $new_dag->m_entry_node = $entry_node;
        $new_dag->addNode(0, $entry_node);
        $this->decorateNodeCostArray($entry_node , $mnumber);
        //用来存储前面所有级别的node
        $old_node_arr = array();
        array_push($old_node_arr , $entry_node);

        //用来存储当前级别的node
        $current_node_arr = array();

        $node_number = 1;//用来记数node

        $current_level_number = 0;//当前level产生多少node
        for($i = 1;$i < $level;$i++) {
            //根据类型判断形状
            switch ($type) {
                case TOP_WIDE:
                    $current_level_number = (($level - $i) > 15?15:($level - $i))*rand(5 , 15);
                    break;
                case MIDDLE_WIDE:
                    $current_level_number = ($i > ($level/2)?($level - $i):$i)*rand(5 , 15);
                    break;
                case BUTTOM_WIDE:
                    $current_level_number = ($i > 15?15:$i)*rand(5 , 15);
                    break;
                default:
                    break;
            }
            
           // printf("当前level： %d  当前number： %d\n" ,$i ,  $current_level_number);
            //清空数组 
            $current_node_arr = array();
            for($j = 0;$j < $current_level_number;$j++) {
                //创建一个新的节点
                $new_node = new Node();
                
                //设置相关参数
                $new_node->m_index = $node_number;
                $node_number++;
                $this->decorateNodeCostArray($new_node , $mnumber);
                $new_dag->addNode($new_node->m_index, $new_node);
                //$new_node->showMe();
                //加入现役节点到当前层array
                array_push($current_node_arr , $new_node);

                //------决定父节点---------
                $pre_node_number = count($old_node_arr);
                $pick_number = rand(1 , $pre_node_number);
                $begin_pos = rand(0 , $pick_number);//在父节点数组中的开始位置,后面会取模

                //printf("供选择的父节点数目： %d\n" , $pick_number);
                //在上一层中
                for($n = 0;$n < $pick_number;$n++) {
                    $index = ($n + $begin_pos)%$pre_node_number;
                    //printf("======选择了父节点 %d\n" , $index);
                    $pre_node = $old_node_arr[$index];
                    //创建一条边
                    $new_edge = new Edge();
                    $new_edge->m_cost = rand(10 , 200);
                    $new_edge->m_pre_node = $pre_node;
                    $new_edge->m_succ_node = $new_node;
                    //设置关系
                    array_push($new_node->m_pre_node_arr , $pre_node);
                    array_push($new_node->m_pre_edge_arr , $new_edge);

                   // array_push($pre_node->getSuccNodes() , $new_node);
                    array_push($pre_node->m_succ_node_arr , $new_node);
                    array_push($pre_node->m_succ_edge_arr , $new_edge);
                    //printf("====此父节点拥有子节点数目： %d\n" , count($pre_node->m_succ_node_arr));
                }
            }
            
            //将当前level加入到总的里面
            $old_node_arr = array_merge($old_node_arr, $current_node_arr);
            
            //如果是最后一行，需要创造尾节点
            if($i == $level - 1) {
                $exit_node = new Node();
                $exit_node->m_index = $node_number;
                $exit_node->m_up_ward_value = 0;
                //把尾节点所有的cost都设置为0
                for($a = 0 ; $a < $mnumber;$a++) {
                     $cost = 0;
                     array_push($exit_node->m_cost_arr, $cost);
                }
                //加入到dag中
                $new_dag->addNode($exit_node->m_index, $exit_node);
                $new_dag->m_exit_node = $exit_node;
                
                printf("^^^^^^^^^^^Exit node is %d\n" , $exit_node->m_index);
                $number = count($current_node_arr);
                for($m = 0;$m < $number;$m++) {
                    $p_node = $current_node_arr[$m];
                    //创建一条边
                    $new_edge = new Edge();
                    $new_edge->m_pre_node = $p_node;
                    $new_edge->m_succ_node = $exit_node;
                    //设置关系
                    array_push($exit_node->m_pre_node_arr , $p_node);
                    array_push($exit_node->m_pre_edge_arr , $new_edge);

                    array_push($p_node->m_succ_node_arr , $exit_node);
                    array_push($p_node->m_succ_edge_arr , $new_edge);
                }
            }
        }
        return $new_dag;
    }    
    
    private function decorateNodeCostArray($node , $m_number) {
        for($i = 0 ; $i < $m_number;$i++) {
            $cost = rand(15 , 200);
            array_push($node->m_cost_arr, $cost);
        }
        $node->countAverageCost();
    }
} 





