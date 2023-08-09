<html>
<head>
</head>
<style>
table, th, td {
  /* border: 3px solid black; */
  border-collapse: collapse;
  font-size:20px;
}
table tbody td {
    vertical-align: middle;
}
</style>
<body>

    <?php
               
        date_default_timezone_set("Asia/Bangkok");

        $nama = 'Export Distribution Report Lahan Petani '. $py . '_' .date("Y-m-d", strtotime($distribution_date)).'.xls';
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=".$nama);
	?>
    
    <div class="flex-center position-ref full-height">
        <div class="content" style="margin:50px">
            <h2>{{$nama_title}}</h2>
            <h3>Distribution Date: {{ date("d F Y", strtotime($distribution_date)) }}</h3>
            <h5>Export Time: {{ date("H:i:s d F Y") }}</h5>
            
            <table class="table" border="1">
                <thead>
                  <tr>
                    <th rowspan="3" scope="col">#</th>
                    <th rowspan="3" scope="col">Lahan No</th>
                    <th rowspan="3" scope="col">Tanggal Distribusi</th>
                    <th rowspan="3" scope="col">Nursery</th>
                    <th rowspan="3" scope="col">Management Unit</th>
                    <th rowspan="3" scope="col">Target Area</th>
                    <th rowspan="3" scope="col">Desa</th>
                    <th rowspan="3" scope="col">Field Coordinator</th>
                    <th rowspan="3" scope="col">Field Facilitator</th>
                    <th rowspan="3" scope="col">Petani</th>
                    <th rowspan="3" scope="col">Verifikasi</th>
                    <th colspan="<?= $trees->count * 3 ?>" scope="col">Trees Amount</th>
                  </tr>
                  <tr>
                    @foreach ($trees->data as $tree)
                        <th scope="col" colspan="3">{{ $tree->tree_name }}</th>
                    @endforeach
                  </tr>
                  <tr>
                    @foreach ($trees->data as $tree)
                        <th scope="col">Rusak</th>
                        <th scope="col">Hilang</th>
                        <th scope="col">Diterima</th>
                    @endforeach
                  </tr>
                </thead>
                <tbody id="tableSO">
                    @foreach ($distributions->data as $rslt)
                        <tr>
                            <th scope="row">{{$loop->iteration}}</th>
                            <td>{{$rslt->lahan_no}}</td>
                            <td>{{date('Y-m-d', strtotime($rslt->distribution_date))}}</td>
                            <td>{{$rslt->nursery}}</td>
                            <td>{{$rslt->mu}}</td>
                            <td>{{$rslt->ta}}</td>
                            <td>{{$rslt->desa}}</td>
                            <td>{{$rslt->fc_name}}</td>
                            <td>{{$rslt->ff_name}}</td>
                            <td>{{$rslt->farmer_name}}</td>
                            <td style="background: <?= $rslt->status == 1 ? '#eba134' : '#34eb52'; ?>">{{$rslt->status == 1 ? 'FC' : 'UM'}}</td>
                            @foreach ($rslt->trees as $tree)
                                <td scope="col" style="background: <?= $tree->broken_seeds > 0 ? '#ff6052' : ''; ?>">{{ $tree->broken_seeds }}</td>
                                <td scope="col" style="background: <?= $tree->missing_seeds > 0 ? '#34eb52' : ''; ?>">{{ $tree->missing_seeds }}</td>
                                <td scope="col" style="background: <?= $tree->total_tree_received > 0 ? '#34eb52' : ''; ?>">{{ $tree->total_tree_received }}</td>
                            @endforeach
                        </tr>
                    @endforeach 
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>