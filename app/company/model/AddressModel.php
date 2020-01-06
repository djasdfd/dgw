<?php
namespace app\company\model;

use think\Model;
class AddressModel extends Model
{
    public function getListByPid(int $pid){
        $list = cache('list_'.$pid,'',null,'address');
        if(!$list){
            $list = $this->where(['pid'=>$pid])->field('id,name')->select()->toArray();
            cache('list_'.$pid,$list,null,'address');
        }
        return empty($list)?[]:$list;
    }

    public function getCity(){
        $list = $this->where(['level'=>2])->field('id,name,first')->order('first ASC,id ASC')->select()->toArray();
        $group = array();
        $group['hot']['key'] = 'hot';
        $group['hot']['data'] = [
            array('id'=>861,'name'=>'苏州市','first'=>'S'),
        ];
        foreach ($list as $key => $value){
            empty($group[$value['first']]['key']) && $group[$value['first']]['key'] = $value['first'];
            $group[$value['first']]['data'][] = $value;
        }
//        halt($group);
        return $group;
    }

    public function getIdByName(String $name){
        return $this->where(array('level'=>2,'name'=>array('like','%'.$name.'%')))->value('id');
    }

    public function getNameById(int $id){
        $data = cache('name_'.$id,'',null,'address');
        if(!$data){
            $data =$this->where('id',$id)->value('name');
            cache('name_'.$id,$data,null,'address');
        }
        return empty($data)?'':$data;
    }
}