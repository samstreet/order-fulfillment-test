# Order Fulfillment Dashboard - Implementation Plan

**Time Budget:** 1-2 hours maximum
**Focus:** Code quality, clean architecture, and demonstrable Laravel/Vue.js skills

## Phase 0: Project Setup (10 minutes)

### Laravel Installation & Configuration
- [ ] Initialize Laravel project (latest stable version with PHP 8.1+)
- [ ] Configure database connection (SQLite for simplicity)
- [ ] Install Laravel Sanctum for API authentication (optional but recommended)
- [ ] Set up CORS configuration for Vue frontend
- [ ] Configure strict type declarations in php.ini/composer.json

### Vue.js Setup
- [ ] Decide on Vue integration approach:
  - Option A: Inertia.js (recommended for Laravel integration)
  - Option B: Standalone Vue 3 with Vite
  - Option C: Laravel Mix with Vue 3
- [ ] Install Vue 3 dependencies
- [ ] Configure build tools (Vite recommended)

**Output:** Clean Laravel + Vue scaffold ready for development

---

## Phase 1: Database Design & Migration (10 minutes)

### Orders Table Schema
```php
// Migration: create_orders_table
- id (bigint, primary key)
- order_number (string, unique, indexed)
- customer_name (string)
- customer_email (string)
- status (enum: pending, processing, fulfilled, cancelled)
- total_amount (decimal 10,2)
- items_count (integer)
- notes (text, nullable)
- ordered_at (timestamp)
- fulfilled_at (timestamp, nullable)
- created_at, updated_at (timestamps)
```

### Optional: Order Items Table (if time permits)
```php
// Migration: create_order_items_table
- id
- order_id (foreign key)
- product_name (string)
- quantity (integer)
- unit_price (decimal)
- subtotal (decimal)
```

### Seeder
- [ ] Create OrderSeeder with 20-30 sample orders
- [ ] Use factories for realistic test data
- [ ] Ensure diverse status distribution

**Output:** Database schema with seeded test data

---

## Phase 2: Laravel Backend - Models & Services (15-20 minutes)

### Order Model (`app/Models/Order.php`)
- [ ] Define fillable/guarded properties
- [ ] Add strict type hints (PHP 8.1+)
- [ ] Implement status enum (PHP 8.1 native enum)
- [ ] Add casts for dates and decimals
- [ ] Define relationships (if order_items table exists)
- [ ] Add query scopes: `scopeByStatus()`, `scopeRecent()`
- [ ] Implement accessor for formatted dates/amounts

```php
enum OrderStatus: string {
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case FULFILLED = 'fulfilled';
    case CANCELLED = 'cancelled';
}
```

### Order Service (`app/Services/OrderService.php`)
**Key principle: Business logic lives here, NOT in controllers**

- [ ] `getAllOrders(array $filters = []): Collection`
  - Filter by status
  - Search by order_number/customer_name
  - Pagination support
- [ ] `getOrderById(int $id): Order`
  - With proper exception handling
- [ ] `createOrder(array $data): Order`
  - Validation happens before this
  - Generate unique order_number
- [ ] `updateOrderStatus(int $id, OrderStatus $status): Order`
  - Add business logic (e.g., set fulfilled_at when status = fulfilled)
- [ ] `deleteOrder(int $id): bool`
  - Optional: soft deletes

**PHP Best Practices:**
- Constructor property promotion (PHP 8.0+)
- Dependency injection for repositories if needed
- Type hints for all parameters and return types
- Use match expressions for status transitions
- Throw custom exceptions for business rule violations

**Output:** Clean service layer with business logic separation

---

## Phase 3: Laravel Backend - API Layer (15-20 minutes)

### Form Requests
- [ ] `StoreOrderRequest` - Validation rules for creating orders
- [ ] `UpdateOrderStatusRequest` - Validation for status updates

### API Controller (`app/Http/Controllers/Api/OrderController.php`)
**Keep controllers thin - delegate to service layer**

```php
public function index(Request $request): JsonResponse
public function show(Order $order): JsonResponse
public function store(StoreOrderRequest $request): JsonResponse
public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
public function destroy(Order $order): JsonResponse
```

- [ ] Use route model binding for `show`, `updateStatus`, `destroy`
- [ ] Inject OrderService via constructor
- [ ] Return API Resources for consistent responses
- [ ] Implement proper HTTP status codes

### API Resource (`app/Http/Resources/OrderResource.php`)
- [ ] Transform model to JSON structure
- [ ] Format dates using Carbon
- [ ] Include computed fields (e.g., status_label, days_pending)

### Routes (`routes/api.php`)
```php
Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/{order}', [OrderController::class, 'show']);
    Route::patch('/{order}/status', [OrderController::class, 'updateStatus']);
    Route::delete('/{order}', [OrderController::class, 'destroy']);
});
```

### Exception Handling
- [ ] Create custom exception: `OrderNotFoundException`
- [ ] Add handler in `app/Exceptions/Handler.php` for API responses

**Output:** RESTful API with proper separation of concerns

---

## Phase 4: Vue.js Frontend Component (20-25 minutes)

### Component Structure (`resources/js/components/OrderDashboard.vue`)

**Template Section:**
- [ ] Orders table with columns:
  - Order Number (clickable to view details)
  - Customer Name
  - Status (with color-coded badges)
  - Total Amount (formatted)
  - Order Date
  - Actions (Update Status, Delete)
- [ ] Filter dropdown for status
- [ ] Search input for order number/customer name
- [ ] Create Order button (optional modal/form)
- [ ] Simple loading states

