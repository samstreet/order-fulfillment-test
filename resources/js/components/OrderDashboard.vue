<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Order Management</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Manage and track all orders in your system</p>
      </div>

      <!-- Filters and Search -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Status
            </label>
            <select
              v-model="filters.status"
              @change="handleStatusFilterChange"
              class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="processing">Processing</option>
              <option value="fulfilled">Fulfilled</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <!-- Search -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Search
            </label>
            <input
              v-model="filters.search"
              @input="debounceSearch"
              type="text"
              placeholder="Search by order number, customer name, or email..."
              class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Per Page -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Per Page
            </label>
            <select
              v-model="filters.per_page"
              @change="handlePerPageChange"
              class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="15">15</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Orders Table -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <!-- Loading State -->
        <div v-if="loading" class="p-8 text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
          <p class="mt-4 text-gray-600 dark:text-gray-400">Loading orders...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="p-8 text-center">
          <div class="text-red-500 mb-4">
            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <p class="text-gray-900 dark:text-white font-medium mb-2">Failed to load orders</p>
          <p class="text-gray-600 dark:text-gray-400 mb-4">{{ error }}</p>
           <button
             @click="() => typedFetchOrders()"
             class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition-colors"
           >
             Try Again
           </button>
        </div>

        <!-- Table -->
        <div v-else>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Order Number
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Customer
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Total
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Ordered At
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-6 py-4 whitespace-nowrap">
                     <div class="text-sm font-medium text-gray-900 dark:text-white">{{ order.order_number }}</div>
                     <div class="text-sm text-gray-500 dark:text-gray-400">{{ order.items_count.formatted }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ order.customer_name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ order.customer_email }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                     <span
                       :class="getStatusClasses(order.status.value)"
                       class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                     >
                       {{ order.status.label }}
                     </span>
                  </td>
                   <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                     {{ order.total_amount.formatted }}
                   </td>
                   <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                     {{ order.ordered_at ? formatDate(order.ordered_at) : 'N/A' }}
                   </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                      <!-- Status Update Dropdown -->
                       <select
                         v-if="!updatingOrder[order.id]"
                         :value="order.status.value"
                         @change="(event) => handleUpdateOrderStatus(order.id, (event.target as HTMLSelectElement).value)"
                         :disabled="updatingOrder[order.id]"
                         class="text-xs border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                       >
                         <option value="pending" :disabled="order.status.value !== 'pending'">Pending</option>
                         <option value="processing" :disabled="!['pending', 'processing'].includes(order.status.value)">Processing</option>
                         <option value="fulfilled" :disabled="!['processing', 'fulfilled'].includes(order.status.value)">Fulfilled</option>
                         <option value="cancelled" :disabled="!['pending', 'processing', 'cancelled'].includes(order.status.value)">Cancelled</option>
                       </select>

                      <!-- Loading Spinner for Status Update -->
                      <div v-else class="flex items-center">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500"></div>
                        <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">Updating...</span>
                      </div>

                      <!-- Delete Button -->
                       <button
                         @click="handleDeleteOrder(order.id)"
                         :disabled="updatingOrder[order.id] || !canDeleteOrder(order.status.value)"
                         class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 disabled:opacity-50 disabled:cursor-not-allowed"
                       >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 011-1V4a1 1 0 011 1z"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="pagination && pagination.last_page > 1" class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700 dark:text-gray-300">
                Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
              </div>
              <div class="flex space-x-1">
                <button
                  v-for="page in visiblePages"
                  :key="page"
                  @click="goToPage(page)"
                  :class="[
                    'px-3 py-1 text-sm border rounded',
                    page === pagination.current_page
                      ? 'bg-blue-500 text-white border-blue-500'
                      : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'
                  ]"
                >
                  {{ page }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useOrders } from '../composables/useOrders'
import type { OrderFilters, OrderStatus } from '../types/api'

// Use the orders composable
const {
  orders,
  loading,
  error,
  updatingOrder,
  updateOrderStatus,
  deleteOrder,
  canDeleteOrder
} = useOrders()

// Filters
const filters = ref<OrderFilters>({
  status: '',
  search: '',
  page: 1,
  per_page: 15
})

// Pagination data from API response
const pagination = ref<any>(null)

// Computed properties
const visiblePages = computed(() => {
  if (!pagination.value) return []

  const current = pagination.value.current_page
  const last = pagination.value.last_page
  const pages: (number | string)[] = []

  // Always show first page
  if (current > 3) pages.push(1, '...')

  // Show current page and surrounding pages
  for (let i = Math.max(1, current - 1); i <= Math.min(last, current + 1); i++) {
    pages.push(i)
  }

  // Always show last page
  if (current < last - 2) pages.push('...', last)

  return pages.filter((page, index, arr) => arr.indexOf(page) === index)
})

// Methods
const handleStatusFilterChange = () => {
  filters.value.page = 1 // Reset to first page when filtering
  typedFetchOrders(filters.value)
}

const handlePerPageChange = () => {
  filters.value.page = 1 // Reset to first page when changing per page
  typedFetchOrders(filters.value)
}

const handleUpdateOrderStatus = async (orderId: number, newStatus: string) => {
  try {
    await updateOrderStatus(orderId, newStatus as OrderStatus)
  } catch (err) {
    // Error is already handled in the composable
  }
}

const handleDeleteOrder = async (orderId: number) => {
  if (!confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
    return
  }

  try {
    await deleteOrder(orderId)
  } catch (err) {
    // Error is already handled in the composable
  }
}

const debounceSearch = (() => {
  let timeout: NodeJS.Timeout
  return () => {
    clearTimeout(timeout)
    timeout = setTimeout(() => {
      filters.value.page = 1 // Reset to first page when searching
      typedFetchOrders(filters.value)
    }, 300)
  }
})()

const goToPage = (page: number | string) => {
  if (page === '...') return
  filters.value.page = page as number
  typedFetchOrders(filters.value)
}

const getStatusClasses = (status: OrderStatus): string => {
  const classes: Record<OrderStatus, string> = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    fulfilled: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
  }
  return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
}

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Override fetchOrders to handle pagination
const typedFetchOrders = async (filtersOverride?: OrderFilters) => {
  const params = filtersOverride || filters.value

  try {
    // We need to make the axios call directly to get the full response with meta
    const axios = (await import('axios')).default
    const response = await axios.get('/api/orders', { params })
    orders.value = response.data.data || response.data
    pagination.value = response.data.meta || null
  } catch (err) {
    // Error handling is done in the composable
  }
}

// Lifecycle
onMounted(() => {
  typedFetchOrders()
})
</script>