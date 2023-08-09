<html>
  <head>
    <title>Cetak Label #<?php echo e($lubangTanamDetail['lahan_no']); ?></title>
    <style>
      @media  print {
        body{
          margin-left: 4px !important;
          margin-top: 4px !important;
          height: 66mm;
          width: 96mm;
         /* height: 100%!important; */
        }
      }
      @page    {
        margin: 0;
        /* width: 100mm;
        height: 70mm; */
        size: a7 landscape;
        /* size: A6;  */
        /*or width x height 150mm 50mm*/
      }
        .a {
          height: 66mm;
          width: 96mm;
          border-collapse: collapse;
          border: 2px solid black; */
          margin-left: 4px !important;
          margin-top: 4px !important;
          overflow: hidden!important;
        }
    
        body{
          height: 66mm;
          width: 96mm;
         /* height: 100%!important; */
        }
        table{
          /* height: 266px;
          width: 365px;
          border-collapse: collapse;
          border: 2px solid black; */
        
        }
        .a {
          height: 66mm;
          width: 96mm;
          border-collapse: collapse;
          border: 2px solid black; */
        }
        th, td {
          /* border: 1.5px solid black; */
        }
        
        table, th, td {
          /* margin: 5px 2px 2px 2px; */
          font-family: Arial, Helvetica, sans-serif;
          /* vertical-align: text-top; */
          font-size: 13px;
        }
        td {
        padding: 4px 6px 0px 6px;
        }
    </style>
  </head>
  
  <body style="margin:0px;" onload="window.print();" >
    <?php $__currentLoopData = $listLabel; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="a" style="margin-left: 1.5mm">
        <table style="width: 100%; height: 55%; padding-left: 7px;">
          <tr>
            <td style="height: 30px;width:63%!important;font-size:18px!important">              
              Kantong : <?php echo e($val['bag_no']); ?>

            </td>
            <?php if(count($val['tree_name']) > 1): ?>
            <td rowspan="2" style="font-size:17px!important;text-align: end;vertical-align: top;">
              <?php $__currentLoopData = $val['tree_name']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pohonIndex => $valpohon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span style="text-align: end"><small><?php echo e($valpohon); ?></small> <strong><?php echo e($val['amount'][$pohonIndex ]); ?></strong></span> <br>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </td>
            <?php else: ?> 
            <td rowspan="2" style="font-size:20px!important;text-align: center;vertical-align: middle;border: 1.5px solid black;">
              <?php $__currentLoopData = $val['tree_name']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pohonIndex => $valpohon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span style="text-align: end"><small><?php echo e($valpohon); ?></small><br><h2 style="margin: 0px;text-align: center"><?php echo e($val['amount'][$pohonIndex ]); ?></h2></span> <br>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </td>
            <?php endif; ?>
          </tr>
          <tr >
              <td style="margin-left:10px !important; border: 1.5px solid black; text-align: center;font-size:20px!important">
                <?php echo e($val['farmer_name']); ?>

              </td>            
          </tr>
          <tr>
            <td style="font-size:18px!important;" colspan="2" style="text-align: left">
              FF : <?php echo e($val['ff_name']); ?>

            </td>
          </tr>
        </table >
        <table style="width: 100%; height: 45%; padding-left: 7px;">
          <tr>
            <td style="width:25%!important;font-size:17px!important"  >              
              <?php echo $val['qr_code']; ?>

            </td>
            <td style="font-size:17px!important" >
              <small>Lahan : <?php echo e($val['lahan_no']); ?></small> <br>
              <small>Tanggal : <?php echo e($val['date']); ?></small> <br>
              <small>Lokasi : <?php echo e($val['location']); ?></small>
            </td>
          </tr>
        </table>
      </div>
      <p style="page-break-after: always"></p>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    
  </body>
</html><?php /**PATH /home/koly7738/public_html/T4T/root/resources/views/cetakLabelLubangTanam.blade.php ENDPATH**/ ?>