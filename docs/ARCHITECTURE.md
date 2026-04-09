# Architecture

This is a **DDD + Clean Architecture** banking application. All domain code lives in `src/` under the `Src\` namespace (mapped in `composer.json`). Laravel's `app/` is minimal — primarily `AppServiceProvider`, `TelescopeServiceProvider`, and custom validation rules in `app/Rules/`.

## Layer Structure (`src/`)

```
src/
├── Domain/         # Core business logic (models, states, events, value objects, contracts)
├── Application/    # Use-case handlers and data objects (DTOs)
├── Infrastructure/ # Eloquent repositories, event store, service providers, storage, auth
├── Interfaces/     # HTTP controllers, Laravel event wrappers, listeners
└── Shared/         # AggregateRoot trait, DomainEvent base, ValueObject base
```

Each domain (e.g., `Identity`, `Accounts`) is a vertical slice across all layers.

## Domain Layer

- **Models** extend Eloquent but act as aggregate roots via the `AggregateRoot` trait (`Shared/Traits/AggregateRoot.php`). They record domain events with `$this->recordEvent(new SomeDomainEvent(...))` and expose them via `pullDomainEvents()`.
- **States** use `spatie/laravel-model-states`. Each model has a state class (e.g., `KycStatus`, `Status`) with allowed transitions defined per concrete state.
- **Contracts** are repository interfaces that the Application layer depends on.
- **Events** (`Domain/.../Events/`) are plain PHP objects extending `Src\Shared\Events\DomainEvent`, not Laravel events.
- **Value Objects** extend `Src\Shared\ValueObjects\ValueObject`.

## Application Layer

- **Handlers** are invokable classes (command handlers). Each handler corresponds to one use case (e.g., `RegisterCustomerHandler`). They are injected with domain contracts, not concrete implementations.
- **DataObjects** use `spatie/laravel-data`. They serve as both request validation (via attributes like `#[Rule]`, `#[Date]`) and DTOs passed between layers.

## Infrastructure Layer

- **Repositories** implement the domain contracts using Eloquent (e.g., `EloquentCustomerRepository` implements `CustomerRepositoryContract`).
- **EventStoreRepository** persists all domain events to a `domain_events` table.
- **Service Providers** (e.g., `IdentityServiceProvider`) bind contracts to implementations and register event listeners. They are registered in `bootstrap/providers.php`.

## Interfaces Layer

- **Controllers** are invokable. They receive a `Data` object (spatie/laravel-data handles validation automatically) and delegate to a handler.
- **Laravel Events** (`Interfaces/Events/`) wrap domain events and the Eloquent model. These are what Laravel's event system dispatches.
- **Listeners** (e.g., `PersistDomainEvent`) react to Laravel events and call infrastructure services.

## Domain Event Flow

1. **Model method** calls `$this->recordEvent(new SomeDomainEvent(...))` — stores the event in memory.
2. **Eloquent Observer** (`saved` hook) calls `$model->pullDomainEvents()`, then maps each domain event to a Laravel event and dispatches it via `event(...)`.
3. **Laravel Listener** (e.g., `PersistDomainEvent`) receives the Laravel event and persists the wrapped domain event via `EventStoreRepository`.

## Database Connections

Each domain has its own MySQL database connection defined in `config/database.php`:

| Connection    | Env Var         | Domain              |
|---------------|-----------------|---------------------|
| `identity`    | `DB_IDENTITY`   | Customer / KYC      |
| `accounts`    | `DB_ACCOUNTS`   | Accounts / Ledger   |
| `cards`       | `DB_CARDS`      | Cards               |
| `investments` | `DB_INVESTMENTS`| Investments         |
| `app`         | `DB_APP`        | App-level / shared  |

Always specify the correct connection on models (e.g., `protected $connection = 'identity';`) and when running raw queries.

## Routes

Routes are organized by domain in `routes/` subdirectories (e.g., `routes/identity/identity.php`). When adding a new domain, add a new subdirectory and register the route file in `bootstrap/app.php`.

## Adding a New Domain

Follow the established pattern in `src/Domain/Identity/`:

1. Create `Domain/<Name>/` with Models, States, Enums, Events, Contracts, Exceptions, ValueObjects.
2. Create `Application/<Name>/` with Handlers and DataObjects.
3. Create `Infrastructure/Persistence/<Name>/` with Eloquent repositories.
4. Create `Infrastructure/Providers/<Name>ServiceProvider.php` to bind contracts and register listeners, then add it to `bootstrap/providers.php`.
5. Create `Interfaces/Http/Controllers/<Name>/` with invokable controllers.
6. Create `Interfaces/Events/<Name>/` for Laravel event wrappers.
7. Add a new database connection in `config/database.php` and a migrations path in `database/migrations/<name>/`.
