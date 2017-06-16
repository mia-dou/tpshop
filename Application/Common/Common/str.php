<?php
//密码加密函数
function encrypt_password($password){
    //加盐
    $salt = "shdkahfhg;agasdfa";
    return md5($salt . md5($password));
}