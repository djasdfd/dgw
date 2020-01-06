<?php
namespace app\company\model;

use think\Model;
class SearchModel extends Model
{
    public function addHistory(string $content,int $user){
        $has = $this->get(['content'=>$content,'account_id'=>$user]);
        if(!empty($has)){
            return true;
        }
        $this->save(['content'=>$content,'account_id'=>$user,'create_time'=>time()]);
        return true;
    }


}