<html>
<head>
</head>
<style>
table, th, td {
  /* border: 3px solid black; */
  border-collapse: collapse;
  font-size:20px;
}
</style>
<body>
    <?php
               
        date_default_timezone_set("Asia/Bangkok");

        $nama = "Export Monitoring-".date("Ymd_h-i-s").'.xls';
        // $download = $_GET['download'] == 'true' ? true : false;
        // if ($download) {
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=".$nama);
        // }
	?>
    <div class="flex-center position-ref full-height">
        <div class="content" style="margin:50px">
            <h2>Export Data Realisasi Tanam / Monitoring 1</h2>
            <h5>Export Time: <?php echo e(date("d m Y, H:i:s")); ?></h5>
            
            <table class="table" border="1">
                <thead>
                  <tr>
                    <th rowspan="3" scope="col">No</th>
                    <th rowspan="3" scope="col">Tahun Program</th>
                    <th rowspan="3" scope="col">Management Unit</th>
                    <th rowspan="3" scope="col">Target Area</th>
                    <th rowspan="3" scope="col">Desa</th>
                    <th rowspan="3" scope="col">Field Coordinator</th>
                    <th rowspan="3" scope="col">Field Facilitator</th>
                    <th rowspan="2" colspan="3" scope="col">Data Petani</th>
                    <th rowspan="2" colspan="10" scope="col">Data Lahan</th>
                    <th rowspan="3" scope="col">Tanggal Tanam</th>
                    <th rowspan="3" scope="col">Qty Standar</th>
                    <th rowspan="3" scope="col">Verifikasi</th>
                    <th colspan="<?= count($trees) * 3 ?>" scope="col">Trees Amount</th>
                    </tr>
                    <tr>
                        <?php $__currentLoopData = $trees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tree): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th scope="col" colspan="3"><?php echo e($tree->tree_name); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                    <tr>
                        <th scope="col">Nama Petani</th>
                        <th scope="col">No KTP</th>
                        <th scope="col">Alamat</th>
                        <th scope="col">No Lahan</th>
                        <th scope="col">No Doc. Lahan</th>
                        <th scope="col">Luas Lahan</th>
                        <th scope="col">Luas Tanam</th>
                        <th scope="col">Opsi Pola Tanam</th>
                        <th scope="col">Jarak Lahan</th>
                        <th scope="col">Aksesibilitas</th>
                        <th scope="col">Koordinat</th>
                        <th scope="col">Status Lahan</th>
                        <th scope="col">Kondisi Lahan</th>
                        <?php $__currentLoopData = $trees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tree): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th scope="col">Ditanam Hidup</th>
                            <th scope="col">Mati</th>
                            <th scope="col">Hilang</th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                </thead>
                <tbody id="tableSO">
                    <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php for($i = 0; $i < count($val->lahan_no); $i++): ?>
                            <tr>
                                <th scope="row"><?php echo e($loop->iteration); ?></th>
                                <td scope="row"><?php echo e($val->program_year); ?></td>
                                <td scope="row"><?php echo e($val->mu_name); ?></td>
                                <td scope="row"><?php echo e($val->ta_name); ?></td>
                                <td scope="row"><?php echo e($val->village_name); ?></td>
                                <td scope="row"><?php echo e($val->fc_name); ?></td>
                                <td scope="row"><?php echo e($val->ff_name); ?></td>
                                <td scope="row"><?php echo e($val->farmer_name); ?></td>
                                <td scope="row">'<?php echo e($val->ktp_no); ?></td>
                                <td scope="row"><?php echo e("$val->farmer_address RT$val->farmer_rt/RW$val->farmer_rw"); ?></td>
                                <td scope="row"><?php echo e($val->lahan_no[$i]); ?></td>
                                <td scope="row">'<?php echo e($val->document_no[$i]); ?></td>
                                <td scope="row"><?php echo e($val->land_area[$i]); ?></td>
                                <td scope="row"><?php echo e($val->planting_area[$i]); ?></td>
                                <td scope="row"><?php echo e($val->planting_pattern[$i]); ?></td>
                                <td scope="row"><?php echo e($val->land_distance[$i]); ?></td>
                                <td scope="row"><?php echo e($val->access_lahan[$i]); ?></td>
                                <td scope="row"><?php echo e($val->coordinate[$i]); ?></td>
                                <td scope="row"><?php echo e($val->land_status[$i]); ?></td>
                                <td scope="row"><?php echo e($val->lahan_condition); ?></td>
                                <?php if($i == 0): ?>
                                    <td rowspan="<?= count($val->lahan_no) ?>" scope="row"><?php echo e($val->planting_date); ?></td>
                                    <td rowspan="<?= count($val->lahan_no) ?>" scope="row"><?php echo e($val->qty_std); ?></td>
                                    <td rowspan="<?= count($val->lahan_no) ?>" scope="row" style="background: <?= $val->is_validate > 0 ? ($val->is_validate == 2 ? '#34eb52' : '#f0a856') : '#ff6052'; ?>"><?php echo e($val->is_validate > 0 ? ($val->is_validate == 2 ? 'UM' : 'FC') : 'Belum'); ?></td>
                                    <?php $__currentLoopData = $val->tree_details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tree): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td scope="col" rowspan="<?= count($val->lahan_no) ?>" style="background: <?= $tree->planted_life > 0 ? '#34eb52' : ''; ?>"><?php echo e($tree->planted_life); ?></td>
                                        <td scope="col" rowspan="<?= count($val->lahan_no) ?>" style="background: <?= $tree->dead > 0 ? '#f0a856' : ''; ?>"><?php echo e($tree->dead); ?></td>
                                        <td scope="col" rowspan="<?= count($val->lahan_no) ?>" style="background: <?= $tree->lost > 0 ? '#ff6052' : ''; ?>"><?php echo e($tree->lost); ?></td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </tr>
                        <?php endfor; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> 
                </tbody>
              </table>
        </div>
    </div>
</body>
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/monitoring/export.blade.php ENDPATH**/ ?>