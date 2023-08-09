<html>
<head>
    <title>Distribution Export | GEKO</title>
    <style>
        body {
            /*font-family: Poppins, sans-serif;*/
        }
        .table, .table th, .table td{
          /*border: 1px solid black; */
          border-collapse: collapse;
          font-size:15px;
        }
        .table td {
          vertical-align: top;
        }
    </style>
</head>
<body>
    <?php
        date_default_timezone_set("Asia/Bangkok");

        $nama = 'DataLahanSPPT-2021&2022.xls';
        // header("Content-type: application/vnd-ms-excel");
        // header("Content-Disposition: attachment; filename=".$nama);
	?>
	<!-- Title -->
	<table>
	    <tr>
    	    <th align="left" colspan="7"><h2>{{ $nama }}</h2></th>
	    </tr>
	    <tr>
    	    <th align="left" colspan="7">Export Time: {{ date("d/m/Y_h:i:s") }}</th>
	    </tr>
	</table>
	<table class="table" border="1">
            <thead>
                <tr>
                    <th>No</th>
                    <th>MU</th>
                    <th>Target Area</th>
                    <th>Desa</th>
                    <th>Petani</th>
                    <th>No Lahan</th>
                    <th>No SPPT</th>
                    <th>Tahun Tanam</th>
                    <th>Monitoring No</th>
                    @foreach ($trees as $tree)
                        <th>{{$tree->tree_name}}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data as $dIndex => $d)
                <tr>
                    <td align="center">{{ $dIndex + 1 }}</td>
                    <td>{{ $d->mu_name }}</td>
                    <td>{{ $d->ta_name }}</td>
                    <td>{{ $d->village_name }}</td>
                    <td>{{ $d->farmer_name }}</td>
                    <td>{{ $d->lahan_no }}</td>
                    <td>{{ $d->document_no }}</td>
                    <td>{{ $d->program_year }}</td>
                    <td>{{ $d->monitoring_no }}</td>
                    @foreach ($trees as $tree)
                        @php 
                            $amount = $d[$tree->tree_code] ?? 0;
                        @endphp
                        <td style="background: <?= $amount > 0 ? '#34eb52' : ''; ?>">{{ $amount }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
	
</body>
</html>