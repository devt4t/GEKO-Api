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
    	    <th align="left" colspan="7"><h2><?php echo e($nama); ?></h2></th>
	    </tr>
	    <tr>
    	    <th align="left" colspan="7">Export Time: <?php echo e(date("d/m/Y_h:i:s")); ?></th>
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
                    <?php $__currentLoopData = $trees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tree): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th><?php echo e($tree->tree_name); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dIndex => $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td align="center"><?php echo e($dIndex + 1); ?></td>
                    <td><?php echo e($d->mu_name); ?></td>
                    <td><?php echo e($d->ta_name); ?></td>
                    <td><?php echo e($d->village_name); ?></td>
                    <td><?php echo e($d->farmer_name); ?></td>
                    <td><?php echo e($d->lahan_no); ?></td>
                    <td><?php echo e($d->document_no); ?></td>
                    <td><?php echo e($d->program_year); ?></td>
                    <td><?php echo e($d->monitoring_no); ?></td>
                    <?php $__currentLoopData = $trees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tree): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php 
                            $amount = $d[$tree->tree_code] ?? 0;
                        ?>
                        <td style="background: <?= $amount > 0 ? '#34eb52' : ''; ?>"><?php echo e($amount); ?></td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
	
</body>
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/temp/ExportLahanSPPTRequired.blade.php ENDPATH**/ ?>