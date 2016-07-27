<?php
namespace Admin\Controller;
use Think\Controller;
class GoodsController extends CommonController {
    
    //商品列表
    public function index(){
        $Goods = D('Goods');
        $code = I('code');
        $title = I('title');
        $page = I('p',1);
        $limit = 10;
        
        $where = array();
        $code ? $where['code'] = $code : '';
        $title ? $where['title'] = array('like','%'.$title.'%') : '';
     
        $count      = $Goods->where($where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
      
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $Goods->getList($where,$limit,$page,'add_time',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
     
        $this->assign('list',$list);  //赋值数据集
        $this->assign('page',$show);// 赋值分页输出
       
        $this->display('list');
    }
    
    //添加与更改商品页面
    public function add() {
      
        $goods_id = I('goods_id');
        
        if($goods_id){
            $Goods = D('Goods');
            $where['id'] = $goods_id;
            $goods_info = $Goods->getDetail($where,$field=array('field'=>array(),'is_opposite'=>false),$cache=array('key'=>false,'expire'=>null,'cache_type'=>null));
            $this->assign('info',$goods_info);
            
            //返利情况
            $AgentGoodsProfitRale = D('AgentGoodsProfitRale');
            
            //官方
            $agent1_where['goods_id'] = $goods_id;
            $agent1_where['agent_lv'] = 1;
            $agent1_profit = $AgentGoodsProfitRale->getDetail($agent1_where,$field=array('field'=>array(),'is_opposite'=>false),$cache=array('key'=>false,'expire'=>null,'cache_type'=>null));
            $this->assign('agent1_profit',$agent1_profit);
            
            //大区
            $agent2_where['goods_id'] = $goods_id;
            $agent2_where['agent_lv'] = 2;
            $agent2_profit = $AgentGoodsProfitRale->getDetail($agent2_where,$field=array('field'=>array(),'is_opposite'=>false),$cache=array('key'=>false,'expire'=>null,'cache_type'=>null));
            $this->assign('agent2_profit',$agent2_profit);
            
            //总代
            $agent3_where['goods_id'] = $goods_id;
            $agent3_where['agent_lv'] = 3;
            $agent3_profit = $AgentGoodsProfitRale->getDetail($agent3_where,$field=array('field'=>array(),'is_opposite'=>false),$cache=array('key'=>false,'expire'=>null,'cache_type'=>null));
            $this->assign('agent3_profit',$agent3_profit);
            
            //特约
            $agent4_where['goods_id'] = $goods_id;
            $agent4_where['agent_lv'] = 4;
            $agent4_profit = $AgentGoodsProfitRale->getDetail($agent4_where,$field=array('field'=>array(),'is_opposite'=>false),$cache=array('key'=>false,'expire'=>null,'cache_type'=>null));
            $this->assign('agent4_profit',$agent4_profit);
        }
     
        $this->display();
    }
    
    //添加与更改商品
    public function addSub() {
        $data = I('post.');
        
        $goods_id = $data['id'];
        $saveData['code'] = $data['code'];
        $saveData['title'] = $data['title'];
        $saveData['mark_price'] = $data['mark_price'];
        $saveData['price'] = $data['price'];
        $saveData['stock_price'] = $data['stock_price'];
        $saveData['specs'] = $data['specs'];
        $saveData['stock'] = $data['stock'];
        $saveData['descs'] = $data['editorValue'];
        $saveData['short_name'] = $data['short_name'];
       
        $Goods = D('Goods');
        $return = array('status'=>0,'msg'=>'','result'=>'');
        
        if($goods_id){
            $get_where['id'] = $goods_id;
            $goods_info = $Goods->getDetail($get_where,$field=array('field'=>array('pic'),'is_opposite'=>false),$cache=array('key'=>false,'expire'=>null,'cache_type'=>null));
            
            if($goods_info['code'] == $data['code']){
                $return['msg'] = '商品编码已经存在,不能添加该商品!';
                $this->ajaxReturn($info,'json');
            }
        }
        
        if($_FILES['images']){
            $upload = new \Think\Upload();// 实例化上传类   
            $upload->maxSize   =     3145728 ;// 设置附件上传大小    
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
            $upload->savePath  =      'Goods/'; // 设置附件上传目录    

            // 上传文件     
            $info   =   $upload->upload();    
            if(!$info) {
                // 上传错误提示错误信息      
                $return['msg'] = $upload->getError();
                $this->ajaxReturn($info,'json');
            }else{
                foreach ($info as $pk => $pv) {
                    $pic_save .=   $pv['savepath'].$pv['savename'].';';
                }
                
                $pic_save = $goods_info['pic'].';'.$pic_save;
                $pic_save = trim($pic_save,';');
                $saveData['pic'] = $pic_save;
            }
        }
        
        $Goods->startTrans();
        
        if($goods_id){
            $result = $Goods->editData($get_where,$saveData); 
            $g_restult = 1;
        }else{
            $goods_id = $result = $Goods->addData($saveData); 
            $g_restult = 2;
        }
        
        //公司返利开始
            $AgentGoodsProfitRale = D('AgentGoodsProfitRale');
            
            //官方
            $agent1_data_id = $data['agent1_profit_id'];
            $agent1_data['agent_price'] = $data['agent1_agent_price'];
            $agent1_data['top1_profit'] = $data['agent1_top1_profit'];
            $agent1_data['top2_profit'] = $data['agent1_top2_profit'];
            $agent1_data['agent1_profit'] = 0;
            $agent1_data['agent2_profit'] = 0;
            $agent1_data['agent3_profit'] = 0;
            $agent1_data['agent4_profit'] = 0;
            $agent1_data['goods_id'] = $goods_id;
            $agent1_lv = $agent1_data['agent_lv'] = 1;
            
            if($agent1_data_id){
                $agent1_where['id'] = $agent1_data_id;
                $agent1_result = $AgentGoodsProfitRale->editData($agent1_where,$agent1_data);
            }else{
                $agent1_data_id = $agent1_result = $AgentGoodsProfitRale->addData($agent1_data);
            }
            
            //大区
            $agent2_data_id = $data['agent2_profit_id'];
            $agent2_data['agent_price'] = $data['agent2_agent_price'];
            $agent2_data['top1_profit'] = 0;
            $agent2_data['top2_profit'] = 0;
            $agent2_data['agent1_profit'] = $data['agent2_agent1_profit'];
            $agent2_data['agent2_profit'] = 0;
            $agent2_data['agent3_profit'] = 0;
            $agent2_data['agent4_profit'] = 0;
            $agent2_data['goods_id'] = $goods_id;
            $agent2_lv = $agent2_data['agent_lv'] = 2;
            
            if($agent2_data_id){
                $agent2_where['id'] = $agent2_data_id;
                $agent2_result = $AgentGoodsProfitRale->editData($agent2_where,$agent2_data);
            }else{
                $agent2_data_id = $agent2_result = $AgentGoodsProfitRale->addData($agent2_data);
            }
            
            //总代
            $agent3_data_id = $data['agent3_profit_id'];
            $agent3_data['agent_price'] = $data['agent3_agent_price'];
            $agent3_data['top1_profit'] = 0;
            $agent3_data['top2_profit'] = 0;
            $agent3_data['agent1_profit'] = $data['agent3_agent1_profit'];
            $agent3_data['agent2_profit'] = $data['agent3_agent2_profit'];
            $agent3_data['agent3_profit'] = $data['agent3_agent3_profit'];
            $agent3_data['agent4_profit'] = 0;
            $agent3_data['goods_id'] = $goods_id;
            $agent3_lv = $agent3_data['agent_lv'] = 3;
            
            if($agent3_data_id){
                $agent3_where['id'] = $agent3_data_id;
                $agent3_result = $AgentGoodsProfitRale->editData($agent3_where,$agent3_data);
            }else{
                $agent3_data_id = $agent3_result = $AgentGoodsProfitRale->addData($agent3_data);
            }
            
            //特约
            $agent4_data_id = $data['agent4_profit_id'];
            $agent4_data['agent_price'] = $data['agent4_agent_price'];
            $agent4_data['top1_profit'] = 0;
            $agent4_data['top2_profit'] = 0;
            $agent4_data['agent1_profit'] = 0;
            $agent4_data['agent2_profit'] = 0;
            $agent4_data['agent3_profit'] = 0;
            $agent4_data['agent4_profit'] = 0;
            $agent4_data['goods_id'] = $goods_id;
            $agent4_lv = $agent4_data['agent_lv'] = 4;
            
            if($agent4_data_id){
                $agent4_where['id'] = $agent4_data_id;
                $agent4_result = $AgentGoodsProfitRale->editData($agent4_where,$agent4_data);
            }else{
                $agent4_data_id = $agent4_result = $AgentGoodsProfitRale->addData($agent4_data);
            }
            
        //公司返利结束
        
        if($result || $agent1_result || $agent2_result || $agent3_result || $agent4_result){
            $msg_sult = ($g_restult == 2) ? '添加成功' : '更改成功';
            $return = array('status'=>1,'msg'=>$msg_sult,'result'=>'');
            $Goods->commit();
            
            //重新赋值
            $goodsCacheKey = md5(C('GOODS_INFO').$goods_id);
            $saveData['id'] = $goods_id;
            S($goodsCacheKey,  serialize($saveData));
            
            $agent1CacheKey = md5(C('AGENT_GOODS_PROFIT').$goods_id.'_'.$agent1_lv);
            $agent1_data['id'] = $agent1_data_id;
            S($agent1CacheKey,serialize($agent1_data));
            
            $agent2CacheKey = md5(C('AGENT_GOODS_PROFIT').$goods_id.'_'.$agent2_lv);
            $agent2_data['id'] = $agent2_data_id;
            S($agent2CacheKey,serialize($agent2_data));
            
            $agent3CacheKey = md5(C('AGENT_GOODS_PROFIT').$goods_id.'_'.$agent3_lv);
            $agent3_data['id'] = $agent3_data_id;
            S($agent3CacheKey,serialize($agent3_data));
            
            $agent4CacheKey = md5(C('AGENT_GOODS_PROFIT').$goods_id.'_'.$agent4_lv);
            $agent4_data['id'] = $agent4_data_id;
            S($agent4CacheKey,serialize($agent4_data));
            
        }else{
            $msg_sult = ($g_restult == 2) ? '添加失败' : '更改失败';
            $return['msg'] = $msg_sult;
            $Goods->rollback();
        }
        $this->ajaxReturn($return,'json');
    }
    
    //删除商品
    public function del() {
        $return = array('status'=>0,'msg'=>'','result'=>'');
        
        $goods_id = I('goods_id');
        
        if(!$goods_id){
            $return['msg'] = '请选择商品';
            $this->ajaxReturn($return,'json');
        }
        
        $Goods = D('Goods');
        $where['id'] = $goods_id;
        $result = $Goods->delData($where);
        
        if($result){
            $return['msg'] = '删除成功';
        }else{
            $return['msg'] = '删除失败';
        }
        
        $this->ajaxReturn($return,'json');
        
    }
    
    //删除商品图片
    public function delPic() {
        $goods_id = I('goods_id');
        $pic_url = I('pic_url');
        
        $return = array('status'=>0,'msg'=>'','result'=>'');
        
        if(!$goods_id){
            $return['msg'] = '请选择商品';
            $this->ajaxReturn($return,'json');
        }
        
        $Goods = D('Goods');
        $get_where['id'] = $goods_id;
        $goods_info = $Goods->getDetail($get_where,$field=array('field'=>array('pic'),'is_opposite'=>false),$cache=array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        if($goods_info){
            $pic_list = $goods_info['y_pic_list'];
            foreach ($pic_list as $k => $v) {
                if($pic_url == $v){
                    unset($pic_list[$k]);
                }
            }
        }
        
        $p_list = implode(';', $pic_list);
        $saveData['pic'] = $p_list;
        $result = $Goods->editData($get_where,$saveData); 
        
        if($result){
            //删除图片
            @unlink ('./Uploads/'.$pic_url);
            $return['status'] = 1; 
            $return['msg'] = '删除成功'; 
        }else{
            $return['msg'] = '删除失败'; 
        }
       
        $this->ajaxReturn($return,'json');
    }
    
}