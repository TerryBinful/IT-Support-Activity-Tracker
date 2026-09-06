# Build Operations Ledger

This file records the implementation work for the roadmap in `IT_ACTIVITY_TRACKER_CURSOR_WORK_PLAN.md`. Each completed build should add a dated entry describing additions, exclusions, validation, and the commit pushed to the repository.

## Baseline Audit

Date: 2026-09-05

The repository already contains a substantial partial implementation:

- Quick Log and full activity CRUD
- Start/complete timing and server-side duration calculation
- Activity templates and recurring activities
- Private attachments/evidence
- Activity history and global search
- Follow-up management
- Reports, CSV/XLSX/PDF exports, dashboard analytics, and calendar
- Database notifications and monthly report reminder infrastructure
- Docker services for the app, PostgreSQL, worker, and scheduler

### Outstanding work

- Repair the test dependency/runtime baseline and replace the stock unauthenticated example assertion.
- Harden activity state transitions and duplicate submission handling.
- Add security tests for every user-owned resource and attachment operation.
- Complete recurring rule configuration and database-level duplicate protection.
- Make report preview, filters, presets, and exports use one consistent report definition.
- Complete preset rename, duplicate, delete, and default-management workflows.
- Add optional overdue/follow-up notifications without notification spam.
- Add Excel import with mapping, preview, validation, and cancellation.
- Add backup/restore documentation or utilities, then defer admin/team features until personal workflows are complete.
- Add a reproducible Composer lock file if the Docker build can generate one without changing the intended dependency set.

### Explicit exclusions for the current build

- No rewrite of the Laravel, Blade, Livewire, PostgreSQL, or Docker architecture.
- No Redis, Elasticsearch, WebSockets, or other infrastructure without a demonstrated requirement.
- No admin/team features before personal workspace isolation and reporting are complete.
- No destructive database reset in application startup or deployment scripts.
- No secrets, local `.env` files, generated certificates, or credentials in Git.

## Build Queue

1. Activity lifecycle hardening and security regression tests
2. Recurring activity correctness and duplicate protection
3. Report definition, filters, and preset lifecycle
4. Optional follow-up notifications
5. Excel import with preview and validation
6. Backup/restore operations and documentation
7. Admin role and team/reporting features, only after the personal workflow is stable

## Build Entries

### 2026-09-05: Baseline review

Status: reviewed; implementation pending.

Validation: Docker services are running. The current test run is blocked by `Class "Mockery" not found` in the test environment, and the stock `ExampleTest` expects unauthenticated `/` to return `200` even though the application redirects to login.

Next build: lifecycle hardening and focused authorization tests.

### 2026-09-05: Activity lifecycle hardening

Status: implemented and pushed.

Additions:

- Added a per-user UUID key to Quick Log submissions and a unique database constraint so repeated form submissions return the original activity.
- Added server-side rejection for completing cancelled tasks.
- Added focused tests for duplicate Quick Log submissions and invalid completion state.
- Added the missing `notifications` migration used by the existing dashboard and notification centre.
- Added the missing `mockery/mockery` development dependency required by the Laravel test harness.

Exclusions:

- Full status-state validation for direct activity edits remains in the next hardening pass.
- Cross-resource authorization coverage for templates, recurring activities, report presets, and attachments remains queued.
- The generated Laravel `ExampleTest` remains unchanged; it expects unauthenticated `/` to return `200`, while this application correctly redirects guests to login.

Validation: Docker rebuild completed, the focused activity workflow suite passed 6 tests (15 assertions), and the full suite passed 7 tests (17 assertions) with only the generated `ExampleTest` failure described above.

Commit: `22c06af` (`feat: harden activity lifecycle`), pushed to `origin/main`.

### 2026-09-05: Recurring activity correctness

Status: implemented; focused validation passed; commit pending push.

Additions:

- Added weekly and monthly recurrence-day input, validation, and day-aware next-run calculation.
- Added initial scheduling that selects the next configured recurrence day instead of always running immediately.
- Made recurring activity creation idempotent for a recurring rule and activity date.
- Added a database uniqueness constraint for recurring generated activities.
- Added a regression test proving repeated scheduler runs do not create duplicate weekly activities.

