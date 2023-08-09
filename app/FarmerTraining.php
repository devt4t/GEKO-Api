<?php

namespace App;


use DB;
use Illuminate\Database\Eloquent\Model;

class FarmerTraining extends Model
{
    protected $table = 'farmer_trainings';

    protected $fillable = ['training_no', 'training_date', 'first_material', 'second_material', 'organic_material', 'program_year', 'absent', 'documentation_photo', 'mu_no', 'target_area', 'village', 'field_coordinator', 'ff_no', 'user_id', 'is_dell', 'deleted_by', 'verified_by', 'status', 'created_at', 'updated_at'];

    
    public function scopeMaxno($query)
    {
        $year=substr(date('Y'), 2);
        $queryMax =  $query->select(DB::raw('SUBSTRING(`training_no` ,8) AS kd_max'))
            ->where(DB::raw('MONTH(created_at)'), '=', date('m'))
            ->where(DB::raw('YEAR(created_at)'), '=', date('Y'))
            ->orderBy('training_no', 'asc')
            ->get();
        $arr1 = array();
        if ($queryMax->count() > 0) {
            foreach ($queryMax as $k=>$v)
            {
                $arr1[$k] = (int)$v->kd_max;
            }
            $arr2 = range(1, max($arr1));
            $missing = array_diff($arr2, $arr1);
            if (empty($missing)) {
                $tmp = end($arr1) + 1;
                $kd_fix = sprintf("%04s", $tmp);
            }else{
                $kd_fix = sprintf("%04s", reset($missing));
            }
        }
        else{
            $kd_fix = '0001';
        }
        return 'FTM'.$year.date('m').$kd_fix;
    }
}
