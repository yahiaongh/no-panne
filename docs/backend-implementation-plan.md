# No Panne Backend — Implementation Plan

Status: In progress (initial milestone)
Date: 2026-09-23
Author: Backend engineering session

## 1. Current state

- The repository is a monorepo with an existing **Expo (React Native) frontend** under
  `frontend/`. No backend code existed.
- **Backend bootstrapped this session:** Laravel 11.6.1 at `backend/`, running on
  PHP 8.3.33 inside a local Docker image (`no-panne-php`) because the host has no
  root/system PHP. Database is **PostgreSQL 16 + PostGIS 3.4** in a Docker container
  (`no-panne-pg`).
- Dependencies installed: `laravel/sanctum` (default), `spatie/laravel-permission`
  (RBAC). Default Laravel migrations run cleanly against Postgres; the stock test
  suite passes (2/2).
- No `.env` secrets: local dev credentials only (`no_panne`/`secret`).
- No official 48-wilaya / 1541-commune dataset exists in the repository. Schema +
  deterministic fixtures are created now; authoritative data import is a documented
  task.

## 2. Constraints from the operating environment

- No root/sudo on this machine (container `no-new-privileges`). System packages are
  therefore installed as Docker images, not apt packages.
- PHP/Composer are executed through thin wrapper scripts (`backend/dev/bin/php`,
  `backend/dev/bin/composer`, `backend/dev/bin/db`). These are committed so the
  project remains runnable in this environment.
- **Known deviation:** Laravel 11 is EOL and every `11.x` release is flagged by
  Composer's security advisory checker. The BRD mandates Laravel 11, so the
  checker was disabled for this development sandbox. This is tracked as a risk
  (see below) and the upgrade path to the current Laravel LTS is documented.

## 3. Proposed architecture

Layered, Laravel-native structure (no over-engineering):

```
backend/app/
├── Http/
│   ├── Controllers/          # thin controllers, grouped by role (Api/v1/{Auth,Client,Provider,Admin,Reference})
│   ├── Middleware/           # e.g. force-json, role guards
│   ├── Requests/             # Form Requests (validation)
│   └── Resources/            # API transformers
├── Models/                   # Eloquent domain models
├── Services/                 # application/business services (OtpService, ProfileService,
│                             #   RequestCreationService, ProviderRegistrationService,
│                             #   ProviderMatchingService, ReviewService, etc.)
├── Contracts/                # integration interfaces (OtpServiceInterface, MapsServiceInterface,
│                             #   PushNotificationServiceInterface, RealtimePublisherInterface, ...)
├── Infrastructure/           # concrete/fake adapters for the contracts
├── Events/                   # domain events (NewRequest, RequestAccepted, ...)
├── Listeners/ Jobs/ Notifications/ Policies/
└── Support/                  # ApiResponse, enums
```

Design rules:
- Controllers stay thin; business rules live in service classes and models.
- Application logic depends on `Contracts` (interfaces), never on Firebase/Google
  SDKs. Fake/local implementations are bound in local & test environments.
- Response envelope: `{ success, message?, data, meta?, errors? }`. Consistent HTTP
  statuses per Laravel conventions (200/201/401/403/404/409/422/429/500).
- UUIDs for all public-facing domain entities; internal/high-volume tables use
  incremental ids where appropriate.

## 4. Database design (initial)

Relationship- and integrity-focused, derived from BRD §10 but normalized:

`users` (auth principal, holds phone), `clients`, `providers`, `admins`,
`roles`/`permissions` (spatie), `services`, `wilayas`, `communes`,
`provider_services`, `provider_wilaya`, `vehicles`, `provider_documents`,
`requests`, `request_photos`, `request_status_history`, `reviews`,
`review_tags`, `favorites`, `notifications`, `saved_addresses`, `device_tokens`,
`account_deletion_requests`, `audit_logs`, `settings`, `otp_codes`.

Constraints enforced: unique phone; unique vehicle plate; partial-unique active
vehicle per provider; one active intervention per provider; one review per request
(unique on request); rating between 1 and 5; request state machine (validated in a
domain service); coverage radius 5–50 km.

