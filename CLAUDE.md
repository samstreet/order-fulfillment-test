# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is an order fulfillment system built with Laravel and Vue.js, demonstrating modern PHP practices and clean architecture principles.

## Development Commands

### Initial Setup
```bash
# Start Docker containers
docker compose up -d

# Or use Laravel Sail alias (recommended)
./vendor/bin/sail up -d

# Install PHP dependencies
./vendor/bin/sail composer install

# Install Node dependencies
./vendor/bin/sail npm install

# Run database migrations
./vendor/bin/sail artisan migrate

# Seed database with test data
./vendor/bin/sail artisan db:seed
```

### Development
```bash
# Start development server (Laravel + Vite + Queue + Logs)
./vendor/bin/sail composer dev

# Or individually:
./vendor/bin/sail artisan serve    # Laravel server
./vendor/bin/sail npm run dev      # Vite dev server
./vendor/bin/sail artisan queue:listen  # Queue worker
./vendor/bin/sail artisan pail     # Real-time logs
```

### Code Quality & Testing
```bash
# Run PHPStan (Level 9)
./vendor/bin/sail composer phpstan

# Run PHPCS (PSR-12)
./vendor/bin/sail composer phpcs

# Auto-fix PHPCS issues
./vendor/bin/sail composer phpcs:fix

# Run all linters (PHPStan + PHPCS)
./vendor/bin/sail composer lint

# Run tests
./vendor/bin/sail composer test
./vendor/bin/sail artisan test

# Run everything (lint + test)
./vendor/bin/sail composer check
```

### Database
```bash
# Run migrations
./vendor/bin/sail artisan migrate

# Rollback migrations
./vendor/bin/sail artisan migrate:rollback

# Fresh migration (drops all tables)
./vendor/bin/sail artisan migrate:fresh

# Fresh migration with seeding
./vendor/bin/sail artisan migrate:fresh --seed

# Create new migration
./vendor/bin/sail artisan make:migration create_example_table
```

### Artisan Commands
```bash
# Create controller
./vendor/bin/sail artisan make:controller ExampleController

# Create model with migration
./vendor/bin/sail artisan make:model Example -m

# Create service
./vendor/bin/sail artisan make:service ExampleService

# Create form request
./vendor/bin/sail artisan make:request StoreExampleRequest

# Create resource
./vendor/bin/sail artisan make:resource ExampleResource

# Create factory
./vendor/bin/sail artisan make:factory ExampleFactory

# Create seeder
./vendor/bin/sail artisan make:seeder ExampleSeeder

# Create test
./vendor/bin/sail artisan make:test ExampleTest
```

### Docker Management
```bash
# Start containers
./vendor/bin/sail up -d

# Stop containers
./vendor/bin/sail down

# Restart containers
./vendor/bin/sail restart

# View logs
./vendor/bin/sail logs

# Access container shell
./vendor/bin/sail shell

# Access MySQL CLI
./vendor/bin/sail mysql

# Access Redis CLI
./vendor/bin/sail redis
```

### Alias Setup (Optional but Recommended)
Add to your shell profile (~/.bashrc, ~/.zshrc):
```bash
alias sail='./vendor/bin/sail'
```

Then use `sail` instead of `./vendor/bin/sail` for all commands.

## Code Standards & Principles

### SOLID Principles
Apply SOLID principles at all times:
- **Single Responsibility:** Each class has one reason to change
- **Open/Closed:** Classes open for extension, closed for modification
- **Liskov Substitution:** Subtypes must be substitutable for base types
- **Interface Segregation:** Many specific interfaces over one general interface
- **Dependency Inversion:** Depend on abstractions, not concretions

### Test-Driven Development (TDD)
- Write tests BEFORE implementation code
- Follow Red-Green-Refactor cycle:
  1. Write failing test (Red)
  2. Write minimal code to pass (Green)
  3. Refactor while keeping tests green (Refactor)
- All new features and bug fixes must have corresponding tests
- Test coverage for service layer is mandatory

### Laravel Controller Standards
- ALL controller methods MUST use FormRequest classes for validation
- Never validate directly in controllers using `$request->validate()`
- Controllers should be thin - delegate business logic to service classes
- Example:
  ```php
  public function store(StoreOrderRequest $request): JsonResponse
  {
      $order = $this->orderService->createOrder($request->validated());
      return new OrderResource($order);
  }
  ```

### PHP Standards (PSR Compliance)
- Follow PSR-12 coding style standard at all times
- Use PSR-4 autoloading
- Adhere to PSR-7 HTTP message interfaces where applicable
- All PHP files must declare strict types: `declare(strict_types=1);`
- Use type hints for all method parameters and return types
- Leverage PHP 8.1+ features (enums, readonly properties, constructor promotion)

### Code Reusability & Traits
- Always consider abstracting code into Traits when functionality is likely to be reused across multiple classes
- Traits should be used for horizontal code reuse (shared behavior across unrelated classes)
- Place traits in `app/Traits/` directory with descriptive names
- Examples of good trait candidates:
  - Common query scopes (e.g., `HasUuid`, `HasStatus`)
  - Shared model behaviors (e.g., `Auditable`, `SoftDeletable`)
  - Repeated utility methods (e.g., `FormatsDateTime`, `CalculatesTotals`)
- Each trait should follow Single Responsibility Principle
- Document trait usage and requirements clearly with PHPDoc

## Available Agents

This project has specialized agents configured in `.claude/agents/`:

### @php-pro
**Use for:** Generating PHP code and assessing code quality
- Specializes in modern PHP 8+ features (enums, match expressions, constructor promotion)
- Focuses on performance, memory efficiency, and type safety
- Enforces PSR standards and SOLID principles
- Prefers built-in PHP functions over external dependencies

### @code-review
**Use for:** Comprehensive code quality assurance
- Elite code review with security vulnerability detection
- Performance optimization and production reliability analysis
- Modern AI-powered code analysis techniques
- Configuration and infrastructure review

### @docs-architect
**Use for:** Creating comprehensive technical documentation
- System documentation and architecture guides
- Long-form technical manuals from existing codebases
- Design decision documentation and rationale
- Technical deep-dives and onboarding materials

## Architecture

### Service Layer Pattern
- Business logic resides in service classes (e.g., `app/Services/OrderService.php`)
- Services are injected into controllers via constructor dependency injection
- Services should have interface contracts for testability

### Request/Response Flow
```
Route → Controller → FormRequest (validation) → Service (business logic) → Model → Database
                                                     ↓
Response ← Resource ← Controller ←──────────────────┘
```

To be expanded as the system is built.
