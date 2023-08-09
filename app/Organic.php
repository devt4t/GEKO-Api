<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Organic extends Model
{
    protected $table = 'organics';

    protected $fillable = ['organic_no', 'organic_name', 'uom', 'organic_amount', 'organic_type', 'farmer_no', 'farmer_signature', 'organic_photo', 'status', 'created_by', 'verified_by', 'is_dell', 'deleted_by', 'created_at', 'updated_at'];
    
    public function scopeMaxno($query)
    {
        $year=substr(date('Y'), 2);
        $queryMax =  $query->select(DB::raw('SUBSTRING(`organic_no` ,8) AS kd_max'))
            ->where(DB::raw('MONTH(created_at)'), '=', date('m'))
            ->where(DB::raw('YEAR(created_at)'), '=', date('Y'))
            ->orderBy('organic_no', 'asc')
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
        return 'ORG'.$year.date('m').$kd_fix;
    }
}
