<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/22
 * Time: 11:55
 */

return array(

    'DATA_CACHE_TIME'       =>  0,      // 数据缓存有效期 0表示永久缓存
    'DATA_CACHE_COMPRESS'   =>  false,   // 数据缓存是否压缩缓存
    'DATA_CACHE_CHECK'      =>  false,   // 数据缓存是否校验缓存
    'DATA_CACHE_PREFIX'     =>  'smb_',     // 缓存前缀
    'DATA_CACHE_TYPE'       =>  'Redis',  // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator|Redis
    'DATA_CACHE_PATH'       =>  TEMP_PATH,// 缓存路径设置 (仅对File方式缓存有效)
    'DATA_CACHE_SUBDIR'     =>  false,    // 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)
    'DATA_PATH_LEVEL'       =>  1,        // 子目录缓存级别
    
    //缓存键值
    'GOODS_INFO'=>'goods_info_',                       //商品信息
    'AGENT_INFO'=>'agent_info_',                       //代理信息
    'ORDER_GOODS'=>'order_goods_',                    //订单商品信息
    'CODE_LABEL'=>'code_label_',                    //防伪码信息
    'AGENT_GOODS_PROFIT'=>'agent_goods_profit_',    //代理分润信息
    'ENCOURAGE_INFO'=>'encourage_info_',            //激励语信息
    'OAUTH_ACCESS_TOKEN'=>'OauthAccessToken',        //获取微信网页授权OauthAccessToken
    
);