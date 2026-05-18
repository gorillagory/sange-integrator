# Service Handling Flow

Last updated: 2026-05-15

This flow describes how a service vector is handled from capture to documents and downstream analytics.

## Runtime Flow

```mermaid
flowchart TD
    A[User enters tenant workspace] --> B[IdentifyTenant resolves company and tenant DB]
    B --> C[Operations module loads available service schemas]
    C --> D[User selects client and contract]
    D --> E[User adds one or more service vectors]
    E --> F[UI renders schema-defined fields dynamically]
    F --> G[User enters operational payload and finance values]
    G --> H[OperationController validates request envelope]
    H --> I[OperationServicePayloadValidator validates service_details against schema]
    I --> J[CreateOperationAction creates operation envelope]
    J --> K[CreateOperationAction creates service_instances]
    K --> L[Payload snapshots stored per service instance]
    L --> M[Operation review page reads operation + service_instances]
    M --> N[User locks document routing]
    N --> O[DocumentRenderContextFactory builds canonical payload]
    O --> P[Template compiler generates invoice/document output]
    L --> Q[OperationExtractionManager normalizes extraction rows]
    Q --> R[Future analytics panel / projection / AI enrichment]
```

## Logical Stages

### 1. Tenant and handler context

The request must first resolve:

- active company
- active tenant database
- enabled module context
- current handler context

### 2. Dynamic capture

The UI does not hardcode passenger/patient/etc. models.
It renders fields from the selected service vector and stores them inside the service instance payload.

### 3. Commercial capture

At the same capture point, the operator supplies:

- qty
- supplier cost
- tax
- client price
- totals

This makes the operational record commercially meaningful immediately.

### 4. Runtime persistence

The system persists:

- one parent `operation`
- one or more child `service_instances`
- one snapshot per service instance

### 5. Document generation

Documents are built from canonical runtime roots, not from domain-specific child tables.

### 6. Downstream extraction

Analytics should consume normalized extraction rows from service instances, not query raw runtime payloads directly forever.

## Decision Boundary

The most important architectural rule in this flow is:

**capture dynamically first, project later**

That keeps the runtime flexible while preserving a path to reporting and AI-assisted analysis.
