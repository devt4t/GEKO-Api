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

        $nama = 'Export Distribution Report Lahan Umum '. $py . '_' .date("Y-m-d", strtotime($distribution_date)).'.xls';
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
                    <th rowspan="3" scope="col">Tanggal Distribusi</th>
                    <th rowspan="3" scope="col">Nursery</th>
                    <th rowspan="3" scope="col">Management Unit</th>
                    <th rowspan="3" scope="col">Provinsi</th>
                    <th rowspan="3" scope="col">Kabupaten</th>
                    <th rowspan="3" scope="col">Kecamatan</th>
                    <th rowspan="3" scope="col">Desa</th>
                    <th rowspan="3" scope="col">PIC T4T</th>
                    <th rowspan="3" scope="col">PIC Lahan</th>
                    <th rowspan="3" scope="col">MOU No</th>
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
                            <td scope="row">{{$loop->iteration}}</td>
                            <td>{{date('Y-m-d', strtotime($rslt->distribution_date))}}</td>
                            <td>{{$rslt->nursery}}</td>
                            <td>{{$rslt->mu}}</td>
                            <td>{{$rslt->province}}</td>
                            <td>{{$rslt->regency}}</td>
                            <td>{{$rslt->district}}</td>
                            <td>{{$rslt->village}}</td>
                            <td>{{$rslt->pic_t4t}}</td>
                            <td>{{$rslt->pic_lahan}}</td>
                            <td>{{$rslt->mou_no}}</td>
                            <td style="background: <?= $rslt->status == 1 ? '#eba134' : '#34eb52'; ?>">{{$rslt->status == 1 ? 'PIC T4T' : 'UM / PM'}}</td>
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