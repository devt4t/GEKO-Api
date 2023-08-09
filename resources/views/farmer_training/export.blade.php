<html>
<head>
</head>
<style>
table, th, td {
  /* border: 3px solid black; */
  border-collapse: collapse;
  font-size:20px;
}
tr:nth-child(even) {
    background: #f2f2f2;
}

</style>
<body>
    <?php
               
        date_default_timezone_set("Asia/Bangkok");

        $nama = "Export Pelatihan Petani - ".date("Ymd_h-i-s").'.xls';
        // $download = $_GET['download'] == 'true' ? true : false;
        // if ($download) {
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=".$nama);
        // }
	?>
    <div class="flex-center position-ref full-height">
        <div class="content" style="margin:50px">
            <h2>Export Data Pelatihan Petani ({{ $program_year }})</h2>
            <h5>Export Time: {{ date("d m Y, H:i:s") }}</h5>
            
            <table class="table" border="1">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Form No</th>
                        <th scope="col">Tahun Program</th>
                        <th scope="col">Tanggal Pelatihan</th>
                        <th scope="col">Management Unit</th>
                        <th scope="col">Target Area</th>
                        <th scope="col">Desa</th>
                        <th scope="col">Field Coordinator</th>
                        <th scope="col">Field Facilitator</th>
                        <th scope="col">Materi 1 (Wajib)</th>
                        <th scope="col">Materi 2</th>
                        <th scope="col">Total Partisipan Petani</th>
                        <th align="left" scope="col" colspan="<?= $cap_farmer ?>">Nama Partisipan Petani</th>
                    </tr>
                </thead>
                <tbody id="tableSO">
                    @foreach ($datas as $data)
                    @if ($loop->iteration % 2 == 0)
                        <tr style="background: #d9ffd1">
                    @else
                        <tr>
                    @endif
                            <td scope="row">{{$loop->iteration}}</td>
                            <td scope="row">{{$data->training_no}}</td>
                            <td scope="row">{{$data->program_year}}</td>
                            <td scope="row">{{$data->training_date}}</td>
                            <td scope="row">{{$data->mu_name}}</td>
                            <td scope="row">{{$data->ta_name}}</td>
                            <td scope="row">{{$data->village_name}}</td>
                            <td scope="row">{{$data->fc_name}}</td>
                            <td scope="row">{{$data->ff_name}}</td>
                            <td scope="row">{{$data->materi1}}</td>
                            <td scope="row">{{$data->materi2}}</td>
                            <td scope="row">{{count($data->farmers)}}</td>
                            @foreach ($data->farmers as $farmer)
                                <td scope="row">{{$farmer}}</td>
                            @endforeach
                        </tr>
                    @endforeach 
                </tbody>
              </table>
        </div>
    </div>
</body>
</html>