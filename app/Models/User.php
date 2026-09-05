<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function activityTemplates(): HasMany
    {
        return $this->hasMany(ActivityTemplate::class);
    }

    public function recurringActivities(): HasMany
    {
        return $this->hasMany(RecurringActivity::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function reportPreferences(): HasMany
    {
        return $this->hasMany(ReportPreference::class);
    }

    public function reportReminders(): HasMany
    {
        return $this->hasMany(ReportReminder::class);
    }
}
