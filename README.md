# Lab Bank

A banking application built with **Domain-Driven Design (DDD)** and **Clean Architecture** principles. This is a study project focused on applying enterprise-grade software design patterns in a PHP/Laravel stack.

---

## Table of Contents

- [Tech Stack](#tech-stack)
- [Architecture Overview](#architecture-overview)
- [Layer Structure](#layer-structure)
- [Domain Design](#domain-design)
- [Domain Event Flow](#domain-event-flow)
- [Database Design](#database-design)
- [Getting Started](#getting-started)
- [Development](#development)
- [Testing](#testing)
- [Code Style](#code-style)

---

## Tech Stack

| Layer | Technology |
|---|---|
| Runtime | PHP 8.4 |
| Framework | Laravel 13 |
| HTTP Server | Laravel Octane |
| Queue | Laravel Horizon |
| Observability | Laravel Telescope |
| State Machine | spatie/laravel-model-states |
| Data Transfer | spatie/laravel-data |
| Testing | Pest PHP 4 |
| Code Style | Laravel Pint (PSR-12) |
| Containerization | Laravel Sail (Docker) |
| Storage | AWS S3 |
| Cache / Queue | Redis |
| Database | MySQL (per-domain connections) |

---

## Architecture Overview

The application is structured around **DDD vertical slices** combined with **Clean Architecture** dependency rules. All business code lives under `src/` with the `Src\` namespace. Laravel's `app/` directory is intentionally minimal — it contains only the `AppServiceProvider`, `TelescopeServiceProvider`, and custom validation rules.

```
Dependency Rule: outer layers depend on inner layers, never the reverse.

┌─────────────────────────────────────────┐
│              Interfaces                 │  HTTP controllers, Laravel event wrappers, listeners
├─────────────────────────────────────────┤
│             Application                 │  Use-case handlers, DTOs
├─────────────────────────────────────────┤
│           Infrastructure                │  Eloquent repositories, event store, providers
├─────────────────────────────────────────┤
│               Domain                    │  Models, states, events, value objects, contracts
└─────────────────────────────────────────┘
                  ↑
              Shared/
         AggregateRoot, DomainEvent, ValueObject base classes
```

Each **domain** (Identity, Accounts, Cards, Investments) is a vertical slice that spans all four layers. Domains are isolated from each other and communicate exclusively through domain events.

---

## Layer Structure

```
src/
├── Domain/
│   ├── Identity/
│   │   ├── Contracts/         # Repository interfaces
│   │   ├── Enums/
│   │   ├── Events/            # Plain PHP domain event objects
│   │   │   ├── Customer/      # CustomerRegisteredEvent, CustomerActivatedEvent, CustomerBlockedEvent
│   │   │   └── Kyc/           # KycApprovedEvent, KycRejectedEvent
│   │   ├── Exceptions/
│   │   ├── Models/            # Customer, CustomerAddress, KycVerification (aggregate roots)
│   │   ├── Observers/
│   │   ├── States/            # KycStatus, Status (spatie/laravel-model-states)
│   │   └── ValueObjects/      # Cpf
│   └── Accounts/
│       ├── Contracts/
│       ├── Enums/
│       ├── Events/
│       │   ├── Account/       # AccountOpenedEvent
│       │   └── Transaction/
│       ├── Exceptions/
│       ├── Models/            # Account, AccountBalance, LedgerEntry, Transaction
│       ├── Observers/
│       └── States/            # AccountStatus, TransactionStatus
│
├── Application/
│   ├── Identity/
│   │   ├── DataObjects/       # RegisterCustomerData, ApproveKycData, RejectKycData, ...
│   │   ├── Handlers/          # RegisterCustomerHandler, ApproveKycHandler, ...
│   │   ├── Commands/
│   │   └── Queries/
│   └── Accounts/
│       ├── DataObjects/       # OpenAccountData
│       └── Handlers/          # OpenAccountHandler
│
├── Infrastructure/
│   ├── Auth/
│   ├── EventStore/
│   ├── Messaging/
│   ├── Persistence/
│   │   ├── Identity/          # EloquentCustomerRepository, EloquentKycVerificationRepository, ...
│   │   └── Accounts/          # EloquentAccountRepository, EloquentAccountBalanceRepository
│   ├── Providers/
│   │   ├── IdentityServiceProvider.php
│   │   └── AccountServiceProvider.php
│   ├── Services/
│   └── Storage/
│
├── Interfaces/
│   ├── Events/                # Laravel event wrappers (domain event + Eloquent model)
│   └── Http/
│       └── Controllers/
│           ├── Identity/      # RegisterCustomerController, ApproveKycController, ...
│           └── Accounts/
│
└── Shared/
    ├── Events/                # DomainEvent base class
    ├── Traits/                # AggregateRoot trait
    └── ValueObjects/          # ValueObject base class
```

---

## Domain Design

### Identity Domain

Manages customer lifecycle and KYC (Know Your Customer) compliance.

**Aggregate Roots**
- `Customer` — core customer entity with personal data, CPF (Brazilian tax ID), and lifecycle status
- `KycVerification` — tracks document submission and review state

**Value Objects**
- `Cpf` — encapsulates CPF validation logic; throws on invalid input

**State Machines**

`Customer::$status`:
```
pending → active → blocked
```

`KycVerification::$status`:
```
pending_documents → under_review → approved
                               └→ rejected
```

**Handlers (Use Cases)**

| Handler | Responsibility |
|---|---|
| `RegisterCustomerHandler` | Creates a new customer with address and KYC record |
| `SubmitKycDocumentsHandler` | Attaches documents to a KYC record |
| `StartKycReviewHandler` | Moves KYC to `under_review` |
| `ApproveKycHandler` | Approves KYC and activates customer |
| `RejectKycHandler` | Rejects KYC submission |

**HTTP Endpoints** (`routes/identity/identity.php`)

```
POST   /customers                          RegisterCustomerController
POST   /customers/{id}/kyc/documents       SubmitKycDocumentsController
POST   /customers/{id}/kyc/review          StartReviewController
POST   /customers/{id}/kyc/approve         ApproveKycController
POST   /customers/{id}/kyc/reject          RejectKycController
```

---

### Accounts Domain

Manages bank account lifecycle, balances, ledger entries, and transactions.

**Aggregate Roots**
- `Account` — bank account with status and balance tracking
- `AccountBalance` — current balance snapshot
- `LedgerEntry` — immutable double-entry bookkeeping record
- `Transaction` — financial movement with state

**State Machines**

`Account::$status`:
```
pending → active → blocked → closed
```

`Transaction::$status`:
```
pending → completed
       └→ failed
```

**Handlers (Use Cases)**

| Handler | Responsibility |
|---|---|
| `OpenAccountHandler` | Opens a new bank account for a registered customer |

---

## Domain Event Flow

Domain events are the backbone of cross-cutting concerns (audit, persistence, notifications). The flow is:

```
1. Model method
   └─ $this->recordEvent(new CustomerRegisteredEvent(...))
      Stored in-memory on the aggregate root

2. Eloquent Observer (saved hook)
   └─ $model->pullDomainEvents()
      Maps each DomainEvent → Laravel Event wrapper
      Dispatches via event(new CustomerRegisteredLaravelEvent($domainEvent, $model))

3. Laravel Listener
   └─ PersistDomainEvent
      Receives the Laravel event wrapper
      Persists the domain event payload to the domain_events table
```

This design keeps domain events as pure PHP objects (no Laravel coupling), while still leveraging Laravel's event system for dispatching and listening.

---

## Database Design

Each domain has its **own isolated MySQL database connection**. There are no cross-domain foreign keys. This enforces domain boundaries at the infrastructure level and allows each domain to be scaled or extracted independently.

| Connection | Env Variable | Domain | Key Tables |
|---|---|---|---|
| `identity` | `DB_IDENTITY` | Customer / KYC | `customers`, `customer_addresses`, `kyc_verifications`, `audit_logs`, `domain_events` |
| `accounts` | `DB_ACCOUNTS` | Accounts / Ledger | `accounts`, `account_balances`, `ledger_entries`, `transactions`, `domain_events`, `sequences` |
| `cards` | `DB_CARDS` | Cards | _(planned)_ |
| `investments` | `DB_INVESTMENTS` | Investments | _(planned)_ |
| `app` | `DB_APP` | Shared / App-level | `personal_access_tokens`, `sessions` |

### Notable Tables

**`domain_events`** (per-domain) — append-only event store. Persists every domain event with its type, payload, and metadata. Each domain maintains its own isolated table.

**`audit_logs`** — records all state changes and sensitive operations in the Identity domain.

**`sequences`** — manages account number generation in the Accounts domain.

**`ledger_entries`** — double-entry bookkeeping. Every financial movement produces at least two ledger entries (debit + credit) that must always balance.

### Migrations

Migrations are organized per domain under `database/migrations/`:

```
database/migrations/
├── identity/     # customers, customer_addresses, kyc_verifications, audit_logs, domain_events
├── accounts/     # accounts, account_balances, ledger_entries, transactions, domain_events, sequences
├── cards/        # (planned)
└── investments/  # (planned)
```

---

## Getting Started

### Prerequisites

- Docker + Docker Compose
- PHP 8.4 + Composer
- Node.js + npm

### Setup

```bash
# Clone the repository
git clone <repo-url> && cd lab-bank

# Copy environment file
cp .env.example .env

# Run initial setup (install dependencies, generate key, run migrations, build assets)
composer run setup
```

Configure per-domain database connections in `.env`:

```env
DB_IDENTITY=identity_db
DB_ACCOUNTS=accounts_db
DB_CARDS=cards_db
DB_INVESTMENTS=investments_db
DB_APP=app_db
```

### Running with Sail (Docker)

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
```

---

## Development

```bash
# Start all services: HTTP server, queue worker, log viewer, Vite
composer run dev
```

Runs concurrently:

| Process | Command | Purpose |
|---|---|---|
| server | `php artisan serve` | HTTP server |
| queue | `php artisan queue:listen` | Queue worker |
| logs | `php artisan pail` | Real-time log viewer |
| vite | `npm run dev` | Asset bundler |

---

## Testing

Tests use **Pest PHP 4** with the Laravel plugin.

```bash
# Run all tests
php artisan test --compact

# Run a specific test
php artisan test --compact --filter=RegisterCustomerTest
```

Test files live in `tests/` and mirror the `src/` structure. Integration tests use real database connections — no mocking of the persistence layer.

---

## Code Style

All code follows **PSR-12**, **Clean Code**, **SOLID**, and **Object Calisthenics**. Key rules:

- One level of indentation per method — extract until it hurts
- No `else` — use early returns and guard clauses
- Primitives with domain behavior wrapped in Value Objects (`Cpf`, `Money`, `Email`)
- Classes under 150 lines (hard limit: 200)
- No abbreviations (`$customer` not `$cust`)
- Interfaces defined in the Domain layer; implementations live in Infrastructure

Format after every change:

```bash
vendor/bin/pint --dirty --format agent
```

See [docs/CODE_STYLE.md](docs/CODE_STYLE.md) and [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) for full documentation.

---

## License

MIT
