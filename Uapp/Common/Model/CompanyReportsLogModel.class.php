<?php
namespace Common\Model;
use Think\Model;

//公司每月分润总额报表
class CompanyReportsLogModel extends Model {
	//定义数据表字段
//	protected $fields = array();
        
        //数据表
        protected $trueTableName = 'company_reports_log'; 
        
	//调用配置文件中的数据库配置    
	//protected $connection = 'DB_CONFIG1';

	// 新增数据的时候允许写入name和email字段    
//	protected $insertFields = 'name,email'; 

	// 编辑数据的时候只允许写入email字段
	//protected $updateFields = 'email'; 

	//字段映射
	// protected $_map = array(         
	// 	'name' =>'username', // 把表单中name映射到数据表的username字段         
	// 	'mail'  =>'email', // 把表单中的mail映射到数据表的email字段     
	// );

	//自动验证规则
//	protected $_validate = array(     
//		array('verify','require','验证码必须！'), //默认情况下用正则进行验证     
//		array('name','','帐号名称已经存在！',0,'unique',1), // 在新增的时候验证name字段是否唯一     
//		array('value',array(1,2,3),'值的范围不正确！',2,'in'), // 当值不为空的时候判断是否在一个范围内     
//		array('repassword','password','确认密码不正确',0,'confirm'), // 验证确认密码是否和密码一致     
//		array('password','checkPwd','密码格式不正确',0,'function'), // 自定义函数验证密码格式   
//	);

	//自动完成,设置默认值
//	protected $_auto = array (          
//		array('status','1'),  // 新增的时候把status字段设置为1         
//		array('password','md5',3,'function') , // 对password字段在新增和编辑的时候使md5函数处理         
//		array('name','getName',3,'callback'), // 对name字段在新增和编辑的时候回调getName方法         
//		array('update_time','time',2,'function'), // 对update_time字段在更新的时候写入当前时间戳     
//	);
        
        /*
         * 获取详情
         * $where 查询条件
         * $field 查询字段
         * $cache 缓存设置
         */
        public function getDetail($where=array(),$field=array('field'=>array(),'is_opposite'=>false),$cache=array('key'=>false,'expire'=>null,'cache_type'=>null)){
            if($cache === TRUE){
                $info = $this->where($where)
                        ->cache($cache)
                        ->field($field['field'],$field['is_opposite'])
                        ->find();
            }else{
                $info = $this->where($where)
                        ->cache($cache['key'],$cache['expire'],$cache['cache_type'])
                        ->field($field['field'],$field['is_opposite'])
                        ->find();
            }
          
            return $info;
        }
        
        /*
         * 获取列表
         * $where 查询条件
         * $limit 限制数量
         * $page 分页数
         * $order 排序
         * $field 字段
         * $cache 缓存
         */
        public function getList($where=array(),$limit=10,$page=1,$order='',$field=array('field'=>array(),'is_opposite'=>false),$cache=array('key'=>false,'expire'=>null,'cache_type'=>null)){
            
            if($cache === TRUE){
                $list = $this->where($where)
                        ->cache($cache)
                        ->page($page)
                        ->limit($limit)
                        ->field($field['field'],$field['is_opposite'])
                        ->order($order)
                        ->select();
            }else{
                $list = $this->where($where)
                        ->page($page)
                        ->limit($limit)
                        ->cache($cache['key'],$cache['expire'],$cache['cache_type'])
                        ->field($field['field'],$field['is_opposite'])
                        ->order($order)
                        ->select();
            }
            
            return $list;
        }
        
        /*
         * 获取所有列表
         * $where 查询条件
         * $limit 限制数量
         * $page 分页数
         * $order 排序
         * $field 字段
         * $cache 缓存
         */
        public function getAllList($where=array(),$order='',$field=array('field'=>array(),'is_opposite'=>false),$cache=array('key'=>false,'expire'=>null,'cache_type'=>null)){
            if($cache === TRUE){
                $list = $this->where($where)
                        ->cache($cache)
                        ->field($field['field'],$field['is_opposite'])
                        ->order($order)
                        ->select();
            }else{
                $list = $this->where($where)
                        ->cache($cache['key'],$cache['expire'],$cache['cache_type'])
                        ->field($field['field'],$field['is_opposite'])
                        ->order($order)
                        ->select();
            }
            
            return $list;
        }
        
        /*
         * 获取总条数
         * $where 查询条件
         * $cache 缓存条件
         */
        public function getCount($where=array(),$cache=array('key'=>false,'expire'=>null,'cache_type'=>null)) {
            $count  = $this->where($where)
                        ->cache($cache['key'],$cache['expire'],$cache['cache_type'])
                        ->count();
            
            return $count;
        }
        
        /*
         * 获取数量总和
         */
        public function getSum($where=array(),$field='') {
            if(empty($field)){
                return FALSE;
            }
            
            return $this->where($where)->sum($field);
        }
        
        /*
         *增加数量 
         *$where 条件
         *$field 增加字段名
         *$number 增加数量
         *$lazyTime 延迟执行时间
         * @return boolean
         */
        public function editInc($where=array(),$field = '',$number=0,$lazyTime=0) {
            if(empty($where)){
                return FALSE; 
            }
            
            return $this->where($where)->setInc($field,$number,$lazyTime); 
        }
        
        /*
         *减少数量 
         *$where 条件
         *$field 增加字段名
         *$number 增加数量
         *$lazyTime 延迟执行时间
         * @return boolean
         */
        public function editDec($where=array(),$field = '',$number=0,$lazyTime=0) {
            if(empty($where)){
                return FALSE; 
            }
           
            return $this->where($where)->setDec($field,$number,$lazyTime); 
        }
        
        /*
         * 添加单个
         */
        public function addData($data=array()) {
            $result = FALSE;
            
            // $data 为空时,默认或取POST数据
            if($this->create($data)){    
                $result = $this->add();
            }
            
            return $result;
        }
        
        /*
         * 修改
         */
        public function editData($where=array(),$data=array()) {
            $result = FALSE;
            
            // $data 为空时,默认或取POST数据
            if($this->create($data)){    
                $result = $this->where($where)->save();
            }
            
            return $result;
        }
        
        /*
         * 删除
         * 
         */
        public function delData($where=array()) {
            if(empty($where)){
                return FALSE; 
            }
            return $this->where($where)->delete();
        }
}