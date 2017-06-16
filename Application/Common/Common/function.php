<?php
//手机号加密函数
function encrypt_phone($phone){
    // dump($phone);die;
	return substr($phone, 0, 3) . '****' . substr($phone, 7, 4);
}

//防范xss攻击
function remove_xss($string){
	//相对index.php入口文件，引入HTMLPurifier.auto.php核心文件
    require_once './Public/Admin/htmlpurifier/HTMLPurifier.auto.php';
    // 生成配置对象
    $cfg = HTMLPurifier_Config::createDefault();
    // 以下就是配置：
    $cfg -> set('Core.Encoding', 'UTF-8');
    // 设置允许使用的HTML标签
    $cfg -> set('HTML.Allowed','div,b,strong,i,em,a[href|title],ul,ol,li,br,p[style],span[style],img[width|height|alt|src]');
    // 设置允许出现的CSS样式属性
    $cfg -> set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align');
    // 设置a标签上是否允许使用target="_blank"
    $cfg -> set('HTML.TargetBlank', TRUE);
    // 使用配置生成过滤用的对象
    $obj = new HTMLPurifier($cfg);
    // 过滤字符串
    return $obj -> purify($string);
}
#递归方法实现无限极分类
function getTree($list,$pid=0,$level=0) {
    static $tree = array();
    foreach($list as $row) {
        if($row['pid']==$pid) {
            $row['level'] = $level;
            $tree[] = $row;
            getTree($list, $row['id'], $level + 1);
        }
    }
    return $tree;
}
//发送curl请求
function curl_request($url,$psot=true,$https=false,$data=[]){
    //使用curl_init初始化
    $ch= curl_init($url);
    //使用curl_setopt设置会话
    //获取返回值
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    //判断是何请求
    //设置post
    if(post){
        curl_setopt($ch,CURLOPT_PORT,TRUE);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
    }
    //默认使用http协议
    if(https){
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
    }
    //使用curl_exec(); 发送请求
    $result = curl_exec($ch);
    //使用curl_close($ch) 关闭
    curl_close($ch);
    //返回结果
    return $result;
}