/*
SQLyog 企业版 - MySQL GUI v8.14 
MySQL - 5.5.38 : Database - kudousmb
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`kudousmb` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `kudousmb`;

/*Table structure for table `agent` */

DROP TABLE IF EXISTS `agent`;

CREATE TABLE `agent` (
  `agentId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL,
  `weixin` varchar(30) DEFAULT NULL,
  `star` tinyint(1) unsigned DEFAULT NULL,
  `startime` int(10) unsigned DEFAULT NULL,
  `endtime` int(10) unsigned DEFAULT NULL,
  `parent_id` int(10) unsigned DEFAULT '0',
  `password` varchar(35) DEFAULT NULL,
  `add_time` timestamp NULL DEFAULT NULL,
  `dailidanwei` varchar(150) DEFAULT NULL,
  `qq` varchar(20) DEFAULT NULL,
  `agentNo` varchar(20) DEFAULT NULL,
  `cardNo` varchar(40) DEFAULT NULL,
  `tel` varchar(15) DEFAULT NULL,
  `stat` tinyint(1) DEFAULT '0',
  `agentCode` varchar(20) DEFAULT NULL,
  `province` varchar(20) DEFAULT NULL,
  `city` varchar(20) DEFAULT NULL,
  `county` varchar(10) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `pre_not_profit_total_money` int(10) unsigned DEFAULT '0' COMMENT '代理上月可分利润总额',
  `not_profit_total_money` int(11) unsigned DEFAULT '0' COMMENT '代理可分润总金额',
  `company_total_profit` bigint(20) unsigned DEFAULT '0' COMMENT '公司分润总金额',
  `all_sale_total_money` bigint(20) unsigned DEFAULT '0' COMMENT '代理出货总金额\r\n',
  `all_buy_total_money` bigint(20) unsigned DEFAULT '0' COMMENT '代理进货总金额\r\n',
  `all_sale_total_profit` bigint(20) unsigned DEFAULT '0' COMMENT '代理销售额总利润\r\n',
  `all_buy_total_stock` int(10) unsigned DEFAULT '0' COMMENT '进库总库存',
  `all_sale_total_stock` int(10) unsigned DEFAULT '0' COMMENT '出库总库存',
  `use_integral` int(10) unsigned DEFAULT '0' COMMENT '代理可使用积分\r\n',
  `agent_true_name` varchar(50) DEFAULT NULL COMMENT '代理真实姓名\r\n',
  `bank_account` varchar(50) DEFAULT NULL COMMENT '代理银行账户\r\n',
  `bank_name` varchar(100) DEFAULT NULL COMMENT '代理账户银行名称\r\n',
  `team_name` varchar(50) DEFAULT NULL COMMENT '团队名称',
  `head_img` varchar(255) DEFAULT NULL COMMENT '头像图片',
  PRIMARY KEY (`agentId`),
  KEY `name` (`name`),
  KEY `weixin` (`weixin`),
  KEY `star` (`star`),
  KEY `tel` (`tel`)
) ENGINE=InnoDB AUTO_INCREMENT=696 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `agent_goods_profit_rale` */

DROP TABLE IF EXISTS `agent_goods_profit_rale`;

CREATE TABLE `agent_goods_profit_rale` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `agent_lv` tinyint(1) unsigned DEFAULT '0' COMMENT '代理等级: 1:官方合伙人,2:大区,3:总代,4:特约',
  `top1_profit` tinyint(3) unsigned DEFAULT '0' COMMENT '直接官方分润\r\n',
  `top2_profit` tinyint(3) unsigned DEFAULT '0' COMMENT '间接官方分润\r\n',
  `agent1_profit` tinyint(3) unsigned DEFAULT '0' COMMENT '官方分润\r\n',
  `agent2_profit` tinyint(3) unsigned DEFAULT '0' COMMENT '大区分润\r\n',
  `agent3_profit` tinyint(3) unsigned DEFAULT '0' COMMENT '总代分润\r\n',
  `agent4_profit` tinyint(3) unsigned DEFAULT '0' COMMENT '特约分润\r\n',
  `agent_price` mediumint(5) unsigned DEFAULT '0' COMMENT '代理产品价格\r\n',
  `goods_id` int(11) DEFAULT '0' COMMENT '产品id',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '添加时间\r\n',
  PRIMARY KEY (`id`),
  KEY `agent_lv` (`agent_lv`),
  KEY `goods_id` (`goods_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='代理等级与商品分润关系表';

/*Table structure for table `agent_goods_stock_rale` */

DROP TABLE IF EXISTS `agent_goods_stock_rale`;

CREATE TABLE `agent_goods_stock_rale` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` int(10) unsigned DEFAULT '0' COMMENT '产品主键\r\n',
  `goods_stock` mediumint(5) unsigned DEFAULT '0' COMMENT '产品库存\r\n',
  `agent_id` int(10) unsigned DEFAULT '0' COMMENT '代理id',
  `buy_total_stock` bigint(20) unsigned DEFAULT '0' COMMENT '进库总数量\r\n',
  `sale_total_stock` bigint(20) unsigned DEFAULT '0' COMMENT '出库总数量\r\n',
  `buy_total_money` bigint(20) unsigned DEFAULT '0' COMMENT '进库总金额\r\n',
  `sale_total_money` bigint(20) unsigned DEFAULT '0' COMMENT '出库总金额\r\n',
  `agent_price` int(10) unsigned DEFAULT '0' COMMENT '代理商品价格',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '添加时间\r\n',
  PRIMARY KEY (`id`),
  KEY `goods_id` (`goods_id`),
  KEY `agent_id` (`agent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='代理与产品库存关系表';

/*Table structure for table `agent_month_profit` */

DROP TABLE IF EXISTS `agent_month_profit`;

CREATE TABLE `agent_month_profit` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` int(10) unsigned DEFAULT '0' COMMENT '代理ID',
  `year` mediumint(4) unsigned DEFAULT NULL COMMENT '年\r\n',
  `month` tinyint(2) DEFAULT NULL COMMENT '月\r\n',
  `company_profit` int(10) unsigned DEFAULT '0' COMMENT '每月公司总返利润\r\n',
  `sale_profit` int(10) unsigned DEFAULT '0' COMMENT '每月自己销售利润\r\n',
  `buy_total_money` int(10) unsigned DEFAULT '0' COMMENT '每月自己进货金额\r\n',
  `sale_total_money` int(10) unsigned DEFAULT '0' COMMENT '每月自己销售金额\r\n',
  `buy_total_stock` int(10) unsigned DEFAULT '0' COMMENT '进库总数量\r\n',
  `sale_total_stock` int(10) unsigned DEFAULT '0' COMMENT '出库总数量\r\n',
  `is_profit` tinyint(1) DEFAULT '2' COMMENT '是否已分润:1:已分润,2:未分润\r\n',
  `edit_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间\r\n',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间\r\n',
  PRIMARY KEY (`id`),
  KEY `year` (`year`),
  KEY `month` (`month`),
  KEY `is_profit` (`is_profit`),
  KEY `agent_id` (`agent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='代理每月销售与进货与分润总额报表';

/*Table structure for table `agent_profit_log` */

DROP TABLE IF EXISTS `agent_profit_log`;

CREATE TABLE `agent_profit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `profit_type` tinyint(1) DEFAULT '1' COMMENT '分润的类型: 1:下级,2:推荐,3:买断首次分润\r\n',
  `profit_agent_id` int(10) unsigned DEFAULT '0' COMMENT '分润代理ID\r\n',
  `profit_agent_name` varchar(50) DEFAULT NULL COMMENT '分润代理名字\r\n',
  `profit_agent_lv` tinyint(1) DEFAULT '0' COMMENT '分润代理等级\r\n',
  `buy_agent_id` int(10) unsigned DEFAULT '0' COMMENT '购买代理id\r\n',
  `buy_agent_name` varchar(50) DEFAULT NULL COMMENT '购买代理名字\r\n',
  `buy_agent_lv` tinyint(1) DEFAULT '0' COMMENT '购买代理等级\r\n',
  `order_id` bigint(20) unsigned DEFAULT NULL COMMENT '订单ID\r\n',
  `order_sn` varchar(30) DEFAULT NULL COMMENT '订单编号\r\n',
  `code` varchar(20) DEFAULT NULL COMMENT '条形码\r\n',
  `code_type` tinyint(1) DEFAULT '0' COMMENT '条形码类型\r\n',
  `goods_id` int(11) DEFAULT '0' COMMENT '商品ID\r\n',
  `goods_name` varchar(255) DEFAULT NULL COMMENT '商品名称\r\n',
  `goods_num` int(10) unsigned DEFAULT '0' COMMENT '商品数量\r\n',
  `goods_price` int(10) unsigned DEFAULT '0' COMMENT '商品价格\r\n',
  `profit_total_money` int(10) unsigned DEFAULT '0' COMMENT '本产品分润总金额\r\n',
  `profit_money` int(10) unsigned DEFAULT '0' COMMENT '本产品单个分润金额\r\n',
  `year` mediumint(4) unsigned DEFAULT '0' COMMENT '年\r\n',
  `month` tinyint(2) unsigned DEFAULT '0' COMMENT '月\r\n',
  `day` tinyint(2) unsigned DEFAULT '0' COMMENT '日\r\n',
  `is_profit` tinyint(1) DEFAULT '2' COMMENT '是否已分润:1:已分润,2:未分润\r\n',
  `is_refund` tinyint(1) DEFAULT '1' COMMENT '是否退货: 1:否,2:是\r\n',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '分润时间\r\n',
  PRIMARY KEY (`id`),
  KEY `profit_type` (`profit_type`),
  KEY `profit_agent_id` (`profit_agent_id`),
  KEY `buy_agent_id` (`buy_agent_id`),
  KEY `order_id` (`order_id`),
  KEY `code` (`code`),
  KEY `goods_id` (`goods_id`),
  KEY `is_profit` (`is_profit`),
  KEY `is_refund` (`is_refund`),
  KEY `year` (`year`),
  KEY `month` (`month`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='代理分润记录表';

/*Table structure for table `agent_relation` */

DROP TABLE IF EXISTS `agent_relation`;

CREATE TABLE `agent_relation` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `member_id` int(10) unsigned NOT NULL COMMENT '会员ID',
  `top1_id` int(10) unsigned DEFAULT '0' COMMENT '间接官方',
  `top2_id` int(10) unsigned DEFAULT '0' COMMENT '直接官方',
  `agent1_id` int(10) unsigned DEFAULT '0' COMMENT '代理1ID(省)',
  `agent2_id` int(10) unsigned DEFAULT '0' COMMENT '代理2ID（市）',
  `agent3_id` int(10) unsigned DEFAULT '0' COMMENT '代理3ID（区）',
  `agent4_id` int(10) unsigned DEFAULT '0' COMMENT '代理4ID（VIP消费者）',
  `pid` int(10) unsigned DEFAULT '0' COMMENT '上级ID',
  `agent_grade` tinyint(1) DEFAULT '5' COMMENT '代理等级: 1:省代,2:市代,3:区代,4:VIP消费者',
  `is_cancel` tinyint(1) unsigned DEFAULT '0' COMMENT '是否取消: 0:否,1:是',
  `is_agent` tinyint(1) DEFAULT '1' COMMENT '是否是代理: 0:否,1:是',
  `is_validate` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态: 1:已审核,0:待总部审核,-1:黑名单,-2:待上级审核,-3:驳回修改',
  `is_founder` tinyint(1) DEFAULT '2' COMMENT '是否创始人: 1:是,2:否. 创始人才能看到间接官方与享受间接官方的分润',
  `line_number` tinyint(3) unsigned DEFAULT '1' COMMENT '代理下线数字',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `agent1_id` (`agent1_id`),
  KEY `agent2_id` (`agent2_id`),
  KEY `agent3_id` (`agent3_id`),
  KEY `agent4_id` (`agent4_id`),
  KEY `pid` (`pid`),
  KEY `agent_grade` (`agent_grade`),
  KEY `is_cancel` (`is_cancel`),
  KEY `member_id` (`member_id`),
  KEY `top1_id` (`top1_id`),
  KEY `top2_id` (`top2_id`),
  KEY `line_number` (`line_number`)
) ENGINE=InnoDB AUTO_INCREMENT=696 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `auth_agent_relation` */

DROP TABLE IF EXISTS `auth_agent_relation`;

CREATE TABLE `auth_agent_relation` (
  `auth_id` tinyint(3) unsigned DEFAULT NULL COMMENT '授权书ID',
  `agent_id` int(10) unsigned DEFAULT NULL COMMENT '代理ID',
  `auth_lv` tinyint(1) unsigned DEFAULT NULL COMMENT '权限等级: 1:最高等级,2:单个级别',
  `is_show` tinyint(1) DEFAULT '1' COMMENT '是否显示: 0:否,1:是',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `auth_id` (`auth_id`),
  KEY `agent_id` (`agent_id`),
  KEY `auth_lv` (`auth_lv`),
  KEY `is_show` (`is_show`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='代理与授权书关系表';

/*Table structure for table `auth_book` */

DROP TABLE IF EXISTS `auth_book`;

CREATE TABLE `auth_book` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `img_path` varchar(255) DEFAULT NULL COMMENT '授权书图片路径',
  `name` varchar(100) DEFAULT NULL COMMENT '授权书名字',
  `auth_lv` tinyint(1) unsigned DEFAULT NULL COMMENT '权限等级: 1:最高等级,2:单个级别',
  `is_show` tinyint(1) DEFAULT '1' COMMENT '是否显示: 0:否,1:是',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='授权书';

/*Table structure for table `ban_cha_list` */

DROP TABLE IF EXISTS `ban_cha_list`;

CREATE TABLE `ban_cha_list` (
  `error_count` int(2) DEFAULT NULL,
  `expiry` int(20) DEFAULT NULL,
  `ip` varchar(30) DEFAULT NULL,
  `sesskey` varchar(64) DEFAULT NULL,
  `flag` int(2) DEFAULT '0',
  `log_time` int(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `ban_list` */

DROP TABLE IF EXISTS `ban_list`;

CREATE TABLE `ban_list` (
  `username` varchar(80) NOT NULL,
  `error_count` int(2) DEFAULT NULL,
  `expiry` varchar(20) DEFAULT '0',
  `ip` varchar(30) DEFAULT NULL,
  `sesskey` varchar(64) DEFAULT NULL,
  `flag` int(2) DEFAULT '0',
  `log_time` int(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `cache` */

DROP TABLE IF EXISTS `cache`;

CREATE TABLE `cache` (
  `cache_id` varchar(32) NOT NULL DEFAULT '',
  `cache_language_id` tinyint(1) NOT NULL DEFAULT '0',
  `cache_name` varchar(255) NOT NULL DEFAULT '',
  `cache_data` mediumtext NOT NULL,
  `cache_global` tinyint(1) NOT NULL DEFAULT '1',
  `cache_gzip` tinyint(1) NOT NULL DEFAULT '1',
  `cache_method` varchar(20) NOT NULL DEFAULT 'RETURN',
  `cache_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cache_expires` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`cache_id`,`cache_language_id`),
  KEY `cache_id` (`cache_id`),
  KEY `cache_language_id` (`cache_language_id`),
  KEY `cache_global` (`cache_global`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `cards` */

DROP TABLE IF EXISTS `cards`;

CREATE TABLE `cards` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发货人ID',
  `member_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收货人ID',
  `code` varchar(20) DEFAULT NULL COMMENT '条形码(大标)',
  `code_type` tinyint(1) DEFAULT '0' COMMENT '条形码的类型',
  `goods_id` int(8) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `goods_name` varchar(225) DEFAULT NULL COMMENT '商品名称',
  `goods_number` tinyint(3) unsigned DEFAULT '1' COMMENT '商品数量',
  `market_price` decimal(10,2) DEFAULT '0.00' COMMENT '市场价格',
  `admin_price` int(10) unsigned DEFAULT '0' COMMENT '发货人价格',
  `member_price` int(10) unsigned DEFAULT '0' COMMENT '收货人价格',
  `goods_profit` mediumint(5) unsigned DEFAULT '0' COMMENT '产品利润',
  `add_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='购物车';

/*Table structure for table `cash_prize_log` */

DROP TABLE IF EXISTS `cash_prize_log`;

CREATE TABLE `cash_prize_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` int(10) unsigned DEFAULT '0',
  `prize_code` varchar(16) DEFAULT '0' COMMENT '兑奖码',
  `is_prize` tinyint(1) DEFAULT '2' COMMENT '是否兑奖(1:已兑奖,2:未兑奖)\r\n',
  `get_time` date DEFAULT NULL COMMENT '获取时间\r\n',
  `out_time` date DEFAULT NULL COMMENT '过期时间\r\n',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '添加时间\r\n',
  PRIMARY KEY (`id`),
  KEY `agent_id` (`agent_id`),
  KEY `prize_code` (`prize_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='特约扫码兑换奖品';

/*Table structure for table `code_prefix` */

DROP TABLE IF EXISTS `code_prefix`;

CREATE TABLE `code_prefix` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code_type` tinyint(1) DEFAULT NULL COMMENT '前缀类型:1:大标,2:小标:3:防伪标,4:中标',
  `code_pre` int(11) DEFAULT NULL COMMENT '号码前缀',
  `code_num` mediumint(9) DEFAULT NULL COMMENT '前缀使用次数',
  `surplus` int(11) DEFAULT NULL COMMENT '号码剩余数量',
  `amount` int(11) DEFAULT NULL COMMENT '号码总数',
  `start_section` varchar(8) DEFAULT NULL COMMENT '号码区间前缀',
  `end_section` varchar(8) DEFAULT NULL COMMENT '号码区间后缀',
  `add_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `code_type` (`code_type`),
  KEY `code_pre` (`code_pre`),
  KEY `surplus` (`surplus`),
  KEY `start_section` (`start_section`),
  KEY `end_section` (`end_section`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='号码前缀';

/*Table structure for table `common_code` */

DROP TABLE IF EXISTS `common_code`;

CREATE TABLE `common_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` text COMMENT '号码',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1000001 DEFAULT CHARSET=utf8 COMMENT='公共号码表';

/*Table structure for table `common_code_max` */

DROP TABLE IF EXISTS `common_code_max`;

CREATE TABLE `common_code_max` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1000001 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='大标签公共号码';

/*Table structure for table `common_code_middle` */

DROP TABLE IF EXISTS `common_code_middle`;

CREATE TABLE `common_code_middle` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1000001 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='中标签公共号码';

/*Table structure for table `common_code_min` */

DROP TABLE IF EXISTS `common_code_min`;

CREATE TABLE `common_code_min` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1000001 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='小标签公共号码';

/*Table structure for table `common_code_security` */

DROP TABLE IF EXISTS `common_code_security`;

CREATE TABLE `common_code_security` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1000001 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='防伪标签公共号码';

/*Table structure for table `company_payment_agent_log` */

DROP TABLE IF EXISTS `company_payment_agent_log`;

CREATE TABLE `company_payment_agent_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned DEFAULT '0' COMMENT '管理员',
  `admin_name` varchar(50) DEFAULT NULL COMMENT '管理员名字\r\n',
  `agent_id` int(10) unsigned DEFAULT '0' COMMENT '代理id',
  `agent_name` varchar(50) DEFAULT NULL COMMENT '代理姓名\r\n',
  `bank_account` varchar(50) DEFAULT NULL COMMENT '代理银行账户\r\n',
  `bank_name` varchar(100) DEFAULT NULL COMMENT '代理银行账户名称\r\n',
  `money` int(10) unsigned DEFAULT '0' COMMENT '转账金额\r\n',
  `year` mediumint(4) unsigned DEFAULT '0' COMMENT '年',
  `month` tinyint(2) DEFAULT '0' COMMENT '月',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '转账时间\r\n',
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `agent_id` (`agent_id`),
  KEY `year` (`year`),
  KEY `month` (`month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='公司财务支付代理分润记录表';

/*Table structure for table `company_reports_log` */

DROP TABLE IF EXISTS `company_reports_log`;

CREATE TABLE `company_reports_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `year` mediumint(4) unsigned DEFAULT '0' COMMENT '年\r\n',
  `month` tinyint(2) unsigned DEFAULT '0' COMMENT '月\r\n',
  `total_profit` int(10) unsigned DEFAULT '0' COMMENT '每月总支出利润\r\n',
  `real_profit` int(10) unsigned DEFAULT '0' COMMENT '每月实际支出利润\r\n',
  `not_profit` int(10) DEFAULT '0' COMMENT '每月未支出利润\r\n',
  `all_surplus_profit` bigint(20) unsigned DEFAULT '0' COMMENT '剩余总利润\r\n',
  `all_total_profit` bigint(20) unsigned DEFAULT '0' COMMENT '总支出\r\n',
  `all_real_profit` bigint(20) unsigned DEFAULT '0' COMMENT '实际总支出\r\n',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态:1:不可更改,2:可更改,3:最新',
  `edit_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间\r\n',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间\r\n',
  PRIMARY KEY (`id`),
  KEY `year` (`year`),
  KEY `month` (`month`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='公司每月分润总额报表';

/*Table structure for table `configuration` */

DROP TABLE IF EXISTS `configuration`;

CREATE TABLE `configuration` (
  `configuration_id` int(11) NOT NULL AUTO_INCREMENT,
  `configuration_title` varchar(255) NOT NULL,
  `configuration_key` varchar(255) NOT NULL,
  `configuration_value` varchar(255) NOT NULL,
  `configuration_description` varchar(255) NOT NULL,
  `configuration_group_id` int(11) NOT NULL,
  `sort_order` int(5) DEFAULT NULL,
  `last_modified` datetime DEFAULT NULL,
  `date_added` datetime NOT NULL,
  `use_function` varchar(255) DEFAULT NULL,
  `set_function` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`configuration_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1197 DEFAULT CHARSET=utf8;

/*Table structure for table `configuration_group` */

DROP TABLE IF EXISTS `configuration_group`;

CREATE TABLE `configuration_group` (
  `configuration_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `configuration_group_title` varchar(64) NOT NULL,
  `configuration_group_description` varchar(255) NOT NULL,
  `sort_order` int(5) DEFAULT NULL,
  `visible` int(1) DEFAULT '1',
  PRIMARY KEY (`configuration_group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=74 DEFAULT CHARSET=utf8;

/*Table structure for table `counter` */

DROP TABLE IF EXISTS `counter`;

CREATE TABLE `counter` (
  `startdate` char(8) DEFAULT NULL,
  `counter` int(12) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `counter_history` */

DROP TABLE IF EXISTS `counter_history`;

CREATE TABLE `counter_history` (
  `month` char(8) DEFAULT NULL,
  `counter` int(12) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `countries` */

DROP TABLE IF EXISTS `countries`;

CREATE TABLE `countries` (
  `countries_id` int(11) NOT NULL AUTO_INCREMENT,
  `countries_name` varchar(64) NOT NULL,
  `countries_iso_code_2` char(2) NOT NULL,
  `countries_iso_code_3` char(3) NOT NULL,
  `address_format_id` int(11) NOT NULL,
  PRIMARY KEY (`countries_id`),
  KEY `IDX_COUNTRIES_NAME` (`countries_name`)
) ENGINE=MyISAM AUTO_INCREMENT=240 DEFAULT CHARSET=utf8;

/*Table structure for table `customers` */

DROP TABLE IF EXISTS `customers`;

CREATE TABLE `customers` (
  `customers_id` int(11) NOT NULL AUTO_INCREMENT,
  `customers_gender` char(1) NOT NULL,
  `customers_firstname` varchar(32) NOT NULL,
  `customers_lastname` varchar(32) NOT NULL,
  `customers_dob` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `customers_email_address` varchar(96) NOT NULL,
  `customers_default_address_id` int(11) DEFAULT NULL,
  `customers_telephone` varchar(32) NOT NULL,
  `customers_fax` varchar(32) DEFAULT NULL,
  `customers_password` varchar(40) NOT NULL,
  `customers_newsletter` char(1) DEFAULT NULL,
  PRIMARY KEY (`customers_id`),
  KEY `idx_customers_email_address` (`customers_email_address`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `deliver_goods` */

DROP TABLE IF EXISTS `deliver_goods`;

CREATE TABLE `deliver_goods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned DEFAULT NULL COMMENT '订单ID',
  `order_sn` varchar(20) DEFAULT NULL COMMENT '订单编号',
  `goods_id` int(11) unsigned DEFAULT NULL COMMENT '商品ID',
  `agent_id` int(11) unsigned DEFAULT '0' COMMENT '代理ID(收货人)',
  `code` varchar(20) DEFAULT NULL COMMENT '条形码号码',
  `code_type` tinyint(1) DEFAULT '0' COMMENT '条形码类型: 1:大标,2:小标,3:防伪标,4:中标',
  `admin_id` int(11) unsigned DEFAULT '0' COMMENT '发货人ID',
  `web_type` tinyint(1) unsigned DEFAULT '1' COMMENT '网站类型:1:酷兜云商,2:独恋幽草',
  `add_time` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agent_id` (`agent_id`),
  KEY `admin_id` (`admin_id`),
  KEY `code` (`code`),
  KEY `web_type` (`web_type`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='发货表';

/*Table structure for table `encourage` */

DROP TABLE IF EXISTS `encourage`;

CREATE TABLE `encourage` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `is_select` tinyint(1) DEFAULT '2' COMMENT '是否选择作为显示:1:是,2:否',
  `add_time` datetime DEFAULT NULL,
  `edit_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='励志语表';

/*Table structure for table `err_label_code` */

DROP TABLE IF EXISTS `err_label_code`;

CREATE TABLE `err_label_code` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `max_code` bigint(15) unsigned DEFAULT NULL COMMENT '大标签码',
  `min_code` bigint(15) unsigned DEFAULT NULL COMMENT '小标签码',
  `security_code` bigint(17) unsigned DEFAULT NULL COMMENT '防伪码',
  `min_number` mediumint(9) unsigned DEFAULT NULL COMMENT '小标的数量',
  `status` tinyint(1) DEFAULT '0' COMMENT '是否已打印:  0:否,1:是',
  `add_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `min_code` (`min_code`),
  KEY `status` (`status`),
  KEY `max_code` (`max_code`),
  KEY `min_number` (`min_number`),
  KEY `add_time` (`add_time`),
  KEY `security_code` (`security_code`)
) ENGINE=MyISAM AUTO_INCREMENT=200041 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='防伪码错误_标签号码表';

/*Table structure for table `fodder_article` */

DROP TABLE IF EXISTS `fodder_article`;

CREATE TABLE `fodder_article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cate_id` int(10) unsigned DEFAULT NULL COMMENT '分类ID',
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `head_img` varchar(255) DEFAULT NULL COMMENT '文章标题图',
  `likes` mediumint(8) unsigned DEFAULT '0' COMMENT '点赞数',
  `is_show` tinyint(1) unsigned DEFAULT '1' COMMENT '是否显示: 1:是,0:否',
  `reads` mediumint(8) unsigned DEFAULT '0' COMMENT '阅读数',
  `content` text COMMENT '内容',
  `sort` tinyint(3) unsigned DEFAULT NULL COMMENT '排序:数字越小排前面',
  `add_time` int(10) unsigned DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `is_show` (`is_show`),
  KEY `cate_id` (`cate_id`),
  KEY `title` (`title`),
  KEY `sort` (`sort`),
  KEY `likes` (`likes`),
  KEY `reads` (`reads`)
) ENGINE=MyISAM AUTO_INCREMENT=528 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='素材文章';

/*Table structure for table `fodder_category` */

DROP TABLE IF EXISTS `fodder_category`;

CREATE TABLE `fodder_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) DEFAULT NULL COMMENT '分类标题',
  `head_img` varchar(255) DEFAULT NULL COMMENT '分类图标',
  `pid` int(10) unsigned DEFAULT '0' COMMENT '父级ID',
  `cate_lv` tinyint(1) unsigned DEFAULT '1' COMMENT '分类等级',
  `cate_type` tinyint(1) unsigned DEFAULT '1' COMMENT '分类的类型: 1:图片,2:文章,3:朋友圈,4:视频',
  `is_show` tinyint(1) unsigned DEFAULT '1' COMMENT '是否显示: 1:是,0:否',
  `sort` tinyint(3) unsigned DEFAULT NULL COMMENT '排序:数字越小排前面',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `title` (`title`),
  KEY `pid` (`pid`),
  KEY `cate_lv` (`cate_lv`),
  KEY `cate_type` (`cate_type`),
  KEY `is_show` (`is_show`),
  KEY `sort` (`sort`)
) ENGINE=MyISAM AUTO_INCREMENT=259 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='素材分类表';

/*Table structure for table `fodder_picture` */

DROP TABLE IF EXISTS `fodder_picture`;

CREATE TABLE `fodder_picture` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cate_id` int(10) unsigned DEFAULT '0' COMMENT '分类ID',
  `art_id` int(10) unsigned DEFAULT '0' COMMENT '文章ID',
  `original` varchar(255) DEFAULT NULL COMMENT '原图',
  `shrink` varchar(255) DEFAULT NULL COMMENT '缩列图',
  `is_show` tinyint(1) DEFAULT '1' COMMENT '是否显示: 1:是,0:否',
  `sort` tinyint(3) unsigned DEFAULT NULL COMMENT '排序: 数字越小排前面',
  `add_time` int(10) unsigned DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `cate_id` (`cate_id`),
  KEY `is_show` (`is_show`),
  KEY `sort` (`sort`),
  KEY `art_id` (`art_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4167 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='素材图片';

/*Table structure for table `goods` */

DROP TABLE IF EXISTS `goods`;

CREATE TABLE `goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(25) NOT NULL COMMENT '商品编码',
  `title` varchar(200) NOT NULL COMMENT '商品标题',
  `short_name` varchar(20) DEFAULT NULL COMMENT '商品名简称',
  `price` decimal(10,2) DEFAULT NULL COMMENT '商品价格',
  `price1` decimal(10,2) DEFAULT NULL,
  `price2` decimal(10,2) DEFAULT NULL,
  `price3` decimal(10,2) DEFAULT NULL,
  `price4` decimal(10,2) DEFAULT NULL,
  `mark_price` decimal(10,2) NOT NULL COMMENT '市场价',
  `stock_price` decimal(10,2) DEFAULT NULL COMMENT '出厂价',
  `specs` varchar(50) NOT NULL COMMENT '规格',
  `stock` mediumint(9) DEFAULT NULL COMMENT '库存',
  `pic` text NOT NULL COMMENT '图片',
  `descs` text NOT NULL COMMENT '商品详情',
  `auth_id` tinyint(3) unsigned DEFAULT '1' COMMENT '授权书ID',
  `auth_lv` tinyint(1) DEFAULT '1' COMMENT '授权书等级',
  `add_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `title` (`title`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COMMENT='商品表';

/*Table structure for table `information` */

DROP TABLE IF EXISTS `information`;

CREATE TABLE `information` (
  `information_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `information_group_id` int(11) unsigned NOT NULL DEFAULT '0',
  `information_title` varchar(255) NOT NULL DEFAULT '',
  `information_seo_url` varchar(100) NOT NULL,
  `information_title_tag` varchar(100) NOT NULL,
  `information_dec_tag` varchar(255) NOT NULL,
  `information_description` text NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `datetime` datetime NOT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `visible` enum('1','0') NOT NULL DEFAULT '1',
  `language_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`information_id`,`language_id`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;

/*Table structure for table `information_group` */

DROP TABLE IF EXISTS `information_group`;

CREATE TABLE `information_group` (
  `information_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `information_group_title` varchar(64) NOT NULL DEFAULT '',
  `information_group_description` varchar(255) NOT NULL DEFAULT '',
  `sort_order` int(5) DEFAULT NULL,
  `visible` int(1) DEFAULT '1',
  `locked` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`information_group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `invitecode` */

DROP TABLE IF EXISTS `invitecode`;

CREATE TABLE `invitecode` (
  `inviteId` int(10) NOT NULL AUTO_INCREMENT,
  `inviteCode` varchar(40) DEFAULT NULL,
  `name` varchar(80) DEFAULT NULL,
  `agentId` int(10) DEFAULT '0',
  `star` int(2) DEFAULT '0',
  `stat` int(2) DEFAULT '0',
  `line_number` tinyint(1) DEFAULT NULL COMMENT '代理线的数字',
  `top1_id` int(10) unsigned DEFAULT '0' COMMENT '官方上两级ID',
  `top2_id` int(10) unsigned DEFAULT '0' COMMENT '官方上级ID',
  `is_founder` tinyint(1) DEFAULT '2' COMMENT '是否创始人: 1:是,2:否',
  `team_name` varchar(100) DEFAULT NULL COMMENT '团队名称',
  `auth_lv_list` varchar(20) DEFAULT NULL COMMENT '授权证书列表',
  PRIMARY KEY (`inviteId`),
  UNIQUE KEY `code` (`inviteCode`),
  KEY `agentId` (`agentId`)
) ENGINE=MyISAM AUTO_INCREMENT=11865 DEFAULT CHARSET=utf8;

/*Table structure for table `label_code` */

DROP TABLE IF EXISTS `label_code`;

CREATE TABLE `label_code` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `max_code` varchar(13) DEFAULT NULL COMMENT '大标签码',
  `middle_code` varchar(13) DEFAULT NULL COMMENT '中标签码',
  `min_code` varchar(13) DEFAULT NULL COMMENT '小标签码',
  `security_code` varchar(17) DEFAULT NULL COMMENT '防伪码',
  `min_number` tinyint(3) unsigned DEFAULT '0' COMMENT '小标的数量',
  `middle_number` tinyint(3) unsigned DEFAULT '0' COMMENT '中标的数量',
  `status` tinyint(1) DEFAULT '0' COMMENT '是否已打印:  0:否,1:是',
  `add_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `min_code` (`min_code`),
  UNIQUE KEY `security_code` (`security_code`),
  KEY `status` (`status`),
  KEY `max_code` (`max_code`),
  KEY `min_number` (`min_number`),
  KEY `add_time` (`add_time`),
  KEY `middle_code` (`middle_code`)
) ENGINE=InnoDB AUTO_INCREMENT=125001 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='标签号码表';

/*Table structure for table `languages` */

DROP TABLE IF EXISTS `languages`;

CREATE TABLE `languages` (
  `languages_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `code` char(2) NOT NULL,
  `image` varchar(64) DEFAULT NULL,
  `directory` varchar(32) DEFAULT NULL,
  `sort_order` int(3) DEFAULT NULL,
  PRIMARY KEY (`languages_id`),
  KEY `IDX_LANGUAGES_NAME` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

/*Table structure for table `order_goods` */

DROP TABLE IF EXISTS `order_goods`;

CREATE TABLE `order_goods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned DEFAULT NULL COMMENT '订单ID',
  `order_sn` varchar(22) NOT NULL DEFAULT '0' COMMENT '订单编号',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '发货人ID',
  `member_id` int(11) NOT NULL DEFAULT '0' COMMENT '收货人ID',
  `code` varchar(20) DEFAULT NULL COMMENT '条形码',
  `code_type` tinyint(1) DEFAULT '0' COMMENT '条形码类型: 1:大标,2:小标,3:防伪标,4:中标',
  `goods_id` int(8) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `goods_name` varchar(225) DEFAULT '' COMMENT '商品名称',
  `goods_number` tinyint(3) unsigned DEFAULT '1' COMMENT '商品数量',
  `market_price` decimal(10,2) DEFAULT '0.00' COMMENT '市场价格',
  `admin_price` int(10) unsigned DEFAULT '0' COMMENT '发货人价格',
  `member_price` int(10) unsigned DEFAULT '0' COMMENT '收货人价格',
  `is_gift` tinyint(1) unsigned DEFAULT '1' COMMENT '是否赠品:0:否,1是',
  `is_refund` tinyint(1) DEFAULT '1' COMMENT '是否退货:1:否,2:是',
  `goods_profit` mediumint(5) unsigned DEFAULT '0' COMMENT '产品利润',
  `goods_total_profit` int(10) unsigned DEFAULT '0' COMMENT '产品总利润',
  `year` mediumint(4) unsigned DEFAULT NULL COMMENT '年',
  `month` tinyint(2) unsigned DEFAULT NULL COMMENT '月',
  `day` tinyint(2) DEFAULT NULL COMMENT '日',
  `add_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `goods_id` (`goods_id`),
  KEY `code` (`code`),
  KEY `order_id` (`order_id`),
  KEY `admin_id` (`admin_id`),
  KEY `member_id` (`member_id`),
  KEY `is_refund` (`is_refund`),
  KEY `year` (`year`),
  KEY `month` (`month`),
  KEY `day` (`day`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `order_info` */

DROP TABLE IF EXISTS `order_info`;

CREATE TABLE `order_info` (
  `order_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `order_sn` varchar(22) NOT NULL COMMENT '订单号',
  `admin_id` int(10) unsigned DEFAULT '0' COMMENT '管理员ID(发货人)',
  `admin_name` varchar(20) DEFAULT NULL COMMENT '发货人',
  `admin_lv` tinyint(1) DEFAULT '0' COMMENT '发货人代理等级',
  `order_status` tinyint(1) unsigned DEFAULT '1' COMMENT '订单状态: 1:发货,2:退货',
  `shipping_status` tinyint(1) unsigned DEFAULT '0' COMMENT '发货状态:',
  `pay_status` tinyint(1) unsigned DEFAULT '0' COMMENT '付款状态:',
  `member_id` int(10) unsigned DEFAULT '0' COMMENT '收货人ID',
  `member_name` varchar(20) NOT NULL DEFAULT '' COMMENT '收货人姓名',
  `member_lv` tinyint(1) unsigned DEFAULT '0' COMMENT '收货人代理等级',
  `country` smallint(5) unsigned DEFAULT '0' COMMENT '国家',
  `province` smallint(5) unsigned DEFAULT '0' COMMENT '省份',
  `city` smallint(5) unsigned DEFAULT '0' COMMENT '城市',
  `district` smallint(5) unsigned DEFAULT '0' COMMENT '地区',
  `address` varchar(255) DEFAULT '' COMMENT '详细地址',
  `zipcode` varchar(60) DEFAULT '' COMMENT '邮编',
  `tel` varchar(60) DEFAULT '' COMMENT '电话',
  `mobile` varchar(60) DEFAULT '' COMMENT '手机号码',
  `best_time` int(11) DEFAULT '0' COMMENT '送货时间',
  `shipping_id` tinyint(3) DEFAULT '0' COMMENT '物流ID',
  `shipping_name` varchar(120) DEFAULT '' COMMENT '物流名称',
  `pay_id` tinyint(3) DEFAULT '0' COMMENT '支付ID',
  `pay_name` varchar(120) DEFAULT '' COMMENT '支付名称',
  `shipping_fee` decimal(10,2) DEFAULT '0.00' COMMENT '物流费',
  `pay_fee` decimal(10,2) DEFAULT '0.00' COMMENT '支付金额',
  `order_total_money` int(10) unsigned DEFAULT '0' COMMENT '订单总金额',
  `order_total_profit` int(10) unsigned DEFAULT '0' COMMENT '订单销售总利润',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  `confirm_time` int(10) unsigned DEFAULT '0' COMMENT '确认时间',
  `pay_time` int(10) unsigned DEFAULT '0' COMMENT '支付时间',
  `shipping_time` int(10) unsigned DEFAULT '0' COMMENT '发货时间',
  `collect_time` int(10) unsigned DEFAULT '0' COMMENT '收货时间',
  `pay_account` varchar(100) DEFAULT NULL COMMENT '支付账户',
  `pay_sn` varchar(30) DEFAULT NULL COMMENT '第三方支付订单号',
  `third_pay` decimal(10,2) DEFAULT NULL COMMENT '第三方支付金额',
  `goods_total_stock` mediumint(9) DEFAULT '0' COMMENT '订单商品总数量',
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_sn` (`order_sn`),
  KEY `order_status` (`order_status`),
  KEY `shipping_status` (`shipping_status`),
  KEY `pay_status` (`pay_status`),
  KEY `shipping_id` (`shipping_id`),
  KEY `pay_id` (`pay_id`),
  KEY `collect_time` (`collect_time`),
  KEY `admin_id` (`admin_id`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

/*Table structure for table `security_check_log` */

DROP TABLE IF EXISTS `security_check_log`;

CREATE TABLE `security_check_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` bigint(12) DEFAULT NULL COMMENT '防伪码',
  `add_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '查询时间',
  PRIMARY KEY (`id`),
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='防伪查询记录表';

/*Table structure for table `sessions` */

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `sesskey` varchar(32) NOT NULL,
  `expiry` int(11) unsigned NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`sesskey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `specials` */

DROP TABLE IF EXISTS `specials`;

CREATE TABLE `specials` (
  `specials_id` int(11) NOT NULL AUTO_INCREMENT,
  `products_id` int(11) NOT NULL,
  `specials_new_products_price` decimal(15,4) NOT NULL,
  `specials_date_added` datetime DEFAULT NULL,
  `specials_last_modified` datetime DEFAULT NULL,
  `expires_date` datetime DEFAULT NULL,
  `date_status_change` datetime DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`specials_id`),
  KEY `idx_specials_products_id` (`products_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

/*Table structure for table `user_com` */

DROP TABLE IF EXISTS `user_com`;

CREATE TABLE `user_com` (
  `userId` int(8) NOT NULL AUTO_INCREMENT,
  `userName` varchar(80) NOT NULL,
  `password` varchar(80) NOT NULL,
  `companyName` varchar(120) NOT NULL,
  `address` varchar(200) NOT NULL,
  `permisson` int(2) NOT NULL DEFAULT '0',
  `score` int(8) NOT NULL DEFAULT '0',
  `valid_date` int(24) DEFAULT NULL,
  `codeqy` int(24) DEFAULT NULL,
  `qrcodeurl` varchar(200) DEFAULT NULL,
  `nofcode` text,
  `agentRule` text,
  `agent_notice` varchar(225) DEFAULT NULL,
  `agent_guoqi_notice` varchar(225) DEFAULT NULL,
  `agent_hei_notice` varchar(225) DEFAULT NULL,
  `agent_nors_notice` varchar(225) DEFAULT NULL,
  PRIMARY KEY (`userId`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
