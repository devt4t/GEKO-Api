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

        $nama = 'Export_T4T_'.date("Ymd_h-i-s").'.xls';
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=".$nama);
	?>
    
    <div class="flex-center position-ref full-height">
        <div class="content" style="margin:50px">
            <h2><?php echo e($nama_title); ?></h2>
            
            <table class="table">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">No_Lahan</th>
                    <th scope="col">Petani</th>
                    <th scope="col">Desa</th>
                    <th scope="col">kecamatan</th>
                    <th scope="col">Management Unit</th>
                    <th scope="col">Location</th>
                    <th scope="col">Luas Area/Tanam</th>
                    <th scope="col">Pohon Kayu/MPTS</th>
                    <th scope="col">Status</th>
                    <th scope="col">Nama FF</th>
                    <th scope="col">Nama FC</th>
                    <th scope="col">Opsi Pola Tanam</th>
                    <th scope="col">planting_year</th>
                    <th scope="col">pembuatan_lubang_tanam</th>
                    <th scope="col">distribution_time</th>
                    <th scope="col">distribution_location</th>
                    <th scope="col">planting_time</th>
                    
                    <?php $__currentLoopData = $getTrees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th scope="col"><?php echo e($val->tree_name); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> 
                  </tr>
                </thead>
                <tbody id="tableSO">
                    
                    <?php $__currentLoopData = $listval; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rslt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <th scope="row"><?php echo e($loop->iteration); ?></th>
                            <td><?php echo e($rslt['lahanNo']); ?></td>
                            <td><?php echo e($rslt['petani']); ?></td>
                            <td><?php echo e($rslt['desa']); ?></td>
                            <td><?php echo e($rslt['nama_kec']); ?></td>
                            <td><?php echo e($rslt['nama_mu']); ?></td>
                            <td><?php echo e($rslt['location']); ?></td>
                            <td><?php echo e($rslt['land_area']); ?>m<sup>2</sup> / <?php echo e($rslt['planting_area']); ?> m<sup>2</sup></td>
                            <td><?php echo e($rslt['pohon_kayu']); ?> pcs/ <?php echo e($rslt['pohon_mpts']); ?> pcs</td>
                            <td><?php echo e($rslt['status']); ?></td>
                            <td><?php echo e($rslt['ff']); ?></td>
                            <td><?php echo e($rslt['nama_fc_lahan']); ?></td>
                            <td><?php echo e($rslt['opsi_pola_tanam']); ?></td>
                            <td><?php echo e($rslt['planting_year']); ?></td>
                            <td><?php echo e($rslt['pembuatan_lubang_tanam']); ?></td>
                            <td><?php echo e($rslt['distribution_time']); ?></td>
                            <td><?php echo e($rslt['distribution_location']); ?></td>
                            <td><?php echo e($rslt['planting_time']); ?></td>
                            <?php $__currentLoopData = $rslt['listvaltrees']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $valuetrees): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($valuetrees != 0): ?>
                                <th scope="col" style="background-color: aqua;"><?php echo e($valuetrees); ?></th>
                                <?php else: ?>
                                <th scope="col"><?php echo e($valuetrees); ?></th>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> 
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> 
                </tbody>
            </table>
        </div>
    </div>
</body>
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/exportSostamSuperAdmin.blade.php ENDPATH**/ ?>