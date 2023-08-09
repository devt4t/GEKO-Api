<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Rra extends Model
{
    protected $table = 'rras';
    
    protected $fillable = ['form_no', 'rra_no', 'rra_pra_date_start', 'rra_pra_date_end', 'village', 'tanah_sawah', 'tegal_ladang', 'pemukiman', 'pekarangan', 'tanah_rawa', 'waduk_danau', 'tanah_perkebunan_rakyat', 'tanah_perkebunan_negara', 'tanah_perkebunan_swasta', 'hutan_lindung', 'hutan_rakyat', 'fasilitas_umum', 'lahan_menurut_masyarakat','user_id', 'created_at', 'updated_at', 'is_dell', 'verified_by', 'verified_at', 'is_verify', 'complete_data', 'status', 'commodity_photo', 'institution_photo', 'organic_farming_photo'];
    
    public function scopeMaxno($query)
    {
        $year=substr(date('Y'), 2);
        $queryMax =  $query->select(DB::raw('SUBSTRING(`rra_no` ,5) AS kd_max'))
            ->orderBy('rra_no', 'asc')
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

        return 'RRA-'.$kd_fix;
    }
}