Geospatial: PostGIS present. Provider location kept as `current_lat`/`current_lng`
decimals; proximity queries use PostGIS `ST_DWithin(ST_MakePoint(...))` at runtime,
kept inside the matching service. A `geography` column/expression index may be
added when the matching service matures.

Full rationale for schema enhancements is in `docs/architecture-decisions.md`.

## 5. OTP authentication (local)

- `OTP`: 6 digits, valid 5 minutes, resend cooldown 60 s.
- Lockout: 3 failed attempts → block 10 min; next 3 failures after that → block 1 h.
- One account per phone (unique `users.phone_number`).
- `OtpServiceInterface` + `FakeOtpService`: deterministic codes in tests/dev,
  and `services.otp.fake.expose_code` (dev only) so the client app can read the code
  without real SMS/WhatsApp. Real WhatsApp/Firebase providers are deferred to later
  phases; the contract is stable.
- Sessions via Laravel Sanctum personal access tokens; `POST /auth/refresh-token`
  rotates the token.

## 6. Detected risks

| Risk | Mitigation |
|---|---|
| Laravel 11 EOL + security advisories | Documented; upgrade to current LTS before production; security controls (validation, RBAC, rate limits, audit) still implemented |
| No official wilaya/commune dataset | Schema + deterministic fixtures now; authoritative import documented as a task |
| Single-machine no-root environment | Everything runs in committed Docker tooling |
| Admin web app not in scope yet | Admin RBAC schema + audit trail prepared; APIs land in a later phase |
| External integrations unavailable | All integration boundaries are interfaces + fakes; API remains functional with zero external config |

## 7. BRD ambiguities / inconsistencies

- **`services[]`/`photos[]`/`cancelled_by` fields** are normalized into relational
  tables (`provider_services`, `request_photos`, `request_status_history`, etc.).
- **One phone ↔ client or provider?** BRD says "one account per phone" but both
  client and provider register with phone. Design: a single `users` principal per
  phone; `POST /provider/register` (authenticated via OTP) adds a provider profile
  to the same principal. Documented in ADR.
- **"another 3 incorrect attempts → 1 hour lock"** is interpreted as escalating
  blocks based on cumulative consecutive failures since last success (10 min, then
  1 h). Documented in ADR/tests.
- **Planned-request acceptance window (2 h) and 48 h minimum lead time**: stored as
  configurable settings, enforced when planned requests are implemented (later phase).
- **Provider documents** are enumerated per BRD; schema allows arbitrary new
  document types without redesign (`provider_documents.type` as indexed string).

## 8. Internal configurations (settings)

Implemented as a `settings` table (key/value + type) + `Settings` model, seeded
with BRD defaults and readable/writable only by admin APIs (later phase):
emergency search radii 10/20/30 km, provider response timeout 45 s, planned
acceptance timeout 2 h, cancellation warning threshold 5, trial duration 7 days,
client deletion delay 10 days, provider deletion delay 3 days, archive window, etc.

## 9. Initial implementation milestones

1. **M0 (done):** environment (PHP via Docker, PostGIS), Laravel 11 bootstrap,
   DB connectivity, stock tests green.
2. **M1:** API foundation — `routes/api.php` under `/api/v1`, health endpoint,
   response envelope, error handling, rate limiting, `.env.example`.
3. **M2:** schema migrations + factories + seeders (services SRV-01…SRV-10,
   wilayas/communes fixtures, test users, test clients/providers/admin).
4. **M3:** local OTP authentication (send/verify/refresh) with lockout + tests.
5. **M4:** client APIs — profile, services, wilayas, requests (create/list/detail/
   cancel, initial `en_recherche`), nearby providers.
6. **M5:** provider registration + profile/availability/location + active requests.
7. Later: provider request workflow/state machine, matching service, reviews,
   favorites, notifications, device tokens, documents/storage, admin + RBAC,
   scheduler jobs, OpenAPI.

## 10. Definition of done (per milestone)

Routes exist; controllers/services implemented; validation; authorization;
migrations from clean DB; seeders/factories; feature + negative tests pass
(`php artisan test`); consistent API responses; docs updated; no real external
credentials required; `php artisan route:list` reviewed.