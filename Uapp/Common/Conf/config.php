<?php
return array(
	//'配置项'=>'配置值'
//	'SHOW_PAGE_TRACE' =>true,                       // 显示页面Trace信息
	'DEFAULT_FILTER'        =>  'htmlspecialchars,trim', // 默认参数过滤方法 用于I函数
	'DEFAULT_CHARSET'       =>  'utf-8', // 默认输出编码
	'DEFAULT_TIMEZONE'      =>  'PRC',  // 默认时区
	'DEFAULT_MODULE'     => 'Index', //默认模块    
	'URL_MODEL'          => '2', //URL模式    
	'SESSION_AUTO_START' => true, //是否开启session
	'LOAD_EXT_CONFIG' => 'user,db,cache,weixin', // 加载扩展配置文件
    
        //日志记录
        'LOG_RECORD' => true, // 开启日志记录
        'LOG_LEVEL'  =>'EMERG,ALERT,CRIT,ERR', // 只记录EMERG ALERT CRIT ERR 错误
        'LOG_TYPE'              =>  'File', // 日志记录类型 默认为文件方式
    
        //下级限制总人数
        'NEXT_AGENT_COUNT'=>20,
    
        //授权书号码前缀
        'AGENT_NO_PRE'=>'MSB',
    
        //会员表信息
        'M_TABLE_NAME'=>'Member',
        'MID_FIELD_NAME'=>'ID',
    
        //会员等级
        'MEMBER_LEVEL'=>array(
            0=>array('lv'=>0,'name'=>'蜜舒宝公司'),
            1=>array('lv'=>1,'name'=>'联合创始人'),
            2=>array('lv'=>2,'name'=>'大区合伙人'),
            3=>array('lv'=>3,'name'=>'总代合伙人'),
            4=>array('lv'=>4,'name'=>'特约合伙人'),
        ),
    
        //会员状态
        'MEMBER_STAT' => array(
            1=>array('lv'=>1,'name'=>'已审核'),
            0=>array('lv'=>0,'name'=>'待总部审核'),
            -1=>array('lv'=>-1,'name'=>'黑名单'),
            -2=>array('lv'=>-2,'name'=>'待上级审核'),
            -3=>array('lv'=>-3,'name'=>'驳回修改'),
        ),
    
        //标签区间 type: 1:大标,2:小标,3:防伪标:4:中标,
        'LABEL_CODE_SECTION'=>array(
            array('start'=>1200001,'end'=>2200000,'type'=>1),
            array('start'=>2800001,'end'=>3800000,'type'=>2),
            array('start'=>4200001,'end'=>5200000,'type'=>3),
            array('start'=>7800001,'end'=>8800000,'type'=>4),
            
        ),

	// 设置默认的模板主题
	'TMPL_LOAD_DEFAULTTHEME'=>true, //如果本模块没有这个页面就会自动定位到默认模板
        'TMPL_PARSE_STRING'  =>array(    
//            '__PUBLIC__' => 'http://'.$_SERVER['HTTP_HOST'].'/Public', // 更改默认的/Public 替换规则 
//            '__APP__' => 'http://'.$_SERVER['HTTP_HOST'].'/index.php/', // 更改默认的/Public 替换规则     
//            '__JS__'     => '/Public/JS/', // 增加新的JS类库路径替换规则     
//            '__UPLOAD__' => '/Uploads', // 增加新的上传路径替换规则
            '__WEBNAME__'=>'优利宝',  //网站名称
        ),
        'WEB_URL'=>'http://'.$_SERVER['HTTP_HOST'],
	'DEFAULT_THEME'    =>    'Default', //默认主题
	'TMPL_L_DELIM'=>'<{',
	'TMPL_R_DELIM'=>'}>',

	//设置提示页面
//	'TMPL_ACTION_ERROR' => 'Public:error',//默认错误跳转对应的模板文件
//	'TMPL_ACTION_SUCCESS' => 'Public:success',//默认成功跳转对应的模板文件

	// 开启路由
	'URL_ROUTER_ON'   => true,
	'URL_ROUTE_RULES'=>array(    
			//'news/:year/:month/:day' => array('News/archive', 'status=1'),    
			'/^goods\/(\d+)$/'               => 'Home/Goods/index',    
			//'news/read/:id'          => '/news/:1',
		), 

	//静态缓存
	'HTML_FILE_SUFFIX'  =>    '.html', // 设置静态缓存文件后缀,为空支持所有的静态后缀
	// 'HTML_CACHE_ON'     =>    true, // 开启静态缓存
	// 'HTML_CACHE_TIME'   =>    60,   // 全局静态缓存有效期（秒）
	// 'HTML_CACHE_RULES'  =>     array(  // 定义静态缓存规则    
	// 	// 定义格式1 数组方式     
	// 	'Index:'=>array('{:module}/{:controller}/{:action}/{id}',10),    
	// 	// 定义格式2 字符串方式     
	// 	//'静态地址'    =>     '静态规则', 
	// ),

	'MODULE_DENY_LIST'      =>  array('Common','Runtime'),		// 设置禁止访问的模块列表
	'MODULE_ALLOW_LIST'    =>    array('Home','Api','Mobile','Admin'), 	//设置允许访问列表
	'DEFAULT_MODULE'       =>    'Mobile',						//设置默认模块
	'APP_SUB_DOMAIN_DEPLOY'   =>    1, // 开启子域名配置
	'APP_SUB_DOMAIN_RULES'    =>    array( 
		'adminmsb'   => 'Admin',   // api.domain.com域名指向Test模块),
		'msb'   => 'Mobile',  // m.domain.com域名指向Test模块),
             
	),
);