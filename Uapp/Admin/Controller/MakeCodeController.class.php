<?php
/*
 * 移动到新系统使用步骤说明:
 * 1.执行saveRedis方法
 * 2.清空数据表(code_prefix): TRUNCATE TABLE code_prefix
 * 3.执行makeCodeSuffix方法
 * 4.执行clearCodeKey方法
 * 
 */
namespace Admin\Controller;
use Think\Controller;
class MakeCodeController extends CommonController {
    
    const MaxCommonCodeKey = 1000000; //最大公共码键值
    protected $codePreKey = array(1=>'maxCodeInfoPre',2=>'minCodeInfoPre',3=>'securityCodeInfoPre',4=>'midCodeInfoPre',); //大标,中标,小标,防伪标缓存键值
    protected $codeSurplusKey = array(1=>'maxCodeSurplusPre',2=>'minCodeSurplusPre',3=>'securityCodeSurplusPre',4=>'midCodeSurplusPre'); //大标,中标,小标,防伪标,剩余数量缓存键值
    
    public function __construct() {
        parent::__construct();
        
        ini_set('memory_limit', '8024M');
    }

        //标签列表
    public function index(){
       
        $LabelCode = D('LabelCode');
        $post = I('post.');
        $code_type = $post['code_type'];
        $code_value = $post['code_value'];
        $status = $post['status'];
        $min_number = $post['min_number'];
        $start_time = $post['start_time'];
        $end_time = $post['end_time'];
     
        $page = I('p');
        $limit = 15;
       
        $where = array();
        $code ? $where['code'] = $code : '';
        $code_value ? $where[$code_type] = $code_value : '';
        is_numeric($status) ? $where['status'] = $status : '';
        $min_number ? $where['min_number'] = $min_number : '';
         
        $count      = $LabelCode->where($where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数
      
        $show       = $Page->show();// 分页显示输出
     
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $LabelCode->getList($where,$limit,$page,'status',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        $this->assign('post',$post);
        $this->assign('list',$list);  //赋值数据集
        $this->assign('page',$show);// 赋值分页输出
       
        $this->display('list');
//        dump(microtime());
    }
    
    //生成新的单个记录
    public function newMakeOne() {
        ini_set("max_execution_time", 0);
//        dump(md5('zhou2016.'));die;
//        $start_num = 1200000; //大
//        $start_num = 2800000;   //中
//        $start_num = 4200000;   //小
        $start_num = 7800000;   //防伪
        
        for($i=1;$i<=1000000;$i++){
            $data[$i]  = $start_num+$i;
        }
     
        shuffle($data);
//        $CommonCode = D('CommonCodeMax');  
//        $CommonCode = D('CommonCodeMiddle');
//        $CommonCode = D('CommonCodeMin');
        $CommonCode = D('CommonCodeSecurity');
        
        foreach ($data as $k => $v) {
            $k = $k+1;
            $savecode[] = array('code'=>$v);
            $mod = $k%10000 ;
            if($k > 0 && $mod == 0){
                $result = $CommonCode->addAll($savecode);
                unset($savecode);
            }
        }
        
        dump('生成公共数据成功_'.$start_num);
        return false;
        
    }
    
    //将共用数据添加到redis中
    public function saveRedis() {
        
        ini_set("max_execution_time", 0);
       
        $type_code_list = array(1,2,3,4);
        $code_type = I('type');
        $id = I('id');
        
        if($code_type && $id){
            dump(S('commoncode_'.$code_type.'_'.$id));die;
        }
//        if(!in_array($code_type, $type_code_list)){
//            dump('请选择类型');die;
//        }
//        foreach ($type_code_list as $code_type) {
            G('begin');
            switch ($code_type) {
                case 1:  
                    $CommonCode = D('CommonCodeMax');
                    break;
                case 4: 
                    $CommonCode = D('CommonCodeMiddle');
                    break;
                case 2:  
                    $CommonCode = D('CommonCodeMin');
                    break;
                case 3:  
                    $CommonCode = D('CommonCodeSecurity');
                    break;
            }

            $list = $CommonCode->select();

            if($list){
                foreach ($list as $k => $v) {
                    S('commoncode_'.$code_type.'_'.$v['id'],$v['code']);
                }
            }
            
            G('end');
            dump('成功将公共数据保存到redis中_'.$code_type.'_'.G('begin','end',6).'s');

//        }
    }
    
    //生成初始化大标,中标,小标,防伪标后缀数据
    public function makeCodeSuffix() {
        $CodePrefix = D('CodePrefix'); //code_type前缀类型:1:大标,2:小标:3:防伪标,4:中标
//        $CodePrefix->query('TRUNCATE TABLE code_prefix');
        $type_code_list = array(1,4,2,3);
        $pre_code_list = array(
                                array('pre'=>1001,'section'=>array(1200001,2200000)),
                                array('pre'=>1001,'section'=>array(2800001,3800000)),
                                array('pre'=>1001,'section'=>array(4200001,5200000)),
                                array('pre'=>145561696,'section'=>array(7800001,8800000))
                        );
        
        foreach ($pre_code_list as $k => $v) {
            $savedata['start_section'] = $v['section'][0];
            $savedata['end_section'] = $v['section'][1];
            $savedata['code_type'] = $type_code_list[$k];
            $savedata['code_pre'] = $v['pre'];
            $savedata['code_num'] = 1;
            $savedata['surplus'] = self::MaxCommonCodeKey;
            $savedata['amount'] = self::MaxCommonCodeKey;
           
            $CodePrefix->addData($savedata);
        }
        
        dump('生成初始化数据前缀成功');
    }
    
    //清空大标,小标,防伪标缓存键值与剩余数量缓存键值
    public function clearCodeKey() {
        $codePreKey = $this->codePreKey; //大标,小标,防伪标缓存键值
        $codeSurplusKey = $this->codeSurplusKey; //大标,小标,防伪标,剩余数量缓存键值
        foreach ($codePreKey as $ck => $cv) {
            
            S($codePreKey[$ck],NULL);
            S($codeSurplusKey[$ck],NULL);
        }
        
//        dump('清除缓存成功');
    }
    
    //显示生成标签的页面
    public function makeCodeView() {
        $this->display('makeCodeView');
    }
    
    //生成大标,小标,防伪码,中标
    public function makeTrueCode() {
        ini_set("max_execution_time", 0);
        
        $return = array('status'=>0,'msg'=>'','result'=>'');
        
        //标签总数量
        $total_number = I('total');
        
        //获取一组大标对应一组小标的数量
        $minCodeNumber = I('min_num');
        
        if(empty($total_number) || empty($minCodeNumber)){
            $return['msg'] = '请输入标签总数量与小标的数量';
            $this->ajaxReturn($return,'json');
        }
        
        //获取大标生成的组数
        $maxCodeNumber = ceil($total_number/$minCodeNumber);
//        dump($maxCodeNumber);
        $CodePrefix = D('CodePrefix'); //code_type前缀类型:1:大标,2:小标:3:防伪标
        $LabelCode = D('LabelCode');
        $CodePrefix->startTrans(); //开启事务
       
        //获取大标数量
        $listKey = 0;
        $addNumber = 0;
        $maxCodeKeyNumber = 0;
        $minCodeKeyNumber = 0;
        $midCodeKeyNumber = 0;
        
        for($i=1;$i <= $maxCodeNumber;$i++){
            $newMaxInfo = $this->getMaxCode($maxCodeKeyNumber,1);
            $maxCodeKeyNumber = $newMaxInfo['key'];
            $maxCode = $newMaxInfo['code'];
            $maxCodeKeyNumber++;
            
            
            //获取小标与防伪标的数量
            $min_start_num = 0;
            for($mk=1;$mk <= $minCodeNumber;$mk++){
               
                $addData['max_code'] = $maxCode;
                
                if($minCodeNumber == 100){
                    $fenzuNum = 2; //一个大标对应中标的数量
                    $midNum = $minCodeNumber/$fenzuNum;
                    $modNum = $midNum*$min_start_num+1;
                    $midMod48 = $mk%$modNum;
                   
                    if($mk==0 || $midMod48 == 0){
                        $min_start_num++;
                        $midCodeKeyNumber++;
                        $newMidInfo = $this->getMaxCode($midCodeKeyNumber,4);
                        $midCodeKeyNumber = $newMidInfo['key'];
                        $middleCode = $newMidInfo['code'];
                    }
                }
                
                $addData['middle_code'] = $middleCode;
                $newMinInfo = $this->getMaxCode($minCodeKeyNumber,2);
                
                $addData['min_code'] = $newMinInfo['code'];
                $newSecurityInfo = $this->getMaxCode($minCodeKeyNumber,3);
                
                $minCodeKeyNumber = $newMinInfo['key'];
                
                $addData['security_code'] = $newSecurityInfo['code'];
                $addData['status'] = 0;
                $addData['min_number'] = $minCodeNumber;
                $addData['middle_number'] = $midNum ? $midNum : 0;
                
//                $mid_list[$middleCode][] = $mk;
//                $datalist[$mk] = $addData;
                $result = $LabelCode->addData($addData);
                if($result){
                    $addNumber++;
                }
                $listKey++;
                $minCodeKeyNumber++;
            }
            
        }
//        dump($mid_list);
//        dump($datalist);
//        die;
        //修改大标剩余数量
        $this->editCodeNumber(1,$CodePrefix,$maxCodeNumber);

        //修改小标剩余数量
        $this->editCodeNumber(2,$CodePrefix,$maxCodeNumber,$minCodeNumber);

        //修改防伪标剩余数量
        $this->editCodeNumber(3,$CodePrefix,$maxCodeNumber,$minCodeNumber);
        
        //修改中标剩余数量
        if($fenzuNum){
            $this->editCodeNumber(4,$CodePrefix,$maxCodeNumber,$fenzuNum);
        }
            
        if($listKey == $addNumber && $addNumber > 0){
            
            $return = array('status'=>1,'msg'=>'生成标签成功!','result'=>'');
            $CodePrefix->commit(); //提交事务
        }else{
            $this->clearCodeKey(); //清除缓存
            
            $return['msg'] = '生成标签失败!';
            $CodePrefix->rollback(); //事务回滚
        }
        
        $this->ajaxReturn($return,'json');
    }
    
    //修改大标,小标,防伪标的数量
    public function editCodeNumber($code_type = 1,$CodePrefix='',$maxCodeNumber=0,$minCodeNumber=0) {
        
        $maxSurplusNuber = S($this->codeSurplusKey[$code_type]);
       
        if(in_array($code_type,array(2,3,4))){
//            if($code_type == 4){
//                dump($maxSurplusNuber.'__');
//            }
            $maxSurplusNuber = $maxSurplusNuber ? ($maxCodeNumber*$minCodeNumber-$maxSurplusNuber) : $maxCodeNumber*$minCodeNumber;
            S($this->codeSurplusKey[$code_type],NULL); //清空剩余数量
        }else{
            $maxSurplusNuber = $maxSurplusNuber ? $maxSurplusNuber : $maxCodeNumber;
        }
        
        $maxInfo = $this->getPreCode($code_type);
        $result = $CodePrefix->editDec(array('id'=>$maxInfo['id']),'surplus',$maxSurplusNuber);
        
        $maxInfo['surplus'] = $maxInfo['surplus'] - $maxSurplusNuber;
        S($this->codePreKey[$code_type],serialize($maxInfo));
        
        
    }
    
    //获取大标,小标,防伪标,中标的号码
    public function getMaxCode($key = 0,$code_type = 1) {
        
        $maxInfo = $this->getPreCode($code_type);
        
        $maxSurplus = $maxInfo['surplus'] - $key;
        
        if($maxSurplus <= 0){
            $CodePrefix = D('CodePrefix'); //code_type前缀类型:1:大标,2:小标:3:防伪标,4:中标
            $maxId = $CodePrefix->maxId();
            $savedata['id'] = $maxId + 1;
            $savedata['code_type'] = $code_type;
            $savedata['code_pre'] = $maxInfo['code_pre']+1;
            $savedata['code_num'] = $maxInfo['code_num']+1;
            $savedata['start_section'] = $maxInfo['start_section'];
            $savedata['end_section'] = $maxInfo['end_section'];
            $savedata['surplus'] = self::MaxCommonCodeKey;
            $savedata['amount'] = self::MaxCommonCodeKey;
            
            S($this->codeSurplusKey[$code_type],$maxInfo['surplus']);  //保存上一次剩余数量
            
            $CodePrefix->addData($savedata);
            $editdata['surplus'] = 0;
            $CodePrefix->editData(array('id'=>$maxInfo['id']),$editdata);
            
            $maxInfo = $savedata;
            S($this->codePreKey[$code_type],serialize($maxInfo));
            
            $key=0;
            $maxSurplus = $maxInfo['surplus'] - $key;
        }
        
        $maxcode = $this->getCommonCode($code_type,$maxSurplus);
        $codeNumber = $maxInfo['code_pre'].$maxcode;
        
//        if($code_type == 4){
//            dump($maxInfo['code_pre'].'__'.$maxSurplus.'__'.$maxcode.'__'.$key);
//        }
        
        $arr = array('code'=>$codeNumber,'key'=>$key);
        return $arr;
       
    }
   
    //获取前缀号码
    public function getPreCode($code_type=1) {
        $codePreKey = $this->codePreKey[$code_type];
        $maxInfo = S($codePreKey);
        
        if(!$maxInfo){
            $CodePrefix = D('CodePrefix'); //code_type前缀类型:1:大标,2:小标:3:防伪标
            $maxwhere['code_type'] = $code_type;
            $maxwhere['surplus'] = array('GT',0);
            $maxInfo = $CodePrefix->getDetail($maxwhere,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null),'id DESC');
            S($codePreKey,serialize($maxInfo));
        }else{
            $maxInfo = unserialize($maxInfo);
            
        }
        
        return $maxInfo;
    }
    
    //获取公共号码 code_type前缀类型:1:大标,2:中标,3:小标:4:防伪标,
    /**
     * 
     * @param type $code_type //标签类型
     * @param type $key //标签键值
     * 
     */
    public function getCommonCode($code_type=1,$key=0) {
       
        $code =  S('commoncode_'.$code_type.'_'.$key);
        
        if(empty($code)){
            switch ($code_type) {
                case 1:  
                    $CommonCode = D('CommonCodeMax');
                    break;
                case 2: 
                    $CommonCode = D('CommonCodeMiddle');
                    break;
                case 3:  
                    $CommonCode = D('CommonCodeMin');
                    break;
                case 4:  
                    $CommonCode = D('CommonCodeSecurity');
                    break;
            }
            
            $codeInfo = $CommonCode->find($key); 
            $code = $codeInfo['code'];
            S('commoncode_'.$code_type.'_'.$key,$code);
        }
        
        return $code;
    }
    
    //导出已生成标签的数据excel页面
    public function exportCodeView() {
        $this->display('exportCodeView');
    }
    
    //导出已生成标签的数据excel
    public function exportCode() {
        ini_set("max_execution_time", 0);
        
        $min_number = I('m_num'); //一拖几的数量
        $status = I('status',0); //0:未打印,1:已打印
        $add_time = I('add_time'); //默认只打印今天的数据
        $add_time = $add_time ? $add_time : date('Y-m-d');
        
        if(empty($min_number)){
            dump('请输入拖数的数量');die;
        }
        
        $LabelCode = D('LabelCode');
        is_numeric($status) ? $where['status'] = $status : '';
        $where['min_number'] = $min_number ;
        $where['add_time'] = array('EGT',$add_time);
//        $where['id'] = array(array('ELT',520104),array('GT',420072),'and'); 
//       $data = $LabelCode->where($where)->count();
//       dump($data);die;
        $data = $LabelCode->where($where)->field(array('id','max_code','middle_code','min_code','security_code'))->order('id ASC')->select();
//         $data = $LabelCode->where($where)->limit(5)->field(array('id','max_code','middle_code','min_code','security_code'))->order('id ASC')->select();
//        dump($data);die;
        if($status != 1){
            $editData['status'] = 1;
            $LabelCode->editData($where,$editData);
        }
        
//        if($list){
//            foreach ($list as $k => $v) {
//                $data[$k][] = $v['id'];
//                $data[$k][] = $v['max_code'];
//                $data[$k][] = $v['min_code'];
//                $data[$k][] = $v['security_code'];
//            }
//        }
//        dump($data);die;
        $config = array(
            'fields'=>array('编号','大标签','中标签','小标签','防伪标'),//导入/导出文件字段[导入时为数据字段,导出时为字段标题]
             'data'=>$data, //导出Excel的数组
             'savename'=>date('Y-m-d_H_I_s').'_一拖'.$min_number,
             'title'=>'一拖'.$min_number,     //导出文件栏目标题
             'suffix'=>'xlsx',//文件格式
    	   );
        
    	$Excel = new \Common\Library\Excel($config);
        $Excel::export($data);
        
    }

    //生成一万每组存储
    public function makeTenThousand() {
        for($i=1;$i<=1000000;$i++){
            $data[$i]  = 1325300+$i;
        }
     
        shuffle($data);
        foreach ($data as $k => $v) {
            $k = $k+1;
            $new_data[] = $v;
            $mod = $k%10000 ;
            if($k > 0 && $mod == 0){
                $savecode[] = array('code'=>serialize($new_data));
                unset($new_data);
            }
        }

        $CommonCode = D('CommonCode');
        foreach ($savecode as $k=>$v){
            $result = $CommonCode->add($savecode[$k]);
        }
        dump($result);
        return FALSE;
    }
    
    //因为导出excel时防伪码后面的数字做了四舍五入,,所以做多一个表查询记录
    public function newMakeSecurityCode() {
        ini_set("max_execution_time", 0);
        $ErrLabelCode = M('ErrLabelCode');
       
        $LabelCode = D('LabelCode');
        $list = $LabelCode->where(array('min_number'=>48))->select();
//        $list = $LabelCode->where(array('min_number'=>12))->limit(5)->select();
//        dump($list);
        foreach ($list as $k=>$v){
            $last_number = substr($v['security_code'],-1);
            if($last_number >= 5){
                $security_code = substr($v['security_code'],-7)+10-$last_number;
            }else{
                $security_code = substr($v['security_code'],-7)-$last_number;
            }
            
            
            $new_list = $v;
            $new_list['security_code'] = '145561693'.$security_code;
          
            $ErrLabelCode->add($new_list);
          
        }
//        dump($new_list);
    }
    
    
}