<?php
return array(
    //'配置项'=>'配置值'
    'URL_MODEL' => 1,
    'URL_CASE_INSENSITIVE' => false,
    'MODULE_ALLOW_LIST' => array('Home', 'Admin'), 'DEFAULT_MODULE' => 'Home',
    'TMPL_CACHE_ON' => false,//禁止模板编译缓存
    'HTML_CACHE_ON' => false,//禁止静态缓存
    'OUTPUT_ENCODE' => true, // 页面压缩输出
    'LOAD_EXT_CONFIG' => 'db', //数据库文件
    'VAR_FILTERS' => 'trim,htmlspecialchars',     // 全局系统变量的默认过滤方法 多个用逗号分割
    'MD5_RANDOM' => 'youpei2017', //md5加密的附加值
    'VAR_PAGE' => 'p',
    'PAGE_SIZE' => 12,
    'YP_UPLOAD_DIR' => 'wechat,appraises,image,qrcode,adspic,brands,complains,file,friendlinks,shops,staffs,users,mall,goods,complains,temp,uploads',
    'YP_M_IMG_SUFFIX' => '',
    'SESSION_AUTO_START' => true,
    'APPID' => 'wx2a478415b419d817',  //APPID
    'SECRET' => '5777fd0e27efa96cf5c85bbf1d2e5baa', //APP_SECRET
    'AES_KEY' => '3ZkDsBzfA1TcSv07v78EiHD0y8u4ioCu0J8Ipug9R2b',
    'TEMPLATE' => array(
        'INTEGRAL_CHANGE' => '{
           "touser":"__OPENID__",
           "template_id":"wHpoMGjkM5hULMfubjGq-DQFayS0TDSIKlDknRvfjlg",
           "url":"__URL__",
           "topcolor":"#FF0000",
           "data":{
                   "first": {
                       "value":"__FIRST__",
                       "color":"#000000"
                   },
                   "keyword1":{
                       "value":"__KEYWORD1__",
                       "color":"#173177"
                   },
                   "keyword2": {
                       "value":"__KEYWORD2__",
                       "color":"#173177"
                   },
                   "keyword3": {
                       "value":"__KEYWORD3__",
                       "color":"#173177"
                   },
                          "keyword4": {
                       "value":"__KEYWORD4__",
                       "color":"#173177"
                   },
                   "remark":{
                       "value":"__REMARK__",
                       "color":"#000000"
                   }
           }
       }',
        'INFORM' => '{
           "touser":"__OPENID__",
           "template_id":"nr3_WzzV5l8A_WuK51IF-azZB8s8kTW2CahM_z4gGEE",
           "url":"__URL__",
           "topcolor":"#FF0000",
           "data":{
                   "first": {
                       "value":"__FIRST__",
                       "color":"#000000"
                   },
                   "keyword1":{
                       "value":"__KEYWORD1__",
                       "color":"#173177"
                   },
                   "keyword2": {
                       "value":"__KEYWORD2__",
                       "color":"#173177"
                   },
                   "keyword3": {
                       "value":"__KEYWORD3__",
                       "color":"#173177"
                   },
                   "remark":{
                       "value":"__REMARK__",
                       "color":"#000000"
                   }
           }
       }',
        'URGE_PAY' => '{
           "touser":"__OPENID__",
           "template_id":"8nA82auK6t-G7pZjfnZOF69IDFFF-qpsMWSRTLdLpBM",
           "url":"__URL__",
           "topcolor":"#FF0000",
           "data":{
                   "first": {
                       "value":"__FIRST__",
                       "color":"#000000"
                   },
                   "keyword1":{
                       "value":"__KEYWORD1__",
                       "color":"#173177"
                   },
                   "keyword2": {
                       "value":"__KEYWORD2__",
                       "color":"#173177"
                   },
                   "keyword3": {
                       "value":"__KEYWORD3__",
                       "color":"#173177"
                   },
                   "keyword4": {
                   "value":"__KEYWORD3__",
                   "color":"#173177"
                   },
                   "keyword5": {
                   "value":"__KEYWORD3__",
                   "color":"#173177"
                   },
                   "remark":{
                       "value":"__REMARK__",
                       "color":"#000000"
                   }
           }
       }',
        'PAY_INFORM' => '{
           "touser":"__OPENID__",
           "template_id":"AZXuQy3X4q_739ZgdCoiDjpf0YlBdil708ljLUf4Y-A",
           "url":"__URL__",
           "topcolor":"#FF0000",
           "data":{
                   "first": {
                       "value":"__FIRST__",
                       "color":"#000000"
                   },
                   "keyword1":{
                       "value":"__KEYWORD1__",
                       "color":"#173177"
                   },
                   "keyword2": {
                       "value":"__KEYWORD2__",
                       "color":"#173177"
                   },
                   "keyword3": {
                       "value":"__KEYWORD3__",
                       "color":"#173177"
                   },
                   "keyword4": {
                       "value":"__KEYWORD4__",
                       "color":"#173177"
                   },
                   "keyword5": {
                       "value":"__KEYWORD5__",
                       "color":"#173177"
                   },
                   "remark":{
                       "value":"__REMARK__",
                       "color":"#000000"
                   }
           }
        }',
        'BARGAIN_SCHEDULE' => '{
           "touser":"__OPENID__",
           "template_id":"qP4vd7kEioOWlpAzU_wRCEoXMieZfA3wyRYNaEoMMws",
           "url":"__URL__",
           "topcolor":"#FF0000",
           "data":{
                   "first": {
                       "value":"__FIRST__",
                       "color":"#000000"
                   },
                   "keyword1":{
                       "value":"__KEYWORD1__",
                       "color":"#173177"
                   },
                   "keyword2": {
                       "value":"__KEYWORD2__",
                       "color":"#173177"
                   },
                   "keyword3": {
                       "value":"__KEYWORD3__",
                       "color":"#173177"
                   },
                   "keyword4": {
                       "value":"__KEYWORD4__",
                       "color":"#173177"
                   },
                   "keyword5": {
                       "value":"__KEYWORD5__",
                       "color":"#173177"
                   },
                   "remark":{
                       "value":"__REMARK__",
                       "color":"#000000"
                   }
           }
        }',
        'CONTRIBUTION' => '{
                   "touser":"__OPENID__",
                   "template_id":"tUUp-uNbOjAt7tn2u_3m4y5WgkDfckfyf5L2VTS2AWw",
                   "url":"__URL__",
                   "topcolor":"#FF0000",
                   "data":{
                           "first": {
                               "value":"__FIRST__",
                               "color":"#000000"
                           },
                           "keyword1":{
                               "value":"__KEYWORD1__",
                               "color":"#173177"
                           },
                           "keyword2": {
                               "value":"__KEYWORD2__",
                               "color":"#173177"
                           },
                           "remark":{
                               "value":"__REMARK__",
                               "color":"#000000"
                           }
                   }
                }',
        'ADD_ORDER' => '{
                   "touser":"__OPENID__",
                   "template_id":"q96nT4GJGF6IIU5HF5GW50feIhPl3eGhZZn3JcIKBZA",
                   "url":"__URL__",
                   "topcolor":"#FF0000",
                   "data":{
                           "first": {
                               "value":"__FIRST__",
                               "color":"#000000"
                           },
                           "keyword1":{
                               "value":"__KEYWORD1__",
                               "color":"#173177"
                           },
                           "keyword2": {
                               "value":"__KEYWORD2__",
                               "color":"#173177"
                           },
                            "keyword3": {
                               "value":"__KEYWORD3__",
                               "color":"#173177"
                           },
                           "remark":{
                               "value":"__REMARK__",
                               "color":"#000000"
                           }
                   }
        }',
        'COURSE_NOTICE' => '{
                   "touser":"__OPENID__",
                   "template_id":"wcze6WSpJYIIsSTM-NARq8ItW11HovFcf31q8WLeBx0",
                   "url":"__URL__",
                   "topcolor":"#FF0000",
                   "data":{
                           "first": {
                               "value":"__FIRST__",
                               "color":"#000000"
                           },
                           "keyword1":{
                               "value":"__KEYWORD1__",
                               "color":"#173177"
                           },
                           "keyword2": {
                               "value":"__KEYWORD2__",
                               "color":"#173177"
                           },
                           "remark":{
                               "value":"__REMARK__",
                               "color":"#000000"
                           }
                   }
        }'
    ),

);