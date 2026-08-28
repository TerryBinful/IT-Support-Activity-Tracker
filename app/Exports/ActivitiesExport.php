<?php
namespace App\Exports;
use App\Models\User;use Maatwebsite\Excel\Concerns\FromCollection;use Maatwebsite\Excel\Concerns\WithHeadings;
class ActivitiesExport implements FromCollection,WithHeadings{
 public function __construct(private User $user,private string $from,private string $to,private array $order){}
 public function collection(){return $this->user->activities()->with('category')->whereBetween('activity_date',[$this->from,$this->to])->orderBy('activity_date')->orderBy('created_at')->get()->map(fn($a)=>collect($this->order)->map(fn($c)=>$this->value($a,$c))->all());}
 public function headings():array{return collect($this->order)->map(fn($c)=>str($c)->replace('_',' ')->title()->toString())->all();}
 private function value($a,string $c):mixed{return match($c){'activity_date'=>$a->activity_date?->format('Y-m-d'),'created_at'=>$a->created_at?->format('Y-m-d H:i:s'),'category'=>$a->category?->name,'started_at'=>$a->started_at?->format('Y-m-d H:i:s'),'completed_at'=>$a->completed_at?->format('Y-m-d H:i:s'),'follow_up_required'=>$a->follow_up_required?'Yes':'No',default=>$a->{$c}};}
 public function toCsv():string{$h=fopen('php://temp','r+');fputcsv($h,$this->headings());foreach($this->collection() as $row)fputcsv($h,$row);rewind($h);$csv=stream_get_contents($h);fclose($h);return $csv;}
}