Exclusions:

- Recurrence rules remain limited to daily, weekly, and monthly schedules; no cron-style expressions were added.
- Per-user timezone configuration is not introduced; scheduling continues to use the application timezone.

Validation: Docker image rebuild completed. Focused workflow and recurring tests passed 7 tests (18 assertions). Full-suite status remains 7 passed and the generated unauthenticated `ExampleTest` failure recorded above.

Commit: `5d11fe9` (`feat: harden recurring activity generation`), pushed to `origin/main`.

### 2026-09-05: Report preset lifecycle

Status: implemented; focused validation passed; commit pending push.

Additions:

- Added user-scoped report preset rename, duplicate, delete, and default-selection actions.
- Added preset loading by authenticated user ownership rather than trusting a submitted record id.
- Added report preset actions to the saved-layout UI.
- Preserved the existing report/export implementation while wiring the missing preset lifecycle.

Exclusions:

- Preview and export filter synchronization remains for the next report-engine build.
- Drag-and-drop ordering remains the existing accessible arrow-based ordering control.
- No cross-user preset access is permitted.

Validation: Rebuilt Docker image, confirmed all seven report routes are registered, and passed the focused suite with 7 tests (18 assertions).

### 2026-09-06: Docker environment wiring

Status: implemented; validation passed; commit pending push.

Additions:

- Added documented root `.env.example` support for Laravel and PostgreSQL connection values.
- Forwarded database, application, queue, cache, filesystem, and session settings into every Laravel container.
- Added a configurable named Docker network through `DOCKER_NETWORK_NAME`.
- Added setup instructions for copying `.env.example` to a local, Git-ignored `.env`.
- Corrected root environment ignore rules so local credentials are not committed.

Exclusions:

- The PostgreSQL service remains internal to the Compose network and is not exposed on a host port.
- Default development credentials remain placeholders and must be replaced for non-local deployments.

Validation: `docker compose config` rendered the expected values, Docker created `it-activity-tracker-network`, and the running app received the configured database host and credentials.

### 2026-09-06: Attachment upload interface

Status: implemented; validation passed; commit pending push.

Additions:

- Replaced the plain file input on the activity detail page with a visible evidence upload area.
- Added clear supported-file guidance and the 10 MB per-file limit.
- Added multiple-file selection feedback showing the filenames before upload.
- Preserved the existing private storage, validation, authorization, download, deletion, and history behavior.

Exclusions:

- Drag-and-drop file handling, inline previews, thumbnails, and virus scanning remain future enhancements.

Validation: Running the focused Docker test suite passed 7 tests (18 assertions), and the attachment view passed `git diff --check`.

### 2026-09-06: PostgreSQL migration startup race

Status: implemented; validation pending.

Problem found: the app, worker, and scheduler all execute the migration step from the shared Docker entrypoint. They can start together and race to create the same table, producing `relation "recurring_activities" already exists`.

Addition:

- Added a shared application preparation command that wraps migrations and seeding in a PostgreSQL advisory lock.
- Updated the Docker entrypoint so all services use the locked preparation step while retaining retry behavior for database startup.

Exclusion:

- No database volume or existing application data is deleted. The fix addresses startup concurrency only.

Validation: The updated Docker image built successfully and editor diagnostics report no PHP errors in the preparation command. The final container recreation command was issued without volume deletion, but the terminal stopped returning Docker output before the post-restart migration/test result could be captured.

### 2026-09-06: Controller authorization helper

Status: implemented; validation passed; commit pending push.

Problem found: activity creation succeeded, but redirecting to the activity detail page failed because controllers called `$this->authorize(...)` while the shared base controller did not include Laravel's authorization support.

Additions:

- Restored the framework authorization helper on the shared controller base.
- Added a regression test confirming an authenticated user can open an activity they own.

Validation: Focused activity workflow tests passed 7 tests (18 assertions), including the owned-activity detail request.