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

        $nama = 'Export Lahan Umum Penilikan Lubang '. $py . '_' .date("Ymd_h-i-s").'.xls';
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=".$nama);
	?>
    
    <div class="flex-center position-ref full-height">
        <div class="content" style="margin:50px">
            <h2><?php echo e($nama_title); ?></h2>
            <h5>Export Time: <?php echo e(date("H:i:s d F Y")); ?></h5>
            
            <table class="table" border="1">
                <thead>
                  <tr>
                    <th rowspan="2" scope="col">#</th>
                    <th rowspan="2" scope="col">Provinsi</th>
                    <th rowspan="2" scope="col">Kabupaten</th>
                    <th rowspan="2" scope="col">Kecamatan</th>
                    <th rowspan="2" scope="col">Management Unit</th>
                    <th rowspan="2" scope="col">Desa</th>
                    <th rowspan="2" scope="col">MOU No</th>
                    <th rowspan="2" scope="col">No Lahan</th>
                    <th rowspan="2" scope="col">Status</th>
                    <th rowspan="2" scope="col">PIC T4T</th>
                    <th rowspan="2" scope="col">Nama PIC Lahan</th>
                    <th rowspan="2" scope="col">Luas Area/Tanam</th>
                    <th rowspan="2" scope="col">Pola Tanam</th>
                    <th rowspan="2" scope="col">Longitude</th>
                    <th rowspan="2" scope="col">Latitude</th>
                    <th rowspan="2" scope="col">Coordinate</th>
                    <th colspan="2" scope="col">Lubang</th>
                    <th rowspan="2" scope="col">Verifikasi</th>
                    <th colspan="3" scope="col">Trees Total</th>
                    <th colspan="<?= $trees->count ?>" scope="col">Trees Amount</th>
                  </tr>
                  <tr>
                    <th scope="col">Total</th>
                    <th scope="col">Standard</th>
                    <th scope="col">Kayu</th>
                    <th scope="col">Mpts</th>
                    <th scope="col">Crops</th>
                    <?php $__currentLoopData = $trees->data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tree): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th scope="col"><?php echo e($tree->tree_name); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </tr>
                </thead>
                <tbody id="tableSO">
                    <?php $__currentLoopData = $lahan->data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rslt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <th scope="row"><?php echo e($loop->iteration); ?></th>
                            <td><?php echo e($rslt->province); ?></td>
                            <td><?php echo e($rslt->kabupaten); ?></td>
                            <td><?php echo e($rslt->kecamatan); ?></td>
                            <td><?php echo e($rslt->mu); ?></td>
                            <td><?php echo e($rslt->desa); ?></td>
                            <td><?php echo e($rslt->mou_no); ?></td>
                            <td><?php echo e($rslt->lahan_no); ?></td>
                            <td><?php echo e($rslt->status); ?></td>
                            <td><?php echo e(\App\Employee::where('nik', $rslt->employee_no)->first()->name ?? '-'); ?></td>
                            <td><?php echo e($rslt->pic_lahan); ?></td>
                            <td><?php echo e($rslt->luas_lahan); ?>m<sup>2</sup> / <?php echo e($rslt->luas_tanam); ?> m<sup>2</sup></td>
                            <td><?php echo e($rslt->pattern_planting); ?></td>
                            <td><?php echo e(str_replace(".", ",", $rslt->longitude)); ?></td>
                            <td><?php echo e(str_replace(".", ",", $rslt->latitude)); ?></td>
                            <td><?php echo e($rslt->coordinate); ?></td>
                            <td><?php echo e($rslt->total_holes); ?></td>
                            <td style="background: <?= $rslt->total_holes >= $rslt->counter_hole_standard ? '' : '#ff6052'; ?>"><?php echo e($rslt->counter_hole_standard); ?></td>
                            <td style="background: <?= $rslt->is_verified > 1 ? '#34eb52' : '#ff6052'; ?>"><?php echo e($rslt->is_verified ? 'Sudah' : 'Belum'); ?></td>
                            <td style="background: <?= $rslt->pohon_kayu > 0 ? '#34eb52' : ''; ?>"><?php echo e($rslt->pohon_kayu); ?></td>
                            <td style="background: <?= $rslt->pohon_mpts > 0 ? '#34eb52' : ''; ?>"><?php echo e($rslt->pohon_mpts); ?></td>
                            <td style="background: <?= $rslt->tanaman_bawah > 0 ? '#34eb52' : ''; ?>"><?php echo e($rslt->tanaman_bawah); ?></td>
                            <?php $__currentLoopData = $rslt->lahan_trees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $amount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td scope="col" style="background: <?= $amount > 0 ? '#34eb52' : ''; ?>"><?php echo e($amount); ?></td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> 
                </tbody>
            </table>
        </div>
    </div>
</body>
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/lahan_umum/export_penilikan_lubang.blade.php ENDPATH**/ ?>