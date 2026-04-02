# Architecture Best Practices

## Clean Code, SOLID & Object Calisthenics

Apply all of the following on every class and method written. They are not optional suggestions.

### Clean Code
- **Names reveal intent.** `getCustomersWithPendingKyc()` not `getData()`. Avoid generic names like `$data`, `$info`, `$result`, `$temp`.
- **Methods do one thing.** If you need the word "and" to describe a method, split it into two.
- **Guard clauses over nesting.** Return or throw early; never nest `if` blocks more than one level deep.
- **No magic values.** Use named constants, enums, or configuration — never raw strings or numbers in logic.
- **No dead code.** Remove commented-out code, unused variables, and unreachable branches before committing.

```php
// Incorrect — nested, magic string, no intent
public function handle($user) {
    if ($user->status === 'A') {
        if ($user->kyc !== null) {
            // process...
        }
    }
}

// Correct — guard clauses, named comparison, intent clear
public function handle(Customer $customer): void
{
    if (! $customer->isActive()) {
        return;
    }

    if (! $customer->hasCompletedKyc()) {
        return;
    }

    // process...
}
```

### SOLID

**Single Responsibility** — one reason to change per class. Handlers handle one use case, repositories persist, validators validate.

**Open/Closed** — add behaviour by creating new classes or strategies, not by modifying existing ones. Use polymorphism and the Strategy pattern over `if/switch` on type.

**Liskov Substitution** — subtypes must be fully substitutable. Don't throw exceptions or return `null` from methods that the parent contract promises to return a value from.

**Interface Segregation** — keep contracts small and focused. A `CustomerRepositoryContract` should not carry reporting methods. Split into `CustomerRepositoryContract` and `CustomerReportingContract`.

**Dependency Inversion** — always depend on the contract, never on the concrete. Bind in a service provider.

```php
// Incorrect
class RegisterCustomerHandler
{
    public function __construct(private EloquentCustomerRepository $repository) {}
}

// Correct
class RegisterCustomerHandler
{
    public function __construct(private CustomerRepositoryContract $repository) {}
}
```

### Object Calisthenics

1. **One level of indentation per method.** If you need a second level, extract a private method.
2. **No `else`.** Use early returns and guard clauses instead.
3. **Wrap primitives in Value Objects** when the value has domain rules or behaviour (`Cpf`, `Money`, `Email`). A plain `string $cpf` has no validation; a `Cpf` value object is always valid by construction.
4. **First-class collections.** When a collection carries domain behaviour (filtering, summing, formatting), wrap it in a dedicated class rather than passing raw arrays.
5. **One `->` per line** (Law of Demeter). Don't chain through unrelated objects — only talk to your immediate collaborators.
6. **No abbreviations.** `$customer` not `$cust`; `$transaction` not `$tx`; `$repository` not `$repo`.
7. **Small classes.** Aim for under 150 lines. If a class exceeds 200 lines, it has more than one responsibility.
8. **Few instance variables.** More than 3–4 instance variables is a signal to split into collaborating objects.
9. **No anemic domain objects.** Domain models expose behaviour (`$customer->approve()`, `$account->debit($amount)`), not naked getters and setters.



## Single-Purpose Action Classes

Extract discrete business operations into invokable Action classes.

```php
class CreateOrderAction
{
    public function __construct(private InventoryService $inventory) {}

    public function execute(array $data): Order
    {
        $order = Order::create($data);
        $this->inventory->reserve($order);

        return $order;
    }
}
```

## Use Dependency Injection

Always use constructor injection. Avoid `app()` or `resolve()` inside classes.

Incorrect:
```php
class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        $service = app(OrderService::class);

        return $service->create($request->validated());
    }
}
```

Correct:
```php
class OrderController extends Controller
{
    public function __construct(private OrderService $service) {}

    public function store(StoreOrderRequest $request)
    {
        return $this->service->create($request->validated());
    }
}
```

## Code to Interfaces

Depend on contracts at system boundaries (payment gateways, notification channels, external APIs) for testability and swappability.

Incorrect (concrete dependency):
```php
class OrderService
{
    public function __construct(private StripeGateway $gateway) {}
}
```

Correct (interface dependency):
```php
interface PaymentGateway
{
    public function charge(int $amount, string $customerId): PaymentResult;
}

class OrderService
{
    public function __construct(private PaymentGateway $gateway) {}
}
```

Bind in a service provider:

```php
$this->app->bind(PaymentGateway::class, StripeGateway::class);
```

## Default Sort by Descending

When no explicit order is specified, sort by `id` or `created_at` descending. Explicit ordering prevents cross-database inconsistencies between MySQL and Postgres.

Incorrect:
```php
$posts = Post::paginate();
```

Correct:
```php
$posts = Post::latest()->paginate();
```

## Use Atomic Locks for Race Conditions

Prevent race conditions with `Cache::lock()` or `lockForUpdate()`.

```php
Cache::lock('order-processing-'.$order->id, 10)->block(5, function () use ($order) {
    $order->process();
});

// Or at query level
$product = Product::where('id', $id)->lockForUpdate()->first();
```

## Use `mb_*` String Functions

When no Laravel helper exists, prefer `mb_strlen`, `mb_strtolower`, etc. for UTF-8 safety. Standard PHP string functions count bytes, not characters.

Incorrect:
```php
strlen('José');          // 5 (bytes, not characters)
strtolower('MÜNCHEN');  // 'mÜnchen' — fails on multibyte
```

Correct:
```php
mb_strlen('José');             // 4 (characters)
mb_strtolower('MÜNCHEN');     // 'münchen'

// Prefer Laravel's Str helpers when available
Str::length('José');          // 4
Str::lower('MÜNCHEN');        // 'münchen'
```

## Use `defer()` for Post-Response Work

For lightweight tasks that don't need to survive a crash (logging, analytics, cleanup), use `defer()` instead of dispatching a job. The callback runs after the HTTP response is sent — no queue overhead.

Incorrect (job overhead for trivial work):
```php
dispatch(new LogPageView($page));
```

Correct (runs after response, same process):
```php
defer(fn () => PageView::create(['page_id' => $page->id, 'user_id' => auth()->id()]));
```

Use jobs when the work must survive process crashes or needs retry logic. Use `defer()` for fire-and-forget work.

## Use `Context` for Request-Scoped Data

The `Context` facade passes data through the entire request lifecycle — middleware, controllers, jobs, logs — without passing arguments manually.

```php
// In middleware
Context::add('tenant_id', $request->header('X-Tenant-ID'));

// Anywhere later — controllers, jobs, log context
$tenantId = Context::get('tenant_id');
```

Context data automatically propagates to queued jobs and is included in log entries. Use `Context::addHidden()` for sensitive data that should be available in queued jobs but excluded from log context. If data must not leave the current process, do not store it in `Context`.

## Use `Concurrency::run()` for Parallel Execution

Run independent operations in parallel using child processes — no async libraries needed.

```php
use Illuminate\Support\Facades\Concurrency;

[$users, $orders] = Concurrency::run([
    fn () => User::count(),
    fn () => Order::where('status', 'pending')->count(),
]);
```

Each closure runs in a separate process with full Laravel access. Use for independent database queries, API calls, or computations that would otherwise run sequentially.

## Convention Over Configuration

Follow Laravel conventions. Don't override defaults unnecessarily.

Incorrect:
```php
class Customer extends Model
{
    protected $table = 'Customer';
    protected $primaryKey = 'customer_id';

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_customer', 'customer_id', 'role_id');
    }
}
```

Correct:
```php
class Customer extends Model
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
```