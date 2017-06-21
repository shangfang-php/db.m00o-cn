<?php
##公用函数文件
function write_log($file, $data){
	
    //global $truename;

	$config_path = dirname(__FILE__).'/log';
	list($filepath, $filename) = explode ( '/', $file );
	$dirPath = $config_path.'/'.$filepath;
	if (!is_dir($dirPath)){
    	mkdir( $dirPath, 0777,TRUE);
    }
    $readpath = $dirPath.'/'.$filename;
	if (!$handle=fopen($readpath, 'a')) {
         return false;
    }
    if(flock($handle, LOCK_EX)) { 
	    if (fwrite($handle, date('Y-m-d H:i:s').'===='.$data."\r\n") === FALSE) {
	        return false;
	    }
	    flock($handle, LOCK_UN);
    }
    fclose($handle);
    return true;
}

function array2sql($array){
	$sql_array = array();
	foreach ($array AS $_k=>$_v){
	   if (empty($_k)){
	       continue;
       }
       $_v = trim($_v);
       if(function_exists('get_magic_quotes_gpc') && !get_magic_quotes_gpc()){
	      $_v = addslashes($_v);
	   }
       $sql_array[] = "`{$_k}`='{$_v}'";
	}
	return implode(',', $sql_array);
}

/**
 * array2select()
 * 将数组或字符串转化为select语句字段
 * @param int $array string $array
 * @return void
 */
function array2select($array){
    $select =   '';
    if(is_array($array)){
        foreach($array as $val){
            $select .=   $val.',';
        }
        $select =  trim($select, ',');
    }else{
        $select .=  $array;
    }
    return $select;
}

/**
 * array2where()
 * 将数组转化为where语句字段
 * @param int $array
 * @return void
 */
function array2where($array){
    $where =   '';
    if(is_numeric($array)){
		$where  .=  "id = $array";
	}else if(!is_array($array)){ //不是数组直接返回
        $where  .=  $array;
    }else{
        foreach($array as $key=>$val){
            if(stripos($key, ' in') !== FALSE){ //存在in 或 like语句
                if(!is_array($val)){ //值不是数组，则不匹配该条件
                    $where  .=  " {$key} ('$val') and";
                    continue;
                }
                $keys   =   explode(' ', $key);
                $key    =   array_shift($keys); //where字段
                $char   =   implode(' ', $keys); //where条件 适用于not in
                /*$value  =   '';
                foreach($val as $v){
                    $value  .=  "'$v',"; 
                }*/
				$value  = array();
				foreach($val as $v){
                    $value[] =  $v; 
                }
				$value  =   "('".join("','", $value)."')";
                //$value  =   '('.trim($value, ',').')';
                $where  .=  " {$key} {$char} $value and";
            }else if(preg_match("/[!><]/", $key)){
                $keys   =   explode(' ', $key);
                $key    =   $keys[0];
                $char   =   $keys[1];
                $where  .=  " {$key} {$char} '{$val}' and";
            }else if(in_array( $key, array('limit', 'order by', 'group by') ) ){
                $where  =   rtrim($where, 'and');
                $where  .=  " $key $val ";
            }else if(stripos($key, 'like') !== FALSE){
                $where  .=  " {$key} '$val' and";
            }else if(stripos($key, 'between') !== FALSE){
                $start  =   $val[0];
                $end    =   $val[1];
                $where  .=  "$key '{$start}' and '{$end}' and"; 
            }else{
                $where .=   " $key = '{$val}' and"; 
            }
        }
        $where  =   rtrim($where, 'and');
    }
    return $where;
}

/**
 * getOrderInfo()
 * 获取订单信息
 * @param mixed $ordernum
 * @param string $table
 * @return
 */
function getOrderInfo($ordernum, $table = ''){
    global $conn;
    if(!$table){
        $date   =   date('Y-m');
        list($year, $month) =   explode('-', $date);
        $table = 'order_'.$year.'_'.$month.'_tb';
    }
    
    $sql    =   'select * from '.$table.' where o_ordernum = "'.$ordernum.'"';
    $sql    =   mysql_query($sql,$conn);
    $data   =   mysql_fetch_assoc($sql);
    return $data;
}

/**
 * returnApiRes()
 * 生成接口返回数据
 * @return void
 */
function returnApiRes(){
    global $code, $codeMsg;
    $return =   array('code'=>$code, 'res'=>$codeMsg);
    echo json_encode($return);
    exit;
}

