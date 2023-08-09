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

        $nama = "Export Monitoring-".date("Ymd_h-i-s").'.xls';
        // $download = $_GET['download'] == 'true' ? true : false;
        // if ($download) {
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=".$nama);
        // }
	?>
    <div class="flex-center position-ref full-height">
        <div class="content" style="margin:50px">
            <h2>Export Data Realisasi Tanam / Monitoring 1</h2>
            <h5>Export Time: {{ date("d m Y, H:i:s") }}</h5>
            
            <table class="table" border="1">
                <thead>
                  <tr>
                    <th rowspan="3" scope="col">No</th>
                    <th rowspan="3" scope="col">Tahun Program</th>
                    <th rowspan="3" scope="col">Management Unit</th>
                    <th rowspan="3" scope="col">Target Area</th>
                    <th rowspan="3" scope="col">Desa</th>
                    <th rowspan="3" scope="col">Field Coordinator</th>
                    <th rowspan="3" scope="col">Field Facilitator</th>
                    <th rowspan="2" colspan="3" scope="col">Data Petani</th>
                    <th rowspan="2" colspan="10" scope="col">Data Lahan</th>
                    <th rowspan="3" scope="col">Tanggal Tanam</th>
                    <th rowspan="3" scope="col">Qty Standar</th>
                    <th rowspan="3" scope="col">Verifikasi</th>
                    <th colspan="<?= count($trees) * 3 ?>" scope="col">Trees Amount</th>
                    </tr>
                    <tr>
                        @foreach ($trees as $tree)
                            <th scope="col" colspan="3">{{ $tree->tree_name }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        <th scope="col">Nama Petani</th>
                        <th scope="col">No KTP</th>
                        <th scope="col">Alamat</th>
                        <th scope="col">No Lahan</th>
                        <th scope="col">No Doc. Lahan</th>
                        <th scope="col">Luas Lahan</th>
                        <th scope="col">Luas Tanam</th>
                        <th scope="col">Opsi Pola Tanam</th>
                        <th scope="col">Jarak Lahan</th>
                        <th scope="col">Aksesibilitas</th>
                        <th scope="col">Koordinat</th>
                        <th scope="col">Status Lahan</th>
                        <th scope="col">Kondisi Lahan</th>
                        @foreach ($trees as $tree)
                            <th scope="col">Ditanam Hidup</th>
                            <th scope="col">Mati</th>
                            <th scope="col">Hilang</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="tableSO">
                    @foreach ($data as $val)
                        @for ($i = 0; $i < count($val->lahan_no); $i++)
                            <tr>
                                <th scope="row">{{$loop->iteration}}</th>
                                <td scope="row">{{$val->program_year}}</td>
                                <td scope="row">{{$val->mu_name}}</td>
                                <td scope="row">{{$val->ta_name}}</td>
                                <td scope="row">{{$val->village_name}}</td>
                                <td scope="row">{{$val->fc_name}}</td>
                                <td scope="row">{{$val->ff_name}}</td>
                                <td scope="row">{{$val->farmer_name}}</td>
                                <td scope="row">'{{$val->ktp_no}}</td>
                                <td scope="row">{{"$val->farmer_address RT$val->farmer_rt/RW$val->farmer_rw"}}</td>
                                <td scope="row">{{$val->lahan_no[$i]}}</td>
                                <td scope="row">'{{$val->document_no[$i]}}</td>
                                <td scope="row">{{$val->land_area[$i]}}</td>
                                <td scope="row">{{$val->planting_area[$i]}}</td>
                                <td scope="row">{{$val->planting_pattern[$i]}}</td>
                                <td scope="row">{{$val->land_distance[$i]}}</td>
                                <td scope="row">{{$val->access_lahan[$i]}}</td>
                                <td scope="row">{{$val->coordinate[$i]}}</td>
                                <td scope="row">{{$val->land_status[$i]}}</td>
                                <td scope="row">{{$val->lahan_condition}}</td>
                                @if ($i == 0)
                                    <td rowspan="<?= count($val->lahan_no) ?>" scope="row">{{$val->planting_date}}</td>
                                    <td rowspan="<?= count($val->lahan_no) ?>" scope="row">{{$val->qty_std}}</td>
                                    <td rowspan="<?= count($val->lahan_no) ?>" scope="row" style="background: <?= $val->is_validate > 0 ? ($val->is_validate == 2 ? '#34eb52' : '#f0a856') : '#ff6052'; ?>">{{$val->is_validate > 0 ? ($val->is_validate == 2 ? 'UM' : 'FC') : 'Belum'}}</td>
                                    @foreach ($val->tree_details as $tree)
                                        <td scope="col" rowspan="<?= count($val->lahan_no) ?>" style="background: <?= $tree->planted_life > 0 ? '#34eb52' : ''; ?>">{{ $tree->planted_life }}</td>
                                        <td scope="col" rowspan="<?= count($val->lahan_no) ?>" style="background: <?= $tree->dead > 0 ? '#f0a856' : ''; ?>">{{ $tree->dead }}</td>
                                        <td scope="col" rowspan="<?= count($val->lahan_no) ?>" style="background: <?= $tree->lost > 0 ? '#ff6052' : ''; ?>">{{ $tree->lost }}</td>
                                    @endforeach
                                @endif
                            </tr>
                        @endfor
                    @endforeach 
                </tbody>
              </table>
        </div>
    </div>
</body>
</html>