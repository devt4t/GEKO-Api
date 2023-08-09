<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ScoopingVisit extends Model
{
    protected $table = 'scooping_visits';
    
    protected $fillable = ['data_no', 'start_scooping_date', 'end_scooping_date', 'dry_land_area', 'province', 'city', 'district', 'village', 'land_area', 'accessibility', 'goverment_place', 'water_source', 'electricity_source', 'land_coverage', 'total_dusun', 'potential_dusun', 'potential_description', 'total_male', 'total_female', 'total_kk', 'land_type', 'slope', 'altitude', 'vegetation_density', 'rainfall', 'agroforestry_type', 'village_polygon', 'dry_land_polygon', 'photo_road_access', 'photo_meeting', 'photo_dry_land', 'village_profile', 'user_id', 'verified_by', 'is_verify', 'complete_data', 'is_dell', 'created_at', 'updated_by', 'updated_at', 'status' ];
    
    public function scopeMaxno($query)
    {
        $year=substr(date('Y'), 2);
        $queryMax =  $query->select(DB::raw('SUBSTRING(`data_no` ,5) AS kd_max'))
            ->orderBy('data_no', 'asc')
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

        return 'SCP-'.$kd_fix;
    }
}