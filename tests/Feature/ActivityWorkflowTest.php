<?php

use App\Models\Activity;
use App\Models\Category;
use App\Models\RecurringActivity;
use App\Models\User;
use App\Services\Activities\CompleteActivity;
use App\Services\Activities\CreateActivity;
use App\Services\Activities\QuickLogActivity;
use App\Services\Activities\StartActivity;
use App\Services\Recurring\GenerateRecurringActivities;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    Category::firstOrCreate(['name' => 'User Support'], ['sort_order' => 0]);
});

it('creates a quick log activity for the authenticated user', function () {
    $activity = app(QuickLogActivity::class)->handle($this->user, [
        'title' => 'Reset password for HR user',
    ]);

    expect($activity->title)->toBe('Reset password for HR user')
        ->and($activity->user_id)->toBe($this->user->id)
        ->and($activity->status)->toBe('completed');
});

it('returns the existing activity for a repeated quick log submission', function () {
    $data = [
        'title' => 'Reset password for HR user',
        'quick_log_key' => (string) \Illuminate\Support\Str::uuid(),
    ];

    $first = app(QuickLogActivity::class)->handle($this->user, $data);
    $second = app(QuickLogActivity::class)->handle($this->user, $data);

    expect($second->id)->toBe($first->id)
        ->and(Activity::forUser($this->user)->count())->toBe(1);
});

it('starts and completes a task with server-side duration', function () {
    $activity = app(CreateActivity::class)->handle($this->user, [
        'title' => 'Network troubleshooting',
        'activity_date' => now()->toDateString(),
        'priority' => 'medium',
        'status' => 'pending',
    ]);

    app(StartActivity::class)->handle($activity, $this->user);
    $activity->refresh();

    expect($activity->status)->toBe('in_progress')
        ->and($activity->started_at)->not->toBeNull();

    app(CompleteActivity::class)->handle($activity, $this->user);
    $activity->refresh();

    expect($activity->status)->toBe('completed')
        ->and($activity->completed_at)->not->toBeNull()
        ->and($activity->duration_minutes)->toBeInt();
});

it('does not complete a cancelled task', function () {
    $activity = app(CreateActivity::class)->handle($this->user, [
        'title' => 'Cancelled task',
        'activity_date' => now()->toDateString(),
        'priority' => 'medium',
        'status' => 'cancelled',
    ]);

    expect(fn () => app(CompleteActivity::class)->handle($activity, $this->user))
        ->toThrow(\InvalidArgumentException::class, 'Cancelled tasks cannot be completed.');
});

it('generates a weekly recurring activity only once for its run date', function () {
    $recurring = RecurringActivity::create([
        'user_id' => $this->user->id,
        'title' => 'Weekly backup verification',
        'priority' => 'medium',
        'recurrence_type' => 'weekly',
        'recurrence_day' => Carbon::MONDAY,
        'next_run_at' => Carbon::parse('2026-09-07 00:00:00'),
        'is_active' => true,
    ]);

    $generator = app(GenerateRecurringActivities::class);
    $first = $generator->handle(Carbon::parse('2026-09-07 08:00:00'));
    $second = $generator->handle(Carbon::parse('2026-09-07 09:00:00'));

    expect($first)->toBe(1)
        ->and($second)->toBe(0)
        ->and($recurring->activities()->count())->toBe(1);
});

it('prevents users from viewing another users activity', function () {
    $other = User::factory()->create();
    $activity = Activity::create([
        'user_id' => $other->id,
        'title' => 'Private task',
        'activity_date' => now()->toDateString(),
        'priority' => 'medium',
        'status' => 'completed',
    ]);

    $this->get(route('activities.show', $activity))->assertNotFound();
});

it('shows an activity owned by the authenticated user', function () {
    $activity = Activity::create([
        'user_id' => $this->user->id,
        'title' => 'Review diagnostic output',
        'activity_date' => now()->toDateString(),
        'priority' => 'medium',
        'status' => 'on_hold',
    ]);

    $this->get(route('activities.show', $activity))
        ->assertOk()
        ->assertSee('Review diagnostic output');
});

it('shows the quick log form on the dashboard', function () {
    $this->get(route('dashboard'))->assertOk()->assertSee('Quick Log');
});

it('shows the report review page for the authenticated user', function () {
    $this->get(route('reports.index'))
        ->assertOk()
        ->assertSee('Reports');
});

it('exports reports in every supported format', function () {
    Activity::create([
        'user_id' => $this->user->id,
        'title' => 'Exportable activity',
        'activity_date' => now()->toDateString(),
        'priority' => 'medium',
        'status' => 'completed',
    ]);

    foreach (['xlsx', 'csv', 'pdf'] as $format) {
        $this->get(route('reports.export', ['format' => $format]))
            ->assertSuccessful();
    }
});
