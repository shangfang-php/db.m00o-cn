<?php
error_reporting(-1);
//连接数据库
//$conn = mysql_connect('120.77.60.239','shangfang','BjKs7645yH');
//$conn = mysql_connect('192.168.1.191','root','root');
$conn = mysql_connect('127.0.0.1','root','');
//设置字符编码
mysql_query('set names UTF8');
//选择数据库
mysql_select_db('shangfang',$conn);


/**
 * mysql_start()
 * 开启mysql事物
 * @return void
 */
function mysql_start(){
    mysql_query('start transaction');
    mysql_query('SET autocommit=0');
}

/**
 * mysql_commit()
 * 事物提交
 * @return void
 */
function mysql_commit(){
    mysql_query('commit');
    mysql_query('SET autocommit=1');
}

/**
 * mysql_rollback()
 * 事物回滚
 * @return void
 */
function mysql_rollback(){
    mysql_query('rollback');
}