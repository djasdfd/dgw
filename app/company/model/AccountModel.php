<?php

namespace app\company\model;



use think\Model;

class AccountModel extends Model

{

    public function getUserDetail(int $id){

        $data = $this->where('id',$id)->field('avatar,username,sex,age,phone,school,type,score,id_card')->find()->toArray();

        $data['sex_text'] = ($data['sex'] == 1)?'男':'女';

        $data['type_text'] = ($data['type'] == 1)?'学生工':'社会工';

        return $data;

    }
    public function sendCurlInfo($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // 不验证服务器证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)]
        );
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}