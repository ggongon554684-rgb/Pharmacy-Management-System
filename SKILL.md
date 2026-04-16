name: laravel-php-best-practices
summary: Use this skill when creating or reviewing Laravel/PHP code in this workspace to enforce naming, typing, ORM usage, routing, and security standards.

# Laravel & PHP Best Practices Skill

## What this skill produces

- A reusable workflow for Laravel application changes in this repository.
- Code that uses singular PascalCase model names, `declare(strict_types=1);`, Eloquent ORM by default, and Laravel security features.
- Route definitions that use controller class callables.
- Guidance for choosing raw SQL or query builder only for complex, performance-sensitive queries.

## When to use

- Writing new Laravel models, controllers, migrations, or routes.
- Refactoring existing Laravel code to improve naming, typing, or security.
- Implementing database interactions in this workspace.

## Step-by-step workflow

1. Clarify the feature or requirement in the current Laravel context.
2. Choose model names using singular PascalCase, e.g. `Product`, `PurchaseOrderItem`, `InventoryLocation`.
3. For modern PHP files, include `declare(strict_types=1);` at the top.
4. Implement database interactions using Eloquent ORM first.
5. If the query is highly complex or performance-critical, use Laravel query builder or raw SQL with careful sanitization.
6. Use Laravel request validation and input sanitization; rely on built-in CSRF and XSS protections from forms and Blade.
7. Define routes with controller class callables, e.g. `[InventoryController::class, 'index']`.
8. Review output for adherence to Laravel conventions and security best practices.

## Decision points and branching logic

- Use Eloquent when the query maps cleanly to a model and relationships.
- Use query builder or raw SQL when the query requires multi-join aggregation, advanced windowing, or a performance optimization that Eloquent cannot express cleanly.
- Prefer `FormRequest` validation or `request()->validate()` over manual input parsing.
- Use controllers and route class callables rather than closures in route files.

## Quality criteria

- Model names are singular and PascalCase.
- PHP files use strict typing when applicable.
- Database inputs are sanitized and validated.
- Routes use controller class callables.
- Queries use Eloquent ORM unless complexity or performance demands otherwise.
- Laravel CSRF and XSS protections are applied in forms and views.

## Example prompts

- "Generate a Laravel controller, model, migration, and routes for a prescription refill feature using strict typing and Eloquent."
- "Refactor this raw SQL query into an Eloquent-based query builder query while preserving performance and security."
- "Add authenticated route definitions for the pharmacy inventory module using controller class callables."

## Related customizations to create next

- A skill for Laravel route, middleware, and authorization patterns.
- A skill for form request validation and sanitization in Laravel.
- A skill for database migration and schema design using Laravel conventions.
