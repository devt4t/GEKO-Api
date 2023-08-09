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

        $nama = 'ExportFarmer_'.date("Ymd_h-i-s").'.xls';
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=".$nama);
	?>
    <div class="flex-center position-ref full-height">
        <div class="content" style="margin:50px">
            <h2>Export Data Petani</h2>
            <h5>Export Time: <?php echo e(date("d m Y, H:i:s")); ?></h5>
            
            <table class="table">
                <thead>
                  <tr>
                    <th scope="col">No</th>
                    <th scope="col">Program Year</th>
                    <th scope="col">Management Unit</th>
                    <th scope="col">Target Area</th>
                    <th scope="col">Desa</th>
                    <th scope="col">Field Coordinator</th>
                    <th scope="col">Field Facilitator</th>
                    <th scope="col">Nama</th>
                    <th scope="col">Gender</th>
                    <th scope="col">Status</th>
                    <th scope="col">No KTP</th>
                    <th scope="col">No HP</th>
                    <th scope="col">Tanggal Lahir</th>
                    <th scope="col">Alamat</th>
                    <th scope="col">Suku</th>
                    <th scope="col">Asal</th>
                    <th scope="col">Jml Keluarga</th>
                    <th scope="col">Edukasi</th>
                    <th scope="col">Edukasi Non-Formal</th>
                    <th scope="col">Pekerjaan Utama</th>
                    <th scope="col">Penghasilan Utama</th>
                    <th scope="col">Pekerjaan Sampingan</th>
                    <th scope="col">Penghasilan Sampingan</th>
                  </tr>
                </thead>
                <tbody id="tableSO">
                    <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <th scope="row"><?php echo e($loop->iteration); ?></th>
                            <td scope="row"><?php echo e($val->program_year); ?></td>
                            <td scope="row"><?php echo e($val->mu_name); ?></td>
                            <td scope="row"><?php echo e($val->ta_name); ?></td>
                            <td scope="row"><?php echo e($val->village_name); ?></td>
                            <td scope="row"><?php echo e($val->fc_name); ?></td>
                            <td scope="row"><?php echo e($val->ff_name); ?></td>
                            <td scope="row"><?php echo e($val->farmer_name); ?></td>
                            <td scope="row"><?php echo e($val->farmer_gender); ?></td>
                            <td scope="row"><?php echo e($val->marrital_status); ?></td>
                            <td scope="row">'<?php echo e($val->farmer_ktp); ?></td>
                            <td scope="row">'<?php echo e($val->phone); ?></td>
                            <td scope="row"><?php echo e($val->birthday); ?></td>
                            <td scope="row"><?php echo e($val->farmer_address); ?> RT<?php echo e($val->farmer_rt ?? 0); ?>/RW<?php echo e($val->farmer_rw ?? 0); ?>, <?php echo e($val->post_code); ?></td>
                            <td scope="row"><?php echo e($val->ethnic); ?></td>
                            <td scope="row"><?php echo e($val->origin); ?></td>
                            <td scope="row"><?php echo e($val->number_family_member); ?></td>
                            <td scope="row"><?php echo e($val->education); ?></td>
                            <td scope="row"><?php echo e($val->non_formal_education); ?></td>
                            <td scope="row"><?php echo e($val->main_job); ?></td>
                            <td scope="row"><?php echo e($val->main_income); ?></td>
                            <td scope="row"><?php echo e($val->side_job); ?></td>
                            <td scope="row"><?php echo e($val->side_income); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> 
                </tbody>
              </table>
        </div>
    </div>
</body>
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/exportpetani.blade.php ENDPATH**/ ?>