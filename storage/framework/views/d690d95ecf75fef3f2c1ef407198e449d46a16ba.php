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

        $nama = 'Lahan Umum - ' . $data['py'] . '_' . ($data['distributions'][0]->DistributionDate ?? '-') . '.xls';
        if ($data['download'] == 'true') {
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=".$nama);
        }
        
        $capColumn = 8;
	?>
	<!-- Title -->
	<table>
	    <tr>
    	    <th align="left" colspan="<?php echo e($capColumn); ?>"><h2><?php echo e($nama); ?></h2></th>
	    </tr>
	    <tr>
    	    <td align="left" colspan="<?php echo e($capColumn); ?>">Export Time: <?php echo e(date("d/m/Y_h:i:s")); ?></th>
	    </tr>
	</table>
	<table class="table" border="1">
            <thead>
                <tr>
                    <th rowspan="3">No</th>
                    <th rowspan="3">MoU No</th>
                    <th rowspan="3">Distribution Date</th>
                    <th rowspan="3">Planting Date</th>
                    <th rowspan="3">Nursery</th>
                    <th rowspan="3">MU</th>
                    <th rowspan="3">Regency</th>
                    <th rowspan="3">District</th>
                    <th rowspan="3">Village</th>
                    <th rowspan="3">PIC T4T</th>
                    <th rowspan="3">PIC Lahan</th>
                    <th rowspan="3">Planting Pattern</th>
                    <?php if($data['trees_count']->crops > 0): ?>
                        <th style="background: #43fadf" colspan="<?= $data['trees_count']->crops * 7 ?>">CROPS</th>
                    <?php endif; ?>
                    <th style="background: #563232;color: #fff" colspan="<?= $data['trees_count']->kayu * 7 ?>">KAYU</th>
                    <th style="background: #E1E96B" colspan="<?= $data['trees_count']->mpts * 7 ?>">MPTS</th>
                </tr>
                <tr>
                    <?php $__currentLoopData = $data['trees_data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tree): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> 
                        <th colspan="7"><?php echo e($tree->tree_name); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
                <tr>
                    <?php $__currentLoopData = $data['trees_data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tree): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> 
                        <th>input</th>
                        <th>penlub</th>
                        <th>loaded</th>
                        <th>received</th>
                        <th>planted_live</th>
                        <th>dead</th>
                        <th>lost</th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </thead>
            <tbody>
                <?php
                    function getBGColorSeed($index, $amount) {
                        if ((int)$amount > 0) {
                            if (strpos($index, 'dead')) return '#f0a856';
                            else if (strpos($index, 'lost')) return '#ff6052';
                            else return '#34eb52';
                        } else return '';
                    }
                ?>
                <?php $__currentLoopData = $data['distributions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dIndex => $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td align="center"><?php echo e($d->No); ?></td>
                    <td><?php echo e($d->MoUNo); ?></td>
                    <td><?php echo e($d->DistributionDate); ?></td>
                    <td><?php echo e($d->PlantingDate); ?></td>
                    <td><?php echo e($d->Nursery); ?></td>
                    <td><?php echo e($d->MU); ?></td>
                    <td><?php echo e($d->Regency); ?></td>
                    <td><?php echo e($d->District); ?></td>
                    <td><?php echo e($d->Village); ?></td>
                    <td><?php echo e($d->pic_t4t); ?></td>
                    <td><?php echo e($d->pic_lahan); ?></td>
                    <td><?php echo e($d->PlantingPattern); ?></td>
                    <?php if(isset($d->jenis_bibit)): ?>
                        <?php $__currentLoopData = $d->jenis_bibit; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jIndex => $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> 
                            <td style="background: <?= getBGColorSeed($jIndex, $j) ?>"><?php echo e($j); ?></td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
	
</body>
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/temp/ExportAllDataLahanUmum.blade.php ENDPATH**/ ?>