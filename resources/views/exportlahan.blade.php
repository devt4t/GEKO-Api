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

        $nama = 'Export Lahan '. $py . '_' .date("Ymd_h-i-s").'.xls';
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
                    <th scope="col">#</th>
                    <th scope="col">Provinsi</th>
                    <th scope="col">Kabupaten</th>
                    <th scope="col">Kecamatan</th>
                    <th scope="col">Management Unit</th>
                    <th scope="col">Target Area</th>
                    <th scope="col">Desa</th>
                    <th scope="col">FC</th>
                    <th scope="col">FF</th>
                    <th scope="col">Petani</th>
                    <th scope="col">No Lahan</th>
                    <th scope="col">Document No</th>
                    <th scope="col">Luas Area/Tanam</th>
                    <th scope="col">Tipe Lahan</th>
                    <th scope="col">Pola Tanam</th>
                    <th scope="col">Longitude</th>
                    <th scope="col">Latitude</th>
                    <th scope="col">Coordinate</th>
                    <!--<th scope="col">Pohon Kayu/MPTS</th>-->
                    <th scope="col">Verifikasi Lahan</th>
                    <th scope="col">GIS Updated Status</th>
                  </tr>
                </thead>
                <tbody id="tableSO">
                    @foreach ($listval as $rslt)
                        <tr>
                            <th scope="row">{{$loop->iteration}}</th>
                            <td>{{$rslt->province}}</td>
                            <td>{{$rslt->kabupaten}}</td>
                            <td>{{$rslt->kecamatan}}</td>
                            <td>{{$rslt->mu}}</td>
                            <td>{{$rslt->ta}}</td>
                            <td>{{$rslt->desa}}</td>
                            <td>{{\App\Employee::where('nik', $rslt->fc_no)->first()->name ?? '-'}}</td>
                            <td>{{$rslt->ff_name}}</td>
                            <td>{{$rslt->farmer_name}}</td>
                            <td>{{$rslt->lahanNo}}</td>
                            <td>'{{$rslt->document_no}}</td>
                            <td>{{$rslt->land_area}}m<sup>2</sup> / {{$rslt->planting_area}} m<sup>2</sup></td>
                            <td>{{$rslt->lahan_type}}</td>
                            <td>{{$rslt->opsi_pola_tanam}}</td>
                            <td>{{str_replace(".", ",", $rslt->longitude)}}</td>
                            <td>{{str_replace(".", ",", $rslt->latitude)}}</td>
                            <td>{{$rslt->coordinate}}</td>
                            <td style="background: <?= $rslt->approve ? '#34eb52' : '#ff6052'; ?>">{{$rslt->approve ? 'sudah' : 'belum'}}</td>
                            <td style="background: <?= $rslt->updated_gis == 'sudah' ? '#34eb52' : '#f0a856'; ?>">{{$rslt->updated_gis}}</td>
                        </tr>
                    @endforeach 
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>