<html>
<head>
    <title>Export Bibit </title>
    <style>
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

        $nama = 'Bibit ' . $datas['activity'].date("Ymd_h-i-s").'.xls';
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=".$nama);
	?>
    
    <div class="flex-center position-ref full-height">
        <div class="content">
            <!-- Title + General Desc -->
            <table>
                <tr>
                    <th colspan="3">Bibit {{ $datas['activity'] == 'sostam' ? 'Sosialisasi Tanam' : 'Penilikan Lubang' }}</th>
                </tr>
                <tr>
                    <th colspan="3">Export date: {{ date("d F Y") }}</th>
                </tr>
                <tr></tr>
                <tr></tr>
                <tr>
                    <td colspan="2">
                        Tahun Program :
                    </td>
                    <td>
                        {{ $datas['program_year'] }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        Tanggal Distribusi :
                    </td>
                    <td>
                        {{ $datas['distribution_date'] }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        Total Bibit :
                    </td>
                    <td>
                        {{ $datas['total_bibit'] }}
                    </td>
                </tr>
                <tr></tr>
                <tr></tr>
            </table>
            
            <!-- Table FF -->
            <table>
                <tr></tr>
                <tr>
                    <th colspan="3">Field Facilitator Data</th>
                </tr>
            </table>
            <table class="table">
                <thead>
                    <tr>
                        <th rowspan="2">No</th>
                        <th colspan="2">Field Facilitator(s)</th>
                    </tr>
                    <tr>
                        <th>Nama</th>
                        <th>Desa</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($datas['FF_details'] as $ffIndex => $ff)
                    <tr>
                        <td>{{ $ffIndex + 1 }}</td>
                        <td>{{ $ff->name }}</td>
                        <td>{{ $ff->village_name }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Table Bibit KAYU -->
            <table>
                <tr></tr>
                <tr>
                    <th colspan="3">Detail Bibit KAYU</th>
                </tr>
            </table>
            <table class="table">
                <thead>
                    <tr>
                        <th rowspan="2">No</th>
                        <th colspan="2">Bibit</th>
                    </tr>
                    <tr>
                        <th>Nama</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($datas['total_bibit_details']['KAYU'] as $treeIndex => $tree)
                    <tr>
                        <td>{{ $treeIndex + 1 }}</td>
                        <td>{{ $tree->tree_name }}</td>
                        <td>{{ $tree->amount }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Table Bibit MPTS -->
            <table>
                <tr></tr>
                <tr>
                    <th colspan="3">Detail Bibit MPTS</th>
                </tr>
            </table>
            <table class="table">
                <thead>
                    <tr>
                        <th rowspan="2">No</th>
                        <th colspan="2">Bibit</th>
                    </tr>
                    <tr>
                        <th>Nama</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($datas['total_bibit_details']['MPTS'] as $treeIndex => $tree)
                    <tr>
                        <td>{{ $treeIndex + 1 }}</td>
                        <td>{{ $tree->tree_name }}</td>
                        <td>{{ $tree->amount }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Table Bibit CROPS -->
            <table>
                <tr></tr>
                <tr>
                    <th colspan="3">Detail Bibit CROPS</th>
                </tr>
            </table>
            <table class="table">
                <thead>
                    <tr>
                        <th rowspan="2">No</th>
                        <th colspan="2">Bibit</th>
                    </tr>
                    <tr>
                        <th>Nama</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($datas['total_bibit_details']['CROPS'] as $treeIndex => $tree)
                    <tr>
                        <td>{{ $treeIndex + 1 }}</td>
                        <td>{{ $tree->tree_name }}</td>
                        <td>{{ $tree->amount }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>