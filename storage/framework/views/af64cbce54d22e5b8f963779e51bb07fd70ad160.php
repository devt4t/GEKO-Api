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
	    <!--<?php echo e($datas); ?>-->
	    <tr>
    	    <th colspan="<?php echo e($capColumn); ?>"><h2>Penilikan Lubang Tanam</h2></th>
	    </tr>
	    <tr>
    	    <td align="center" colspan="<?php echo e($capColumn); ?>">Export Time: <?php echo e(date("d/m/Y_h:i:s")); ?></th>
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
	        <?php $__currentLoopData = $datas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    	        <?php if($data->is_validate == 1): ?>
        	        <tr style="background-color: #bceb9d">
        	    <?php else: ?>
        	        <tr style="background-color: #ebab9d">
    	        <?php endif; ?>
    	            <td><?php echo e($index + 1); ?></td>
    	            <td><?php echo e($data->um_name); ?></td>
    	            <td><?php echo e($data->fc_name); ?></td>
    	            <td><?php echo e($data->ff_name); ?></td>
    	            <td><?php echo e($data->mu_name); ?></td>
    	            <td><?php echo e($data->ta_name); ?></td>
    	            <td><?php echo e($data->village_name); ?></td>
    	            <td><?php echo e($data->farmer_name); ?></td>
    	            <td align="center"><?php echo e($data->holes); ?></td>
    	            <td align="center"><?php echo e($data->holes_standard); ?></td>
    	            <td><?php echo e($data->is_validate == 1 ? 'Sudah Verifikasi' : 'Belum Verifikasi'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
	    </tbody>
	</table>
</body>
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/exportExcelPenilikanLubangTanam.blade.php ENDPATH**/ ?>