# IT Activity Tracker — Cursor Implementation Work Plan

## Purpose

Extend the existing working **IT Activity Tracker** Laravel application into a polished multi-user personal work/activity management system for IT professionals.

This document is intended to be given directly to **Cursor/AI coding agents** as the implementation specification.

The existing application already has a working Docker-based Laravel foundation, authentication, user-owned activities, dashboard, basic reporting/export functionality, and monthly reminder infrastructure.

**Do not replace the existing application or rewrite working features unnecessarily. Inspect the current codebase first, preserve what already works, and implement the features below incrementally.**

---

# 1. Product Vision

The application should make it extremely easy for an IT professional to:

1. Quickly record something they did.
2. Track work from start to completion.
3. Keep useful evidence and context.
4. Remember outstanding follow-ups.
5. Reuse common task patterns.
6. Review their work over days/weeks/months.
7. Generate professional reports without manually rebuilding them.
8. Receive a reminder to prepare/submit their monthly report.
9. Maintain a private workspace where their records belong only to them unless an administrator/team feature is explicitly added later.

The application should feel like a **fast personal IT work journal + reporting system**, not a generic project-management application.

---

# 2. Technical Constraints

## Required stack

Keep the existing stack unless inspection of the current repository shows a compelling reason to change it:

- Laravel 13
- PHP 8.4
- Livewire
- Blade
- Tailwind CSS / existing UI framework
- PostgreSQL
- Docker / Docker Compose
- Nginx or the existing Laravel web container arrangement
- Queue worker
- Laravel scheduler

## Docker-first rule

The project must remain fully reproducible from Docker.

A developer/server with Docker should not need to install:

- PHP
- Composer
- Node.js
- npm
- PostgreSQL
- Nginx

on the host machine.

All application dependencies and runtime services must remain containerized.

## Source control

The GitHub repository is the source of truth for:

- application source
- migrations
- seeders
- configuration templates
- Docker configuration
- dependency lock files
- tests
- documentation

Never commit:

- `.env`
- production secrets
- passwords
- API keys
- private credentials
- generated private certificates

---

# 3. Existing Functionality to Preserve

Before making changes, inspect the repository and confirm what currently exists.

Preserve and improve, rather than unnecessarily replacing:

- Authentication
- User registration/login/logout
- User-owned activity records
- Dashboard
- Activity CRUD
- Categories
- Priority/status
- Activity timestamps
- Basic filtering/search
- Reports
- CSV export
- Excel export
- PDF export
- Column selection/order functionality
- Saved report preferences
- Monthly report reminder infrastructure
- Docker setup
- PostgreSQL persistence

Where existing implementation differs from this plan, adapt the plan to the codebase while preserving the intended behaviour.

---

# 4. Core UX Principles

The application should follow these principles throughout development.

## 4.1 Fast data entry

Logging a tiny task should take seconds.

Do not force users to fill every field.

Required fields should be minimal:

- Activity title/description
- Activity date (default today)

Everything else may be optional unless the user starts/completes a task or explicitly chooses additional details.

## 4.2 Progressive disclosure

Do not expose every advanced field on every form.

Show common fields first.

Advanced information can live under sections such as:

- Details
- Timing
- Outcome
- Follow-up
- Evidence
- Reference

## 4.3 Preserve user input

Do not silently discard text or selections when navigating between sections, applying filters, opening modals, etc.

## 4.4 Clear status

Users must always be able to understand:

- what is happening
- whether a task is active
- whether something requires action
- whether an operation succeeded
- what was saved

## 4.5 Keyboard-friendly

Forms and major actions should be usable efficiently with a keyboard.

## 4.6 Responsive

The application must work well on:

- desktop
- laptop
- tablet
- mobile

The primary use case is desktop/laptop, but mobile should remain usable.

## 4.7 Accessibility

Use:

- semantic labels
- visible focus states
- adequate contrast
- accessible buttons
- keyboard navigation
- sensible error messages
- screen-reader-friendly form controls where practical

