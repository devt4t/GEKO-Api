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
            <table class="table" style="border: 1px solid black;">
                <thead >
                    
                    <tr style="border: 1px solid black;">
                        <th colspan="3" scope="col">Distribution Date</th>
                        <th colspan="4" scope="col">{{$distribution_time}}</th>
                    </tr>
                  </thead>
            </table>
            <br>
            <table class="table" style="border: 1px solid black;">
                <thead >
                  <tr style="border: 1px solid black;">
                    <th scope="col">#</th>
                    <th scope="col">Field Facilitator</th>
                    <th scope="col">Farmer</th>
                    <th scope="col">Lahan No</th>
                    <th scope="col">Total Lubang</th>
                    <th scope="col">Bag Number</th>
                    <th scope="col">Category</th>
                    <th scope="col">Dist Date</th>
                    <th scope="col">Village</th>
                    <th scope="col">Address</th>
                    <th scope="col">Species1</th>
                    <th scope="col">Qty1</th>
                    <th scope="col">Species2</th>
                    <th scope="col">Qty2</th>
                    <th scope="col">Species3</th>
                    <th scope="col">Qty3</th>
                    <th scope="col">Species4</th>
                    <th scope="col">Qty4</th>
                    <th scope="col">Species5</th>
                    <th scope="col">Qty5</th>
                    <th scope="col">Species6</th>
                    <th scope="col">Qty6</th>
                    <th scope="col">Species7</th>
                    <th scope="col">Qty7</th>
                    {{-- dd($getTrees) --}}
                    {{-- @foreach ($getTrees as $val)
                        <th scope="col">{{$val->tree_name}}</th>
                    @endforeach  --}}
                  </tr>
                </thead>
                <tbody id="tableSO" >
                    {{-- @php
                        dd($listvalbag);
                    @endphp --}}
                    @foreach ($listvalbag as $rslt)
                        <tr style="border: 1px solid black;">
                            <th scope="row">{{$loop->iteration}}</th>
                            <td>{{$rslt['nama_ff']}}</td>
                            <td>{{$rslt['nama_petani']}}</td>
                            <td>{{$rslt['lahan_no']}}</td>
                            <td>{{$rslt['total_holes']}}</td>
                            <td>'{{$rslt['no_bag']}}</td>
                            <td>{{$rslt['pohon_kategori']}}</td>
                            <td>{{$rslt['distribution_time']}}</td>
                            <td>{{$rslt['nama_desa']}}</td>
                            <td>{{$rslt['distribution_location']}}</td>
                            @foreach ($rslt['listvaltemp'] as $valuetrees)
                            <td style="width:150px">{{$valuetrees['pohon']}}</td>
                            <td style="width:100px">{{$valuetrees['amount']}}</td>
                                {{-- @if($valuetrees['distribution_location'] != 0)
                                <th scope="col" style="background-color: aqua;">{{$valuetrees}}</th>
                                @else
                                <th scope="col">{{$valuetrees}}</th>
                                @endif --}}
                            @endforeach 
                        </tr>
                    @endforeach 
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>