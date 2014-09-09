<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function sortBigFirst($node_1 , $node_2) {
    if($node_1->m_up_ward_value == $node_2->m_up_ward_value) {
        return 0;
    }
    return $node_1->m_up_ward_value > $node_2->m_up_ward_value?-1:1;
}

function sortByFinishTime($node_1 , $node_2) {
    return $node_1->m_finish_time < $node_2->m_finish_time?-1:1;
}
function sortByReachTimeValue($dag_1 , $dag_2) {
    return $dag_1->m_reach_time > $dag_2->m_reach_time?1:-1;
}

function sortSmallFirst($node_1 , $node_2) {
    if($node_1->m_up_ward_value == $node_2->m_up_ward_value) {
        return 0;
    }   
    return $node_1->m_up_ward_value < $node_2->m_up_ward_value?-1:1;
}