---

# 5. Feature Roadmap

Implement in this order.

## Phase 1 — Excellent activity logging

1. Quick Log
2. Start / Complete Task
3. Activity templates
4. Recurring activities
5. Attachments/evidence
6. Better activity details/history
7. Global search

## Phase 2 — Follow-up and reporting

8. Follow-up management
9. Enhanced monthly report
10. Saved report presets
11. Dashboard analytics
12. Calendar view

## Phase 3 — Notifications and productivity

13. Notification centre
14. Monthly reminder improvements
15. Optional follow-up/overdue reminders
16. Activity completion prompts where useful

## Phase 4 — Data portability and administration

17. Excel import
18. Data backup/restore utilities
19. Admin role
20. Team/reporting features
21. Audit controls

Do not jump ahead to team features before the personal workflow is polished.

---

# 6. Phase 1 — Quick Log

## Goal

Allow users to record a small activity extremely quickly.

## UX

Add a prominent:

`+ Quick Log`

action in the dashboard and activity screens.

Example:

```text
Quick Log

What did you do?

[ Resolved Outlook issue for Finance user                  ]

Category
[ User Support ▼ ]

Optional:
[ Add more details ]

                    [Save Activity]
```

On save, automatically record:

- authenticated user
- created timestamp
- activity date
- selected category
- default status = Completed unless explicitly configured otherwise

## Important

Quick Log should not duplicate a large activity form.

Use the existing activity creation logic/service where possible.

## Acceptance criteria

- User can create an activity in a few seconds.
- Activity is attached to the authenticated user.
- User is redirected/updated without losing the saved record.
- No fields from the full activity form are unnecessarily required.
- Validation is friendly.
- Duplicate activity creation is avoided on repeated form submission.

---

# 7. Phase 1 — Start / Complete Task

## Goal

Allow users to track real work duration without manually calculating it.

## UX

Add:

`Start Task`

When clicked:

- record `started_at`
- status becomes `In Progress`
- UI shows active timer state

Example:

```text
Network configuration

Started 09:14

Running for 01:27

[Complete Task]
```

On completion:

- record `completed_at`
- calculate duration
- status becomes `Completed`

## Timing model

Keep these values conceptually separate:

- `created_at` = record creation time
- `activity_date` = date the activity belongs to
- `started_at` = work start
- `completed_at` = work completion
- `duration_minutes` = calculated duration

Do not use `created_at` as the work start time.

## Edge cases

Handle:

- completing a task that has no start time
- starting an already active task
- completing an already completed task
- editing timestamps after completion
- a task crossing midnight
- browser refresh while a task is active
- user closing the browser while task is active

Never rely only on a JavaScript timer for authoritative duration; server-side timestamps must remain the source of truth.

## Acceptance criteria

- User can start one or more permitted tasks according to the chosen UX.
- Active task state survives page refresh.
- Duration is calculated server-side.
- Completed task displays the duration clearly.
- Invalid state transitions are prevented.

---

# 8. Phase 1 — Activity Templates

## Goal

Reduce repetitive typing.

## Examples

Templates may include:

- Daily server health check
- User account provisioning
- Password reset
- Network troubleshooting
- Firewall rule change
- Backup verification
- Software deployment
- Vulnerability remediation
- Printer troubleshooting
- Vendor follow-up
- Monthly license review

## Data model

Introduce a user-owned template entity such as:

`activity_templates`

Suggested fields:

- id
- user_id
- category_id
- title
- description
- default_priority
- default_status
- default_follow_up_required
- default_follow_up_action
- default tags/configuration as appropriate
- is_active
- created_at
- updated_at

## UX

Allow:

- Create template
- Edit template
- Delete/deactivate template
- Create activity from template

Example:

```text
New Activity

[Use Template ▼]

○ Daily Server Health Check
○ User Account Provisioning
○ Network Troubleshooting
○ Firewall Change
```

When selected, populate sensible defaults.

User must be able to modify populated values before saving.

## Acceptance criteria

