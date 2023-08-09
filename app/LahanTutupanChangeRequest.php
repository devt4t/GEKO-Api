<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class LahanTutupanChangeRequest extends Model
{
    protected $table = 'lahan_tutupan_change_requests';
    protected $fillable = ['form_no', 'farmer_no', 'land_area', 'tutupan_lahan_now', 'tutupan_lahan_new', 'reason', 'lahan_no', 'year_active', 'program_year', 'submit_date_ff', 'submit_date_fc', 'is_verified', 'verified_by', 'mu_no', 'target_area', 'created_at', 'updated_at', 'is_dell', 'tutupan_photo1', 'tutupan_photo2', 'tutupan_photo3', 'user_id'];
    
    public function scopeMaxno($query)
    {
        $year=substr(date('Y'), 2);
        $queryMax =  $query->select(DB::raw('SUBSTRING(`form_no` ,8) AS kd_max'))
            ->where(DB::raw('MONTH(created_at)'), '=', date('m'))
            ->where(DB::raw('YEAR(created_at)'), '=', date('Y'))
            ->orderBy('form_no', 'asc')
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
        return 'TLH'.$year.date('m').$kd_fix;
    }
}