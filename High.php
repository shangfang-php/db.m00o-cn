<?php
##代理找品
include "database.php";
$act = $_GET['act'];
if($act == 'hlist'){  //------------success----------------//
    if(empty($_POST['alimamaid'])||empty($_POST['time'])|| !isset($_POST['type'])){
        exit(json_encode(array('code'=>'2009','res'=>'请传入必要参数')));
    }
    $amid = trim($_POST['alimamaid']);
    $time = trim($_POST['time']);
    $type = trim($_POST['type']);
    //echo 323;exit;
    /*$amid = trim($_GET['alimamaid']);
    $time = trim($_GET['time']);
    $type = trim($_GET['type']);*/
    if($type > 1 || $type < 0){
        $arr = array("code"=>"2009","res"=>"参数错误");
    }else{
        $sql = "select * from high_tb where h_time='".$time."' and h_type='".$type."' and h_alimamaid='".$amid."'";
        $result = mysql_query($sql,$conn);
        
        $list = array();
        while($row = mysql_fetch_assoc($result)){
            $list[] = $row;
        }
        
        if(!empty($list)){
            $arr = array("code"=>"1002","res"=>"查询成功","data"=>$list);
        }else{
            $arr = array("code"=>"2008","res"=>"没有数据");
        }
    }
}elseif($act == 'savetype'){  //------------success----------------//
    if(empty($_POST['hid'])||empty($_POST['type'])){
        exit(json_encode(array('code'=>'2009','res'=>'请传入必要参数')));
    }
    $hid = trim($_POST['hid']);
    $type = trim($_POST['type']);
   /* $hid = trim($_GET['hid']);
    $type = trim($_GET['type']);*/
    //echo $type;exit;
    if($type > 1 || $type < 0){
        $arr = array("code"=>"2009","res"=>"参数错误");
    }else{
        //$sel = M('high_tb')->where(array('h_id'=>$hid))->find();
        $sel = mysql_fetch_assoc(mysql_query("select * from high_tb where h_id='".$hid."'",$conn));
        //var_dump($sel);exit;
        if(empty($sel)){
            $arr = array("code"=>"2010","res"=>"不存在的hid");
        }else{
            //$save = M('high_tb')->where(array('h_id'=>$hid))->save(array('h_type'=>$type));
            $res = mysql_query("update high_tb set h_type='".$type."'where h_id=".$hid." ",$conn);
            //var_dump($res);
            $save = mysql_affected_rows();
            //print_r("update high_tb set h_type='".$type."'where h_id=".$hid." ");exit;
            //$save == 1;
            //var_dump($save);
            if($save){
                $arr = array("code"=>"1002","res"=>"修改成功");
            //}else if($save === 0){
                //$arr = array("code"=>"1002","res"=>"修改成功(没有任何修改)");
            }else{
                $arr = array("code"=>"2008","res"=>"修改失败");
            }
        }
    }
}else{
    $arr = array("code"=>"2009","res"=>"参数错误");
}
exit(json_encode($arr));
