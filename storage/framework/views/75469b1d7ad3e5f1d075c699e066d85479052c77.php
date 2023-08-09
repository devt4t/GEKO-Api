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
                    <th colspan="5">Bibit <?php echo e($datas['activity'] == 'lahan' ? 'Pendataan Lahan' : 'Penilikan Lubang'); ?></th>
                </tr>
                <tr>
                    <th colspan="5">Export date: <?php echo e(date("d F Y")); ?></th>
                </tr>
                <tr></tr>
                <tr></tr>
                <tr>
                    <td colspan="2">
                        Tahun Program :
                    </td>
                    <td colspan="3">
                        <?php echo e($datas['program_year']); ?>

                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        Tanggal Distribusi :
                    </td>
                    <td colspan="3">
                        <?php echo e($datas['distribution_date']); ?>

                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        Total Bibit :
                    </td>
                    <td colspan="3">
                        <?php echo e($datas['total_bibit']); ?> Bibit
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
                    <?php $__currentLoopData = $datas['pic_details']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $picIndex => $pic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($picIndex + 1); ?></td>
                        <td><?php echo e($pic->lahan_no); ?></td>
                        <td><?php echo e(\App\Employee::where('nik', $pic->employee_no)->first()->name ?? '-'); ?></td>
                        <td><?php echo e($pic->pic_lahan); ?></td>
                        <td><?php echo e($pic->village_name); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                    <?php $__currentLoopData = $datas['total_bibit_details']['KAYU']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $treeIndex => $tree): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($treeIndex + 1); ?></td>
                        <td><?php echo e($tree->tree_name); ?></td>
                        <td><?php echo e($tree->amount); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                    <?php $__currentLoopData = $datas['total_bibit_details']['MPTS']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $treeIndex => $tree): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($treeIndex + 1); ?></td>
                        <td><?php echo e($tree->tree_name); ?></td>
                        <td><?php echo e($tree->amount); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                    <?php $__currentLoopData = $datas['total_bibit_details']['CROPS']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $treeIndex => $tree): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($treeIndex + 1); ?></td>
                        <td><?php echo e($tree->tree_name); ?></td>
                        <td><?php echo e($tree->amount); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/exportBibitLahanUmum.blade.php ENDPATH**/ ?>