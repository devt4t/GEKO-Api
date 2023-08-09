<html>
<head>
</head>
<style>
table, th, td {
  /* border: 3px solid black; */
  border-collapse: collapse;
  font-size:20px;
}
tr:nth-child(even) {
    background: #f2f2f2;
}

</style>
<body>
    <?php
               
        date_default_timezone_set("Asia/Bangkok");

        $nama = "Export Pelatihan Petani - ".date("Ymd_h-i-s").'.xls';
        // $download = $_GET['download'] == 'true' ? true : false;
        // if ($download) {
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=".$nama);
        // }
	?>
    <div class="flex-center position-ref full-height">
        <div class="content" style="margin:50px">
            <h2>Export Data Pelatihan Petani (<?php echo e($program_year); ?>)</h2>
            <h5>Export Time: <?php echo e(date("d m Y, H:i:s")); ?></h5>
            
            <table class="table" border="1">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Form No</th>
                        <th scope="col">Tahun Program</th>
                        <th scope="col">Tanggal Pelatihan</th>
                        <th scope="col">Management Unit</th>
                        <th scope="col">Target Area</th>
                        <th scope="col">Desa</th>
                        <th scope="col">Field Coordinator</th>
                        <th scope="col">Field Facilitator</th>
                        <th scope="col">Materi 1 (Wajib)</th>
                        <th scope="col">Materi 2</th>
                        <th scope="col">Total Partisipan Petani</th>
                        <th align="left" scope="col" colspan="<?= $cap_farmer ?>">Nama Partisipan Petani</th>
                    </tr>
                </thead>
                <tbody id="tableSO">
                    <?php $__currentLoopData = $datas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($loop->iteration % 2 == 0): ?>
                        <tr style="background: #d9ffd1">
                    <?php else: ?>
                        <tr>
                    <?php endif; ?>
                            <td scope="row"><?php echo e($loop->iteration); ?></td>
                            <td scope="row"><?php echo e($data->training_no); ?></td>
                            <td scope="row"><?php echo e($data->program_year); ?></td>
                            <td scope="row"><?php echo e($data->training_date); ?></td>
                            <td scope="row"><?php echo e($data->mu_name); ?></td>
                            <td scope="row"><?php echo e($data->ta_name); ?></td>
                            <td scope="row"><?php echo e($data->village_name); ?></td>
                            <td scope="row"><?php echo e($data->fc_name); ?></td>
                            <td scope="row"><?php echo e($data->ff_name); ?></td>
                            <td scope="row"><?php echo e($data->materi1); ?></td>
                            <td scope="row"><?php echo e($data->materi2); ?></td>
                            <td scope="row"><?php echo e(count($data->farmers)); ?></td>
                            <?php $__currentLoopData = $data->farmers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $farmer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td scope="row"><?php echo e($farmer); ?></td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> 
                </tbody>
              </table>
        </div>
    </div>
</body>
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/farmer_training/export.blade.php ENDPATH**/ ?>