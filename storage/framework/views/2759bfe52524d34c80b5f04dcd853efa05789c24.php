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

        $nama = "Export Material Organic-$organic_type-".date("Ymd_h-i-s").'.xls';
        // $download = $_GET['download'] == 'true' ? true : false;
        // if ($download) {
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=".$nama);
        // }
	?>
    <div class="flex-center position-ref full-height">
        <div class="content" style="margin:50px">
            <h2>Export Data Material Organic (<?php echo e($organic_type); ?>)</h2>
            <h5>Export Time: <?php echo e(date("d m Y, H:i:s")); ?></h5>
            
            <table class="table" border="1">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Tahun Program</th>
                        <th scope="col">Organic Type</th>
                        <th scope="col">Organic No</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Management Unit</th>
                        <th scope="col">Target Area</th>
                        <th scope="col">Desa</th>
                        <th scope="col">Field Coordinator</th>
                        <th scope="col">Field Facilitator</th>
                        <th scope="col">Petani</th>
                        <th colspan="2" scope="col">Amount</th>
                        <th scope="col">Verifikasi</th>
                    </tr>
                </thead>
                <tbody id="tableSO">
                    <?php $__currentLoopData = $datas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td scope="row"><?php echo e($loop->iteration); ?></td>
                            <td scope="row"><?php echo e($program_year); ?></td>
                            <td scope="row"><?php echo e($data->organic_name); ?></td>
                            <td scope="row"><?php echo e($data->organic_no); ?></td>
                            <td scope="row"><?php echo e(date('Y-m-d', strtotime($data->created_at))); ?></td>
                            <td scope="row"><?php echo e($data->mu_name); ?></td>
                            <td scope="row"><?php echo e($data->ta_name); ?></td>
                            <td scope="row"><?php echo e($data->village_name); ?></td>
                            <td scope="row"><?php echo e($data->fc_name); ?></td>
                            <td scope="row"><?php echo e($data->ff_name); ?></td>
                            <td scope="row"><?php echo e($data->farmer_name); ?></td>
                            <td scope="row"><?php echo e($data->organic_amount); ?></td>
                            <td scope="row"><?php echo e($data->uom); ?></td>
                            <td scope="row" style="background: <?= $data->status == 1 ? '#34eb52' : '#ff6052'; ?>"><?php echo e($data->status == 1 ? ($data->verified_by_name ?? '?') : '-'); ?></td>
                                   
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> 
                </tbody>
              </table>
        </div>
    </div>
</body>
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/material_organic/petani/export.blade.php ENDPATH**/ ?>