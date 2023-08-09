<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pra extends Model
{
    protected $table = 'pras';
    
    protected $fillable = ['pra_no', 'form_no', 'land_ownership_description', 'distribution_of_critical_land_locations_description', 'collection_type', 'man_min_income', 'man_max_income', 'man_income_source', 'man_commodity_name', 'man_method', 'man_average_capacity', 'man_marketing', 'man_period', 'man_source', 'woman_min_income', 'woman_max_income', 'woman_income_source', 'woman_commodity_name', 'woman_method', 'woman_average_capacity', 'woman_marketing', 'woman_period', 'woman_source', 'income_description', 'land_utilization_source', 'land_utilization_plant_type', 'land_utilization_description', 'pra_watersource_description', 'complete_data', 'is_dell', 'user_id', 'created_at', 'updated_at', 'verified_at', 'verified_by', 'is_verify', 'status', 'dry_land_photo', 'dry_land_photo2', 'watersource_photo'];
    
    public function scopeMaxno($query)
    {
        $year=substr(date('Y'), 2);
        $queryMax =  $query->select(DB::raw('SUBSTRING(`pra_no` ,5) AS kd_max'))
            ->orderBy('pra_no', 'asc')
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

        return 'PRA-'.$kd_fix;
    }
}