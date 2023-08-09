<html>
<head>
    <title>Distribution Export | GEKO</title>
    <style>
        body {
            font-family: Poppins, sans-serif;
        }
        .table, .table th, .table td{
          border: 1px solid black; 
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

        $nama = 'Activities Report - ' . $datas['fc'] . '_' . date("Ymd_h-i-s").'.xls';
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=".$nama);
        if ((in_array('Pendataan Petani & Lahan', $datas['activities'])) || (in_array('Realisasi Tanam', $datas['activities']))) $capColumn = 10;
        else if ((in_array('Distribusi', $datas['activities'])) || (in_array('Pelatihan Petani', $datas['activities']))) $capColumn = 9;
        else if ((in_array('Penilikan Lubang', $datas['activities']))) $capColumn = 7;
        else $capColumn = 8;
	?>
	<!-- Title -->
	<table>
	    <tr>
    	    <th colspan="{{$capColumn}}"><h2>Activities Progression</h2></th>
	    </tr>
	    <tr>
    	    <td align="center" colspan="{{$capColumn}}">Export Time: {{ date("d/m/Y_h:i:s") }}</th>
	    </tr>
	</table>
	
    <!-- General Data -->
    <table>
        <tr>
            <td colspan="2">
                Tahun Program
            </td>
            <th align="right">:</th>
            <td colspan="{{$capColumn - 3}}">
                {{ $datas['program_year'] }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                Unit Manager
            </td>
            <th align="right">:</th>
            <td colspan="{{$capColumn - 3}}">
                {{ $datas['um'] }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                Field Coordinator
            </td>
            <th align="right">:</th>
            <td colspan="{{$capColumn - 3}}">
                {{ $datas['fc'] }}
            </td>
        </tr>
    </table>
    
    <!-- Activities Data -->
    <!-- Pendataan Petani & Lahan -->
    @if (in_array('Pendataan Petani & Lahan', $datas['activities']))
        <table>
            <tr>
                <th colspan="10"><h4>Pendataan Petani & Lahan</h4></th>
            </tr>
        </table>
        <table class="table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Field Facilitator</th>
                    <th colspan="4">Petani</th>
                    <th colspan="4">Lahan</th>
                </tr>
                <tr>
                    <th>{{ $datas['dates'][0] }}</th>
                    <th>{{ $datas['dates'][1] }}</th>
                    <th>Total</th>
                    <th>Progress</th>
                    <th>{{ $datas['dates'][2] }}</th>
                    <th>{{ $datas['dates'][3] }}</th>
                    <th>Total</th>
                    <th>Progress</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datas['petani_lahan'] as $petLahanIndex => $petLahan)
                <tr>
                    <td align="center">{{ $petLahanIndex + 1 }}</td>
                    <td>{{ $petLahan['ff'] }}</td>
                    <td align="center">{{ $petLahan['petani']['petani1'] }}</td>
                    <td align="center">{{ $petLahan['petani']['petani2'] }}</td>
                    <td align="center">{{ $petLahan['petani']['total_petani'] }}</td>
                    <td align="center">{{ $petLahan['petani']['progress_petani'] }}%</td>
                    <td align="center">{{ $petLahan['lahan']['lahan1'] }}</td>
                    <td align="center">{{ $petLahan['lahan']['lahan2'] }}</td>
                    <td align="center">{{ $petLahan['lahan']['total_lahan'] }}</td>
                    <td align="center">{{ $petLahan['lahan']['progress_lahan'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    
    <!-- Sosialisasi Tanam -->
    @if (in_array('Sosialisasi Tanam', $datas['activities']))
        <table>
            <tr></tr>
            <tr>
                <th colspan="8"><h4>Sosialisasi Tanam</h4></th>
            </tr>
        </table>
        @php 
            $totalBibitSostam = 0;
            $listBibitSostam = [];
            $listExistBibitSostam = [];
        @endphp
        <table class="table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Field Facilitator</th>
                    <th>Petani</th>
                    <th>Lahan</th>
                    <th colspan="4">Sosialisasi Tanam</th>
                </tr>
                <tr>
                    <th>{{ $datas['dates'][1] }}</th>
                    <th>{{ $datas['dates'][3] }}</th>
                    <th>{{ $datas['dates'][4] }}</th>
                    <th>Progress</th>
                    <th>Total Bibit</th>
                    <th>Tanggal Distribusi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datas['sostam'] as $index => $data)
                    @php
                        foreach($data['total_bibit_details'] as $bibitSostam) {
                            $totalBibitSostam += $bibitSostam->amount;
                            if (in_array($bibitSostam->tree_name, $listExistBibitSostam)) {
                                $listBibitSostam[array_search($bibitSostam->tree_name, $listExistBibitSostam)]->amount += $bibitSostam->amount;
                            } else {
                                array_push($listBibitSostam, $bibitSostam);
                                array_push($listExistBibitSostam, $bibitSostam->tree_name);
                            }
                        }
                    @endphp
                    <tr>
                        <td align="center">{{ $index + 1 }}</td>
                        <td>{{ $data['ff'] }}</td>
                        <td align="center">{{ $data['total_petani'] }}</td>
                        <td align="center">{{ $data['total_lahan'] }}</td>
                        <td align="center">{{ $data['total_sostam'] }}</td>
                        <td align="center">{{ $data['progress_sostam'] }}%</td>
                        <td align="center">{{ $data['total_bibit'] }}</td>
                        <td align="center">{{ $data['distribution_time'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <table>
            <tr>
                <th colspan="4">List Bibit</th>
            </tr>
            <tr>
                <th colspan="4">{{ $totalBibitSostam }} Bibit</th>
            </tr>
        </table>
        <table class="table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th colspan="3">Seedling</th>
                </tr>
                <tr>
                    <th>Kategori</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($listBibitSostam as $indexListBibitSostamData => $listBibitSostamData)
                <tr>
                    <td align="center">{{ $indexListBibitSostamData + 1 }}</td>
                    <td>{{ $listBibitSostamData->category }}</td>
                    <td>{{ $listBibitSostamData->tree_name }}</td>
                    <td>{{ $listBibitSostamData->amount }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    
    <!-- Pelatihan Petani -->
    @if (in_array('Pelatihan Petani', $datas['activities']))
        <table>
            <tr>
                <th colspan="9"><h4>Pelatihan Petani</h4></th>
            </tr>
        </table>
        <table class="table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Management Unit</th>
                    <th rowspan="2">Field Facilitator</th>
                    <th>Total Petani</th>
                    <th colspan="5">Pelatihan Petani</th>
                </tr>
                <tr>
                    <th>{{ $datas['dates'][1] }}</th>
                    <th>Participant</th>
                    <th>Trainer</th>
                    <th>Date</th>
                    <th>Materi 1</th>
                    <th>Materi 2</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datas['pelpet'] as $pelPetIndex => $pelPet)
                <tr>
                    <td align="center">{{ $pelPetIndex + 1 }}</td>
                    <td>{{ $pelPet['mu_name'] }}</td>
                    <td>{{ $pelPet['ff'] }}</td>
                    <td align="center">{{ $pelPet['total_farmer'] }}</td>
                    <td align="center">{{ $pelPet['total_participant'] }}</td>
                    <td align="center">{{ $pelPet['trainee'] }}</td>
                    <td align="center">{{ $pelPet['training_date'] }}</td>
                    <td align="center">{{ $pelPet['materi1'] }}</td>
                    <td align="center">{{ $pelPet['materi2'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    
    <!-- Penilikan Lubang -->
    @if (in_array('Penilikan Lubang', $datas['activities']))
        <table>
            <tr></tr>
            <tr>
                <th colspan="7"><h4>Penilikan Lubang Tanam</h4></th>
            </tr>
        </table>
        @php 
            $totalBibitPenlub = 0;
            $listBibitPenlub = [];
            $listExistBibitPenlub = [];
        @endphp
        <table class="table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Field Facilitator</th>
                    <th>Petani</th>
                    <th>Lahan</th>
                    <th colspan="3">Penilikan Lubang</th>
                </tr>
                <tr>
                    <th>{{ $datas['dates'][1] }}</th>
                    <th>{{ $datas['dates'][3] }}</th>
                    <th>{{ $datas['dates'][5] }}</th>
                    <th>Progress</th>
                    <th>Total Bibit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datas['penlub'] as $index => $data)
                    @php
                        foreach($data['total_bibit_details'] as $bibitPenlub) {
                            $totalBibitPenlub += $bibitPenlub->amount;
                            if (in_array($bibitPenlub->tree_name, $listExistBibitPenlub)) {
                                $listBibitPenlub[array_search($bibitPenlub->tree_name, $listExistBibitPenlub)]->amount += $bibitPenlub->amount;
                            } else {
                                array_push($listBibitPenlub, $bibitPenlub);
                                array_push($listExistBibitPenlub, $bibitPenlub->tree_name);
                            }
                        }
                    @endphp
                    <tr>
                        <td align="center">{{ $index + 1 }}</td>
                        <td>{{ $data['ff'] }}</td>
                        <td align="center">{{ $data['total_petani'] }}</td>
                        <td align="center">{{ $data['total_lahan'] }}</td>
                        <td align="center">{{ $data['total_penlub'] }}</td>
                        <td align="center">{{ $data['progress_penlub'] }}%</td>
                        <td align="center">{{ $data['total_bibit'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <table>
            <tr>
                <th colspan="4">List Bibit</th>
            </tr>
            <tr>
                <th colspan="4">{{ $totalBibitPenlub }} Bibit</th>
            </tr>
        </table>
        <table class="table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th colspan="3">Seedling</th>
                </tr>
                <tr>
                    <th>Kategori</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($listBibitPenlub as $indexListBibitPenlubData => $listBibitPenlubData)
                <tr>
                    <td align="center">{{ $indexListBibitPenlubData + 1 }}</td>
                    <td>{{ $listBibitPenlubData->category }}</td>
                    <td>{{ $listBibitPenlubData->tree_name }}</td>
                    <td>{{ $listBibitPenlubData->amount }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    
    <!-- Distribusi -->
    @if (in_array('Distribusi', $datas['activities']))
        <table>
            <tr></tr>
            <tr>
                <th colspan="9"><h4>Distribusi</h4></th>
            </tr>
        </table>
        <table class="table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Field Facilitator</th>
                    <th>Petani</th>
                    <th colspan="2">Penilikan Lubang</th>
                    <th colspan="4">Distribusi</th>
                </tr>
                <tr>
                    <th>{{ $datas['dates'][1] }}</th>
                    <th>{{ $datas['dates'][5] }} (%)</th>
                    <th>Total Bibit</th>
                    <th>Total</th>
                    <th>Bibit All</th>
                    <th>Bibit Loaded</th>
                    <th>Bibit Distributed</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datas['distribusi'] as $index => $data)
                <tr>
                    <td align="center">{{ $index + 1 }}</td>
                    <td>{{ $data['ff'] }}</td>
                    <td align="center">{{ $data['total_petani'] }}</td>
                    <td align="center">{{ $data['progress_penlub'] }}%</td>
                    <td align="center">{{ $data['penlub_total_bibit'] }}</td>
                    <td align="center">{{ $data['total_distribusi'] }}</td>
                    <td align="center">{{ $data['total_bibit_distribusi_all'] }}</td>
                    <td align="center">{{ $data['total_bibit_distribusi_loaded'] }}</td>
                    <td align="center">{{ $data['total_bibit_distribusi_distributed'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    
    <!-- Realisasi Tanam -->
    @if (in_array('Realisasi Tanam', $datas['activities']))
        <table>
            <tr></tr>
            <tr>
                <th colspan="10"><h4>Realisasi Tanam / Monitoring 1</h4></th>
            </tr>
        </table>
        <table class="table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Field Facilitator</th>
                    <th>Petani</th>
                    <th rowspan="2">Total Distribusi</th>
                    <th colspan="2">Monitoring</th>
                    <th colspan="4">Monitoring Bibit</th>
                </tr>
                <tr>
                    <th>{{ $datas['dates'][1] }}</th>
                    <th>Total</th>
                    <th>Progress</th>
                    <th>Diterima</th>
                    <th>Tertanam Hidup</th>
                    <th>Mati</th>
                    <th>Hilang</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datas['monitoring1'] as $index => $data)
                <tr>
                    <td align="center">{{ $index + 1 }}</td>
                    <td>{{ $data['ff'] }}</td>
                    <td align="center">{{ $data['total_petani'] }}</td>
                    <td align="center">{{ $data['total_distribusi'] }}</td>
                    <td align="center">{{ $data['total_monitoring'] }}</td>
                    <td align="center">{{ $data['progress_monitoring'] }}%</td>
                    <td align="center">{{ $data['total_seed_received'] }}</td>
                    <td align="center">{{ $data['total_seed_planted_live'] }}</td>
                    <td align="center">{{ $data['total_seed_dead'] }}</td>
                    <td align="center">{{ $data['total_seed_lost'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>