- Templates belong to individual users unless explicitly designated system-wide.
- User cannot access another user's private templates.
- Creating from a template never automatically saves until the user submits.
- Template values can be overridden.

---

# 9. Phase 1 — Recurring Activities

## Goal

Handle repeated operational tasks.

## Examples

Daily:
- Server health check

Weekly:
- Backup verification
- Monitoring review

Monthly:
- License review
- Antivirus status review

## Suggested model

Create a recurring activity configuration, for example:

`recurring_activities`

Fields:

- id
- user_id
- title
- description
- category_id
- priority
- recurrence_type
- recurrence_rule/configuration
- next_run_at
- is_active
- template/reference information
- created_at
- updated_at

Use Laravel's scheduler for generation.

## Important

Recurring activities should create activity records. They should not merely appear as fake UI placeholders.

Prevent duplicate generation if the scheduler runs more than once.

## Acceptance criteria

- User can create, pause, resume, edit and delete recurring activities.
- Scheduler generates the expected activity.
- Duplicate runs do not create duplicate activities.
- Recurring definitions belong to the correct user.
- User can see that an activity originated from a recurring rule.

---

# 10. Phase 1 — Attachments / Evidence

## Goal

Allow users to preserve evidence of work.

Typical examples:

- screenshots
- change evidence
- configuration exports
- PDF correspondence
- vendor documents
- diagnostic output

## UX

Within activity details:

```text
Evidence

[+ Add Evidence]

📎 firewall-change.png
📎 switch-config.txt
📎 vendor-email.pdf
```

## Storage

Use Laravel's filesystem abstraction so storage is not tightly coupled to one storage implementation.

For local deployment, a persistent Docker-mounted storage location is acceptable.

Design storage so it can later move to S3-compatible storage without changing application business logic.

## Security

Attachments must not be publicly accessible by predictable URL unless explicitly intended.

Downloads/previews must verify:

- authenticated user
- ownership/authorization
- file existence
- safe filename handling

Validate:

- allowed file types
- maximum size
- MIME type where practical

Do not trust the extension alone.

## Data model

An `activity_attachments` table may include:

- id
- activity_id
- user_id
- original_name
- stored_name/path
- mime_type
- size
- created_at

## Acceptance criteria

- User can upload one or multiple files.
- User can remove attachments.
- User can securely download attachments.
- Unauthorized users cannot access another user's evidence.
- File metadata is stored correctly.
- Deleting an activity handles related files safely.

---

# 11. Phase 1 — Activity Detail and History

## Goal

Give each activity a useful audit-style timeline.

Example:

```text
Activity Timeline

08:42  Activity created
08:43  Task started
09:16  Status changed → In Progress
10:12  Evidence attached
10:17  Task completed
```

## Data model

Use a simple immutable activity history/event table if practical:

`activity_histories`

Suggested fields:

- id
- activity_id
- user_id
- event_type
- old_values
- new_values
- metadata
- created_at

Do not allow ordinary users to edit history records.

## Acceptance criteria

Important activity changes produce a useful history entry.

At minimum capture:

- creation
- status changes
- start
- completion
- important edits
- attachment add/remove
- follow-up changes

---

# 12. Phase 1 — Global Search

## Goal

Find historical work quickly.

Search should cover at least:

- title
- description
- outcome
- blockers
- reference number
- follow-up action
- tags
- category where appropriate

## UX

Global search box:

```text
🔍 Search activities...
```

Results should show:

- date
- title
- category
- status
- relevant match context where practical

## Performance

Start with database search appropriate to PostgreSQL.

Avoid introducing a separate search engine unless there is a measured need.

## Acceptance criteria

- Search only returns records the authenticated user is allowed to see.
- Searches are reasonably fast.
- Empty/no-result states are clear.
- Search can be combined with filters where practical.

---

# 13. Phase 2 — Follow-up Management

## Goal

Turn "Follow-up required" into an actual task-management mechanism.

## UX

Example:

