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

        $nama = $data['py'] . '_' . ($data['distributions'][0]->DistributionDate ?? '-') . '_' . $data['ff_no'].'.xls';
        $url = "https://t4tapi.kolaborasikproject.com/api/GetDistributionReportFullOutside?program_year={$data['py']}&ff_no={$data['queue']}";
        
        // if ($data['queue']) echo '<script>window.open("https://t4tapi.kolaborasikproject.com/api/GetDistributionReportFullOutside?program_year=' . $data['py'] . '&ff_no='. $data['queue']. '", "_blank");</script>';
        
        if ($data['download'] == 'true') {
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=".$nama);
        }
        
        $capColumn = 8;
	?>
	<!--<button onclick="downloadFile()">Download</button>-->
	<script>
	    
        // function downloadFile() {
            const queueFF = "<?php echo $data['queue']; ?>"
            const downloadFF = "<?php echo $data['ff_no']; ?>"
            if (queueFF) {
                setTimeout(switchQueueFF, 120000)
            }
            function switchQueueFF() {
                window.location = "https://t4tapi.kolaborasikproject.com/api/GetDistributionReportFullOutside?download=false&per=<?php echo $data['per']; ?>&program_year=<?php echo $data['py']; ?>&ff_no=<?php echo $data['queue']; ?>";
            }
            window.open(`https://t4tapi.kolaborasikproject.com/api/GetDistributionReportFullOutside?download=true&per=<?php echo $data['per']; ?>&program_year=<?php echo $data['py']; ?>&ff_no=${downloadFF}`, "_blank");
        // }
    </script>
	<!-- Title -->
	<table>
	    <tr>
    	    <th align="left" colspan="{{$capColumn}}"><h2>{{ $nama }}</h2></th>
	    </tr>
	    <tr>
    	    <td align="left" colspan="{{$capColumn}}">Export Time: {{ date("d/m/Y_h:i:s") }}</th>
	    </tr>
	</table>
	<table class="table" border="1">
            <thead>
                <tr>
                    <th rowspan="3">No</th>
                    <th rowspan="3">Distribution Date</th>
                    <th rowspan="3">Planting Date</th>
                    <th rowspan="3">Nursery</th>
                    <th rowspan="3">MU</th>
                    <th rowspan="3">TA</th>
                    <th rowspan="3">Village</th>
                    <th rowspan="3">UM</th>
                    <th rowspan="3">FC</th>
                    <th rowspan="3">FF</th>
                    <th rowspan="3">Farmer</th>
                    <th rowspan="3">Planting Pattern</th>
                    <th style="background: #43fadf" colspan="<?= $data['trees_count']->crops * 7 ?>">CROPS</th>
                    <th style="background: #563232;color: #fff" colspan="<?= $data['trees_count']->kayu * 7 ?>">KAYU</th>
                    <th style="background: #E1E96B" colspan="<?= $data['trees_count']->mpts * 7 ?>">MPTS</th>
                </tr>
                <tr>
                    @foreach($data['trees_data'] as $tree) 
                        <th colspan="7">{{$tree->tree_name}}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach($data['trees_data'] as $tree) 
                        <th>sostam</th>
                        <th>penlub</th>
                        <th>loaded</th>
                        <th>received</th>
                        <th>planted_live</th>
                        <th>dead</th>
                        <th>lost</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                    function getBGColorSeed($index, $amount) {
                        if ((int)$amount > 0) {
                            if (strpos($index, 'dead')) return '#f0a856';
                            else if (strpos($index, 'lost')) return '#ff6052';
                            else return '#34eb52';
                        } else return '';
                    }
                @endphp
                @foreach($data['distributions'] as $dIndex => $d)
                <tr>
                    <td align="center">{{ $d->No }}</td>
                    <td>{{ $d->DistributionDate }}</td>
                    <td>{{ $d->PlantingDate }}</td>
                    <td>{{ $d->Nursery }}</td>
                    <td>{{ $d->MU }}</td>
                    <td>{{ $d->TA }}</td>
                    <td>{{ $d->Village }}</td>
                    <td>{{ $d->UM }}</td>
                    <td>{{ $d->FC }}</td>
                    <td>{{ $d->FF }}</td>
                    <td>{{ $d->Farmer }}</td>
                    <td>{{ $d->PlantingPattern }}</td>
                    @isset($d->jenis_bibit)
                        @foreach($d->jenis_bibit as $jIndex => $j) 
                            <td style="background: <?= getBGColorSeed($jIndex, $j) ?>">{{$j}}</td>
                        @endforeach
                    @endisset
                </tr>
                @endforeach
            </tbody>
        </table>
	
</body>
</html>