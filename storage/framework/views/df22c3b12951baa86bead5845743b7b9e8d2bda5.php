<html>
<head>
</head>
<style>
table, th, td {
  /* border: 3px solid black; */
  border-collapse: collapse;
  font-size:20px;
}
table tbody td {
    vertical-align: middle;
}
</style>
<body>

    <?php
               
        date_default_timezone_set("Asia/Bangkok");

        $nama = 'Export Distribution Report Lahan Umum '. $py . '_' .date("Y-m-d", strtotime($distribution_date)).'.xls';
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=".$nama);
	?>
    
    <div class="flex-center position-ref full-height">
        <div class="content" style="margin:50px">
            <h2><?php echo e($nama_title); ?></h2>
            <h3>Distribution Date: <?php echo e(date("d F Y", strtotime($distribution_date))); ?></h3>
            <h5>Export Time: <?php echo e(date("H:i:s d F Y")); ?></h5>
            
            <table class="table" border="1">
                <thead>
                  <tr>
                    <th rowspan="3" scope="col">#</th>
                    <th rowspan="3" scope="col">Tanggal Distribusi</th>
                    <th rowspan="3" scope="col">Nursery</th>
                    <th rowspan="3" scope="col">Management Unit</th>
                    <th rowspan="3" scope="col">Provinsi</th>
                    <th rowspan="3" scope="col">Kabupaten</th>
                    <th rowspan="3" scope="col">Kecamatan</th>
                    <th rowspan="3" scope="col">Desa</th>
                    <th rowspan="3" scope="col">PIC T4T</th>
                    <th rowspan="3" scope="col">PIC Lahan</th>
                    <th rowspan="3" scope="col">MOU No</th>
                    <th rowspan="3" scope="col">Verifikasi</th>
                    <th colspan="<?= $trees->count * 3 ?>" scope="col">Trees Amount</th>
                  </tr>
                  <tr>
                    <?php $__currentLoopData = $trees->data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tree): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th scope="col" colspan="3"><?php echo e($tree->tree_name); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </tr>
                  <tr>
                    <?php $__currentLoopData = $trees->data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tree): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th scope="col">Rusak</th>
                        <th scope="col">Hilang</th>
                        <th scope="col">Diterima</th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </tr>
                </thead>
                <tbody id="tableSO">
                    <?php $__currentLoopData = $distributions->data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rslt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td scope="row"><?php echo e($loop->iteration); ?></td>
                            <td><?php echo e(date('Y-m-d', strtotime($rslt->distribution_date))); ?></td>
                            <td><?php echo e($rslt->nursery); ?></td>
                            <td><?php echo e($rslt->mu); ?></td>
                            <td><?php echo e($rslt->province); ?></td>
                            <td><?php echo e($rslt->regency); ?></td>
                            <td><?php echo e($rslt->district); ?></td>
                            <td><?php echo e($rslt->village); ?></td>
                            <td><?php echo e($rslt->pic_t4t); ?></td>
                            <td><?php echo e($rslt->pic_lahan); ?></td>
                            <td><?php echo e($rslt->mou_no); ?></td>
                            <td style="background: <?= $rslt->status == 1 ? '#eba134' : '#34eb52'; ?>"><?php echo e($rslt->status == 1 ? 'PIC T4T' : 'UM / PM'); ?></td>
                            <?php $__currentLoopData = $rslt->trees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tree): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td scope="col" style="background: <?= $tree->broken_seeds > 0 ? '#ff6052' : ''; ?>"><?php echo e($tree->broken_seeds); ?></td>
                                <td scope="col" style="background: <?= $tree->missing_seeds > 0 ? '#34eb52' : ''; ?>"><?php echo e($tree->missing_seeds); ?></td>
                                <td scope="col" style="background: <?= $tree->total_tree_received > 0 ? '#34eb52' : ''; ?>"><?php echo e($tree->total_tree_received); ?></td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> 
                </tbody>
            </table>
        </div>
    </div>
</body>
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/lahan_umum/export_distribution_report.blade.php ENDPATH**/ ?>