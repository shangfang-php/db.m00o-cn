<?php

include 'database.php';
if(empty($_POST['tkid'])||empty($_POST['key'])){
    exit(json_encode(array('code'=>"2009","res"=>"请传入参数")));
}
$id = trim($_POST['tkid']);
$key = trim($_POST['key']);
/*$id = trim($_GET['tkid']);
$key = trim($_GET['key']);*/
$sql = "select * from safe_tb where s_u_id='".$id."' and s_key='".$key."'";
$res = mysql_query($sql,$conn);

$Safearr = mysql_fetch_assoc($res);
if(!$Safearr){
    $arr = array("code"=>"2012","res"=>"登录状态已失效，请重新登录！");
    exit(json_encode($arr));
}
//接收参数
$act = $_GET['act'];
if($act == 'userlist'){  //------------success----------------//
    $sql1 = "select t_id,u_fcbl,u_fcbl2,u_fcbl3,u_id,u_username,u_parent_u_id,u_leve,t_nicname,t_mm1,t_mm2,t_mm3 ,u_allmoney,u_money from user_tb left join tgw_tb on user_tb.u_id = tgw_tb.t_u_id where user_tb.u_state=1 and user_tb.u_u_idss='".$id."'";
   // print($sql1);exit;
    $result = mysql_query($sql1,$conn);
    $user = array();
    $i = 0;
    //if(mysql_fetch_assoc($result)){
    while($row = mysql_fetch_assoc($result)){
        $user[$i] = $row;
        $i++;
    }
    if(!empty($user)){
        mysql_query("update safe_tb set s_ret=0 where s_u_id='".$id."'",$conn);
        $arr = array("code"=>"1002","res"=>"查询成功！！！","data"=>$user);
    }else{
        $arr = array("code"=>"2001","res"=>"没有代理");
    }
}elseif($act == 'savemoney'){   //------------success----------------//
    if(empty($_POST['uid'])||empty($_POST['money'])){
        exit(json_encode(array('code'=>"2009","res"=>"请传入参数")));
    }
    $uid = trim($_POST['uid']);
    $money = trim($_POST['money']);
    //查找当前UID的用户信息
    $user = mysql_fetch_assoc(mysql_query("select * from user_tb where u_id='".$uid."'",$conn));
    if(empty($user)||$user['u_u_idss'] != $id){
        $arr = array("code"=>"2011","res"=>"此用户不是你的用户");
    }else{
        if(!$money){
            $money = 0;
        }
        $m = $user['u_money'] + $money;
        $mm = $user['u_allmoney'] + $money;
        if($m < 0){
            $m = 0;
        }
        if($money > 0){

            $res = mysql_query("update user_tb set u_allmoney=".$mm." , u_money=".$m." where u_id=".$uid." ",$conn);
        }else{
            $res = mysql_query("update user_tb set u_money='".$m."' where u_id='".$uid."'",$conn);
        }
        $save = mysql_affected_rows();
        if($save){
            $arr = array("code"=>"1002","res"=>"修改成功");
        }else{
            $arr = array("code"=>"2001","res"=>"修改失败！！！");
        }
    }
}elseif($act == 'savewqrmoney'){  //------------success----------------//
    if(empty($_POST['uid'])||empty($_POST['wqrmoney'])){
        exit(json_encode(array('code'=>"2009","res"=>"请传入参数")));
    }
    $uid = trim($_POST['uid']);
    $money = trim($_POST['wqrmoney']);
    //查找当前UID的用户信息
    $user = mysql_fetch_assoc(mysql_query("select * from user_tb where u_id='".$uid."'",$conn));
    if(empty($user)||$user['u_u_idss'] != $id){
        $arr = array("code"=>"2011","res"=>"此用户不是你的用户");
    }else{
        if(!$money){
            $money = 0;
        }
        $m = $user['u_wqrmoney'] + $money;
        if($m < 0){
            $m = 0;
        }
        $res = mysql_query("update user_tb set u_wqrmoney='".$m."' where u_id='".$uid."'",$conn);
        $save = mysql_affected_rows();
       // $save = M('user_tb')->where(array('u_id'=>$uid))->save(array('u_wqrmoney'=>$m));
        //echo $save;exit;
        if($save){
            $arr = array("code"=>"1002","res"=>"修改成功");
        }else{
            $arr = array("code"=>"2001","res"=>"修改失败！！！");
        }
    }
}elseif($act == 'saveclick'){   //------------success----------------//
    if(empty($_POST['uid'])||empty($_POST['click'])){
        exit(json_encode(array('code'=>"2009","res"=>"请传入参数")));
    }
    $uid = trim($_POST['uid']);
    $click = trim($_POST['click']);

    //判断当前传入的click是否是整形
    $pos = strpos($click,'.');
    $param = substr($click,$pos+1);
    //var_dump($param);exit;
    //查找当前UID的用户信息
    $user = mysql_fetch_assoc(mysql_query("select * from user_tb where u_id='".$uid."'",$conn));
    if(empty($user)||$user['u_u_idss'] != $id){
        $arr = array("code"=>"2011","res"=>"此用户不是你的用户");
    }else{
        if($click < 0||$param>0){
            $arr = array("code"=>"2009","res"=>"点击数不能小于0且必须为整数");
        }else{
            $res = mysql_query("update user_tb set u_click='".$click."' where u_id='".$uid."'",$conn);
            $save = mysql_affected_rows();
            if($save){
                $arr = array("code"=>"1002","res"=>"修改成功");
            }else{
                $arr = array("code"=>"2001","res"=>"修改失败！！！");
            }
        }
    }
}elseif($act == 'safe'){  //------------success----------------//
    if($Safearr){
        $arr = array("code"=>"1002","res"=>"查询成功","ret"=>$Safearr['s_ret']);
    }else{
        $arr = array("code"=>"2001","res"=>"未知错误！！！");
    }
}elseif($act == 'savefcbl'){   //------------success----------------//
    if(empty($_POST['uid'])||empty($_POST['fcbl'])){
        exit(json_encode(array('code'=>"2009","res"=>"请传入参数")));
    }
    $uid = trim($_POST['uid']);
    $fcbl = trim($_POST['fcbl']);
    //查找当前UID的用户信息
    $user = mysql_fetch_assoc(mysql_query("select * from user_tb where u_id='".$uid."'",$conn));
    //print_r($user);exit;
    if(empty($user)||$user['u_u_idss'] != $id){
        $arr = array("code"=>"2011","res"=>"此用户不是你的用户");
    }else{
        $res = mysql_query("update user_tb set u_fcbl='".$fcbl."' where u_id='".$uid."'",$conn);
        $save = mysql_affected_rows();
        if($save){
            $arr = array("code"=>"1002","res"=>"修改成功");
        }else{
            $arr = array("code"=>"2001","res"=>"修改失败！！！");
        }
    }
}elseif($act == 'savefj'){  //------------success----------------//
    if(empty($_POST['uid'])){
        exit(json_encode(array('code'=>"2009","res"=>"请传入参数")));
    }
    $uid = trim($_POST['uid']);
    //查找当前UID的用户信息
    $user = mysql_fetch_assoc(mysql_query("select * from user_tb where u_id='".$uid."'",$conn));
    //print_r($user);exit;
    if(empty($user)||$user['u_u_idss'] != $id){
        $arr = array("code"=>"2011","res"=>"此用户不是你的用户");
    }else{
        $res = mysql_query("update user_tb set u_state=2 where u_id='".$uid."'",$conn);
        $save = mysql_affected_rows();
        if($save){
            $arr = array("code"=>"1002","res"=>"修改成功");
        }else{
            $arr = array("code"=>"2001","res"=>"修改失败！！！");
        }
    }
}elseif($act == 'tgw'){  //------------success----------------//
    $result = mysql_query("select * from tgw_tb where t_u_id='".$id."'",$conn);
    //$result = mysql_query("select * from tgw_tb where t_u_id=163",$conn);
    $tgw = array();
    $i=0;
    while($row = mysql_fetch_assoc($result)){
        $tgw[$i] = $row;
        $i++;
    }
    //print_r($tgw);exit;
    if(!empty($tgw)){
        $arr = array("code"=>"1002","res"=>"查询成功","data"=>$tgw);
    }else{
        $arr = array("code"=>"2001","res"=>"没有推广位！！！");
    }
}else{
    $arr = array("code"=>"2009","res"=>"参数错误");
}
exit(json_encode($arr));