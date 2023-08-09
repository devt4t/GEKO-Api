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

        $nama = 'ExportFarmer_'.date("Ymd_h-i-s").'.xls';
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=".$nama);
	?>
    <div class="flex-center position-ref full-height">
        <div class="content" style="margin:50px">
            <h2>Export Data Petani</h2>
            <h5>Export Time: {{ date("d m Y, H:i:s") }}</h5>
            
            <table class="table">
                <thead>
                  <tr>
                    <th scope="col">No</th>
                    <th scope="col">Program Year</th>
                    <th scope="col">Management Unit</th>
                    <th scope="col">Target Area</th>
                    <th scope="col">Desa</th>
                    <th scope="col">Field Coordinator</th>
                    <th scope="col">Field Facilitator</th>
                    <th scope="col">Nama</th>
                    <th scope="col">Gender</th>
                    <th scope="col">Status</th>
                    <th scope="col">No KTP</th>
                    <th scope="col">No HP</th>
                    <th scope="col">Tanggal Lahir</th>
                    <th scope="col">Alamat</th>
                    <th scope="col">Suku</th>
                    <th scope="col">Asal</th>
                    <th scope="col">Jml Keluarga</th>
                    <th scope="col">Edukasi</th>
                    <th scope="col">Edukasi Non-Formal</th>
                    <th scope="col">Pekerjaan Utama</th>
                    <th scope="col">Penghasilan Utama</th>
                    <th scope="col">Pekerjaan Sampingan</th>
                    <th scope="col">Penghasilan Sampingan</th>
                  </tr>
                </thead>
                <tbody id="tableSO">
                    @foreach ($data as $val)
                        <tr>
                            <th scope="row">{{$loop->iteration}}</th>
                            <td scope="row">{{$val->program_year}}</td>
                            <td scope="row">{{$val->mu_name}}</td>
                            <td scope="row">{{$val->ta_name}}</td>
                            <td scope="row">{{$val->village_name}}</td>
                            <td scope="row">{{$val->fc_name}}</td>
                            <td scope="row">{{$val->ff_name}}</td>
                            <td scope="row">{{$val->farmer_name}}</td>
                            <td scope="row">{{$val->farmer_gender}}</td>
                            <td scope="row">{{$val->marrital_status}}</td>
                            <td scope="row">'{{$val->farmer_ktp}}</td>
                            <td scope="row">'{{$val->phone}}</td>
                            <td scope="row">{{$val->birthday}}</td>
                            <td scope="row">{{$val->farmer_address}} RT{{ $val->farmer_rt ?? 0 }}/RW{{ $val->farmer_rw ?? 0 }}, {{ $val->post_code }}</td>
                            <td scope="row">{{$val->ethnic}}</td>
                            <td scope="row">{{$val->origin}}</td>
                            <td scope="row">{{$val->number_family_member}}</td>
                            <td scope="row">{{$val->education}}</td>
                            <td scope="row">{{$val->non_formal_education}}</td>
                            <td scope="row">{{$val->main_job}}</td>
                            <td scope="row">{{$val->main_income}}</td>
                            <td scope="row">{{$val->side_job}}</td>
                            <td scope="row">{{$val->side_income}}</td>
                        </tr>
                    @endforeach 
                </tbody>
              </table>
        </div>
    </div>
</body>
</html>