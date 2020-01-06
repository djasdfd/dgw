<?php
namespace app\company\controller;

use cmf\controller\AdminBaseController;
use app\company\model\AccountModel;
use app\company\model\TimeModel;
use think\Db;
class AccountController extends AdminBaseController
{
    private $model_account;

    private $model_time;

    public function __construct()
    {
        parent::__construct();
        $this->model_account = new AccountModel();
        $this->model_time = new TimeModel();
    }

    public function index(){
        $where = array();
        $keywords = request()->param('keywords');
        $sex = request()->param('sex',0,'intval');
        $type = request()->param('type',0,'intval');
        if(isset($keywords) && $keywords != '')
            $where['username|phone'] = ['like','%'.$keywords.'%'];
        if(!empty($sex))
            $where['sex'] = $sex;
        if(!empty($type))
            $where['type'] = $type;
        $data = $this->model_account->where($where)->paginate(10)->each(function($item){
            $item->sex_text = $item->sex == 1?'男':'女';
            $item->type_text = $item->type == 1?'学生工':'社会工';
            return $item;
        });
        return $this->fetch('',['list'=>$data,'page'=>$data->render()]);
    }


    public function salaryRecord(){
        $id_card= request()->param('id_card',-1);
        $data = Db::name('Salary')->where('id_card',$id_card)->select();
        return $this->fetch('',['list'=>$data]);
    }
    public function record(){
        $account_id = request()->param('id',0,'intval');
        $data = $this->model_time->where('account_id',$account_id)->order('create_time DESC')->paginate(10);
        return $this->fetch('',['list'=>$data,'page'=>$data->render()]);
    }

    public function uploadExcel(){
        if(request()->isPost()){
            $upload_time = request()->param('upload_time');
            empty($upload_time) && $this->error('请选择月份');
            //分别获取年月
            list($year,$month) =  explode('-',$upload_time);
            $file = request()->file('excel');
            empty($file) && $this->error('请上传表格');
            header("Content-type:text/html;charset=utf-8");
            vendor('phpoffice.phpexcel.Classes.PHPExcel');
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
            $filename = $_FILES['excel']['tmp_name'];
            $objPHPExcel = $objReader->load($filename);
            $sheet = $objPHPExcel->getSheet(0);
            $rowNum = $sheet->getHighestRow();
            $rowNum<2 && $this->error('数据为空');
            //这里做删除处理
            Db::name('Salary')->where(['year'=>$year,'month'=>$month])->delete();

            for($j=2;$j<=$rowNum;$j++)
            {
                $data['id'] = $objPHPExcel->getActiveSheet()->getCell("A".$j)->getValue();
                $data['name'] = $objPHPExcel->getActiveSheet()->getCell("B".$j)->getValue();
                $data['come_time'] = $objPHPExcel->getActiveSheet()->getCell("C".$j)->getValue();
                $data['base_attendance'] = $objPHPExcel->getActiveSheet()->getCell("D".$j)->getValue();
                $data['really_attendance'] = $objPHPExcel->getActiveSheet()->getCell("E".$j)->getValue();
                $data['base_salary'] = $objPHPExcel->getActiveSheet()->getCell("F".$j)->getValue();
                $data['really_work_salary'] = $objPHPExcel->getActiveSheet()->getCell("G".$j)->getValue();
                $data['day_work_time'] = $objPHPExcel->getActiveSheet()->getCell("H".$j)->getValue();
                $data['day_work_salary'] = $objPHPExcel->getActiveSheet()->getCell("I".$j)->getValue();
                $data['week_work_time'] = $objPHPExcel->getActiveSheet()->getCell("J".$j)->getValue();
                $data['week_work_salary'] = $objPHPExcel->getActiveSheet()->getCell("K".$j)->getValue();
                $data['other_add']  = $objPHPExcel->getActiveSheet()->getCell("L".$j)->getValue();
                $data['salary'] = $objPHPExcel->getActiveSheet()->getCell("M".$j)->getValue();
                $data['work_time'] =  $objPHPExcel->getActiveSheet()->getCell("N".$j)->getValue();
                $data['id_card'] = $objPHPExcel->getActiveSheet()->getCell("O".$j)->getValue();
                $data['year'] = $year;
                $data['month'] = $month;
                $account = $this->model_account->get(['id_card'=>$data['id_card']]);
                if(empty($account))
                    continue;
                Db::name('Salary')->insert($data);
            }
            $this->success('成功');

        }else{
          return  $this->fetch();
        }
    }
function  getAllUserOpenid(){
    $data = Db::name('account')->where('id'>'0')->field('openid')->select();
    $data = json_decode($data,true);
    $data = json_encode($data);
    $data = json_decode($data,true);
    foreach ( $data as $k => $value) {
       $data['openid'] =  $value['openid'];
       /*foreach ($data[$k]['openid'] as $d =>$va){

       }*/
      // var_dump($value['openod']);
    }
    $appId = 'wx941b57eced98c631';
    $secret = '467de1e9df41ec97b50bbbeb78292c01';
    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appId}&secret={$secret}";
    $Result=XiusoftCurl($url,'',0,true);
    $Result = json_decode($Result,true);
    $ACCESS_TOKEN = $Result['access_token'];
    if (!empty($Result)){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appId}&secret={$secret}APPSECRET";
       /* $touser = ["$data[$k]['openid'].','"];*/
    }
    $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $ACCESS_TOKEN;
    $body = $_POST['body'];

    $info = array(
        'touser'=>"[$data[$k]['openid'].',']",
        'msgtype' =>'text',
        'text'=>"$body"
    );
$info = json_encode($info);
$sendinfo = AccountModel::sendCurlInfo($url,$info);
if ($sendinfo){
    $this->success('成功');
}
}
}