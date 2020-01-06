<?php

namespace app\company\model;



use think\Model;

class CompanyPictureModel extends Model

{

    public function getCompanyPicture(int $company_id):string

    {

        $data = $this->get(['company_id'=>$company_id,'is_top'=>1]);

        $http_type = (request()->isSsl()) ? 'https://' : 'http://';

        $cdn_path = $http_type . $_SERVER['HTTP_HOST'].DS.'static'.DS.'images'.DS.'work.png';

        return empty($data->url)?$cdn_path:$data->url;

    }



    public function getPictureArray(int $company_id):array

    {

        $data = $this->where('company_id',$company_id)->column('url');

        return empty($data)?array():$data;

    }



}