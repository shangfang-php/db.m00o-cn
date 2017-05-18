<?php
include "database.php";

//接收数据          //------------success----------------//

//$username = trim($_GET['username']);
//$pass     = trim($_GET['password']);
if(empty($_POST['username'])||empty($_POST['password'])){
    exit(json_encode(array('code'=>"2009","res"=>"请传入参数")));
}
$username = trim($_POST['username']);
$pass     = trim($_POST['password']);
$a = substr( md5($pass),12);
$pass = substr($a,0,-10);
//print_r($password);exit;
//sql语句
$sql = "select * from user_tb where u_username= '".$username."' and u_pass='".$pass."' ";
//操作sql
$res = mysql_query($sql,$conn);
$Userarr = mysql_fetch_assoc($res);
//print_r($Userarr['u_leve']);
    if(!empty($Userarr)){
    if($Userarr['u_leve'] != 0){
        $arr = array("code"=>"2000","res"=>"大兄弟，你不是淘客");
    }else{
        if($Userarr['u_state']==2) {
            $arr = array("code"=>"2009","res"=>"帐号被停封");
        } else if($Userarr['u_state']==1) {
            $keytt='';
            $result = mysql_query("select * from safe_tb where s_u_id='".$Userarr['u_id']."'",$conn);
            $safearr = mysql_fetch_assoc($result);
           // $safe_guid = create_guid();
            $charid = strtoupper(md5(uniqid(mt_rand(), true)));
            $hyphen = chr(45);// "-"
            $safe_guid = ""
                .substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12)
                ."";
            if(!$safearr) {
                $keytt=$safe_guid;
                $add['s_u_id'] = $Userarr['u_id'];
                $add['s_key']  = $safe_guid;
                mysql_query("insert into safe_tb (s_u_id,s_key)values('".$Userarr['u_id']."','".$safe_guid."')",$conn);
            } else if($safearr) {
                $keytt=$safearr['s_key'];
            }
            $arr = array(
                'code'      =>'1002',
                'res'       =>'登陆成功',
                'tkid'      =>$Userarr['u_id'],
                'key'       =>$keytt,
                'username'  =>$Userarr['u_username'],
                'money'     =>$Userarr['u_money'],
                'wqr'       =>$Userarr['u_wqrmoney'],
                'ytx'       =>$Userarr['u_ytxmoney'],
                'fcbl'      =>$Userarr['u_fcbl'],
                'ucode'     =>$Userarr['u_code']
            );
        } else {
            $arr = array("code"=>"2011","res"=>"登录失败");
        }
    }
}else{
    $arr = array("code"=>"2008","res"=>"帐号密码不匹配");
}

exit(json_encode($arr));
