<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
class User extends Authenticatable{
 use Notifiable;
 protected $fillable=['name','email','password'];
 protected function casts():array{return ['email_verified_at'=>'datetime','password'=>'hashed'];}
 public function activities():HasMany{return $this->hasMany(Activity::class);}
 public function reportPreferences():HasMany{return $this->hasMany(ReportPreference::class);}
 public function reportReminders():HasMany{return $this->hasMany(ReportReminder::class);}
}
