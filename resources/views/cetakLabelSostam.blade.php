<style>
  @media print {
    .a {
      height: 66mm;
      width: 96mm;
      border-collapse: collapse;
      border: 2px solid black; */
      margin-left: 4px !important;
      margin-top: 4px !important;
    }
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
    <html>
      <head>
        <title>Cetak Label #{{$SosialisasiDetail['no_lahan']}}</title>
      </head>
      
      <body style="margin:1px;" onload="window.print();" >
        @foreach ($SosialisasiDetail['listvalbag'] as $val)
          <div class="a" style="margin-bottom: 5px !important">
            <table style="width: 100%; height: 60%; padding-left: 7px;">
              <tr>
                <td style="height: 30px;width:63%!important;font-size:18px!important">              
                  Kantong : {{$val['no_bag']}}
                </td>
                <td rowspan="2" style="font-size:17px!important;text-align: end">
                  @foreach ($val['listvaltemp'] as $valpohon)
                    <span style="text-align: end">{{$valpohon['pohon']}}   {{$valpohon['amount']}}</span> <br>
                  @endforeach
                  @for ($x = 1; $x <= $val['n']; $x++)
                    <span style="text-align: end">-  -</span> <br>
                  @endfor
                  {{-- <p style="text-align: left">Jabon : 2</p>
                  <p style="text-align: left">Jeruk nipis : 10</p>
                  <p style="text-align: left">Jambu air : 2</p> --}}
                </td>
              </tr>
              <tr >
                @if($SosialisasiDetail['countnama'] > 2)
                  <td style="margin-left:10px !important; border: 1.5px solid black; text-align: center;font-size:20px!important">
                    {{$SosialisasiDetail['nama_petani']}}
                  </td>
                @else
                  <td style="margin-left:10px !important; border: 1.5px solid black; text-align: center;font-size:30px!important">
                    {{$SosialisasiDetail['nama_petani']}}
                  </td>
                @endif
                
              </tr>
              <tr >
                <td style="font-size:20px!important" colspan="2" style="text-align: left">
                  FF : {{$SosialisasiDetail['ff_name']}}
                </td>
              </tr>
            </table >
            <table style="width: 100%; height: 40%; padding-left: 7px; margin-top: -2px;">
              <tr>
                <td style="width:25%!important;font-size:17px!important"  >              
                  {!! $val['qrcodelahan'] !!}
                </td>
                <td style="font-size:17px!important" >
                  <span>Lahan : {{$SosialisasiDetail['no_lahan']}}</span> <br>
                  <span>Tanggal : {{$SosialisasiDetail['newDateformatdistribution']}}</span> <br>
                  <span>Lokasi : {{$SosialisasiDetail['distribution_location']}}</span>
                </td>
              </tr>
              {{-- <tr >
                <td >
                  
                </td>
              </tr>
              <tr >
                <td >
                  
                </td>
              </tr> --}}
            </table>
          </div>
          <p style="page-break-after: always"></p>
        @endforeach
        
      </body>
    </html>