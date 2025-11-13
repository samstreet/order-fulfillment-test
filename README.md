# Order Fulfillment System

A modern Laravel + Vue.js order management system demonstrating **PHP 8.4 experimental features**, SOLID principles, Test-Driven Development, and clean architecture patterns.

## ✨ Key Features

- **PHP 8.4 Experimental Features**: Property hooks, new array functions (`array_any`, `array_find`, `array_find_key`)
- **Vue 3 Frontend**: Reactive dashboard with real-time order management
- **RESTful API**: Complete CRUD operations with proper HTTP status codes
- **Advanced Filtering**: Status-based filtering, search across multiple fields, pagination
- **Order Status Management**: Workflow validation with business rules
- **Transaction Safety**: Database transactions with rollback on errors
- **Comprehensive Testing**: 149+ tests covering all functionality
- **Code Quality**: PHPStan Level 9, PSR-12 compliance, strict typing

## Technical Stack

- **Backend:** Laravel 12 with **PHP 8.4** (experimental features)
- **Frontend:** Vue 3 with Composition API and TypeScript support
- **Database:** SQLite (development), MySQL (via Docker)
- **Containerization:** Docker with Laravel Sail
- **Code Quality:** PHPStan Level 9, PHP_CodeSniffer (PSR-12)
- **API:** RESTful API with proper error handling and validation

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
./vendor/bin/sail up -d           # Start Docker containers
./vendor/bin/sail artisan serve   # Laravel API server (port 80)
./vendor/bin/sail npm run dev     # Vite dev server (port 5173)
```

**Access Points:**
- **Frontend Dashboard:** `http://localhost` (Vue.js SPA)
- **API Documentation:** `http://localhost/api/orders` (JSON responses)
- **Vite Dev Server:** `http://localhost:5173` (hot reload)

### 3. Vue.js Frontend Features

The Vue 3 dashboard includes:
- **Real-time Order Management**: View, filter, and update orders
- **Advanced Filtering**: Status filters, search across customer/order data
- **Status Updates**: Dropdown-based status changes with validation
- **Order Deletion**: Safe deletion with confirmation dialogs
- **Pagination**: Efficient loading with customizable page sizes
- **Loading States**: Visual feedback for all async operations
- **Error Handling**: Comprehensive error states and retry mechanisms

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

## 🏗️ Architecture Overview

### Service Layer Pattern
```
Route → Controller → FormRequest (validation) → Service (business logic) → Model → Database
                                                     ↓
Response ← Resource ← Controller ←──────────────────┘
```

### PHP 8.4 Experimental Features
- **Property Hooks**: Automatic validation in DTOs (CreateOrderDTO, CreateOrderItemDTO)
- **Array Functions**: `array_any()` for validation, `array_find()` for status checking
- **Type Safety**: Full type coverage with strict typing and readonly properties

## Project Structure

```
├── app/
│   ├── Contracts/          # Interface definitions
│   ├── DataTransferObjects/# Immutable DTOs with PHP 8.4 property hooks
│   ├── Http/
│   │   ├── Controllers/    # Thin API controllers
│   │   ├── Requests/       # FormRequest validation classes
│   │   └── Resources/      # API response transformers
│   ├── Models/             # Eloquent models with observers
│   ├── Services/           # Business logic layer
│   ├── Repositories/       # Data access layer
│   └── Enums/              # PHP 8.1+ enums (OrderStatus)
├── database/
│   ├── migrations/         # Database schema with indexes
│   ├── seeders/            # Test data generation
│   └── factories/          # Model factories for testing
├── resources/
│   ├── js/
│   │   ├── components/     # Vue 3 components
│   │   │   └── OrderDashboard.vue
│   │   ├── app.js          # Vue app entry point
│   │   └── bootstrap.js    # Axios configuration
│   └── views/
│       └── welcome.blade.php # Vue app mount point
├── tests/
│   ├── Feature/           # API integration tests
│   └── Unit/              # Unit tests for all layers
├── phpstan.neon           # PHPStan Level 9 configuration
├── phpcs.xml              # PSR-12 coding standards
└── docker-compose.yml     # Multi-service Docker setup
```

## 🚀 PHP 8.4 Experimental Features

This project showcases cutting-edge PHP 8.4 features:

