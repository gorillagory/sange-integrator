# UI/UX Rules

## Product Direction

- Build for operational SaaS usage, not marketing pages.
- Optimize for repeated workflows: fast scan, low friction, predictable navigation.
- Keep visual language restrained and consistent across system + tenant views.

## Layout and Information Density

- Favor structured tables/lists/forms over decorative cards.
- Keep section hierarchy clear: title, controls, content.
- Avoid oversized hero blocks, novelty effects, or visual noise.

## Interaction Standards

- Use clear primary actions and consistent secondary actions.
- Prefer explicit feedback for success/error/warning states.
- Keep validation messages specific and field-level where possible.

## Forms and Inertia

- Preserve input state on validation errors.
- Return server-side validation in standard Laravel/Inertia flow.
- Avoid cross-origin XHR redirects; use full-page location redirects when crossing subdomains.

## Accessibility and Readability

- Ensure contrast and readable font sizes in dense views.
- Use meaningful labels for form controls and buttons.
- Keep keyboard navigation and focus order sane in modal/form flows.

## Modularity and sustainability

- Think of creative reuse and maintainability.
- Think modularity and reusability.
- include more flows and features.
- Keep codebase organized and maintainable.
- Use consistent naming conventions and coding standards.
