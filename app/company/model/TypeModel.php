<?php
namespace app\company\model;

use think\Model;

class TypeModel extends Model
{
    public function getType(int $type){
        return $this->where(['type'=>$type,'status'=>1])->field('id,name,picture')->select();
    }

    public function getTypeNameArray(string $tagArray):array
    {
        $tags = explode(',',$tagArray);
        $tag = array_map(function($item){
            $item = $this->where('id',$item)->value('name');
            return $item;
        },$tags);
        return $tag;
    }

    public function getTypeNameById(int $id):string
    {
        return $this->where('id',$id)->value('name');
    }
}