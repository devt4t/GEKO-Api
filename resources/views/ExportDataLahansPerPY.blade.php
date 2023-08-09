<html>
<head>
    <title>Lahans Export | GEKO</title>
    <style>
        body {
            /*font-family: Poppins, sans-serif;*/
        }
        .table, .table th, .table td{
          border: 0.5px solid black; 
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

        $nama = 'Data Lahan - PY_' . $data->py . ' - ET_'. date("Ymd") .'.xls';
        // echo $nama;
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=".$nama);
        
        $capColumn = 8;
	?>
	<!-- Title -->
	<table>
	    <tr>
    	    <th colspan="{{$capColumn}}"><h2>Data Lahan - Program Year {{ $data->py }}</h2></th>
	    </tr>
	    <tr>
    	    <td align="center" colspan="{{$capColumn}}">Export Time: {{ date("d/m/Y_h:i:s") }}</th>
	    </tr>
	</table>
	<table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Management Unit</th>
                    <th>Target Area</th>
                    <th>Village</th>
                    <th>FC</th>
                    <th>FF</th>
                    <th>Farmer</th>
                    <th>Lahan No</th>
                    <th>Document No</th>
                    <th>Sppt Type</th>
                    <th>Land Type</th>
                    <th>Land Shape</th>
                    <th>Land Area (m<sup>2</sup>)</th>
                    <th>Land Cover (%)</th>
                    <th>Planting Area (m<sup>2</sup>)</th>
                    <th>Land Distance</th>
                    <th>Land Access</th>
                    <th>Water Availability</th>
                    <th>Water Access</th>
                    <th>Planting Pattern</th>
                    <th>Fertilizer</th>
                    <th>Pesticide</th>
                    <th>Description</th>
                    <th>Longitude</th>
                    <th>Latitude</th>
                    <th>Coordinate</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $typeSppt = ['Pribadi', 'Keterkaitan Keluarga', 'Umum', 'Lain - Lain'];
                @endphp
                @foreach($data->data as $lIndex => $l)
                <tr>
                    <td align="center">{{ $lIndex + 1 }}</td>
                    <td>{{ $l->ManagementUnit }}</td>
                    <td>{{ $l->TargetArea }}</td>
                    <td>{{ $l->Village }}</td>
                    <td>{{ $l->FC }}</td>
                    <td>{{ $l->FF }}</td>
                    <td>{{ $l->Farmer }}</td>
                    <td>{{ $l->LahanNo }}</td>
                    <td>'{{ $l->DocumentNo }}</td>
                    <td>{{ $typeSppt[$l->SPPTType] }}</td>
                    <td>{{ $l->LandType }}</td>
                    <td>{{ $l->LandShape }}</td>
                    <td>{{ $l->LandArea }}</td>
                    <td>{{ $l->LandCover }}</td>
                    <td>{{ $l->PlantingArea }}</td>
                    <td>{{ $l->LandDistance }}</td>
                    <td>{{ $l->LandAccess }}</td>
                    <td>{{ $l->WaterAvailability }}</td>
                    <td>{{ $l->WaterAccess }}</td>
                    <td>{{ $l->PlantingPattern }}</td>
                    <td>{{ $l->Fertilizer }}</td>
                    <td>{{ $l->Pesticide }}</td>
                    <td>{{ $l->Description }}</td>
                    <td>{{ $l->Longitude }}</td>
                    <td>{{ $l->Latitude }}</td>
                    <td>{{ $l->Coordinate }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
	
</body>
</html>