/**
 * insertData()
 * 插入数据
 * @param mixed $table
 * @param mixed $data
 * @return void
 */
function insertData($table, $data){
    global $conn;
    $sql    =   'insert into '.$table.' set '.array2sql($data);
    //echo $sql."<br />";
    $info   =   mysql_query($sql, $conn);
    return $info;
}

function updateData($table, $update, $where){
    global $conn;
    $sql    =   'update '.$table.' set '.array2sql($update).' where '.array2where($where);
    $info   =   mysql_query($sql, $conn);
    return $info;
}

function select_array($table, $field = '', $where = ''){
    global $conn;
    if(!$table){
        return false;
    }
    $field      =   $field ? array2select($field) : '*';
    $where      =   $where ? 'where '.array2where($where) : '';
    $searchSql  =   "select $field from $table $where";
    $searchSql  =   mysql_query($searchSql, $conn);
    $res        =   mysql_fetch_assoc($searchSql);
    return $res;
}

/**
 * assignOrderMoney()
 * 分配订单佣金
 * @param mixed $ordernum 订单编码
 * @param mixed $orderInfo 订单信息
 * @return void
 */
function assignOrderMoney($ordernum, $orderInfo){
    global $conn, $code, $codeMsg;
    $totalMoney =   $orderInfo['o_zzyj']; ##订单最终佣金
    $o_u_id     =   $orderInfo['o_u_id']; ##订单所属用户
    $userMoney  =   assignUserMoney($o_u_id, $totalMoney); ##分配用户佣金
    return $userMoney;
}

/**
 * assignUserMoney()
 * 分配用户佣金
 * @param mixed $o_u_id 所属用户
 * @param mixed $totalMoney 可分配佣金
 * @return void
 */
function assignUserMoney($o_u_id, $totalMoney){
    global $conn;
    $userInfo   =   getUserInfo(intval($o_u_id)); ##获取用户信息
    if(!$userInfo){
        return FALSE;
    }
    //var_dump($totalMoney);
    $userMoney  =   array();
    $u_level    =   $userInfo['u_leve'];
    $u_fcbl     =   $userInfo['u_fcbl']; ##获取比例
    //echo $u_fcbl."<br />";
    $u_parent_id=   $userInfo['u_parent_u_id'];
    $userMoney[]=   array($o_u_id, round($u_fcbl * $totalMoney, 2), $userInfo['u_u_idss']);
    if($u_level != 1){
        $times = $u_level - 1; ##循环次数
        for($i = 1; $i <= $times; $i++){
            $userInfo   =   getUserInfo(intval($u_parent_id)); ##获取上级用户信息
            $u_parent_id=   $userInfo['u_parent_u_id'];
            $percentField   = 'u_fcbl'.( $i + 1 );  
            $u_fcbl     =   $userInfo[$percentField]; ##获取比例
            if($u_fcbl <= 0){
                $u_fcbl = 0;
            }
            $userMoney[]=   array($userInfo['u_id'], round($u_fcbl * $totalMoney, 2), $userInfo['u_u_idss']);
            //echo $u_fcbl."<br />";
        }
    }
    unset($userInfo);
    return $userMoney;
}

/**
 * getUserInfo()
 * 获取用户信息
 * @param mixed $o_u_id
 * @return void
 */
function getUserInfo($o_u_id){
    global $conn;
    $sql    =   'select * from user_tb where u_id = "'.$o_u_id.'"';
    $sql    =   mysql_query($sql,$conn);
    $data   =   mysql_fetch_assoc($sql);
    return $data;
}

/**
 * updateUserMoney()
 * 更新用户金额
 * @param mixed $userMoneyArr 用户金额数组
 * @param mixed $new_o_state 新状态
 * @param mixed $cur_o_state 当前订单状态
 * @return void
 */
