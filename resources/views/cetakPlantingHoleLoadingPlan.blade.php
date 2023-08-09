<?php
               
        date_default_timezone_set("Asia/Bangkok");

        $nama = 'Export_T4T_'.date("Ymd_h-i-s").'.xls';
        // header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=".$nama);
	?>
<html>
<head>
    <meta charset="utf-8">
</head>
<style>
table, th, td {
  /* border: 3px solid black; */
  border-collapse: collapse;
  font-size:20px;
}
</style>
<body>

    
    
    <div class="flex-center position-ref full-height">
        <div class="content" style="margin:50px">
            <h2>{{$nama_title}}</h2>
            <table class="table" style="border: 1px solid black;">
                <thead >
                    @if($detailexcel['type'] == 'loading_plan')
                    <tr style="border: 1px solid black;">
                        <th colspan="3" scope="col">FF Name</th>
                        <th colspan="4" scope="col">{{$detailexcel['nama_ff']}}</th>
                    </tr>
                    @else
                    <tr style="border: 1px solid black;">
                        <th colspan="3" scope="col">Farmer Name</th>
                        <th colspan="4" scope="col">{{$detailexcel['nama_petani']}}</th>
                    </tr>
                    <tr style="border: 1px solid black;">
                        <th colspan="3" scope="col">FF Name</th>
                        <th colspan="4" scope="col">{{$detailexcel['nama_ff']}}</th>
                    </tr>
                    @endif
                    <tr style="border: 1px solid black;">
                        <th colspan="3" scope="col">Distribution Date</th>
                        <th colspan="4" scope="col">{{$detailexcel['distribution_time']}}</th>
                    </tr>
                    <tr style="border: 1px solid black;">
                        <th colspan="3" scope="col">Distribution Place</th>
                        <th colspan="4" scope="col">{{$detailexcel['nama_desa']}}</th>
                    </tr>
                    <tr style="border: 1px solid black;">
                        <th colspan="3" scope="col">Address</th>
                        <th colspan="4" scope="col">{{$detailexcel['distribution_location']}}</th>
                    </tr>
                  </thead>
            </table>
            <br>
            <table class="table" style="border: 1px solid black;">
                <thead >
                  <tr style="border: 1px solid black;">
                    <th scope="col">#</th>
                    <th scope="col">Bag Number</th>
                    <th scope="col">Farmer</th>
                    <th scope="col">Lahan No</th>
                    <th scope="col">Total Lubang</th>
                    <th scope="col">Seedlings Species</th>
                    <th scope="col">Qty</th>
                    @if($detailexcel['type'] == 'loading_plan')
                    <th scope="col">Check</th>
                    @else
                        <th scope="col">Signature</th>
                    @endif
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
                            <td>'{{$rslt['no_bag']}}</td>
                            <td>{{$rslt['nama_petani']}}</td>
                            <td>{{$rslt['lahan_no']}}</td>
                            <td>{{$rslt['total_holes']}}</td>
                            <td style="width:150px">              
                                @foreach ($rslt['listvaltemp'] as $value1)
                                  <span style="text-align: center">{{$value1['pohon']}}</span> <br>
                                @endforeach
                             </td>
                             <td style="width:100px">              
                                @foreach ($rslt['listvaltemp'] as $value2)
                                  <span style="text-align: center">{{$value2['amount']}}</span> <br>
                                @endforeach
                             </td>
                             <td></td>                              
                           
                        </tr>
                    @endforeach 
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>