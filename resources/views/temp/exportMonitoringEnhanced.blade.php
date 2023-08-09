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

        $nama = "Export Monitoring V2 -".date("Ymd_h-i-s").'.xls';
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
                    <th rowspan="2" scope="col">No</th>
                    <th rowspan="2" scope="col">Tahun Program</th>
                    <th rowspan="2" scope="col">No Lahan</th>
                    <th rowspan="2" scope="col">Management Unit</th>
                    <th rowspan="2" scope="col">Target Area</th>
                    <th rowspan="2" scope="col">Desa</th>
                    <th rowspan="2" scope="col">Field Coordinator</th>
                    <th rowspan="2" scope="col">Field Facilitator</th>
                    <th rowspan="2" scope="col">Farmer</th>
                    <th rowspan="2" scope="col">Tanggal Tanam</th>
                    <th rowspan="2" scope="col">Verifikasi</th>
                        @foreach ($trees_data as $tree)
                            <th scope="col" colspan="3">{{ $tree->tree_name }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($trees_data as $tree)
                            <th scope="col">Ditanam Hidup</th>
                            <th scope="col">Mati</th>
                            <th scope="col">Hilang</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="tableSO">
                    @foreach ($export_data->list as $val)
                        <tr>
                            <td scope="row">{{$loop->iteration}}</td>
                            <td scope="row">{{$val->program_year}}</td>
                            <td scope="row">{{$val->lahan_no}}</td>
                            <td scope="row">{{$val->mu_name}}</td>
                            <td scope="row">{{$val->ta_name}}</td>
                            <td scope="row">{{$val->village_name}}</td>
                            <td scope="row">{{$val->fc_name}}</td>
                            <td scope="row">{{$val->ff_name}}</td>
                            <td scope="row">{{$val->farmer_name}}</td>
                            <td scope="row">{{date('Y-m-d', strtotime($val->planting_date))}}</td>
                            <td scope="row" style="background: <?= $val->is_validate > 0 ? ($val->is_validate == 2 ? '#34eb52' : '#f0a856') : '#ff6052'; ?>">{{$val->is_validate > 0 ? ($val->is_validate == 2 ? 'UM' : 'FC') : 'Belum'}}</td>
                            @foreach ($trees_data as $tree)
                                @if (in_array($tree->tree_code, $val->lahan_tree_codes)) 
                                    @php 
                                        $tree_data_filter = array_filter($val->lahan_trees, function ($lt) use ($tree) {
                                            return $lt->tree_code == $tree->tree_code;
                                        });
                                        $tree_data = array_shift($tree_data_filter);
                                    @endphp
                                    <td scope="col" style="background: <?= $tree_data->monitoring_planted_live > 0 ? '#34eb52' : ''; ?>">{{ $tree_data->monitoring_planted_live }}</td>
                                    <td scope="col" style="background: <?= $tree_data->monitoring_dead > 0 ? '#f0a856' : ''; ?>">{{ $tree_data->monitoring_dead }}</td>
                                    <td scope="col" style="background: <?= $tree_data->monitoring_lost > 0 ? '#ff6052' : ''; ?>">{{ $tree_data->monitoring_lost }}</td>
                                @else
                                    <td scope="row">0</td>
                                    <td scope="row">0</td>
                                    <td scope="row">0</td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach 
                </tbody>
              </table>
        </div>
    </div>
</body>
</html>