function updateUserMoney($userMoneyArr, $new_o_state, $cur_o_state, $orderInfo){
    global $conn, $code, $codeMsg;
    //$isNewFail  =   0;
//    if(!$cur_o_state && $new_o_state == 13){ ##新增失败订单不做金额分配
//        $isNewFail = 1; ##新增失败订单
//    }
    $userSqls = $recordsSqls = array();
    $table1 =   'user_tb';
    $createTime =   $orderInfo['o_creattime'];
    list($year, $month) =   explode('-', date('y-m', $createTime));
    $table2 =   'order_record_'.$year.'_'.$month.'_tb';
    foreach($userMoneyArr as $val){
        $o_uid  =   $val[0];
        $money  =   $val[1]; ##新增失败订单记录金额为0;
        
        if($money > 0){
            $userInfo   =   getUserInfo(intval($o_uid));
            ####更新金额 start####
            if(!$cur_o_state){ ##新增订单更新金额
                if($new_o_state == 12){
                    $sql    =   "update $table1 set u_wqrmoney = u_wqrmoney + $money where u_id = {$o_uid}";
                }else{
                    $sql    =   "update $table1 set u_allmoney = u_allmoney + {$money},u_money = u_money + {$money} where u_id = {$o_uid}";
                }
                $info   =   mysql_query($sql, $conn);
                if(!$info){
                    $code   =   '4001';
                    $codeMsg=   $o_uid.'更新未确认金额失败'.$money;
                    return FALSE;
                }
                write_log('sql/'.date('Y-m-d').'.log', $sql);
                
                if(in_array($new_o_state, array(3,14))){ ##结算状态 保存月份金额记录
                    $info   =   saveUserMonthMoney($o_uid, $money, $orderInfo['o_u_idss'], $createTime);
                    if(!$info){
                        $code   =   5003;
                        $codeMsg=   '保存月度金额失败!';
                        return false;
                    }
                }
            }else{ ##已存在订单更新金额
                $wqrmoney       =   $money > $userInfo['u_wqrmoney'] ? $userInfo['u_wqrmoney'] : $money;
                if($cur_o_state == 12 && ($new_o_state == 3 || $new_o_state == 14)){
                    $sql    =   "update $table1 set u_wqrmoney = u_wqrmoney - {$wqrmoney},u_allmoney = u_allmoney + {$money},u_money = u_money + {$money} where u_id = {$o_uid}";
                    
                    ##结算状态 保存月份金额记录
                    $info   =   saveUserMonthMoney($o_uid, $money, $orderInfo['o_u_idss'], $createTime);
                    if(!$info){
                        $code   =   '5003';
                        $codeMsg=   '保存月度金额失败!';
                        return false;
                    }
                }elseif($cur_o_state == 12 && $new_o_state == 13){
                    $sql    =   "update $table1 set u_wqrmoney = u_wqrmoney - {$wqrmoney} where u_id = {$o_uid}";
                    $money  =   $wqrmoney;
                }
                $info   =   mysql_query($sql, $conn);
                if(!$info){
                    $code   =   '4002';
                    $codeMsg=   $o_uid.'更新余额失败'.$money;
                    return FALSE;
                }
                write_log('sql/'.date('Y-m-d').'.log', $sql);
            }
            ####更新金额 end####
        }
        
        ####添加金额变更记录 start####
        $ordernum   =   $orderInfo['o_ordernum'];
        $where      =   array('or_o_ordernum'=>$ordernum, 'or_u_id'=>$o_uid);
        $record     =   select_array($table2, 'or_id', $where);
        $data       =   array('or_money'=>$money);
        if($record){
            $info   =   updateData($table2, $data, $where);
        }else{
            $data['or_o_creattime'] =   $createTime;
            $data['or_u_idss']      =   $orderInfo['o_u_idss'];
            $data   =   array_merge($data, $where);
            $info   =   insertData($table2, $data);
        }
        
        if(!$info){
            $code   =   '4003';
            $codeMsg=   $record ? '更新余额记录失败' : '保存余额记录失败!';
            return FALSE;
        }
        ####添加金额变更记录 send####
        
    }
    return TRUE;
}

/**
 * saveUserMonthMoney()
 * 保存每月分成总记录表
 * @param mixed $uid 用户id
 * @param mixed $money 添加金额
 * @param mixed $o_uid_ss 淘客ID
 * @param mixed $createTime 订单生成时间
 * @return
 */
function saveUserMonthMoney($uid, $money, $o_uid_ss, $createTime){
    global $conn;
    $table  =   'income_tb';
    $month  =   date('Y-m', $createTime);
    $where  =   array('i_y_m'=>$month, 'i_uid'=>$uid, 'i_idss'=>$o_uid_ss);
    $res    =   select_array($table, 'i_id', $where);
    if($res){
        $sql    =   "update {$table} set i_money= i_money + {$money} where ".array2where($where);
    }else{
        $where['i_money']   =   $money;
        $sql    =   "insert into {$table}  set ".array2sql($where);
    }
    //echo $sql;exit;
    $info   =   mysql_query($sql, $conn);
    return $info;
}
?>