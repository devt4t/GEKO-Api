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

        $nama = "Export Monitoring Lahan Umum-".date("Ymd_h-i-s").'.xls';
        // $download = $_GET['download'] == 'true' ? true : false;
        // if ($download) {
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=".$nama);
        // }
	?>
    <div class="flex-center position-ref full-height">
        <div class="content" style="margin:50px">
            <h2>Export Data Realisasi Tanam / Monitoring 1 Lahan Umum</h2>
            <h5>Export Time: <?php echo e(date("d m Y, H:i:s")); ?></h5>
            
            <table class="table" border="1">
                <thead>
                  <tr>
                    <th rowspan="3" scope="col">No</th>
                    <th rowspan="3" scope="col">Tahun Program</th>
                    <th rowspan="3" scope="col">Management Unit</th>
                    <th rowspan="3" scope="col">Provinsi</th>
                    <th rowspan="3" scope="col">Kabupaten</th>
                    <th rowspan="3" scope="col">Kecamatan</th>
                    <th rowspan="3" scope="col">Desa</th>
                    <th rowspan="3" scope="col">Alamat</th>
                    <th rowspan="3" scope="col">PIC T4T</th>
                    <th rowspan="3" scope="col">PIC Lahan</th>
                    <th rowspan="3" scope="col">No Lahan</th>
                    <th rowspan="3" scope="col">Tanggal Tanam</th>
                    <th rowspan="3" scope="col">Kondisi Lahan</th>
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
                        <?php $__currentLoopData = $trees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tree): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th scope="col">Ditanam Hidup</th>
                            <th scope="col">Mati</th>
                            <th scope="col">Hilang</th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                </thead>
                <tbody id="tableSO">
                    <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <th scope="row"><?php echo e($loop->iteration); ?></th>
                            <td scope="row"><?php echo e($val->program_year); ?></td>
                            <td scope="row"><?php echo e($val->mu_name); ?></td>
                            <td scope="row"><?php echo e($val->province_name); ?></td>
                            <td scope="row"><?php echo e($val->regency_name); ?></td>
                            <td scope="row"><?php echo e($val->district_name); ?></td>
                            <td scope="row"><?php echo e($val->village_name); ?></td>
                            <td scope="row"><?php echo e($val->address); ?></td>
                            <td scope="row"><?php echo e($val->pic_t4t); ?></td>
                            <td scope="row"><?php echo e($val->pic_lahan); ?></td>
                            <td scope="row"><?php echo e($val->lahan_no); ?></td>
                            <td scope="row"><?php echo e(date('Y-m-d', strtotime($val->planting_date))); ?></td>
                            <td scope="row"><?php echo e($val->lahan_condition); ?></td>
                            <td scope="row"><?php echo e($val->qty_std); ?></td>
                            <td scope="row" style="background: <?= $val->is_verified > 0 ? ($val->is_verified == 2 ? '#34eb52' : '#f0a856') : '#ff6052'; ?>"><?php echo e($val->is_verified > 0 ? ($val->is_verified == 2 ? 'PM / RM' : 'PIC T4T') : 'Belum'); ?></td>
                            <?php $__currentLoopData = $val->tree_details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tree): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td scope="col" style="background: <?= $tree->planted_life > 0 ? '#34eb52' : ''; ?>"><?php echo e($tree->planted_life); ?></td>
                                <td scope="col" style="background: <?= $tree->dead > 0 ? '#f0a856' : ''; ?>"><?php echo e($tree->dead); ?></td>
                                <td scope="col" style="background: <?= $tree->lost > 0 ? '#ff6052' : ''; ?>"><?php echo e($tree->lost); ?></td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> 
                </tbody>
              </table>
        </div>
    </div>
</body>
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/lahan_umum/export_monitoring.blade.php ENDPATH**/ ?>