# Module Building Document: Service Schema Vectors, Booking Runtime, and Document Rendering

Last updated: 2026-05-12

## 1) Purpose

This document defines the canonical architecture for the service schema, booking payload, document template, and PDF rendering flow.

It replaces ambiguity between:
- service data structure definition
- booking-time captured values
- document rendering structure
- preview and generation payload assembly

The goal is to establish the missing link between schema-driven data capture and schema-aware document rendering, while preserving multi-tenant governance and operational flexibility.

This document supersedes earlier ambiguity around duplicated payload storage, orphaned payload transformers, mixed binding syntax, dead `document_output` usage, and inconsistent layout vector assumptions. The original source module document identified those discrepancies and risks, and this version resolves them with explicit architectural decisions.

---

## 2) Core Concept Model

### 2.1 Service Schema Vector

A service schema vector is the governed blueprint that defines the structure of a service.

Example:

Flight Ticketing Service:
- Passenger Name
- Passenger Email
- Flight Number
- Departure Date
- Arrival Date

Each field carries structural metadata such as:
- key
- type
- required or optional
- single or multiple
- validation rules
- UI hints

A service schema vector does **not** represent filled booking data. It represents the contract for how that data must be captured.

### 2.2 Booking Service Data

Booking service data is the actual instance data captured by an agent at booking time according to the chosen service schema version.

Example:

A flight ticket booking instance may contain:
- passenger names
- passenger email
- flight number
- departure date

This is tenant-side operational data.

### 2.3 Document Vector

A document vector defines how a document should be rendered.

It is the layout contract for printable output and controls:
- page settings
- zones
- blocks
- variable bindings
- layout and formatting behavior

It does **not** define the service data structure itself.

### 2.4 Missing Link

The missing link is the formal binding contract between:
- governed service schema vectors
- runtime booking service data
- tenant-owned document vectors

This architecture defines that bridge explicitly.

---

## 3) Multi-tenant Boundary

### 3.1 Control database

The control database stores governed shared service schema definitions.

Canonical table:
- `service_schemas`

Canonical model:
- `App\Models\ServiceSchema`
- `$connection = 'control'`

Control DB responsibilities:
- service schema identity
- service schema versioning
- canonical field structure
- schema governance by industry
- shared schema publication lifecycle

### 3.2 Tenant database

The tenant database stores operational and rendering records.

Canonical tenant-side responsibilities:
- bookings
- booking services
- booking service runtime payloads
- document templates
- tenant overlays for shared schemas
- generated rendering snapshots

Canonical tenant-side tables include:
- `bookings`
- `booking_services`
- `document_templates`
- tenant overlay tables for schema presentation/local behavior

### 3.3 Boundary rule

Control DB owns the canonical service schema contract.

Tenant DB owns:
- actual booking instances
- local operational flex fields
- document templates
- overlays
- render snapshots

Schema management is shared/governed. Runtime usage and rendering are tenant-owned operational concerns.

This maintains the control-vs-tenant separation described in the original source, while clarifying the link between schema and document rendering.

---

## 4) Canonical Service Schema Identity

The earlier overloaded `service_type` concept is retired as the canonical identifier.

A service schema must use three identity layers:

### 4.1 `service_schema_id`
Internal system identifier and authoritative relational primary key.

### 4.2 `service_code`
Stable machine-readable business identifier, unique per industry.

Examples:
- `travel.flight_ticket`
- `travel.hotel_booking`
- `travel.airport_transfer`

### 4.3 `service_name`
Human-readable display label.

Examples:
- `Flight Ticketing Service`
- `Hotel Reservation`

### 4.4 Uniqueness rule

`service_code` must be unique within industry scope.

Recommended uniqueness:
- unique(`industry`, `service_code`)

### 4.5 Why this exists

`service_type` must no longer simultaneously act as:
- display label
- business key
- lookup key
- uniqueness key

That prior mismatch created architectural inconsistency between DB and controller rules. The original source flagged this explicitly, and this document resolves it by splitting identity into authoritative ID, stable machine key, and display name.

---

## 5) Service Schema Versioning and Lifecycle

### 5.1 Explicit versioning

Each service schema belongs to a schema family and has explicit versions.

