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

        $nama = 'Bibit Lahan Umum_' . $datas['activity'].date("Ymd_h-i-s").'.xls';
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=".$nama);
	?>
    
    <div class="flex-center position-ref full-height">
        <div class="content">
            <!-- Title + General Desc -->
            <table>
                <tr>
                    <th colspan="5">Bibit {{ $datas['activity'] == 'lahan' ? 'Pendataan Lahan' : 'Penilikan Lubang' }}</th>
                </tr>
                <tr>
                    <th colspan="5">Export date: {{ date("d F Y") }}</th>
                </tr>
                <tr></tr>
                <tr></tr>
                <tr>
                    <td colspan="2">
                        Tahun Program :
                    </td>
                    <td colspan="3">
                        {{ $datas['program_year'] }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        Tanggal Distribusi :
                    </td>
                    <td colspan="3">
                        {{ $datas['distribution_date'] }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        Total Bibit :
                    </td>
                    <td colspan="3">
                        {{ $datas['total_bibit'] }} Bibit
                    </td>
                </tr>
                <tr></tr>
                <tr></tr>
            </table>
            
            <!-- Table PIC -->
            <table>
                <tr></tr>
                <tr>
                    <th colspan="5">PIC T4T & Lahan</th>
                </tr>
            </table>
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Lahan No</th>
                        <th>PIC T4T</th>
                        <th>PIC Lahan</th>
                        <th>Desa</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($datas['pic_details'] as $picIndex => $pic)
                    <tr>
                        <td>{{ $picIndex + 1 }}</td>
                        <td>{{ $pic->lahan_no }}</td>
                        <td>{{ \App\Employee::where('nik', $pic->employee_no)->first()->name ?? '-' }}</td>
                        <td>{{ $pic->pic_lahan }}</td>
                        <td>{{ $pic->village_name }}</td>
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