```text
Follow-ups

Overdue
🔴 Replace failed AP
Due: 01 Sep

Due Today
🟠 Confirm ISP quotation

Upcoming
🟢 Vendor follow-up
Due: 09 Sep
```

Add optional fields such as:

- follow_up_due_at
- follow_up_status
- follow_up_completed_at

Do not force every activity to have a follow-up date.

## Status

Suggested:

- Open
- Completed
- Cancelled

## Acceptance criteria

- Follow-ups are filterable.
- Overdue items are clearly indicated.
- Completing an activity does not automatically complete its follow-up unless explicitly intended.
- Follow-up history is retained where useful.

---

# 14. Phase 2 — Monthly Reports

## Goal

Produce a professional report from the user's existing activity data.

The report should summarize:

- activity count
- completed/in-progress/pending
- time logged
- category breakdown
- major activities
- outstanding items
- follow-ups
- optional notes

## Report workflow

```text
Reports
   ↓
Select period
   ↓
Apply filters
   ↓
Choose layout
   ↓
Preview
   ↓
Export
```

## Default monthly period

Provide a quick option:

`Previous Month`

For example, when in September:

`01 Aug → 31 Aug`

Use the application's configured timezone consistently.

## Acceptance criteria

- Report can be generated repeatedly without modifying activity records.
- Results are scoped to the authenticated user.
- Summary numbers match underlying activity records.
- Report preview and exported versions use the same filter/column configuration.

---

# 15. Phase 2 — Saved Report Presets

## Goal

Let users configure a report once and reuse it.

Examples:

```text
My Reports

⭐ Monthly Management Report
📊 Detailed Activity Log
🛡 Security Activities
🌐 Network Work
```

A saved preset should store:

- name
- selected columns
- column order
- filters
- sort order
- date-range mode if applicable
- export preferences where appropriate

Users should be able to:

- create
- rename
- duplicate
- delete
- mark default

## Important

A preset is a user's configuration. It must not expose another user's private configuration.

---

# 16. Phase 2 — Export Engine

## Goal

Create a consistent export pipeline for:

- Excel
- CSV
- PDF

## Architecture

Do not build three separate report-generation implementations with duplicated business logic.

Create a common report query/configuration layer:

```text
Report Definition
      ↓
Filtered Dataset
      ↓
Column Definition
      ↓
Formatter
   ├── XLSX
   ├── CSV
   └── PDF
```

## Column system

Columns should be represented using stable internal keys, not raw database column names in user-facing configuration.

Example:

```text
activity_date
created_at
title
category
priority
status
started_at
completed_at
duration
outcome
blockers
follow_up
reference_number
```

Map these to display labels through a central registry.

## User-controlled order

The UI should support drag-and-drop reordering.

Do not trust submitted column keys blindly. Validate them against the allowed column registry.

## Excel

Use the current compatible Laravel Excel release for Laravel 13/PHP 8.3+ rather than an outdated package version.

Current Laravel Excel 4.x supports Laravel 12/13 and PHP 8.3+, and requires the PHP extensions documented by the package.

Reference:
https://docs.laravel-excel.com/4.x/getting-started/installation.html

## CSV

Use streaming/chunking when practical for large exports.

## PDF

Use a maintained PDF solution compatible with the current Laravel/PHP stack.

## Acceptance criteria

- User can choose format.
- User can select columns.
- User can reorder columns.
- Saved presets can be applied.
- Exported column order exactly matches the configured order.
- Export contains only authorized user data.
- Large exports do not unnecessarily load the full dataset into memory.

---

# 17. Phase 2 — Dashboard Analytics

The dashboard should become genuinely useful without becoming visually noisy.

## Suggested metrics

Today:

- Activities
- Completed
- In progress
- Follow-ups due
- Active tasks

This month:

- Total activities
- Completion rate
- Total time logged
- Category breakdown

## Useful visualizations

Prefer a small number of meaningful charts:

- activities by category
- activities by status
- activity volume over time

Do not add charts merely for decoration.

