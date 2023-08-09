<html>
<head>
    <title>Distribution Export | GEKO</title>
    <style>
        body {
            /*font-family: Poppins, sans-serif;*/
        }
        .table, .table th, .table td{
          /*border: 1px solid black; */
          /*border-collapse: collapse;*/
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
            // window.open(`https://t4tapi.kolaborasikproject.com/api/GetDistributionReportFullOutside?download=true&per=<?php echo $data['per']; ?>&program_year=<?php echo $data['py']; ?>&ff_no=${downloadFF}`, "_blank");
        // }
    </script>
	<!-- Title -->
	<table>
	    <tr>
    	    <th colspan="<?php echo e($capColumn); ?>"><h2><?php echo e($nama); ?></h2></th>
	    </tr>
	    <tr>
    	    <td align="center" colspan="<?php echo e($capColumn); ?>">Export Time: <?php echo e(date("d/m/Y_h:i:s")); ?></th>
	    </tr>
	</table>
	<table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Distribution Date</th>
                    <th>Planting Date</th>
                    <th>Nursery</th>
                    <th>MU</th>
                    <th>TA</th>
                    <th>Village</th>
                    <th>UM</th>
                    <th>FC</th>
                    <th>FF</th>
                    <th>Farmer</th>
                    <th>Planting Pattern</th>
                </tr>
            </thead>
            <tbody>
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
                            <td><?php echo e($j); ?></td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
	
</body>
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/ExportAllDataPerFF.blade.php ENDPATH**/ ?>