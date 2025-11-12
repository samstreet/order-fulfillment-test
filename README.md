# Order Fulfillment Dashboard

A Laravel + Vue.js order management system demonstrating modern PHP development practices, SOLID principles, and Test-Driven Development.

## Technical Stack

- **Backend:** Laravel 12 with PHP 8.3
- **Frontend:** Vue 3 with Vite
- **Database:** SQLite (development), MySQL (via Docker)
- **Containerization:** Docker with Laravel Sail
- **Code Quality:** PHPStan Level 9, PHP_CodeSniffer (PSR-12)
- **API:** RESTful API with Laravel Sanctum authentication

## Prerequisites

- Docker Desktop installed and running
- Git

## Quick Start

### 1. Clone and Setup

```bash
# Clone the repository
git clone <repository-url>
cd order-fulfillment-test

# Start Docker containers
docker compose up -d

# Install dependencies
./vendor/bin/sail composer install
./vendor/bin/sail npm install

# Run migrations
./vendor/bin/sail artisan migrate

# Seed database
./vendor/bin/sail artisan db:seed
```

### 2. Development

```bash
# Start all development services (recommended)
./vendor/bin/sail composer dev

# Or run services individually
./vendor/bin/sail up -d           # Start Docker
./vendor/bin/sail artisan serve   # Laravel server
./vendor/bin/sail npm run dev     # Vite dev server
```

Access the application at: `http://localhost`

### 3. Code Quality Checks

```bash
# Run PHPStan (Level 9)
./vendor/bin/sail composer phpstan

# Run PHPCS (PSR-12 compliance)
./vendor/bin/sail composer phpcs

# Auto-fix code style issues
./vendor/bin/sail composer phpcs:fix

# Run all linters
./vendor/bin/sail composer lint

# Run tests
./vendor/bin/sail composer test

# Run everything (linting + tests)
./vendor/bin/sail composer check
```

## Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/    # Thin controllers
│   │   ├── Requests/       # FormRequest classes for validation
│   │   └── Resources/      # API response transformers
│   ├── Models/             # Eloquent models
│   ├── Services/           # Business logic layer
│   └── Traits/             # Reusable code abstractions
├── database/
│   ├── migrations/         # Database schema
│   ├── seeders/            # Test data
│   └── factories/          # Model factories for testing
├── routes/
│   ├── api.php            # API routes
│   └── web.php            # Web routes
├── tests/
│   ├── Feature/           # Feature tests
│   └── Unit/              # Unit tests
├── resources/
│   ├── js/                # Vue components
│   └── views/             # Blade templates
├── phpstan.neon           # PHPStan configuration (Level 9)
├── phpcs.xml              # PHPCS configuration (PSR-12)
└── docker-compose.yml     # Docker services configuration
```

## Development Standards

### Code Quality
- **SOLID Principles:** All code must follow SOLID design principles
- **TDD Approach:** Write tests before implementation (Red-Green-Refactor)
- **PSR-12:** All PHP code must comply with PSR-12 coding standards
- **PHPStan Level 9:** Maximum static analysis level enforced
- **Strict Types:** All PHP files must declare strict types

### Laravel Conventions
- **FormRequests:** ALL controller methods must use FormRequest classes for validation
- **Service Layer:** Business logic in services, NOT controllers
- **Resources:** Use API Resources for consistent response formatting
- **Type Hints:** Full type coverage for all method parameters and return types

### Code Reusability
- Consider abstracting reusable code into Traits
- Place traits in `app/Traits/` directory
- Each trait should follow Single Responsibility Principle

## Available Agents

This project includes specialized Claude Code agents:

- **@php-pro:** PHP code generation and quality assessment
- **@code-review:** Comprehensive code review and security analysis
- **@docs-architect:** Technical documentation generation

See `.claude/agents/` for detailed agent capabilities.

## Docker Services

The application runs in Docker containers with the following services:

- **Laravel (App):** Port 80
- **MySQL:** Port 3306
- **Redis:** Port 6379
- **Vite:** Port 5173

## Environment Variables

Key environment variables (see `.env`):

```env
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=sqlite

# For Docker/MySQL
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

## Testing

```bash
# Run all tests
./vendor/bin/sail artisan test

# Run specific test
./vendor/bin/sail artisan test --filter=OrderTest

# Run with coverage
./vendor/bin/sail artisan test --coverage
```

## Troubleshooting

### Docker Issues
```bash
# Rebuild containers
./vendor/bin/sail build --no-cache

# Clear Docker volumes
./vendor/bin/sail down -v
docker compose up -d
```

### Permission Issues
```bash
# Fix storage permissions
./vendor/bin/sail artisan storage:link
sudo chmod -R 775 storage bootstrap/cache
```

### Clear Caches
```bash
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan route:clear
./vendor/bin/sail artisan view:clear
```

## Contributing

1. Follow TDD approach: Write tests first
2. Ensure all code passes PHPStan Level 9
3. Maintain PSR-12 compliance
4. Use FormRequests for all validation
5. Keep business logic in service layer
6. Run `composer check` before committing

## Documentation

- See `CLAUDE.md` for Claude Code integration guidelines
- See `docs/PLAN.md` for implementation phases
- API documentation: [Coming soon]

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