## Additional insights

Possible cards:

- Most active category
- Longest completed task
- Outstanding activities
- Overdue follow-ups

---

# 18. Phase 2 — Calendar View

## Goal

Provide a date-oriented way to review activity.

Example:

```text
September 2026

Mon Tue Wed Thu Fri

    1   2   3   4
    3   5   2   4

7   8   9  10  11
4   2   6   3   5
```

Clicking a day should show activities for that date.

Provide:

- month view
- day activity list
- quick add from selected date

Do not build a complex scheduling/calendar platform. This is a work-history calendar.

---

# 19. Phase 3 — Notification Centre

## Goal

Make reminders actionable.

Provide a notification area with examples:

```text
🔔 Notifications

Monthly report is due
Follow-up due tomorrow
Task overdue
Recurring activity created
```

Use Laravel's notification infrastructure.

Notifications should support:

- unread/read state
- mark read
- mark all read
- relevant destination link

Avoid using real-time WebSockets unless there is a strong product need.

The UI can initially update through normal page refresh/Livewire interactions.

---

# 20. Phase 3 — Monthly Report Reminder

The existing requirement is:

**Every last Friday of the month, remind the user to submit/review their monthly report.**

## Behaviour

The scheduler should:

1. Determine whether the current date is the final Friday of the month.
2. Generate one reminder per applicable user.
3. Avoid duplicate reminders if the scheduler executes more than once.
4. Link directly to the monthly report/review page.

Example:

```text
Monthly Report Reminder

It is the last Friday of the month.

Your August 2026 activity report is ready for review.

Activities: 47
Completed: 41
Outstanding: 6

[Review Report]
```

## Duplicate protection

Use a unique logical notification/event key or an equivalent database constraint/design so repeated scheduler execution cannot create duplicate reminders.

## Timezone

The reminder must use the configured application/user timezone consistently.

---

# 21. Phase 3 — Optional Follow-up Notifications

After the monthly reminder is stable, consider:

- overdue follow-up notification
- follow-up due tomorrow
- unusually long-running active task reminder

These should be optional/configurable and should not generate notification spam.

---

# 22. Phase 4 — Excel Import

## Goal

Allow migration from an existing spreadsheet.

Workflow:

```text
Import
 ↓
Upload Excel
 ↓
Detect columns
 ↓
Map columns
 ↓
Preview
 ↓
Validate
 ↓
Import
```

## Safety

Never import immediately without a preview.

Show:

- valid rows
- invalid rows
- mapping issues
- duplicate warnings where possible

Allow the user to cancel before committing.

## Import mapping

Support common fields such as:

- date
- time
- activity
- category
- priority
- status
- start
- end
- outcome
- reference

Use the same canonical activity rules as normal activity creation.

---

# 23. Phase 4 — Admin Role

Do not prioritize this until personal workspaces are solid.

Introduce roles only when needed.

Suggested:

- User
- Admin

Admin capabilities can include:

- user management
- category management
- system settings
- global reporting where explicitly authorized
- audit review

## Security

Do not implement admin access through hidden UI alone.

Use Laravel authorization policies/gates/middleware.

---

# 24. Multi-User Data Isolation

This is critical.

Every user-owned query must be scoped to the authenticated user.

Do not depend solely on:

```text
WHERE user_id = ...
```

manually repeated throughout the application if a safer policy/service pattern can be established.

Use:

- Laravel policies
- authorization checks
- scoped relationships
- route/model authorization

Test explicitly that:

- User A cannot view User B's activity.
- User A cannot edit User B's activity.
- User A cannot delete User B's activity.
- User A cannot download User B's attachment.
- User A cannot access User B's templates.
- User A cannot use User B's report presets.

Never rely on hiding navigation links as a security control.

---

# 25. Database Design Guidance

The exact schema should be adapted to the existing codebase.

Expected core entities may include:

```text
users
categories
activities
activity_templates
recurring_activities
activity_attachments
activity_histories
report_preferences / report_presets
notifications
```

