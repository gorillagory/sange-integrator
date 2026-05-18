# Sange Integrator Rules

Before answering non-trivial code questions, treat `.knowledge` as the project brain.

Mandatory reading order:

1. `.knowledge/README.md`
2. `.knowledge/setup-process.md`
3. `.knowledge/development-rules.md`
4. `.knowledge/architecture.md`
5. `.knowledge/data-model.md`
6. `.knowledge/workflow-sop.md`

Core identity:

Sange Integrator is a dynamic company operations platform, not a booking app.

Architecture rules:

- Runtime capture is vector/schema-driven.
- Line-level service records/service instances are the operational truth.
- Parent records are lightweight workflow/commercial envelopes.
- Finance values are captured at operation time.
- Analytics/reporting should be downstream extraction/projection.
- Handlers provide domain meaning; the platform provides runtime discipline.
- Booking compatibility is legacy transition support only.
- Do not reintroduce booking/passenger-specific runtime logic as canonical architecture.

Coding rules:

- Laravel + Inertia + Vue.
- Vue uses JavaScript only, not TypeScript.
- No god components.
- Keep edits scoped.
- Use Sail for PHP, Artisan, DB, and framework checks.
- Preserve control DB versus tenant DB boundaries.
- Do not introduce destructive migrations without explicit approval.
