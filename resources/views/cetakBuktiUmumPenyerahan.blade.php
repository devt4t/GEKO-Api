<html>
  <head>
    <title>Cetak Tanda Terima #{{ $lubangTanamDetail->lahan_no }}</title>
    <style>
        @media print {
          .thcls1 {
            font-size:18px!important; 
            text-align:center!important;
          }
          table {
              width: 100%;
          }
        }
        .a {
          /* border: 2px solid black; */ */
        }
        .thcls {
          width:200px!important;
          font-size:18px!important; 
          text-align:left!important;
        }
        .thcls1 {
          font-size:18px!important; 
          text-align:center!important;
          background: lightgray;
        }
        .tblcls{
          border: 2px solid black; 
        }
    
        table, th, td {
          border-collapse: collapse;
        }
        
        .ttd-wrapper {
            display: flex;
            justify-content: space-between;
            margin-left:20px;
        }
        .ttd-item {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
        }
        .ttd-item p:first-child {
            margin-bottom: 40px;
        }
    </style>
  </head>
  
  <body style="margin:15px;" onload="window.print();" >
   
      <div class="a" style="margin-bottom: 5px !important">
        <table style="width: 90%; margin-bottom:20px">
          <tr>
            <th style="text-align: center; font-size:20px">              
              Tanda Terima PIC Lahan
            </th>
          </tr>
        </table >
        <table style="width: 100%; margin-left:20px; padding-left: 7px;">
          <tr>
            <th class="thcls">              
              PIC Lahan 
            </th>
            <td>:</td>
            <td style="font-size: 16px; font-weight: 900;text-decoration: underline;">
              {{$lubangTanamDetail->pic_lahan ?? '-'}}
            </td>
          </tr>
          <tr>
            <th class="thcls">              
              No Lahan
            </th>
            <td>:</td>
            <td style="font-size: 16px; font-weight: 900;">
              {{$listvalbag[0]['lahan_no'] ?? '-'}}
            </td>
          </tr>
          <tr>
            <th class="thcls">              
              Tanggal Distribusi 
            </th>          
            <td>:</td>
            <td>
              {{$lubangTanamDetail->distribution_date ?? '-'}}
            </td>
          </tr>
        </table>
        <table  style="margin-top:20px !important;margin-bottom:10px !important; margin-left:10px; padding-left: 7px;">
          <tr  class="tblcls">
            <th class="thcls1 tblcls" style="width:120px!important; ">              
              No. Kantong
            </th>
            <th class="thcls1 tblcls" style="width:210px!important; height: 20px;">              
              Spesies Bibit
            </th>
            <th class="thcls1 tblcls" style="width:75px!important; height: 20px;">              
              Jumlah
            </th>
            <th colspan="2" class="thcls1 tblcls" style="width:150px!important; height: 20px;">              
              Checklist (✔)
            </th>
          </tr>
          <tbody  >
            @foreach ($listvalbag as $val)
            <tr  class="tblcls">
              <td  class="tblcls" style="text-align: center">              
                {{$val['bag_no']}}
              </td>
              <td  class="tblcls" style="padding: 5px">              
                @foreach ($val['tree_name'] as $valpohon)
                  <span style="text-align: end">{{$valpohon}}</span> <br>
                @endforeach
              </td>
              <td  class="tblcls" style="padding: 5px; text-align: right">              
                @foreach ($val['tree_name'] as $indexPohon => $valpohon)
                  <span style="text-align: end">{{$val['amount'][$indexPohon]}}</span> <br>
                @endforeach
              </td>
              <td  class="tblcls"></td>
              <td  class="tblcls"></td>
            </tr>
            
            @endforeach
          </tbody>
        </table >
        <div class="ttd-wrapper">
            <div class="ttd-item">
                <p>Diterima oleh,</p>
                <p>{{$lubangTanamDetail['pic_lahan'] ?? 'PIC'}}</p>
            </div>
            <div class="ttd-item">
                <p>Mengetahui,</p>
                <p>{{$lubangTanamDetail['employee_name'] ?? 'Field Coordinator'}}</p>
            </div>
        </div>
      </div>
      <p style="page-break-after: always"></p>
    
  </body>
</html>