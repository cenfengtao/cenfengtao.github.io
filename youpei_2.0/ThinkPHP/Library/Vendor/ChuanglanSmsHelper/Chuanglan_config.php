<?php

/* *
 * 配置文件
 * 版本：1.3
 * 日期：2017-04-12
 * 说明：
 * 以下代码只是为了方便客户测试而提供的样例代码，客户可以根据自己网站的需要自行编写,并非一定要使用该代码。
 * 该代码仅供学习和研究接口使用，只是提供一个参考。
*/

class ChuanglanConfig
{


//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//创蓝发送短信接口URL, 请求地址请参考253云通讯自助通平台查看或者询问您的商务负责人获取
const API_SEND_URL = 'http://sms.253.com/msg/send/json';

//创蓝变量短信接口URL, 请求地址请参考253云通讯自助通平台查看或者询问您的商务负责人获取
const API_VARIABLE_URL = 'http://sms.253.com/msg/variable/json';

//创蓝短信余额查询接口URL, 请求地址请参考253云通讯自助通平台查看或者询问您的商务负责人获取
const API_BALANCE_QUERY_URL = 'http://sms.253.com/msg/balance/json';
//创蓝账号 替换成你自己的账号
const API_ACCOUNT = 'N5680966';

//创蓝密码 替换成你自己的密码
const API_PASSWORD = 'ZxX3H2qzw';

//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
}

?>