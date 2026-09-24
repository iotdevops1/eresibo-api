<?php
namespace App\Services\Report;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class PayrollReportExportService {
 public function download(array $report,string $format) {
  $rows=$report['rows'];$name='payroll-summary-'.$report['period']['start'].'-'.$report['period']['end'];
  if($format==='json')return response()->json($report)->header('Content-Disposition','attachment; filename="'.$name.'.json"');
  if($format==='csv')return response()->streamDownload(function()use($rows){$out=fopen('php://output','w');fputcsv($out,['Period start','Period end','Pay date','Staff','Gross','Deductions','Net']);foreach($rows as $r)fputcsv($out,[$r['period_start'],$r['period_end'],$r['pay_date'],$r['staff'],$r['gross'],$r['deductions'],$r['net']]);fclose($out);},$name.'.csv',['Content-Type'=>'text/csv']);
  if($format==='xlsx'){ $sheet=(new Spreadsheet())->getActiveSheet();$sheet->fromArray(['Period start','Period end','Pay date','Staff','Gross','Deductions','Net'],null,'A1');foreach($rows as $i=>$r)$sheet->fromArray([$r['period_start'],$r['period_end'],$r['pay_date'],$r['staff'],$r['gross'],$r['deductions'],$r['net']],null,'A'.($i+2));$path=tempnam(sys_get_temp_dir(),'eresibo-').'.xlsx';(new Xlsx($sheet->getParent()))->save($path);return response()->download($path,$name.'.xlsx',['Content-Type'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->deleteFileAfterSend(true);}
  $html='<h1>Payroll Summary</h1><p>'.$report['period']['start'].' to '.$report['period']['end'].'</p><table border="1" cellpadding="5"><tr><th>Period</th><th>Pay date</th><th>Staff</th><th>Gross</th><th>Deductions</th><th>Net</th></tr>';foreach($rows as $r)$html.='<tr><td>'.$r['period_start'].' - '.$r['period_end'].'</td><td>'.$r['pay_date'].'</td><td>'.$r['staff'].'</td><td>'.$r['gross'].'</td><td>'.$r['deductions'].'</td><td>'.$r['net'].'</td></tr>';$html.='</table>';$pdf=new Dompdf();$pdf->loadHtml($html);$pdf->setPaper('A4','landscape');$pdf->render();return response($pdf->output(),200,['Content-Type'=>'application/pdf','Content-Disposition'=>'attachment; filename="'.$name.'.pdf"']);
 }
}
