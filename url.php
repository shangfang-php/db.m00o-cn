//登录*****************************   192.168.1.191
域名/db_test/Login.php //------------success----------------//
POST传入参数：username password   13506595379     123456

//High文件************************
域名/db_test/High.php?act=hlist //------------success----------------//
POST传入参数 alimamaid  time  type    112853417  1491321600  1

域名/db_test/High.php?act=savetype //------------success----------------//
POST传入参数 hid  type  1010546   1\0

//User会员文件************************
域名/db_test/User.php?act=userlist //------------success----------------//
POST传入参数 tkid key  5

域名/db_test/User.php?act=savemoney //------------success----------------//
POST传入参数 tkid key uid money

域名/db_test/User.php?act=savewqrmoney //------------success----------------//
POST传入参数 tkid key uid wqrmoney

域名/db_test/User.php?act=saveclick //------------success----------------//
POST传入参数 tkid key uid click

域名/db_test/User.php?act=safe //------------success----------------//
POST传入参数 tkid key

域名/db_test/User.php?act=savefcbl //------------success----------------//
POST传入参数 tkid key uid fcbl

域名/db_test/User.php?act=savefj //------------success----------------//
POST传入参数 tkid key uid

域名/dbtest/User.php?act=tgw //------------success----------------//
POST传入参数 tkid key

//Order订单文件***********************
域名/dbtest/Order.php?act=getorder //------------success----------------//
POST传入参数 tkid key

域名/dbtest/Order.php?act=getorder2 //------------success----------------//
POST传入参数 tkid key pageindex pagesize

域名/dbtest/Order.php?act=addorder //------------success----------------//
POST传入参数 tkid key data

域名/dbtest/Order.php?act=saveorder //-------------未测 data不好传啊
POST传入参数 tkid key oid ordernum data time

域名/dbtest/Order.php?act=saveorder2**************暂无
POST传入参数 tkid key oid ordernum data time

域名/dbtest/Order.php?act=record_fb //------------success----------------//
POST传入参数 tkid key uid num money creattime type

域名/dbtest/Order.php?act=record //------------success----------------//
POST传入参数 tkid key uid num money creattime type