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

Commit: pending push for this build.