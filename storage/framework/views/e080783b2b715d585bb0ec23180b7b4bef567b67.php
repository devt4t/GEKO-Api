<html>
<head>
    <title>Lahans Export | GEKO</title>
    <style>
        body {
            /*font-family: Poppins, sans-serif;*/
        }
        .table, .table th, .table td{
          border: 0.5px solid black; 
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

        $nama = 'Data Lahan - PY_' . $data->py . ' - ET_'. date("Ymd") .'.xls';
        // echo $nama;
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=".$nama);
        
        $capColumn = 8;
	?>
	<!-- Title -->
	<table>
	    <tr>
    	    <th colspan="<?php echo e($capColumn); ?>"><h2>Data Lahan - Program Year <?php echo e($data->py); ?></h2></th>
	    </tr>
	    <tr>
    	    <td align="center" colspan="<?php echo e($capColumn); ?>">Export Time: <?php echo e(date("d/m/Y_h:i:s")); ?></th>
	    </tr>
	</table>
	<table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Management Unit</th>
                    <th>Target Area</th>
                    <th>Village</th>
                    <th>FC</th>
                    <th>FF</th>
                    <th>Farmer</th>
                    <th>Lahan No</th>
                    <th>Document No</th>
                    <th>Sppt Type</th>
                    <th>Land Type</th>
                    <th>Land Shape</th>
                    <th>Land Area (m<sup>2</sup>)</th>
                    <th>Land Cover (%)</th>
                    <th>Planting Area (m<sup>2</sup>)</th>
                    <th>Land Distance</th>
                    <th>Land Access</th>
                    <th>Water Availability</th>
                    <th>Water Access</th>
                    <th>Planting Pattern</th>
                    <th>Fertilizer</th>
                    <th>Pesticide</th>
                    <th>Description</th>
                    <th>Longitude</th>
                    <th>Latitude</th>
                    <th>Coordinate</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $typeSppt = ['Pribadi', 'Keterkaitan Keluarga', 'Umum', 'Lain - Lain'];
                ?>
                <?php $__currentLoopData = $data->data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lIndex => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td align="center"><?php echo e($lIndex + 1); ?></td>
                    <td><?php echo e($l->ManagementUnit); ?></td>
                    <td><?php echo e($l->TargetArea); ?></td>
                    <td><?php echo e($l->Village); ?></td>
                    <td><?php echo e($l->FC); ?></td>
                    <td><?php echo e($l->FF); ?></td>
                    <td><?php echo e($l->Farmer); ?></td>
                    <td><?php echo e($l->LahanNo); ?></td>
                    <td>'<?php echo e($l->DocumentNo); ?></td>
                    <td><?php echo e($typeSppt[$l->SPPTType]); ?></td>
                    <td><?php echo e($l->LandType); ?></td>
                    <td><?php echo e($l->LandShape); ?></td>
                    <td><?php echo e($l->LandArea); ?></td>
                    <td><?php echo e($l->LandCover); ?></td>
                    <td><?php echo e($l->PlantingArea); ?></td>
                    <td><?php echo e($l->LandDistance); ?></td>
                    <td><?php echo e($l->LandAccess); ?></td>
                    <td><?php echo e($l->WaterAvailability); ?></td>
                    <td><?php echo e($l->WaterAccess); ?></td>
                    <td><?php echo e($l->PlantingPattern); ?></td>
                    <td><?php echo e($l->Fertilizer); ?></td>
                    <td><?php echo e($l->Pesticide); ?></td>
                    <td><?php echo e($l->Description); ?></td>
                    <td><?php echo e($l->Longitude); ?></td>
                    <td><?php echo e($l->Latitude); ?></td>
                    <td><?php echo e($l->Coordinate); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
	
</body>
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/ExportDataLahansPerPY.blade.php ENDPATH**/ ?>