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
    </style>
</head>
<body>
    <?php
               
        date_default_timezone_set("Asia/Bangkok");

        $nama = 'Distribution Report - ' . $main->farmer_name . '_' . date("Ymd_h-i-s").'.xls';
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=".$nama);
	?>
	<!-- Title -->
	<table>
	    <tr>
    	    <th colspan="9"><h3>Distribution Report</h3></th>
	    </tr>
	    <tr>
    	    <td align="center" colspan="9">Export Time: <?php echo e(date("d/m/Y_h:i:s")); ?></th>
	    </tr>
	</table>
	
    <!-- Main Data -->
    <table>
        <tr>
            <td colspan="2">
                Field Facilitator
            </td>
            <th>:</th>
            <td colspan="6">
                <?php echo e($main->ff_name); ?>

            </td>
        </tr>
        <tr>
            <td colspan="2">
                Farmer
            </td>
            <th>:</th>
            <td colspan="6">
                <?php echo e($main->farmer_name); ?>

            </td>
        </tr>
        <tr>
            <td colspan="2">
                Distribution Date
            </td>
            <th>:</th>
            <td colspan="6">
                <?php echo e(date('l, d F Y', strtotime($main->distribution_date))); ?>

            </td>
        </tr>
        <tr>
            <td colspan="2">
                Distribution Note
            </td>
            <th>:</th>
            <td colspan="6">
                <?php echo e($main->distribution_note ?? '-'); ?>

            </td>
        </tr>
        <tr>
            <td colspan="2">
                Total Bags
            </td>
            <th>:</th>
            <td colspan="6">
                <?php echo e(number_format($main->total_bags)); ?> Bags
            </td>
        </tr>
        <tr>
            <td colspan="2">
                Total Seedling
            </td>
            <th>:</th>
            <td colspan="6">
                <?php echo e(number_format($main->total_tree_amount)); ?> Bibit
            </td>
        </tr>
        <tr>
            <td colspan="2">
                PIC Loading
            </td>
            <th>:</th>
            <td colspan="6">
                <?php echo e($main->loaded_by ?? '-'); ?>

            </td>
        </tr>
        <tr>
            <td colspan="2">
                PIC Distribute
            </td>
            <th>:</th>
            <td colspan="6">
                <?php echo e($main->distributed_by ?? '-'); ?>

            </td>
        </tr>
        <tr>
            <td colspan="2">
                PIC Verify
            </td>
            <th>:</th>
            <td colspan="6">
                <?php echo e($main->approved_by ?? '-'); ?>

            </td>
        </tr>
    </table>
    
    <!-- Detail Bag Data -->
    <table class="table">
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Bag Number</th>
                <th colspan="3">Seedling</th>
                <th colspan="2">Loading</th>
                <th colspan="2">Distributed</th>
            </tr>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Status</th>
                <th>PIC</th>
                <th>Status</th>
                <th>PIC</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sIndex => $seed): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td align="center"><?php echo e($sIndex + 1); ?></td>
                <td><?php echo e($seed->bag_number); ?></td>
                <td><?php echo e($seed->tree_name); ?></td>
                <td align="center"><?php echo e($seed->tree_category); ?></td>
                <td align="center"><?php echo e($seed->tree_amount); ?></td>
                <?php if($seed->is_loaded): ?>
                    <td align="center" style="background-color: #0f0">
                <?php else: ?>
                    <td align="center" style="background-color: #f00">
                <?php endif; ?>
                    <?php echo e($seed->is_loaded); ?>

                </td>
                <td><?php echo e($seed->loaded_by ?? '-'); ?></td>
                <?php if($seed->is_distributed): ?>
                    <td align="center" style="background-color: #0f0">
                <?php else: ?>
                    <td align="center" style="background-color: #f00">
                <?php endif; ?>
                    <?php echo e($seed->is_distributed); ?>

                </td>
                <td><?php echo e($seed->distributed_by ?? '-'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/exportDistributionByFarmer.blade.php ENDPATH**/ ?>