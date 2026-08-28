<?php
namespace App\Console\Commands;
use App\Models\ReportReminder;use App\Models\User;use Carbon\Carbon;use Illuminate\Console\Command;
class CreateMonthlyReportReminders extends Command{protected $signature='reports:remind';protected $description='Create monthly report reminders on the last Friday.';public function handle():int{$d=now()->copy()->endOfMonth();while($d->dayOfWeek!==Carbon::FRIDAY)$d->subDay();if(!now()->isSameDay($d))return self::SUCCESS;User::query()->each(fn(User $u)=>ReportReminder::firstOrCreate(['user_id'=>$u->id,'report_month'=>now()->startOfMonth()->toDateString()],['reminded_at'=>now()]));$this->info('Monthly report reminders created.');return self::SUCCESS;}}
