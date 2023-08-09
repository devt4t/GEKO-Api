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
                    <?php $__currentLoopData = $data['trees_data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tree): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> 
                        <th colspan="7"><?php echo e($tree->tree_name); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
                <tr>
                    <?php $__currentLoopData = $data['trees_data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tree): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> 
                        <th>sostam</th>
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
                    <td><?php echo e($d->DistributionDate); ?></td>
                    <td><?php echo e($d->PlantingDate); ?></td>
                    <td><?php echo e($d->Nursery); ?></td>
                    <td><?php echo e($d->MU); ?></td>
                    <td><?php echo e($d->TA); ?></td>
                    <td><?php echo e($d->Village); ?></td>
                    <td><?php echo e($d->UM); ?></td>
                    <td><?php echo e($d->FC); ?></td>
                    <td><?php echo e($d->FF); ?></td>
                    <td><?php echo e($d->Farmer); ?></td>
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
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/temp/ExportAllDataPerFF.blade.php ENDPATH**/ ?>