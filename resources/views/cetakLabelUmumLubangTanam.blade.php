<html>
  <head>
    <title>Cetak Label #{{$lubangTanamDetail['lahan_no']}}</title>
    <style>
      @media print {
        body{
          margin-left: 4px !important;
          margin-top: 4px !important;
          height: 66mm;
          width: 96mm;
         /* height: 100%!important; */
        }
      }
      @page  {
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
    @foreach ($listLabel as $val)
      <div class="a" style="margin-left: 1.5mm">
        <table style="width: 100%; height: 55%; padding: 7px;">
          <tr>
            <td style="height: 30px;width:63%!important;font-size:18px!important">              
              Kantong : {{$val['bag_no']}}
            </td>
            @if (count($val['tree_name']) > 1)
            <td rowspan="2" style="font-size:17px!important;text-align: end;vertical-align: top;">
              @foreach ($val['tree_name'] as $pohonIndex => $valpohon)
                <span style="text-align: end"><small>{{$valpohon}}</small> <strong>{{$val['amount'][$pohonIndex ]}}</strong></span> <br>
              @endforeach
            </td>
            @else 
            <td rowspan="2" style="font-size:20px!important;text-align: center;vertical-align: middle;border: 1.5px solid black;">
              @foreach ($val['tree_name'] as $pohonIndex => $valpohon)
                <span style="text-align: end"><small>{{$valpohon}}</small><br><h2 style="margin: 0px;text-align: center">{{$val['amount'][$pohonIndex ]}}</h2></span> <br>
              @endforeach
            </td>
            @endif
          </tr>
          <tr >
              <td style="margin-left:10px !important; border: 1.5px solid black; text-align: center;font-size:20px!important">
                {{$lubangTanamDetail->pic_lahan ?? '-'}}
              </td>            
          </tr>
        </table >
        <table style="width: 100%; height: 45%; padding-left: 7px;">
          <tr>
            <td style="width:25%!important;font-size:17px!important"  >              
              {!! $val['qr_code'] !!}
            </td>
            <td style="font-size:17px!important" >
              <small>PIC T4T : {{$lubangTanamDetail->employee_name ?? '-'}}</small> <br>
              <small>Lahan : {{$val['lahan_no']}}</small> <br>
              <small>Tanggal : {{ date('d F Y', strtotime($lubangTanamDetail->distribution_date) )}}</small> <br>
            </td>
          </tr>
        </table>
      </div>
      <p style="page-break-after: always"></p>
    @endforeach
    
  </body>
</html>