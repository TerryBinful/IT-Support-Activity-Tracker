<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ReportReminder extends Model{protected $fillable=['user_id','report_month','reminded_at','acknowledged_at'];protected function casts():array{return ['report_month'=>'date','reminded_at'=>'datetime','acknowledged_at'=>'datetime'];}public function user():BelongsTo{return $this->belongsTo(User::class);}}
