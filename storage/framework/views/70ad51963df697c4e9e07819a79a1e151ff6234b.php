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
        .table td {
          vertical-align: top;
        }
    </style>
</head>
<body>
    <?php
        date_default_timezone_set("Asia/Bangkok");

        $nama = 'Activities Report - ' . $datas['fc'] . '_' . date("Ymd_h-i-s").'.xls';
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=".$nama);
        if ((in_array('Pendataan Petani & Lahan', $datas['activities'])) || (in_array('Realisasi Tanam', $datas['activities']))) $capColumn = 10;
        else if ((in_array('Distribusi', $datas['activities'])) || (in_array('Pelatihan Petani', $datas['activities']))) $capColumn = 9;
        else if ((in_array('Penilikan Lubang', $datas['activities']))) $capColumn = 7;
        else $capColumn = 8;
	?>
	<!-- Title -->
	<table>
	    <tr>
    	    <th colspan="<?php echo e($capColumn); ?>"><h2>Activities Progression</h2></th>
	    </tr>
	    <tr>
    	    <td align="center" colspan="<?php echo e($capColumn); ?>">Export Time: <?php echo e(date("d/m/Y_h:i:s")); ?></th>
	    </tr>
	</table>
	
    <!-- General Data -->
    <table>
        <tr>
            <td colspan="2">
                Tahun Program
            </td>
            <th align="right">:</th>
            <td colspan="<?php echo e($capColumn - 3); ?>">
                <?php echo e($datas['program_year']); ?>

            </td>
        </tr>
        <tr>
            <td colspan="2">
                Unit Manager
            </td>
            <th align="right">:</th>
            <td colspan="<?php echo e($capColumn - 3); ?>">
                <?php echo e($datas['um']); ?>

            </td>
        </tr>
        <tr>
            <td colspan="2">
                Field Coordinator
            </td>
            <th align="right">:</th>
            <td colspan="<?php echo e($capColumn - 3); ?>">
                <?php echo e($datas['fc']); ?>

            </td>
        </tr>
    </table>
    
    <!-- Activities Data -->
    <!-- Pendataan Petani & Lahan -->
    <?php if(in_array('Pendataan Petani & Lahan', $datas['activities'])): ?>
        <table>
            <tr>
                <th colspan="10"><h4>Pendataan Petani & Lahan</h4></th>
            </tr>
        </table>
        <table class="table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Field Facilitator</th>
                    <th colspan="4">Petani</th>
                    <th colspan="4">Lahan</th>
                </tr>
                <tr>
                    <th><?php echo e($datas['dates'][0]); ?></th>
                    <th><?php echo e($datas['dates'][1]); ?></th>
                    <th>Total</th>
                    <th>Progress</th>
                    <th><?php echo e($datas['dates'][2]); ?></th>
                    <th><?php echo e($datas['dates'][3]); ?></th>
                    <th>Total</th>
                    <th>Progress</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $datas['petani_lahan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $petLahanIndex => $petLahan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td align="center"><?php echo e($petLahanIndex + 1); ?></td>
                    <td><?php echo e($petLahan['ff']); ?></td>
                    <td align="center"><?php echo e($petLahan['petani']['petani1']); ?></td>
                    <td align="center"><?php echo e($petLahan['petani']['petani2']); ?></td>
                    <td align="center"><?php echo e($petLahan['petani']['total_petani']); ?></td>
                    <td align="center"><?php echo e($petLahan['petani']['progress_petani']); ?>%</td>
                    <td align="center"><?php echo e($petLahan['lahan']['lahan1']); ?></td>
                    <td align="center"><?php echo e($petLahan['lahan']['lahan2']); ?></td>
                    <td align="center"><?php echo e($petLahan['lahan']['total_lahan']); ?></td>
                    <td align="center"><?php echo e($petLahan['lahan']['progress_lahan']); ?>%</td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <!-- Sosialisasi Tanam -->
    <?php if(in_array('Sosialisasi Tanam', $datas['activities'])): ?>
        <table>
            <tr></tr>
            <tr>
                <th colspan="8"><h4>Sosialisasi Tanam</h4></th>
            </tr>
        </table>
        <?php 
            $totalBibitSostam = 0;
            $listBibitSostam = [];
            $listExistBibitSostam = [];
        ?>
        <table class="table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Field Facilitator</th>
                    <th>Petani</th>
                    <th>Lahan</th>
                    <th colspan="4">Sosialisasi Tanam</th>
                </tr>
                <tr>
                    <th><?php echo e($datas['dates'][1]); ?></th>
                    <th><?php echo e($datas['dates'][3]); ?></th>
                    <th><?php echo e($datas['dates'][4]); ?></th>
                    <th>Progress</th>
                    <th>Total Bibit</th>
                    <th>Tanggal Distribusi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $datas['sostam']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        foreach($data['total_bibit_details'] as $bibitSostam) {
                            $totalBibitSostam += $bibitSostam->amount;
                            if (in_array($bibitSostam->tree_name, $listExistBibitSostam)) {
                                $listBibitSostam[array_search($bibitSostam->tree_name, $listExistBibitSostam)]->amount += $bibitSostam->amount;
                            } else {
                                array_push($listBibitSostam, $bibitSostam);
                                array_push($listExistBibitSostam, $bibitSostam->tree_name);
                            }
                        }
                    ?>
                    <tr>
                        <td align="center"><?php echo e($index + 1); ?></td>
                        <td><?php echo e($data['ff']); ?></td>
                        <td align="center"><?php echo e($data['total_petani']); ?></td>
                        <td align="center"><?php echo e($data['total_lahan']); ?></td>
                        <td align="center"><?php echo e($data['total_sostam']); ?></td>
                        <td align="center"><?php echo e($data['progress_sostam']); ?>%</td>
                        <td align="center"><?php echo e($data['total_bibit']); ?></td>
                        <td align="center"><?php echo e($data['distribution_time']); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <table>
            <tr>
                <th colspan="4">List Bibit</th>
            </tr>
            <tr>
                <th colspan="4"><?php echo e($totalBibitSostam); ?> Bibit</th>
            </tr>
        </table>
        <table class="table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th colspan="3">Seedling</th>
                </tr>
                <tr>
                    <th>Kategori</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $listBibitSostam; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indexListBibitSostamData => $listBibitSostamData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td align="center"><?php echo e($indexListBibitSostamData + 1); ?></td>
                    <td><?php echo e($listBibitSostamData->category); ?></td>
                    <td><?php echo e($listBibitSostamData->tree_name); ?></td>
                    <td><?php echo e($listBibitSostamData->amount); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <!-- Pelatihan Petani -->
    <?php if(in_array('Pelatihan Petani', $datas['activities'])): ?>
        <table>
            <tr>
                <th colspan="9"><h4>Pelatihan Petani</h4></th>
            </tr>
        </table>
        <table class="table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Management Unit</th>
                    <th rowspan="2">Field Facilitator</th>
                    <th>Total Petani</th>
                    <th colspan="5">Pelatihan Petani</th>
                </tr>
                <tr>
                    <th><?php echo e($datas['dates'][1]); ?></th>
                    <th>Participant</th>
                    <th>Trainer</th>
                    <th>Date</th>
                    <th>Materi 1</th>
                    <th>Materi 2</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $datas['pelpet']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pelPetIndex => $pelPet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td align="center"><?php echo e($pelPetIndex + 1); ?></td>
                    <td><?php echo e($pelPet['mu_name']); ?></td>
                    <td><?php echo e($pelPet['ff']); ?></td>
                    <td align="center"><?php echo e($pelPet['total_farmer']); ?></td>
                    <td align="center"><?php echo e($pelPet['total_participant']); ?></td>
                    <td align="center"><?php echo e($pelPet['trainee']); ?></td>
                    <td align="center"><?php echo e($pelPet['training_date']); ?></td>
                    <td align="center"><?php echo e($pelPet['materi1']); ?></td>
                    <td align="center"><?php echo e($pelPet['materi2']); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <!-- Penilikan Lubang -->
    <?php if(in_array('Penilikan Lubang', $datas['activities'])): ?>
        <table>
            <tr></tr>
            <tr>
                <th colspan="7"><h4>Penilikan Lubang Tanam</h4></th>
            </tr>
        </table>
        <?php 
            $totalBibitPenlub = 0;
            $listBibitPenlub = [];
            $listExistBibitPenlub = [];
        ?>
        <table class="table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Field Facilitator</th>
                    <th>Petani</th>
                    <th>Lahan</th>
                    <th colspan="3">Penilikan Lubang</th>
                </tr>
                <tr>
                    <th><?php echo e($datas['dates'][1]); ?></th>
                    <th><?php echo e($datas['dates'][3]); ?></th>
                    <th><?php echo e($datas['dates'][5]); ?></th>
                    <th>Progress</th>
                    <th>Total Bibit</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $datas['penlub']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        foreach($data['total_bibit_details'] as $bibitPenlub) {
                            $totalBibitPenlub += $bibitPenlub->amount;
                            if (in_array($bibitPenlub->tree_name, $listExistBibitPenlub)) {
                                $listBibitPenlub[array_search($bibitPenlub->tree_name, $listExistBibitPenlub)]->amount += $bibitPenlub->amount;
                            } else {
                                array_push($listBibitPenlub, $bibitPenlub);
                                array_push($listExistBibitPenlub, $bibitPenlub->tree_name);
                            }
                        }
                    ?>
                    <tr>
                        <td align="center"><?php echo e($index + 1); ?></td>
                        <td><?php echo e($data['ff']); ?></td>
                        <td align="center"><?php echo e($data['total_petani']); ?></td>
                        <td align="center"><?php echo e($data['total_lahan']); ?></td>
                        <td align="center"><?php echo e($data['total_penlub']); ?></td>
                        <td align="center"><?php echo e($data['progress_penlub']); ?>%</td>
                        <td align="center"><?php echo e($data['total_bibit']); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <table>
            <tr>
                <th colspan="4">List Bibit</th>
            </tr>
            <tr>
                <th colspan="4"><?php echo e($totalBibitPenlub); ?> Bibit</th>
            </tr>
        </table>
        <table class="table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th colspan="3">Seedling</th>
                </tr>
                <tr>
                    <th>Kategori</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $listBibitPenlub; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indexListBibitPenlubData => $listBibitPenlubData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td align="center"><?php echo e($indexListBibitPenlubData + 1); ?></td>
                    <td><?php echo e($listBibitPenlubData->category); ?></td>
                    <td><?php echo e($listBibitPenlubData->tree_name); ?></td>
                    <td><?php echo e($listBibitPenlubData->amount); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <!-- Distribusi -->
    <?php if(in_array('Distribusi', $datas['activities'])): ?>
        <table>
            <tr></tr>
            <tr>
                <th colspan="9"><h4>Distribusi</h4></th>
            </tr>
        </table>
        <table class="table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Field Facilitator</th>
                    <th>Petani</th>
                    <th colspan="2">Penilikan Lubang</th>
                    <th colspan="4">Distribusi</th>
                </tr>
                <tr>
                    <th><?php echo e($datas['dates'][1]); ?></th>
                    <th><?php echo e($datas['dates'][5]); ?> (%)</th>
                    <th>Total Bibit</th>
                    <th>Total</th>
                    <th>Bibit All</th>
                    <th>Bibit Loaded</th>
                    <th>Bibit Distributed</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $datas['distribusi']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td align="center"><?php echo e($index + 1); ?></td>
                    <td><?php echo e($data['ff']); ?></td>
                    <td align="center"><?php echo e($data['total_petani']); ?></td>
                    <td align="center"><?php echo e($data['progress_penlub']); ?>%</td>
                    <td align="center"><?php echo e($data['penlub_total_bibit']); ?></td>
                    <td align="center"><?php echo e($data['total_distribusi']); ?></td>
                    <td align="center"><?php echo e($data['total_bibit_distribusi_all']); ?></td>
                    <td align="center"><?php echo e($data['total_bibit_distribusi_loaded']); ?></td>
                    <td align="center"><?php echo e($data['total_bibit_distribusi_distributed']); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <!-- Realisasi Tanam -->
    <?php if(in_array('Realisasi Tanam', $datas['activities'])): ?>
        <table>
            <tr></tr>
            <tr>
                <th colspan="10"><h4>Realisasi Tanam / Monitoring 1</h4></th>
            </tr>
        </table>
        <table class="table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Field Facilitator</th>
                    <th>Petani</th>
                    <th rowspan="2">Total Distribusi</th>
                    <th colspan="2">Monitoring</th>
                    <th colspan="4">Monitoring Bibit</th>
                </tr>
                <tr>
                    <th><?php echo e($datas['dates'][1]); ?></th>
                    <th>Total</th>
                    <th>Progress</th>
                    <th>Diterima</th>
                    <th>Tertanam Hidup</th>
                    <th>Mati</th>
                    <th>Hilang</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $datas['monitoring1']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td align="center"><?php echo e($index + 1); ?></td>
                    <td><?php echo e($data['ff']); ?></td>
                    <td align="center"><?php echo e($data['total_petani']); ?></td>
                    <td align="center"><?php echo e($data['total_distribusi']); ?></td>
                    <td align="center"><?php echo e($data['total_monitoring']); ?></td>
                    <td align="center"><?php echo e($data['progress_monitoring']); ?>%</td>
                    <td align="center"><?php echo e($data['total_seed_received']); ?></td>
                    <td align="center"><?php echo e($data['total_seed_planted_live']); ?></td>
                    <td align="center"><?php echo e($data['total_seed_dead']); ?></td>
                    <td align="center"><?php echo e($data['total_seed_lost']); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/exportKPIByFC.blade.php ENDPATH**/ ?>