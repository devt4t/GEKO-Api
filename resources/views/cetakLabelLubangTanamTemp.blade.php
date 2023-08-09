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
          max-height: 66mm;
          max-width: 96mm;
          border-collapse: collapse;
          border: 2px solid black;
          border-radius: 10px;
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
        <table style="width: 100%; height: 55%;">
            <tr style="width: 100%">
                <td style="width: 60%;font-size:14px!important;text-align: left;vertical-align: top;padding: 0px;" >
                    <table style="margin: 0px;">
                        <tr>
                            <td style="vertical-align: top">FF</td>
                            <td style="vertical-align: top">:</td>
                            <td><strong>{{ $val['ff_name'] }}</strong></td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top">Lahan</td>
                            <td style="vertical-align: top">:</td>
                            <td><strong>{{ $val['lahan_no'] }}</strong></td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top">Petani</td>
                            <td style="vertical-align: top">:</td>
                        </tr>
                        <tr>
                            <td colspan="3"><h2 style="margin-bottom: 0px;text-align: left;padding-left: 5px">{{ $val['farmer_name'] }}</h2></td>
                        </tr>
                    </table>
                </td>
                <td style="width: 40%;font-size:17px!important;text-align: end;border: 2px solid black;border-radius:10px;">
                    @if(count($val['tree_name']) > 1)
                      @foreach ($val['tree_name'] as $pohonIndex => $valpohon)
                        <span style="text-align: end"><small>{{$valpohon}}</small> <strong>{{$val['amount'][$pohonIndex]}}</strong></span> <br>
                      @endforeach
                    @else
                        <div style="text-align: center;">
                            <p style="margin-top: 0px">{{ $val['tree_name'][0] }}</p>
                            <h1 style="margin-bottom: 0px">{{ $val['amount'][0] }}</h1>
                        </div>
                    @endif
                </td>
            </tr>
        </table >
        <table style="width: 100%; height: 45%;">
          <tr style="width: 100%;">
            <td style="max-width:30%!important;font-size:17px!important;border: 2px solid black;vertical-align: middle;text-align: center;border-radius: 10px;position: relative;z-index: 2;"  >
                <h2 style="margin: 0px;">{{ $val['bag_no'] }}</h2>
            </td>
            <td style="width:70%;position: relative;">
            	<img alt="Barcode" src="https://products.aspose.app/barcode/embed/image.Png?BarcodeType=Code128&Content={{ $val['bag_code'] }}&Height=123&Width=457" style="transform: scale(.6);position: absolute;bottom: -10px;right: -103px;" />
            </td>
          </tr>
        </table>
      </div>
      <p style="page-break-after: always"></p>
    @endforeach
    
  </body>
</html>