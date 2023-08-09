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

        $nama = 'Export Distribution Report Lahan Petani '. $py . '_' .date("Y-m-d", strtotime($distribution_date)).'.xls';
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
                    <th rowspan="3" scope="col">Lahan No</th>
                    <th rowspan="3" scope="col">Tanggal Distribusi</th>
                    <th rowspan="3" scope="col">Nursery</th>
                    <th rowspan="3" scope="col">Management Unit</th>
                    <th rowspan="3" scope="col">Target Area</th>
                    <th rowspan="3" scope="col">Desa</th>
                    <th rowspan="3" scope="col">Field Coordinator</th>
                    <th rowspan="3" scope="col">Field Facilitator</th>
                    <th rowspan="3" scope="col">Petani</th>
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
                            <th scope="row"><?php echo e($loop->iteration); ?></th>
                            <td><?php echo e($rslt->lahan_no); ?></td>
                            <td><?php echo e(date('Y-m-d', strtotime($rslt->distribution_date))); ?></td>
                            <td><?php echo e($rslt->nursery); ?></td>
                            <td><?php echo e($rslt->mu); ?></td>
                            <td><?php echo e($rslt->ta); ?></td>
                            <td><?php echo e($rslt->desa); ?></td>
                            <td><?php echo e($rslt->fc_name); ?></td>
                            <td><?php echo e($rslt->ff_name); ?></td>
                            <td><?php echo e($rslt->farmer_name); ?></td>
                            <td style="background: <?= $rslt->status == 1 ? '#eba134' : '#34eb52'; ?>"><?php echo e($rslt->status == 1 ? 'FC' : 'UM'); ?></td>
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
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/distributions/export_report.blade.php ENDPATH**/ ?>