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
                    <th colspan="3">Bibit <?php echo e($datas['activity'] == 'sostam' ? 'Sosialisasi Tanam' : 'Penilikan Lubang'); ?></th>
                </tr>
                <tr>
                    <th colspan="3">Export date: <?php echo e(date("d F Y")); ?></th>
                </tr>
                <tr></tr>
                <tr></tr>
                <tr>
                    <td colspan="2">
                        Tahun Program :
                    </td>
                    <td>
                        <?php echo e($datas['program_year']); ?>

                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        Tanggal Distribusi :
                    </td>
                    <td>
                        <?php echo e($datas['distribution_date']); ?>

                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        Total Bibit :
                    </td>
                    <td>
                        <?php echo e($datas['total_bibit']); ?>

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
                    <?php $__currentLoopData = $datas['FF_details']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ffIndex => $ff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($ffIndex + 1); ?></td>
                        <td><?php echo e($ff->name); ?></td>
                        <td><?php echo e($ff->village_name); ?></td>
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
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/exportBibitByFF.blade.php ENDPATH**/ ?>