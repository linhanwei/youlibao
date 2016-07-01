<?php
/*
 * 励志语管理
 * 
 */
namespace Admin\Controller;
use Think\Controller;
class EncourageManageController extends CommonController {
    
    //列表
    public function index() {
      
        $title = I('title');
        
        $page = I('p',1);
        $limit=10;
        
        $where = array();
        if($title){
            $where['title'] = array('like','%'.$title.'%'); 
        }
        
        $Encourage = D('Encourage');
        
        $count      = $Encourage->getCount($where,array('key'=>false,'expire'=>null,'cache_type'=>null));// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        
        $list = $Encourage->getList($where,$limit,$page,'is_select',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        
        //搜索条件
        $this->assign('title',$title);
       
        $this->assign('list_count',$count);
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        
        $this->display();
    }
    
    //添加或者修改页面
    public function add() {
        $id = I('id');
        
        if($id){
            $Encourage = D('Encourage');
            $info = $Encourage->getDetail(array('id'=>$id));
            $this->assign('info',$info);
        }
        $this->display();
    }
    
    //添加或者修改
    public function addSub() {
        $id = I('id');
        $title = I('title');
        $is_select = I('is_select');
        
        $return = array('status'=>0,'msg'=>'添加失败','result'=>'');
        
        $Encourage = D('Encourage');
        
        $editData['edit_time'] = date('Y-m-d H:i;s');
        $editData['title'] = $title;
        $editData['is_select'] = $is_select;
        
        //如果选择该条信息作为展示,把其他信息全部不展示
        if($is_select == 1){
            $edit_where['id'] = array('GT',0);
            $Encourage->editData($edit_where,array('is_select'=>2));
            
            $cacheKey = md5(C('ENCOURAGE_INFO').$is_select);
            S($cacheKey,NULL);
        }
        
        if($id){
            $return['msg'] = '修改失败';
            $result = $Encourage->editData(array('id'=>$id),$editData);
            
            if($result){
                $return = array('status'=>1,'msg'=>'修改成功','result'=>'');
                
            }
        }else{
        
            $editData['add_time'] = date('Y-m-d H:i;s');
            $result = $Encourage->addData($editData);
            
            if($result){
                $return = array('status'=>1,'msg'=>'添加成功','result'=>'');
            }
        }
        
        $this->ajaxReturn($return,'json');
    }
    
}