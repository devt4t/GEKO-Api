<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lahan extends Model
{
    protected $table = 'lahans';
    protected $fillable = ['lahan_no', 'document_no','internal_code',  'land_area', 'planting_area', 'longitude', 'latitude', 'coordinate', 'polygon', 'village', 'kecamatan', 'city', 'province', 'description', 'elevation', 'soil_type',
    'current_crops', 'active', 'farmer_no','farmer_temp', 'mu_no', 'target_area', 'user_id', 'created_time', 'created_at', 'updated_at', 'sppt','photo1', 'photo2', 'photo3', 'photo4', 'group_no',
    'kelerengan_lahan', 'exposure', 'fertilizer', 'pesticide', 'tutupan_lahan','access_to_water_sources', 'access_to_lahan', 'water_availability','lahan_type', 'jarak_lahan', 'potency','barcode',
    'opsi_pola_tanam', 'pohon_kayu', 'pohon_mpts','tanaman_bawah', 'type_sppt', 'is_dell', 'complete_data', 'approve', 'updated_gis'];
}
