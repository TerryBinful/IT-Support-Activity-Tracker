<?php

use App\Models\Activity;
use App\Models\Category;
use App\Models\User;
use App\Services\Activities\CompleteActivity;
use App\Services\Activities\CreateActivity;
use App\Services\Activities\QuickLogActivity;
use App\Services\Activities\StartActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    Category::create(['name' => 'User Support', 'sort_order' => 0]);
});

it('creates a quick log activity for the authenticated user', function () {
    $activity = app(QuickLogActivity::class)->handle($this->user, [
        'title' => 'Reset password for HR user',
    ]);

    expect($activity->title)->toBe('Reset password for HR user')
        ->and($activity->user_id)->toBe($this->user->id)
        ->and($activity->status)->toBe('completed');
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

it('shows the quick log form on the dashboard', function () {
    $this->get(route('dashboard'))->assertOk()->assertSee('Quick Log');
});