Potential follow-up fields may live on `activities` rather than requiring a separate table initially.

Do not normalize prematurely.

Prefer a simple relational design until actual requirements justify additional entities.

Add proper foreign keys and indexes.

Indexes should target real query patterns, particularly:

- user_id
- user_id + activity_date
- user_id + status
- user_id + category_id
- user_id + created_at
- follow-up due fields
- recurring next-run fields

---

# 26. Service / Domain Structure

Avoid putting all business logic inside Livewire components.

Use clear application services/actions where logic is substantial.

Potential examples:

```text
CreateActivity
StartActivity
CompleteActivity
CreateActivityFromTemplate
GenerateRecurringActivities
GenerateMonthlyReport
GenerateActivityExport
CreateMonthlyReportReminder
```

The exact names may differ based on the repository's conventions.

Livewire components should coordinate UI state, validation, and calls to application logic rather than becoming giant classes.

---

# 27. Validation

Use Laravel validation consistently.

Validate:

- ownership
- category existence and authorization
- enum-like values
- timestamps
- uploaded file types/sizes
- report column keys
- sort/order configuration
- recurrence configuration

Never trust client-side validation alone.

---

# 28. Activity Status State Machine

Define sensible transitions.

Suggested statuses:

```text
Pending
In Progress
Completed
On Hold
Cancelled
```

Avoid allowing nonsensical transitions without explicit handling.

For example:

```text
Completed → In Progress
```

should require deliberate user action and preserve history.

The exact transition model can be simplified if the existing implementation is already stable.

---

# 29. Categories and Tags

Maintain categories as structured data.

Suggested default categories:

- Network
- Systems
- Cybersecurity
- User Support
- Infrastructure
- Applications
- Projects
- Monitoring
- Procurement
- Administration
- Meetings
- Other

Add tags for more flexible classification:

```text
Cisco
VLAN
DHCP
Firewall
Microsoft 365
Active Directory
Backup
Fortinet
Printer
Vendor
```

Tags should be optional.

Do not let category/tag complexity slow down Quick Log.

---

# 30. UI Information Architecture

Recommended navigation:

```text
Dashboard
Activities
   ├── All
   ├── Today
   ├── Active
   ├── Follow-ups
   └── Calendar

Reports
   ├── Report Builder
   ├── Saved Reports
   └── Exports

Templates
Recurring

Notifications

Settings
   ├── Profile
   ├── Preferences
   └── Categories/Tags as authorized
```

Keep navigation concise.

Do not add a navigation item for every small concept.

---

# 31. Activity List UX

The main activity table/list should support:

- date
- title
- category
- status
- priority
- duration
- follow-up indicator
- quick actions

Provide filters for:

- date range
- category
- status
- priority
- follow-up
- search
- tags where implemented

Allow users to clear filters easily.

Provide useful empty states such as:

```text
No activities found.

Try changing your filters or create a new activity.
```

---

# 32. Activity Detail UX

A detail page/drawer should present information in clear sections:

```text
Activity
Description
Timing
Outcome
Follow-up
Evidence
Reference
History
```

The user should not have to scroll through a giant unstructured form.

Use progressive disclosure for optional information.

---

# 33. Error Handling

Provide friendly errors.

Avoid exposing:

- stack traces
- SQL errors
- filesystem internals
- secrets
- server configuration

in normal production UI.

For failures such as export generation or upload errors, provide actionable feedback.

---

# 34. Notification UX

Make the monthly reminder easy to dismiss while still discoverable.

Example dashboard card:

```text
Monthly Report

Your August report is ready.

47 activities
41 completed
6 outstanding

[Review Report] [Dismiss]
```

Dismissal should not delete the report.

It only controls the reminder state.

---

# 35. Testing Requirements

Every significant feature should include tests.

Prioritize:

## Authentication/security

- user ownership
- authorization
- unauthenticated access

## Activities

- create
- update
- delete
- start
- complete
- duration calculation

## Templates

- ownership
- create-from-template

