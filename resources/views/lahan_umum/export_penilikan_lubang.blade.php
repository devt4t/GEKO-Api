<html>
<head>
</head>
<style>
table, th, td {
  /* border: 3px solid black; */
  border-collapse: collapse;
  font-size:20px;
}
</style>
<body>

    <?php
               
        date_default_timezone_set("Asia/Bangkok");

        $nama = 'Export Lahan Umum Penilikan Lubang '. $py . '_' .date("Ymd_h-i-s").'.xls';
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=".$nama);
	?>
    
    <div class="flex-center position-ref full-height">
        <div class="content" style="margin:50px">
            <h2>{{$nama_title}}</h2>
            <h5>Export Time: {{ date("H:i:s d F Y") }}</h5>
            
            <table class="table" border="1">
                <thead>
                  <tr>
                    <th rowspan="2" scope="col">#</th>
                    <th rowspan="2" scope="col">Provinsi</th>
                    <th rowspan="2" scope="col">Kabupaten</th>
                    <th rowspan="2" scope="col">Kecamatan</th>
                    <th rowspan="2" scope="col">Management Unit</th>
                    <th rowspan="2" scope="col">Desa</th>
                    <th rowspan="2" scope="col">MOU No</th>
                    <th rowspan="2" scope="col">No Lahan</th>
                    <th rowspan="2" scope="col">Status</th>
                    <th rowspan="2" scope="col">PIC T4T</th>
                    <th rowspan="2" scope="col">Nama PIC Lahan</th>
                    <th rowspan="2" scope="col">Luas Area/Tanam</th>
                    <th rowspan="2" scope="col">Pola Tanam</th>
                    <th rowspan="2" scope="col">Longitude</th>
                    <th rowspan="2" scope="col">Latitude</th>
                    <th rowspan="2" scope="col">Coordinate</th>
                    <th colspan="2" scope="col">Lubang</th>
                    <th rowspan="2" scope="col">Verifikasi</th>
                    <th colspan="3" scope="col">Trees Total</th>
                    <th colspan="<?= $trees->count ?>" scope="col">Trees Amount</th>
                  </tr>
                  <tr>
                    <th scope="col">Total</th>
                    <th scope="col">Standard</th>
                    <th scope="col">Kayu</th>
                    <th scope="col">Mpts</th>
                    <th scope="col">Crops</th>
                    @foreach ($trees->data as $tree)
                        <th scope="col">{{ $tree->tree_name }}</th>
                    @endforeach
                  </tr>
                </thead>
                <tbody id="tableSO">
                    @foreach ($lahan->data as $rslt)
                        <tr>
                            <th scope="row">{{$loop->iteration}}</th>
                            <td>{{$rslt->province}}</td>
                            <td>{{$rslt->kabupaten}}</td>
                            <td>{{$rslt->kecamatan}}</td>
                            <td>{{$rslt->mu}}</td>
                            <td>{{$rslt->desa}}</td>
                            <td>{{$rslt->mou_no}}</td>
                            <td>{{$rslt->lahan_no}}</td>
                            <td>{{$rslt->status}}</td>
                            <td>{{\App\Employee::where('nik', $rslt->employee_no)->first()->name ?? '-'}}</td>
                            <td>{{$rslt->pic_lahan}}</td>
                            <td>{{$rslt->luas_lahan}}m<sup>2</sup> / {{$rslt->luas_tanam}} m<sup>2</sup></td>
                            <td>{{$rslt->pattern_planting}}</td>
                            <td>{{str_replace(".", ",", $rslt->longitude)}}</td>
                            <td>{{str_replace(".", ",", $rslt->latitude)}}</td>
                            <td>{{$rslt->coordinate}}</td>
                            <td>{{$rslt->total_holes}}</td>
                            <td style="background: <?= $rslt->total_holes >= $rslt->counter_hole_standard ? '' : '#ff6052'; ?>">{{$rslt->counter_hole_standard}}</td>
                            <td style="background: <?= $rslt->is_verified > 1 ? '#34eb52' : '#ff6052'; ?>">{{$rslt->is_verified ? 'Sudah' : 'Belum'}}</td>
                            <td style="background: <?= $rslt->pohon_kayu > 0 ? '#34eb52' : ''; ?>">{{$rslt->pohon_kayu}}</td>
                            <td style="background: <?= $rslt->pohon_mpts > 0 ? '#34eb52' : ''; ?>">{{$rslt->pohon_mpts}}</td>
                            <td style="background: <?= $rslt->tanaman_bawah > 0 ? '#34eb52' : ''; ?>">{{$rslt->tanaman_bawah}}</td>
                            @foreach ($rslt->lahan_trees as $amount)
                                <td scope="col" style="background: <?= $amount > 0 ? '#34eb52' : ''; ?>">{{ $amount }}</td>
                            @endforeach
                        </tr>
                    @endforeach 
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>