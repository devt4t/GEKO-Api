<html>
<head>
    <title>Penilikan Lubang Export | GEKO</title>
    <style>
        body {
            font-family: Poppins, sans-serif;
        }
        .table, .table th, .table td{
          border: 1px solid black; 
          border-collapse: collapse;
          font-size:15px;
        }
    </style>
</head>
<body>
    <?php
        date_default_timezone_set("Asia/Bangkok");

        $nama = 'Export Penilikan Lubang Tanam - ' . date("Ymd_h-i-s").'.xls';
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=".$nama);
        $capColumn = 10;
	?>
	<!-- Title -->
	<table>
	    <!--{{ $datas }}-->
	    <tr>
    	    <th colspan="{{$capColumn}}"><h2>Penilikan Lubang Tanam</h2></th>
	    </tr>
	    <tr>
    	    <td align="center" colspan="{{$capColumn}}">Export Time: {{ date("d/m/Y_h:i:s") }}</th>
	    </tr>
	</table>
	
	<!-- MAIN TABLE -->
	<table class="table">
	    <thead>
	        <tr>
	            <th>No</th>
	            <th>Unit Manager</th>
	            <th>Field Coordinator</th>
	            <th>Field Facilitator</th>
	            <th>Management Unit</th>
	            <th>Target Area</th>
	            <th>Desa</th>
	            <th>Petani</th>
	            <th>Jumlah Lubang</th>
	            <th>Jumlah Lubang Standar</th>
	            <th>Status</th>
	        </tr>
	    </thead>
	    <tbody>
	        @foreach ($datas as $index => $data)
    	        @if ($data->is_validate == 1)
        	        <tr style="background-color: #bceb9d">
        	    @else
        	        <tr style="background-color: #ebab9d">
    	        @endif
    	            <td>{{ $index + 1 }}</td>
    	            <td>{{ $data->um_name }}</td>
    	            <td>{{ $data->fc_name }}</td>
    	            <td>{{ $data->ff_name }}</td>
    	            <td>{{ $data->mu_name }}</td>
    	            <td>{{ $data->ta_name }}</td>
    	            <td>{{ $data->village_name }}</td>
    	            <td>{{ $data->farmer_name }}</td>
    	            <td align="center">{{ $data->holes }}</td>
    	            <td align="center">{{ $data->holes_standard }}</td>
    	            <td>{{ $data->is_validate == 1 ? 'Sudah Verifikasi' : 'Belum Verifikasi' }}</td>
                </tr>
            @endforeach
	    </tbody>
	</table>
</body>
</html>