**Script Section:**
```vue
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

// State
const orders = ref([])
const loading = ref(false)
const filters = ref({ status: '', search: '' })

// API calls
const fetchOrders = async () => { /* ... */ }
const updateOrderStatus = async (orderId, newStatus) => { /* ... */ }
const deleteOrder = async (orderId) => { /* ... */ }

// Computed
const filteredOrders = computed(() => { /* client-side filtering */ })

// Lifecycle
onMounted(() => fetchOrders())
</script>
```

**Style Section:**
- [ ] Use Tailwind CSS (Laravel default) or scoped CSS
- [ ] Status badge colors (green=fulfilled, blue=processing, yellow=pending, red=cancelled)
- [ ] Minimal but clean styling

**Vue.js Best Practices:**
- Use Composition API (script setup)
- Reactive state management with ref/reactive
- Proper error handling with try/catch
- Loading states for async operations
- Emit events for actions (if using child components)

**Output:** Functional Vue component with API integration

---

## Phase 5: Integration & Testing (10 minutes)

### Manual Testing Checklist
- [ ] Test GET /api/orders - returns all orders
- [ ] Test GET /api/orders?status=pending - filters work
- [ ] Test POST /api/orders - validation works
- [ ] Test PATCH /api/orders/{id}/status - status updates correctly
- [ ] Test DELETE /api/orders/{id} - soft/hard delete works
- [ ] Test Vue component loads and displays orders
- [ ] Test status update from UI updates backend
- [ ] Test delete from UI removes order

### Quick Automated Tests (if time permits)
- [ ] Feature test: `OrderApiTest.php`
  - Test index endpoint with filters
  - Test store with validation
  - Test update status
- [ ] Unit test: `OrderServiceTest.php`
  - Test business logic in isolation

**Output:** Verified working application

---

## Phase 6: Documentation & Cleanup (10 minutes)

### README.md
**Required Sections:**

1. **Setup Instructions**
```bash
# Installation
composer install
npm install
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --seed

# Development
php artisan serve
npm run dev
```

2. **Assumptions Made**
- Using SQLite for simplicity
- No authentication (or basic Sanctum)
- Single orders table (or with items table)
- Client-side pagination acceptable for demo

3. **What I'd Improve With More Time**
- Add comprehensive test suite (Feature, Unit, Browser tests)
- Implement pagination on backend
- Add sorting capabilities
- Create order items relationship with separate table
- Add form validation on frontend (Vee-Validate)
- Implement real-time updates (Laravel Echo + Pusher)
- Add export functionality (CSV/PDF)
- Implement advanced search with filters
- Add user authentication and authorization
- Create admin dashboard with analytics
- Add API rate limiting and caching

4. **Challenges Faced**
- Document any blockers or decisions made

### Code Cleanup
- [ ] Remove unused imports
- [ ] Ensure PSR-12 code style compliance
- [ ] Add PHPDoc blocks to service methods
- [ ] Add TypeScript types (if using TS)
- [ ] Remove console.logs and debug code

**Output:** Professional deliverable ready for submission

---

## Technical Decisions & Recommendations

### PHP/Laravel Approach
1. **Use PHP 8.1+ features aggressively:**
   - Native enums for OrderStatus
   - Constructor property promotion in services
   - Match expressions for status transitions
   - Union types and readonly properties where applicable

2. **Service Layer Pattern:**
   - All business logic in `OrderService`
   - Controllers are thin orchestrators
   - Dependency injection for testability

3. **Validation Strategy:**
   - Form Requests for API validation
   - Custom validation rules if needed
   - Return 422 with detailed error messages

4. **Database Approach:**
   - Use SQLite for zero-config simplicity
   - Eloquent ORM with strict typing
   - Seeders with factories for realistic data

### Vue.js Approach
1. **Composition API with `<script setup>`:**
   - Modern, concise syntax
   - Better TypeScript support
   - Improved tree-shaking

2. **State Management:**
   - Local component state (ref/reactive) is sufficient
   - No need for Pinia/Vuex for this scope

3. **API Communication:**
   - Use Axios (Laravel default)
   - Centralize API calls in composable if time permits
   - Handle errors gracefully with user feedback

### Time Management Strategy
- **60 minutes:** Complete Phases 0-3 (Backend complete with API)
- **80 minutes:** Complete Phase 4 (Vue component functional)
- **100 minutes:** Complete Phase 5 (Tested and working)
- **120 minutes:** Complete Phase 6 (Documented and polished)

**If running short on time, prioritize:**
1. Working API with service layer (demonstrates Laravel skills)
2. Basic Vue component that shows orders (demonstrates Vue skills)
3. One working update action (demonstrates full-stack integration)
4. Clear README (demonstrates communication)

---

## Success Criteria

### Must Have (MVP)
- ✅ Orders displayed in Vue component
- ✅ API endpoints functional (GET /orders minimum)
- ✅ Service layer with business logic
- ✅ Status update functionality working
- ✅ Clean, typed PHP code following PSR standards
- ✅ README with setup instructions

### Should Have
- ✅ All CRUD operations working
- ✅ Form validation on API
- ✅ Filters working (status, search)
- ✅ Error handling implemented
- ✅ API Resources for response formatting

### Nice to Have
- ✅ Automated tests
- ✅ Loading states in UI
- ✅ Optimistic UI updates
- ✅ TypeScript in Vue component
- ✅ Order creation form

---

## Post-Submission Considerations

If you finish early, consider these enhancements:
1. Add API endpoint documentation (Postman collection or Swagger)
2. Implement soft deletes with restoration capability
3. Add status transition validation (can't go from fulfilled back to pending)
4. Create a simple dashboard with order statistics
5. Add bulk actions (update multiple order statuses)
6. Implement search highlighting in results
7. Add skeleton loaders while fetching data

Remember: **Quality over quantity. A few features done exceptionally well is better than many features done poorly.**
