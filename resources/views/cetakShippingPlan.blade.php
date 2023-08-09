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
                    <th scope="col">Qty Bags</th>
                    <th scope="col">Destination</th>
                   
                  </tr>
                </thead>
                <tbody id="tableSO" >
                    {{-- @php
                        dd($listvalbag);
                    @endphp --}}
                    @foreach ($listfftemp as $rslt)
                        <tr style="border: 1px solid black;">
                            <th scope="row">{{$loop->iteration}}</th>
                            <td>{{$rslt['nama_ff']}}</td>
                            <td>{{$rslt['qty_total']}}</td>
                            <td>{{$rslt['distribution_location']}}</td>
                        </tr>
                    @endforeach 
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>