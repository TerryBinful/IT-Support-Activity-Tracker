<?php
namespace App\Exports;
use App\Models\User;use Barryvdh\DomPDF\Facade\Pdf;
class PdfActivitiesExport{public function download(User $u,string $from,string $to,array $order,array $labels){$activities=$u->activities()->with('category')->whereBetween('activity_date',[$from,$to])->orderBy('activity_date')->orderBy('created_at')->get();return Pdf::loadView('reports.pdf',compact('activities','order','labels','from','to'))->setPaper('a4','landscape')->download('it-activity-report.pdf');}}
