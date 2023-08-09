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

        $nama = 'Export_T4T_'.date("Ymd_h-i-s").'.xls';
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=".$nama);
	?>
    
    <div class="flex-center position-ref full-height">
        <div class="content" style="margin:50px">
            <h2>{{$nama_title}}</h2>
            
            <table class="table">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">No_Lahan</th>
                    <th scope="col">Petani</th>
                    <th scope="col">Desa</th>
                    <th scope="col">kecamatan</th>
                    <th scope="col">Management Unit</th>
                    <th scope="col">Location</th>
                    <th scope="col">Luas Area/Tanam</th>
                    <th scope="col">Pohon Kayu/MPTS</th>
                    <th scope="col">Status</th>
                    <th scope="col">Nama FF</th>
                    <th scope="col">Nama FC</th>
                    <th scope="col">Opsi Pola Tanam</th>
                    <th scope="col">planting_year</th>
                    <th scope="col">pembuatan_lubang_tanam</th>
                    <th scope="col">distribution_time</th>
                    <th scope="col">distribution_location</th>
                    <th scope="col">planting_time</th>
                    {{-- dd($getTrees) --}}
                    @foreach ($getTrees as $val)
                        <th scope="col">{{$val->tree_name}}</th>
                    @endforeach 
                  </tr>
                </thead>
                <tbody id="tableSO">
                    {{-- @php
                        dd($result);
                    @endphp --}}
                    @foreach ($listval as $rslt)
                        <tr>
                            <th scope="row">{{$loop->iteration}}</th>
                            <td>{{$rslt['lahanNo']}}</td>
                            <td>{{$rslt['petani']}}</td>
                            <td>{{$rslt['desa']}}</td>
                            <td>{{$rslt['nama_kec']}}</td>
                            <td>{{$rslt['nama_mu']}}</td>
                            <td>{{$rslt['location']}}</td>
                            <td>{{$rslt['land_area']}}m<sup>2</sup> / {{$rslt['planting_area']}} m<sup>2</sup></td>
                            <td>{{$rslt['pohon_kayu']}} pcs/ {{$rslt['pohon_mpts']}} pcs</td>
                            <td>{{$rslt['status']}}</td>
                            <td>{{$rslt['ff']}}</td>
                            <td>{{$rslt['nama_fc_lahan']}}</td>
                            <td>{{$rslt['opsi_pola_tanam']}}</td>
                            <td>{{$rslt['planting_year']}}</td>
                            <td>{{$rslt['pembuatan_lubang_tanam']}}</td>
                            <td>{{$rslt['distribution_time']}}</td>
                            <td>{{$rslt['distribution_location']}}</td>
                            <td>{{$rslt['planting_time']}}</td>
                            @foreach ($rslt['listvaltrees'] as $valuetrees)
                                @if($valuetrees != 0)
                                <th scope="col" style="background-color: aqua;">{{$valuetrees}}</th>
                                @else
                                <th scope="col">{{$valuetrees}}</th>
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