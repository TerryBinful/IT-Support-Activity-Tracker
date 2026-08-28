<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Activity extends Model{
 protected $fillable=['user_id','category_id','title','description','priority','status','activity_date','started_at','completed_at','duration_minutes','outcome','blockers','follow_up_required','follow_up_action','reference_number','evidence_url'];
 protected function casts():array{return ['activity_date'=>'date','started_at'=>'datetime','completed_at'=>'datetime','follow_up_required'=>'boolean','duration_minutes'=>'integer'];}
 public function user():BelongsTo{return $this->belongsTo(User::class);} public function category():BelongsTo{return $this->belongsTo(Category::class);}
}
