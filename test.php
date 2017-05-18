<?php
//echo urlencode('\r\n\t');exit;
//$data   =   '{"tkid":"1806","key":"9051F82D-B6FB-95D1-9805-FC778CB7E0F2","data":"[{\"o_ordernum\":\"18706489798875162_522972486980\",\"o_creattime\":\"1494647967\",\"o_goodsinformation\":\"\u82f1\u56fdFG\u6db2\u4f53\u98df\u7528\u9999\u7cbe\u9999\u6599 \u70d8\u7119\u6c34\u679c\u5473\u9999\u8349\u7cbe \u7eaf\u5929\u7136\u98df\u54c1\u6dfb\u52a0\u5242\",\"o_goodsid\":\"522972486980\",\"o_goodsimg\":\"http:\/\/img03.taobaocdn.com\/bao\/uploaded\/i3\/TB1MJTGJVXXXXb2XFXXXXXXXXXX_!!0-item_pic.jpg\",\"o_sellernic\":\"\u7070\u592a\u72fc820206\",\"o_shopnic\":\"\u54c1\u5473\u7ffb\u7cd6\u70d8\u7119\u5e97\",\"o_number\":\"1\",\"o_state\":\"13\",\"o_price\":\"16.16\",\"o_zzfc\":\"7.00\",\"o_payprice\":\"0.00\",\"o_zzyj\":\"0.00\",\"o_endtime\":\"\",\"o_mediaid\":\"24492755\",\"o_adid\":\"82260933\",\"o_t_id\":\"109368\",\"o_u_id\":\"230\",\"o_t_nicname\":\"\u5feb\u4e50\u626b\u8d27\u5708F162\u7fa4\",\"o_u_username\":\"ONLYBOBO523\",\"o_operatingstate\":\"1\",\"o_u_idss\":\"1806\"}]"}';
//$data   =   json_decode($data, TRUE);
//print_r($data);exit;
//$data   =   json_decode($data, TRUE);

//$data   =   array('data'=>json_encode($data), 'tkid'=>19, 'key'=>'29C1A5B0-29B4-B2B7-6A94-E62DFFA0E6A9');
//$data   =   http_build_query($data, '', '&');
$data   =   'tkid=930&key=18E64BCB-A739-89D6-E491-4E177B18B2DE&alimamaid=16356866&type=0&time=1495036800';

$url    =   'http://db.localhost.com/High.php?act=hlist';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
//curl_setopt($ch, CURLOPT_HEADER, 1);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$result =   curl_exec($ch);
curl_close($ch);
print_r($result);exit;
?>