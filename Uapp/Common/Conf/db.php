<?php
	
	//数据库配置信息
	return array(
		
	 	//主从分布
	 	'DB_DEPLOY_TYPE'        =>  0, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
	 	'DB_RW_SEPARATE'        =>  false,       // 数据库读写是否分离 主从式有效
	 	'DB_MASTER_NUM'         =>  1, // 读写分离后 主服务器数量
	 	'DB_SLAVE_NO'           =>  '', // 指定从服务器序号

	 	//字段自动映射
	 	//'READ_DATA_MAP'=>true,
		

		//全局数据库
		'DB_TYPE'   => 'mysql', // 数据库类型
		'DB_HOST'   => '127.0.0.1', // 服务器地址  120.24.225.43
		'DB_NAME'   => 'kudousmb', // 数据库名 msb  //  test_msb
		'DB_USER'   => 'root', // 用户名  admin
		'DB_PWD'    => 'root', // 密码  iA7CCVDpBpx
		'DB_PORT'   => 3306, // 端口
		'DB_PREFIX' => '', // 数据库表前缀 
		'DB_CHARSET'=> 'utf8', // 字符集
               
		//数据库配置2
	 	'DB_CONFIG1' => 'mysql://admin:iA7CCVDpBpx@120.24.225.43:3306/msb', //生产环境
                'DB_CONFIG2' => 'mysql://admin:iA7CCVDpBpx@120.24.225.43:3306/test_msb', //测试环境

	);

	