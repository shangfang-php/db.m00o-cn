<?php
##订单
include_once 'Common.php';
if(empty($_POST['tkid'])||empty($_POST['key'])){
    exit(json_encode(array('code'=>"2009","res"=>"请传入必要参数")));
}
$id = trim($_POST['tkid']);
$key = trim($_POST['key']);
/*$id = trim($_GET['tkid']);
$key = trim($_GET['key']);*/
$sql = "select * from safe_tb where s_u_id='".$id."' and s_key='".$key."'";

$res = mysql_query($sql,$conn);
$Safearr = mysql_fetch_assoc($res);
//var_dump($Safearr);exit;
if(!$Safearr){
    $arr = array("code"=>"2012","res"=>"登录状态已失效，请重新登录！");
    exit(json_encode($arr));
}

$act = $_GET['act'];
if($act == 'getorder'){   //------------success----------------//
    $sday = @strtotime(date("Y-m-d",strtotime("last month")));
    $year = @date('Y',time());
    $month = @date('m',time());
    $table1 = 'order_'.$year.'_'.$month.'_tb';
    //$year2 = date('Y',time());
    //$month2 = date('m',time());
    if($month == '01'){
        $years = $year - 1;
        $table2 =  'order_'.$years.'_12_tb';
    }else{
        $m = $month - 1;
        $table2 = 'order_'.$year.'_0'.$m.'_tb';
    }
    //echo $table1;exit;
    $now = time();
    $where = "o_u_idss='".$id."' and o_creattime between '".$sday."' and '".$now."'";
    $sql = "select * from ".$table1." where ".$where." UNION select * from ".$table2." where ".$where." order by o_creattime";
    $result = mysql_query($sql,$conn);
    $order = array();
    $i=0;
    while($row=mysql_fetch_assoc($result)){
        $order[$i] = $row;
        $i++;
    }
    if(!empty($order)){
        $arr = array("code"=>"1002","res"=>"查询成功","data"=>$order);
    }else{
        $arr = array("code"=>"2003","res"=>"数据为空");
    }
}elseif($act == 'getorder2'){   //------------success----------------//
    //$pageindex  = trim($_POST['pageindex']);	// 当前页页码(从0开始0是第一页)
   // $pagesize 	= trim($_POST['pagesize']);		// 每页显示数据的 条数
    // 计算当前页 取 "第一条数据的下标"
    $pageindex  = isset($_POST['pageindex'])?trim($_POST['pageindex']):0;	// 当前页页码(从0开始0是第一页)?干嘛不是1呢？？？？？？？？？？？
    $pagesize 	= isset($_POST['pagesize'])?trim($_POST['pagesize']):100;		// 每页显示数据的 条数
    //print_r($pagesize);
    $onec = ($pageindex) * $pagesize;		//从0开始的
    $sday = @strtotime(date("Y-m-d",strtotime("last month")));
    $year = @date('Y',time());
    $month = @date('m',time());
    $table1 = 'order_'.$year.'_'.$month.'_tb';
    if($month == '01'){
        $years = $year - 1;
        $table2 =  'order_'.$years.'_12_tb';
    }else{
        $m = $month - 1;
        $table2 = 'order_'.$year.'_0'.$m.'_tb';
    }
    $now = time();
    $where = "o_u_idss='".$id."' and o_creattime between '".$sday."' and '".$now."'";
    $totalNum1 = mysql_query("select count(*) as total1 from ".$table1." where ".$where." ") ;
    $res1 = mysql_fetch_assoc($totalNum1);
    $total1 = $res1['total1'];
    $totalNum2 = mysql_query("select count(*) as total2 from ".$table2." where ".$where." ") ;
    $res2 = mysql_fetch_assoc($totalNum2);
    $total2 = $res2['total2'];
    //$totalNum = $totalNum1 + $totalNum2;
    $totalNum = $total1 + $total2;
    $num = floor($totalNum/$pagesize);			// 舍去法取不完全的总页数
    //总页数
    $pagetotal = ($totalNum%$pagesize)==0 ? $num : $num+1;
    $sql = "select * from ".$table1." where ".$where." UNION select * from ".$table2." where ".$where." order by o_creattime  limit ".$onec.",".$pagesize." ";
    $result = mysql_query($sql,$conn);
    $act = array();
    $i=0;
    while($row=mysql_fetch_assoc($result)){
        $act[$i] = $row;
        $i++;
    }
    //echo '<pre>';
    // print_r($order);
    if(!empty($act)){
        $arr = array("code"=>"1002","res"=>"获取成功","pageindex"=>$pageindex,"pagetotal"=>$pagetotal,'data'=>$act);
    }else{
        if($pageindex>0){
            $arr = array("code"=>"2003","res"=>"再怎么加载也没有了");
        }else{
            $arr = array("code"=>"2003","res"=>"数据为空");
        }
    }
    /*}else{

    }*/
}elseif($act == 'addorder'){
    if(empty($_POST['data'])){
        exit(json_encode(array('code'=>"2009","res"=>"请传入必要参数")));
    }
    write_log('assignRecords/'.date('Y-m-d').'.log', json_encode($_POST));
    //$js = htmlspecialchars_decode(trim(I('post.data')));
    $js = urldecode(trim($_POST['data']));
    $js = str_replace('\"','"',$js);
    $js = str_replace(array('\r', '\n', '\t'),'',$js);
    $js = str_replace('\\','',$js);
   
    $data = json_decode($js,true);
    //echo '<pre>';
    if(!$data){
        $code   =   5001;
        $codeMsg=   '订单无法json解析';
        returnApiRes();
    }

    foreach($data as $k => $v){
        if($v['o_creattime']){
            //$year = @date("Y",$v['o_creattime']);
//            $month = @date("m",$v['o_creattime']);

            $date   =   date('Y-m', $v['o_creattime']);
            list($year, $month) =   explode('-', $date);

            $tb = 'order_'.$year.'_'.$month.'_tb'; ##根据订单生成时间查询数据表
            
            $ordernum   =   $v['o_ordernum'];
            $orderInfo  =   getOrderInfo($ordernum, $tb); ##获取订单信息
            
            $new_o_state=   $v['o_state']; ##订单新状态
            $cur_o_state=   0; ##当前订单状态，无订单数据为0
            
            mysql_start(); ##开启事物
            if($orderInfo){ ##订单存在
                $cur_o_state=   $orderInfo['o_state']; ##订单当前状态
                if(in_array($cur_o_state, array(3, 13, 14))){
                    $code   =   '1002';
                    $codeMsg=   '订单已是完结状态'.$cur_o_state;
                    returnApiRes();
                }
                
                if($cur_o_state == $new_o_state){
                    $code   =   '1002';
                    $codeMsg=   '已存在该订单且状态一致';
                    returnApiRes();
                }
                
                $update =   array('o_state'=>$new_o_state, 'o_u_id'=>$v['o_u_id'], 'o_u_username'=>$v['o_u_username'], 'o_u_idss'=>$v['o_u_idss']);
                $update['o_endtime'] = $v['o_endtime'] ? intval($v['o_endtime']) : 0;
                
                $info   =   updateData($tb, $update, array('o_ordernum'=>$ordernum));
                if(!$info){
                    mysql_rollback();
                    $code   =   '3002';
                    $codeMsg=   '同步订单状态失败!';
                    returnApiRes();
                }
                
            }else{
                ##过滤订单不保存字段
                $fields     =   array('o_ordernum','o_creattime','o_goodsinformation','o_goodsid','o_goodsimg','o_sellernic','o_shopnic','o_number','o_state','o_price','o_zzfc','o_payprice','o_zzyj','o_endtime','o_mediaid','o_adid','o_t_id','o_u_id','o_t_nicname','o_u_username','o_u_idss');
                foreach($v as $k => $vv){
                    if(!in_array($k, $fields)){
                        unset($v[$k]);
                    }
                }
                if(!$v['o_endtime']){
                    unset($v['o_endtime']);
                }
                
                $info   =   insertData($tb, $v); ##插入订单信息数据
               
                if(!$info){
                    mysql_rollback();
                    $code   =   '3001';
                    $codeMsg=   '同步订单失败!';
                    returnApiRes();
                }
            }
            
            //if( !(!$cur_o_state && $new_o_state == 13)  ){ ##非新增失败订单则分配用户金额
                ($cur_o_state == 12 && $new_o_state == 13) && ($v['o_zzyj'] = $orderInfo['o_zzyj']);
                $userMoneyArr   =   assignOrderMoney($ordernum, $v); ##分配订单佣金

                write_log('assignRecords/'.date('Y-m-d').'.log', '分配金额:'.$ordernum.':'.json_encode($userMoneyArr));
                //write_log('assignRecords/'.date('Y-m-d').'.log', json_encode($v));
                
                $info           =   updateUserMoney($userMoneyArr, $new_o_state, $cur_o_state, $v);
                if(!$info){
                    mysql_rollback();
                    $code   =   '3003';
                    $codeMsg=   '更新用户金额失败!';
                    returnApiRes();
                }
            //}
            mysql_commit();
            $code   =   '1002';
            $codeMsg=   '同步订单成功!';
            returnApiRes();
        }else{
            //$arr = array("code"=>"2009","res"=>"时间必填");
            $code   =   '2009';
            $codeMsg=   '时间必填';
            returnApiRes();
        }
    }
    
}elseif($act == 'saveorder'){
    $oid = isset($_POST['oid'])?trim($_POST['oid']):'';
    $ordernum = isset($_POST['ordernum'])?trim($_POST['ordernum']):'';
    if(empty($_POST['oid'])&&empty($_POST['ordernum'])){
        exit(json_encode(array('code'=>"2009","res"=>"请传入参数")));
    }
    $js = htmlspecialchars_decode(trim($_POST['data']));
    $data = json_decode($js,true);
    if(!$data){
        $arr = array("code"=>"2008","res"=>"修改失败");
        exit(json_encode($arr));
    }
    $time = trim($_POST['time']);
    if(!$time){
        $arr = array("code"=>"2009","res"=>"时间是必填");
        exit(json_encode($arr));
    }
    $year = @date("Y",$time);
    $month = @date("m",$time);
    $tb = 'order_'.$year.'_'.$month.'_tb';
    //$datt = $data; $datt未使用**************
    /*$where['o_u_idss'] = $id;
    if($oid){
        $where['o_id'] =  $oid;
        $datt['o_id'] =  $oid;
    }
    if($ordernum){
        $where['o_ordernum'] =  $ordernum;
        $datt['o_ordernum'] =  $ordernum;
    }*/
    //$where['o_creattime'] = $time;
   // $save = M($tb)->where($where)->save($data);
    if($oid){
       $where  = "o_id = ".$oid." ";
    }elseif($ordernum){
        $where  = "o_ordernum = ".$ordernum." ";
    }else{
        $arr = array("code"=>"2009","res"=>"参数错误");
        exit(json_encode($arr));
    }
    //var_dump($where);exit;
    //o_u_idss不可改 create_time未修改
    $sql = "update ".$tb." set o_goodsinformation='".$data['o_goodsinformation']."',o_goodsid='".$data['o_goodsid']."',o_goodsimg='".$data['o_goodsimg']."',	o_sellernic='".$data['o_sellernic']."',o_shopnic='".$data['o_shopnic']."',o_number='".$data['o_number']."',o_state='".$data['o_state']."',o_price='".$data['o_price']."',o_zzfc='".$data['o_zzfc']."',o_payprice='".$data['o_payprice']."',o_zzyj='".$data['o_zzyj']."',o_endtime='".$data['o_endtime']."',o_mediaid='".$data['o_mediaid']."',o_adid='".$data['o_adid']."',o_t_id='".$data['o_t_id']."',o_u_id='".$data['o_u_id']."',o_t_nicname='".$data['o_t_nicname']."',o_u_username='".$data['o_u_username']."',o_operatingstate='".$data['o_operatingstate']."' where ".$where." ";
    $res = mysql_query($sql,$conn);
    $save = mysql_affected_rows();
   // var_dump($res);echo '<pre>';
    if($save){
        $arr = array("code"=>"1002","res"=>"修改成功");
    }else{
        $arr = array("code"=>"2008","res"=>"修改失败");
    }
}elseif($act == 'saveorder2'){
    //暂时没有使用********************操作同上 saveorder************************************************
}elseif($act == 'record_fb'){  //------------success----------------//
    $uid = trim($_POST['uid']);
    $num = trim($_POST['num']);
    $money = trim($_POST['money']);
    $time = trim($_POST['creattime']);
    $type = trim($_POST['type']);//0未确认
    if($uid){
        //查找当前UID的用户信息
        $user = mysql_fetch_assoc(mysql_query("select * from user_tb where u_id='".$uid."'",$conn));
        if(empty($user)||$user['u_u_idss'] != $id){
            $arr = array("code"=>"2011","res"=>"此用户不是你的用户");
        }else{
            if(!$num){
                $arr = array("code"=>"2010","res"=>"请填写订单号");
                //ajaxReturn2($arr,'JSON');
                exit(json_encode($arr));
            }
            if(!$time){
                $arr = array("code"=>"2010","res"=>"请填写订单创建时间");
                exit(json_encode($arr));
            }
            $where = " or_u_id=".$uid." and or_o_ordernum=".$num." ";
            $or = mysql_fetch_assoc(mysql_query("select * from order_record_tb where ".$where." ",$conn));
            if($or){
                $res = mysql_query("update order_record_tb set or_money=".$money." where ".$where." ",$conn);
                $save = mysql_affected_rows();
                if($save){
                    $arr = array("code"=>"1002","res"=>"存在相同用户对应的订单，已修改");
                    if($type == 1){
                        $m = @date('Y-m',time());
                        $where2 = " i_idss=".$id." and i_uid=".$uid." and i_y_m='".$m."' ";

                        $f = mysql_fetch_assoc(mysql_query("select * from income_tb where ".$where2." ",$conn));
                        if(empty($f)){
                            mysql_query("insert into income_tb (i_idss,i_uid,i_y_m,i_money)values('".$id."','".$uid."','".$m."','".$money."')",$conn);
                        }else{
                            $money_new = $f['i_money']+$money;
                            mysql_query("update income_tb set i_money=".$money_new." where ".$where2." ",$conn);
                        }
                    }
                }else{
                    $arr = array("code"=>"2008","res"=>"存在相同用户对应的订单，修改失败，可能是没有做任何修改！");
                }
            }else{
                $add = mysql_query("insert into order_record_tb (or_u_id,or_o_ordernum,or_money,or_u_idss,or_o_creattime)values('".$uid."','".$num."','".$money."','".$id."','".$time."')",$conn);
                if($add){
                    if($type == 1){
                        $m = @date('Y-m',time());
                        $where2 = " i_idss=".$id." and i_uid=".$uid." and i_y_m='".$m."' ";
                        $f = mysql_fetch_assoc(mysql_query("select * from income_tb where ".$where2." ",$conn));
                        //var_dump($f);exit;
                        if(empty($f)){
                            mysql_query("insert into income_tb (i_idss,i_uid,i_y_m,i_money)values('".$id."','".$uid."','".$m."','".$money."')",$conn);
                        }else{
                            $money_new = $f['i_money']+$money;
                            mysql_query("update income_tb set i_money=".$money_new." where ".$where2." ",$conn);
                        }
                    }
                    $arr = array("code"=>"1002","res"=>"添加成功");
                }else{
                    $arr = array("code"=>"2008","res"=>"添加失败");
                }
            }
        }
    }else{
        $arr = array("code"=>"2010","res"=>"请填写用户ID");
    }
}elseif($act == 'record'){ //------------success----------------//
    $uid = trim($_POST['uid']);
    $num = trim($_POST['num']);
    $money = trim($_POST['money']);
    $time = trim($_POST['creattime']);
    $type = trim($_POST['type']);//0未确认
    //$ortb = ortb($time);
    $y = @date('y',$time);
    $m = @date('m',$time);
    $mm = $m + 0;
    if($y <= 17 && $m < 4){
        $ortb = 'order_record_tb';
    }else{
        $ortb = 'order_record_'.$y.'_'.$m.'_tb';
    }
    if(!$uid){
        exit(json_encode(array("code"=>"2010","res"=>"请填写用户ID")));
    }
    if(!$num){
        exit(json_encode(array("code"=>"2010","res"=>"请填写订单号")));
    }
    if(!$time){
        exit(json_encode(array("code"=>"2010","res"=>"请填写订单创建时间")));
    }
    $user = mysql_fetch_assoc(mysql_query("select * from user_tb where u_id='".$uid."'",$conn));
    if(empty($user)||$user['u_u_idss'] != $id){
        exit(json_encode(array("code"=>"2011","res"=>"此用户不是你的用户")));
    }
    $where = "or_u_id=".$uid." and or_o_ordernum=".$num." ";
    $mo = @date('Y-m',time());
    $where2= "i_idss=".$id." and i_uid=".$uid." and i_y_m='".$mo."' ";
    $or = mysql_fetch_assoc(mysql_query("select * from ".$ortb." where ".$where." ",$conn));
    if($or){
        $res = mysql_query("update ".$ortb." set or_money='".$money."' where ".$where."",$conn);
        $save = mysql_affected_rows();
        if($save){
            $arr = array("code"=>"1002","res"=>"存在相同用户对应的订单，已修改");
            if($type == 1){
                 $f = mysql_fetch_assoc(mysql_query("select * from income_tb where ".$where2." ",$conn));
                if(!$f){
                    mysql_query("insert into income_tb (i_idss,i_uid,i_y_m,i_money)values(".$id.",".$uid.",".$m.",".$money.")",$conn);
                }else{
                    $money_new = $f['i_money']+$money;
                    mysql_query("update income_tb set i_money=".$money_new." where ".$where2." ",$conn);
                }
            }
        }else{
            $arr = array("code"=>"2008","res"=>"存在相同用户对应的订单，修改失败，可能是没有做任何修改！");
        }
    }else{
        $add = mysql_query("insert into ".$ortb." (or_u_id,or_o_ordernum,or_money,or_u_idss,or_o_creattime)values(".$uid.",".$num.",".$money.",".$id.",".$time.")",$conn);
        if($add){
            $arr = array("code"=>"1002","res"=>"添加成功");
            if($type == 1){
               $f = mysql_fetch_assoc(mysql_query("select * from income_tb where ".$where2." ",$conn));
                if(empty($f)){
                    mysql_query("insert into income_tb (i_idss,i_uid,i_y_m,i_money)values(".$id.",".$uid.",".$m.",".$money.")",$conn);
                }else{
                    $money_new = $f['i_money']+$money;
                    mysql_query("update income_tb set i_money=".$money_new." where ".$where2." ",$conn);
                }
            }
        }else{
            $arr = array("code"=>"2008","res"=>"添加失败");
        }
    }
}else{
    $arr = array("code"=>"2009","res"=>"参数错误");
}
exit(json_encode($arr));