<?php
namespace app\company\model;

use think\Model;
class TimeModel extends Model
{
    public function clockList(int $year,int $month,int $user){
        $where['account_id'] = $user;
        $where['year'] = $year;
        $where['month'] = $month;
        $data['detail'] = $this->where($where)->field('id,day,duration')->order('day ASC')->select()->toArray();
        $data['year_total'] = $this->where([
            'account_id'=>$user,
            'year'=>$year
        ])->sum('duration');
        $data['month_total'] = $this->where($where)->sum('duration');
        $work_day = $this->where($where+['is_week'=>0,'duration'=>['gt',8]])->count();
        $work_time = $this->where($where+['is_week'=>0,'duration'=>['gt',8]])->sum('duration');
        $data['work_add'] = $work_time - $work_day*8;
        $data['week_add'] = $this->where($where+['is_week'=>1])->sum('duration');
        return $data;
    }
}