### Property Hooks (Experimental)
```php
final class CreateOrderDTO
{
    public string $customerName {
        set(string $value) {
            if (empty(trim($value))) {
                throw new \InvalidArgumentException('Customer name cannot be empty');
            }
            $this->customerName = $value;
        }
    }
    // Automatic validation on property assignment
}
```

### New Array Functions
```php
// Status transition validation
if (!array_any($validTransitions, fn(OrderStatus $valid) => $valid === $to)) {
    throw new InvalidOrderStatusTransitionException($from, $to);
}

// Finding deletion reasons
$reason = array_find($deletionRules, fn($reason, $statusValue) => $statusValue === $order->status->value);
```

### Benefits
- **Reduced Boilerplate**: Property hooks eliminate manual validation methods
- **Type Safety**: Compile-time validation with runtime enforcement
- **Performance**: Native array functions are optimized
- **Future-Proof**: Early adoption of upcoming PHP features

## Development Standards

### Code Quality
- **SOLID Principles:** All code follows SOLID design principles
- **TDD Approach:** Tests written before implementation (Red-Green-Refactor)
- **PSR-12:** Strict PSR-12 coding standards compliance
- **PHPStan Level 9:** Maximum static analysis enforcement
- **Strict Types:** All files declare `strict_types=1`

### Laravel Conventions
- **FormRequests:** ALL controller methods use FormRequest validation
- **Service Layer:** Business logic isolated in service classes
- **API Resources:** Consistent JSON response formatting
- **Type Hints:** 100% type coverage for parameters and returns
- **Repository Pattern:** Data access abstracted for testability

### Vue.js Standards
- **Composition API:** Modern Vue 3 reactive patterns
- **TypeScript Ready:** Structured for TypeScript migration
- **Error Boundaries:** Comprehensive error handling
- **Accessibility:** Semantic HTML and ARIA support

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

## 🧪 Testing & Quality Assurance

### Test Coverage
- **149 Tests Passing** across all layers (Unit, Feature, Integration)
- **1361 Assertions** ensuring comprehensive validation
- **100% API Coverage** with real HTTP requests
- **Database Integration Tests** with transaction rollback

### Running Tests
```bash
# Run all tests
./vendor/bin/sail composer test

# Run specific test suite
./vendor/bin/sail composer test -- --testsuite=Unit
./vendor/bin/sail composer test -- --testsuite=Feature

# Run with coverage report
./vendor/bin/sail composer test -- --coverage --min=80
```

### Test Structure
- **Unit Tests:** DTOs, Services, Repositories, Models
- **Feature Tests:** API endpoints, database operations
- **Integration Tests:** Cross-service interactions

## 📡 API Endpoints

### Orders Management
```
GET    /api/orders              # List orders (with filtering/pagination)
GET    /api/orders/{id}         # Get single order
POST   /api/orders              # Create new order
PATCH  /api/orders/{id}/status  # Update order status
DELETE /api/orders/{id}         # Delete order
```

### Query Parameters
- `status`: Filter by order status (pending|processing|fulfilled|cancelled)
- `search`: Search customer name, email, or order number
- `page`: Page number for pagination
- `per_page`: Items per page (15|25|50|100)

### Response Format
```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 73
  }
}
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

## 📚 Documentation

- **`CLAUDE.md`**: Claude Code integration guidelines and agent configurations
- **`docs/PLAN.md`**: Implementation phases and architectural decisions
- **API Documentation**: Inline code documentation with PHPDoc
- **Vue Components**: Self-documenting with comprehensive comments

### Key Implementation Highlights

1. **PHP 8.4 Property Hooks**: Automatic validation in DTOs
2. **Array Functions**: Modern PHP array operations for cleaner code
3. **Vue 3 Composition API**: Reactive frontend with proper error handling
4. **Transaction Safety**: Database consistency with rollback mechanisms
5. **Status Workflow**: Business rule validation for order state transitions
6. **Comprehensive Testing**: TDD approach with 149+ passing tests

### Architecture Benefits

- **Maintainable**: Clear separation of concerns with service layer
- **Testable**: Dependency injection enables comprehensive testing
- **Scalable**: Repository pattern abstracts data access
- **Type Safe**: Full type coverage prevents runtime errors
- **Future Proof**: PHP 8.4 features ready for production adoption

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
