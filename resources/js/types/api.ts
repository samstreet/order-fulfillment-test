// API Response Types

export type OrderStatus = 'pending' | 'processing' | 'fulfilled' | 'cancelled'

export interface OrderStatusInfo {
  value: OrderStatus
  label: string
  color: string
}

export interface MoneyAmount {
  value: number
  formatted: string
}

export interface CountInfo {
  value: number
  formatted: string
}

export interface OrderItem {
  id: number
  order_id: number
  product_name: string
  quantity: number
  unit_price: MoneyAmount
  subtotal: MoneyAmount
  created_at: string | null
  updated_at: string | null
}

export interface Order {
  id: number
  order_number: string
  customer_name: string
  customer_email: string
  status: OrderStatusInfo
  total_amount: MoneyAmount
  items_count: CountInfo
  notes: string | null
  ordered_at: string | null
  fulfilled_at: string | null
  created_at: string | null
  updated_at: string | null
  items?: OrderItem[]
}

// API Request Types
export interface OrderFilters {
  status?: OrderStatus | ''
  search?: string
  page?: number
  per_page?: number
}

export interface UpdateOrderStatusRequest {
  status: OrderStatus
}

// API Response Types
export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    from: number
    last_page: number
    per_page: number
    to: number
    total: number
  }
  links?: {
    first: string
    last: string
    prev: string | null
    next: string | null
  }
}

export interface OrdersResponse extends PaginatedResponse<Order> {}

export interface OrderResponse {
  data: Order
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
}

// Utility Types
export type OrderStatusColor = 'yellow' | 'blue' | 'green' | 'red'

export interface StatusClasses {
  pending: string
  processing: string
  fulfilled: string
  cancelled: string
}

export interface StatusLabels {
  pending: string
  processing: string
  fulfilled: string
  cancelled: string
}