## Recurrence

- correct generation
- duplicate protection

## Reports

- filtering
- column selection
- ordering
- user isolation

## Exports

- format generation
- selected columns
- selected order
- user isolation

## Notifications

- last Friday detection
- duplicate prevention

## Attachments

- upload
- download authorization
- deletion

Do not consider a feature complete solely because it works manually in the browser.

---

# 36. Database Migration Rules

For every schema change:

- create a migration
- make it reversible where practical
- add indexes intentionally
- use foreign keys
- update seeders where needed
- preserve existing data

Never modify production-style database state by manually editing the database.

If a data migration is needed, create an explicit migration/command.

---

# 37. Docker Requirements

Maintain the Docker-first architecture.

Services should remain clearly separated according to the existing project design, potentially:

```text
app
web
postgres
worker
scheduler
node (development tooling if still required)
```

Do not add Redis, Elasticsearch, WebSockets, or other infrastructure unless a concrete feature actually requires it.

### Docker build requirements

The image must contain everything required to run the application.

A clean environment should be reproducible from the repository.

Do not rely on packages installed manually on the host.

### Persistent volumes

At minimum preserve:

- PostgreSQL data
- application storage/evidence as needed

Do not use ephemeral containers for important user data.

---

# 38. Deployment Behaviour

A clean checkout should support a straightforward startup flow such as:

```bash
docker compose up -d --build
```

The startup process should:

- ensure environment is available
- ensure PostgreSQL is reachable
- prepare the application
- run safe migrations
- start web/worker/scheduler services

Do not run destructive commands such as `migrate:fresh` automatically.

Do not regenerate a production Laravel application key on every restart.

---

# 39. Environment Configuration

Use:

`.env.example`

for documented configuration.

Keep secrets out of GitHub.

Document important variables including:

```text
APP_NAME
APP_URL
APP_KEY
APP_ENV
APP_DEBUG

DB_CONNECTION
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD

MAIL_* (when mail is enabled)
FILESYSTEM_DISK
QUEUE_CONNECTION
```

The exact list should match the implementation.

---

# 40. Performance

For normal use, keep the application simple and responsive.

Avoid:

- N+1 queries
- loading all activities unnecessarily
- loading entire datasets before filtering
- generating large exports entirely in memory
- repeatedly recalculating dashboard metrics without need

Use:

- eager loading
- pagination
- query scopes
- indexes
- chunking/streaming for exports where applicable
- queued jobs for genuinely long-running tasks

Laravel Excel supports query/chunk-based exports, which can be used where export sizes justify it.

---

# 41. Security

Apply secure defaults.

## Authentication

Use the existing Laravel authentication system.

## Authorization

Every user-owned resource must be authorized.

## File uploads

Validate file size/type and authorize all access.

## Mass assignment

Use explicit fillable/guarded strategies appropriate to the repository.

## CSRF

Use Laravel's normal protection.

## Secrets

Never log or expose:

- database passwords
- APP_KEY
- mail credentials
- API keys

## User-supplied content

Escape rendered values appropriately.

Do not render user text as raw HTML unless there is a specific sanitized requirement.

---

# 42. Auditability

The system should eventually make it possible to answer:

- Who created this activity?
- When was it created?
- When was it started?
- When was it completed?
- What changed?
- What evidence was attached?
- What follow-up was created?
- When was the report generated?

Keep immutable history where appropriate.

---

# 43. UX Details That Matter

## Empty states

Every major screen needs a useful empty state.

## Confirmation

Confirm destructive actions:

- delete activity
- delete template
- delete attachment
- delete recurring activity
- delete report preset

## Undo where practical

For non-critical destructive actions, consider an undo interaction rather than always requiring a confirmation modal.

## Toasts

Use short success messages:

```text
Activity saved.
Task started.
Report preset updated.
Export ready.
```

Do not overuse toast notifications.

## Loading states

Buttons should indicate when an operation is running.

Example:

```text
Generating...
```

rather than allowing repeated clicks.