Recommended fields:
- `service_schema_id`
- `service_code`
- `service_name`
- `industry`
- `version`
- `status`
- `is_default`

### 5.2 Lifecycle states

Recommended schema lifecycle states:
- `draft`
- `active`
- `deprecated`
- `archived`

### 5.3 Lifecycle behavior

#### Draft
Editable, not available for normal booking use.

#### Active
Available for new bookings.

#### Deprecated
Readable and renderable, but no longer preferred for new bookings.

#### Archived
Retained for historical compatibility only.

### 5.4 Freeze rule

Once a schema version has been used by bookings:
- structural contract is treated as immutable
- breaking changes require a new schema version

### 5.5 Safe editable metadata after use

After a schema version is used, the following may still be editable if they do not break structure:
- display label
- help text
- placeholder
- description
- internal notes

### 5.6 Breaking change rule

Breaking changes require a new version. Structural edits in place are not allowed for used versions.

Examples of breaking change:
- renaming field keys
- changing types
- changing single/multiple cardinality
- removing fields used by runtime or templates

### 5.7 Default rule for new bookings

Only active/default schema versions should be offered for new bookings by default.

---

## 6) Canonical Service Schema Contract

Canonical schema payload remains stored in control DB.

Recommended shape:

```json
{
  "version": 1,
  "service_code": "travel.flight_ticket",
  "service_name": "Flight Ticketing Service",
  "fields": [
    {
      "key": "passenger_name",
      "label": "Passenger Name",
      "type": "string",
      "required": true,
      "multiple": true,
      "rules": ["required"],
      "ui_component": "text_input",
      "placeholder": "Enter passenger name",
      "help_text": null,
      "default": null,
      "text_transform": "none",
      "allowed_values": null,
      "min": null,
      "max": null,
      "pattern": null,
      "order": 0,
      "file_options": null
    },
    {
      "key": "passenger_email",
      "label": "Passenger Email",
      "type": "email",
      "required": true,
      "multiple": false,
      "rules": ["required", "email"],
      "ui_component": "text_input",
      "placeholder": "Enter passenger email",
      "help_text": null,
      "default": null,
      "text_transform": "none",
      "allowed_values": null,
      "min": null,
      "max": null,
      "pattern": null,
      "order": 1,
      "file_options": null
    },
    {
      "key": "flight_number",
      "label": "Flight Number",
      "type": "string",
      "required": true,
      "multiple": false,
      "rules": ["required"],
      "ui_component": "text_input",
      "placeholder": "Enter flight number",
      "help_text": null,
      "default": null,
      "text_transform": "uppercase",
      "allowed_values": null,
      "min": null,
      "max": null,
      "pattern": null,
      "order": 2,
      "file_options": null
    }
  ],
  "document_output": null,
  "pricing_units": ["pax"]
}
```

---

## 7) Current-state vs Target-state Clarification

This document is a **target-state proposal**, not a statement that all items are already implemented in code.

Current implementation currently uses:
- `service_type` as the business key in multiple runtime paths.
- no explicit schema version lifecycle (`draft`, `active`, `deprecated`, `archived`) in persisted model fields.
- booking runtime validation that is mostly structural (`array`) and not fully schema-enforced per field/type.

The proposal introduces:
- `service_code` as stable machine key and `service_name` as display label.
- explicit schema versioning and lifecycle controls.
- strict server-side schema-aware validation for booking payloads.

---

## 8) Immediate Discrepancies to Resolve Before Execution

1. Canonical key mismatch:
   - Current docs and code use `service_type`.
   - Proposal shifts to `service_code`.
   - Resolution: perform additive migration (`service_code` introduced first), then phased cutover.

2. Data compatibility risk:
   - Existing `booking_services` rows store `service_type`.
   - Resolution: backfill and dual-read/dual-write during transition.

3. Runtime rendering gap:
   - `document_output` exists in schema payload but is not fully wired through active document generation flow.
   - Resolution: define single rendering pipeline contract and remove orphan paths.

4. Legacy transformer risk:
   - `DocumentPayloadTransformer` contract diverges from current dictionary path format.
   - Resolution: either deprecate and remove, or refactor to nested dictionary shape and wire into active flow.

---

## 9) Execution Reference

Execution plan is documented in:
- `.knowledge/subtask/solution-draft.md`
