<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FormMinat extends Model
{
    protected $table = 'form_minats';
    protected $fillable = ['form_date', 'form_no', 'province', 'city', 'district', 'village', 'mu_no', 'target_area', 'program_year', 'user_id', 'created_at', 'updated_at', 'is_verified', 'verified_by'];
    
    public function scopeMaxno($query)
    {
        $year=substr(date('Y'), 2);
        $queryMax =  $query->select(DB::raw('SUBSTRING(`form_no` ,5) AS kd_max'))
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

        return 'SPR-'.$kd_fix;
    }
}