---

# 44. Suggested Milestones

Use these milestones as Git commits/releases.

## Milestone 1

`activity-quick-log`

Deliver:

- Quick Log
- improved activity validation
- tests

## Milestone 2

`activity-timing`

Deliver:

- Start Task
- Complete Task
- duration
- state transitions
- tests

## Milestone 3

`activity-templates`

Deliver:

- templates
- create-from-template
- tests

## Milestone 4

`recurring-activities`

Deliver:

- recurring configuration
- scheduler generation
- duplicate prevention
- tests

## Milestone 5

`activity-evidence`

Deliver:

- uploads
- secure download
- storage
- tests

## Milestone 6

`activity-history-search`

Deliver:

- activity history
- global search
- tests

## Milestone 7

`follow-up-management`

Deliver:

- follow-up due dates/status
- follow-up view
- overdue indicators

## Milestone 8

`report-builder`

Deliver:

- report query/configuration architecture
- column registry
- filters
- preview
- saved presets

## Milestone 9

`export-engine`

Deliver:

- Excel
- CSV
- PDF
- customizable columns/order

## Milestone 10

`dashboard-analytics-calendar`

Deliver:

- metrics
- charts
- calendar

## Milestone 11

`notifications`

Deliver:

- notification centre
- monthly reminder
- duplicate protection

## Milestone 12

`import-backup-admin`

Deliver later:

- Excel import
- backup tooling/documentation
- admin roles
- team features

---

# 45. Cursor Working Instructions

This section is especially important.

## Before coding

Inspect:

- Laravel version
- existing Livewire structure
- existing models
- existing migrations
- existing routes
- existing components
- existing Docker files
- authentication implementation
- reporting/export implementation

Then identify where the requested feature naturally belongs.

## Do not

- recreate the application from scratch
- replace the UI framework without a reason
- replace Livewire with React/Vue
- replace PostgreSQL
- remove working authentication
- remove working export features
- introduce unnecessary infrastructure
- change Docker architecture without necessity
- overwrite migrations that may already contain user data

## Do

- reuse existing patterns
- create focused components
- create services/actions for complex business logic
- write migrations
- add authorization
- add tests
- keep UI consistent
- maintain Docker reproducibility
- keep changes incremental

## After each milestone

Run appropriate checks inside Docker, including:

```bash
php artisan test
php artisan migrate:fresh --seed
```

Use `migrate:fresh --seed` only against disposable/test databases.

Also run the frontend/build checks used by the current project.

Verify the application can still start with:

```bash
docker compose up -d --build
```

---

# 46. Recommended Implementation Strategy for Cursor

Do not ask the coding agent to implement every phase in a single giant change.

Instead, work milestone by milestone.

For every milestone:

1. Inspect existing implementation.
2. State the intended files/components/models to change.
3. Implement.
4. Add/update migrations.
5. Add/update tests.
6. Run tests/checks.
7. Verify Docker build/startup.
8. Summarize changes.
9. Move to the next milestone only after the current one is stable.

This keeps the codebase understandable and makes regressions easier to isolate.

---

# 47. Immediate Next Build

Start with **Milestone 1: Quick Log**, then proceed to **Milestone 2: Start/Complete Task**.

Do not implement templates, recurrence, attachments, reports, or admin functionality until the activity-entry workflow is stable.

The finished first slice should provide:

```text
Dashboard
   │
   ├── + Quick Log
   │
   └── Activities
          │
          ├── New Activity
          ├── Start Task
          └── Complete Task
```

The core experience should become:

```text
Something happened
        ↓
Quick Log
        ↓
Saved in seconds
        ↓
Optionally enrich later
        ↓
Start / Complete if duration matters
        ↓
Included automatically in reports
```

That workflow is the foundation of the entire product.

---

# 48. Product Principle

The application should never make the user do administrative work merely so that the software can feel complete.

A good IT activity tracker should capture useful information with the least friction possible.

**Record first. Enrich when useful. Report automatically.**
