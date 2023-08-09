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

        $nama = 'Export Lahan '. $py . '_' .date("Ymd_h-i-s").'.xls';
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
                    <th scope="col">#</th>
                    <th scope="col">Provinsi</th>
                    <th scope="col">Kabupaten</th>
                    <th scope="col">Kecamatan</th>
                    <th scope="col">Management Unit</th>
                    <th scope="col">Target Area</th>
                    <th scope="col">Desa</th>
                    <th scope="col">FC</th>
                    <th scope="col">FF</th>
                    <th scope="col">Petani</th>
                    <th scope="col">No Lahan</th>
                    <th scope="col">Document No</th>
                    <th scope="col">Luas Area/Tanam</th>
                    <th scope="col">Tipe Lahan</th>
                    <th scope="col">Pola Tanam</th>
                    <th scope="col">Longitude</th>
                    <th scope="col">Latitude</th>
                    <th scope="col">Coordinate</th>
                    <!--<th scope="col">Pohon Kayu/MPTS</th>-->
                    <th scope="col">Verifikasi Lahan</th>
                    <th scope="col">GIS Updated Status</th>
                  </tr>
                </thead>
                <tbody id="tableSO">
                    <?php $__currentLoopData = $listval; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rslt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <th scope="row"><?php echo e($loop->iteration); ?></th>
                            <td><?php echo e($rslt->province); ?></td>
                            <td><?php echo e($rslt->kabupaten); ?></td>
                            <td><?php echo e($rslt->kecamatan); ?></td>
                            <td><?php echo e($rslt->mu); ?></td>
                            <td><?php echo e($rslt->ta); ?></td>
                            <td><?php echo e($rslt->desa); ?></td>
                            <td><?php echo e(\App\Employee::where('nik', $rslt->fc_no)->first()->name ?? '-'); ?></td>
                            <td><?php echo e($rslt->ff_name); ?></td>
                            <td><?php echo e($rslt->farmer_name); ?></td>
                            <td><?php echo e($rslt->lahanNo); ?></td>
                            <td>'<?php echo e($rslt->document_no); ?></td>
                            <td><?php echo e($rslt->land_area); ?>m<sup>2</sup> / <?php echo e($rslt->planting_area); ?> m<sup>2</sup></td>
                            <td><?php echo e($rslt->lahan_type); ?></td>
                            <td><?php echo e($rslt->opsi_pola_tanam); ?></td>
                            <td><?php echo e(str_replace(".", ",", $rslt->longitude)); ?></td>
                            <td><?php echo e(str_replace(".", ",", $rslt->latitude)); ?></td>
                            <td><?php echo e($rslt->coordinate); ?></td>
                            <td style="background: <?= $rslt->approve ? '#34eb52' : '#ff6052'; ?>"><?php echo e($rslt->approve ? 'sudah' : 'belum'); ?></td>
                            <td style="background: <?= $rslt->updated_gis == 'sudah' ? '#34eb52' : '#f0a856'; ?>"><?php echo e($rslt->updated_gis); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> 
                </tbody>
            </table>
        </div>
    </div>
</body>
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/exportlahan.blade.php ENDPATH**/ ?>