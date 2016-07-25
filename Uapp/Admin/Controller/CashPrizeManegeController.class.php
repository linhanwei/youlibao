<?php

namespace Admin\Controller;
use Think\Controller;
/**
 * 代理兑奖管理
 *
 * @author Administrator
 */
class CashPrizeManegeController extends CommonController{
    
    private $CashPrizeLog;
    
    //列表
    public function index() {
        $code = I('code');
        $agent_id = I('aid');
        $page = I('p',1);
        $limit = 15;
        
        if(empty($agent_id)){
            exit('<h4>请选择代理</h4>');
        }
        
        $where['agent_id'] = $agent_id;
        if($code){
            $where['prize_code'] = $code;
        }
        
        $CashPrizeLog = D('CashPrizeLog');
        
        $count      = $CashPrizeLog->where($where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        
        $list = $CashPrizeLog->where($where)->page($page)->limit($limit)->order('add_time DESC')->select();
        
        $this->assign('agent_id',$agent_id);
        $this->assign('code',$code);
        $this->assign('list_count',$count);
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        
        $this->display();
    }
    
    //兑奖
    public function cashPrize() {
        
        $return = array('status'=>0,'msg'=>'兑奖失败','result'=>'');
        $agent_id = I('aid');
        
        if(empty($agent_id)){
            $return['msg'] = '请选择代理';
            $this->ajaxReturn($return,'json');
        }
        
        //获取代理信息
        $AgentRelation = D('AgentRelation');
        
        $ar_where['member_id'] = $agent_id;
        $ar_info = $AgentRelation->getDetail($ar_where);
        
        if(empty($ar_info)){
            $return['msg'] = '代理不存在!';
            $this->ajaxReturn($return,'json');
        }
        
        //只能是特约才能兑奖
        if($ar_info['agent_grade'] != 4){
            $return['msg'] = '只有特约才能兑奖!';
            $this->ajaxReturn($return,'json');
        }
        
        //兑奖 开始
        $dataTime = date('Y-m-d H:i:s');
        $Agent = D('Agent');
        $CashPrizeLog = D('CashPrizeLog');
        $prize_count = $CashPrizeLog->getCount(array('agent_id'=>$agent_id),array('key'=>false,'expire'=>null,'cache_type'=>null));

        $CASH_PRIZE_NUMBER = C('CASH_PRIZE_NUMBER');

        $all_sale_total_stock = $Agent->where(array('agentId'=>$agent_id))->getField('all_sale_total_stock');
        $code_number = floor(($all_sale_total_stock - $prize_count*$CASH_PRIZE_NUMBER)/$CASH_PRIZE_NUMBER);
        
        if($code_number > 0){
            $url = C('GET_CASH_PRIZE_CODE_URL');
            $url_params = array('num'=>$code_number,'pw'=>1);
            $url_method = 'GET';

            $return_data = http($url, $url_params, $url_method);

            if($return_data !== FALSE){
                $json_data = json_decode($return_data);
                $return_result = $json_data->result->list;

                if($json_data->status == 1 && !empty($return_result)){

                    foreach ($return_result as $rk => $rv) {
                        $prize_code = $rv->number;

                        if($prize_code){
                            $addResultData[$rk]['agent_id'] = $agent_id;
                            $addResultData[$rk]['prize_code'] = $prize_code;
                            $addResultData[$rk]['is_prize'] = 2; //是否兑奖(1:已兑奖,2:未兑奖)
                            $addResultData[$rk]['get_time'] = $dataTime;
                            $addResultData[$rk]['out_time'] = date('Y-m-d H:i:s',strtotime(' +7 day'));
                            $addResultData[$rk]['add_time'] = $dataTime;
                        }
                    }

                    $addCashResult = $CashPrizeLog->addAll($addResultData);

                    if($addCashResult){
                        $return = array('status'=>1,'msg'=>'兑奖成功!','result'=>'');
                    }else{
                        $return['msg'] = '添加兑奖码失败!';
                    }
                }else{
                    $return['msg'] = '请求兑奖码失败!';
                }
            }else{
                $return['msg'] = '请求兑奖码错误!';
            }
        }else{
            $return['msg'] = '特约发货数量不够!';
        }
        
        //兑奖结束
        
        $this->ajaxReturn($return,'json');
    }
    
    //添加奖励页面
    public function addReward() {
        $this->display('addReward');
    }
    
    //公司奖励列表
    public function showReward() {
        $code = I('code');
        $page = I('p',1);
        $limit = 15;
        
        $where['agent_id'] = 1;
        if($code){
            $where['prize_code'] = $code;
        }
        
        $CashPrizeLog = D('CashPrizeLog');
        
        $count      = $CashPrizeLog->where($where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        
        $list = $CashPrizeLog->where($where)->page($page)->limit($limit)->order('add_time DESC')->select();
        
        $this->assign('agent_id',$agent_id);
        $this->assign('code',$code);
        $this->assign('list_count',$count);
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        
        $this->display('showReward');
    }
    
    //兑奖奖励,只奖励到公司的账号
    public function rewardCashPrize() {
        
        $return = array('status'=>0,'msg'=>'奖励失败','result'=>'');
        
        $number = I('number',0);
        $agent_id = 1; //只能奖励到公司的账号
        
        //只能是特约才能兑奖
        if($number <= 0){
            $return['msg'] = '数量不能为空!';
            $this->ajaxReturn($return,'json');
        }
        
        $this->CashPrizeLog = D('CashPrizeLog');
       
        $result = self::getCashPrizeCode($number,$agent_id);
        
        if($result === TRUE){
            $return = array('status'=>1,'msg'=>'奖励成功','result'=>'');
        }else{
            $return['msg'] = $result;
        }
        
        $this->ajaxReturn($return,'json');
    }
    
    /**
     * 获取兑奖码
     * @param type $number
     * @return boolean|string
     */
    private function getCashPrizeCode($number,$agent_id) {
        $dataTime = date('Y-m-d H:i:s');
        $outDateTime = date('Y-m-d H:i:s',strtotime(' +7 day'));
        $url = C('GET_CASH_PRIZE_CODE_URL');
        $url_params = array('num'=>$number,'pw'=>1);
        $url_method = 'GET';

        $return_data = http($url, $url_params, $url_method);

        if($return_data !== FALSE){
            $json_data = json_decode($return_data);
            $return_result = $json_data->result->list;

            if($json_data->status == 1 && !empty($return_result)){

                foreach ($return_result as $rk => $rv) {
                    $prize_code = $rv->number;

                    if($prize_code){
                        $addResultData[$rk]['agent_id'] = $agent_id;
                        $addResultData[$rk]['prize_code'] = $prize_code;
                        $addResultData[$rk]['is_prize'] = 2; //是否兑奖(1:已兑奖,2:未兑奖)
                        $addResultData[$rk]['get_time'] = $dataTime;
                        $addResultData[$rk]['out_time'] = $outDateTime;
                        $addResultData[$rk]['add_time'] = $dataTime;
                    }
                }

                $addCashResult = $this->CashPrizeLog->addAll($addResultData);

                if($addCashResult){
                    return TRUE;
                }else{
                    $msg = '添加兑奖码失败!';
                }
            }else{
                $msg = '请求兑奖码失败!';
            }
        }else{
            $msg = '请求兑奖码错误!';
        }
        
        return $msg;
    }
    
}
