import { ref, Ref } from 'vue'
import axios from 'axios'
import type {
  Order,
  OrdersResponse,
  OrderFilters,
  UpdateOrderStatusRequest,
  OrderStatus
} from '../types/api'

export function useOrders() {
  const orders: Ref<Order[]> = ref([])
  const loading: Ref<boolean> = ref(false)
  const error: Ref<string | null> = ref(null)
  const updatingOrder: Ref<Record<number, boolean>> = ref({})

  const fetchOrders = async (filters: OrderFilters = {}): Promise<void> => {
    loading.value = true
    error.value = null

    try {
      const params = { ...filters }
      // Remove empty filters
      Object.keys(params).forEach(key => {
        const value = params[key as keyof OrderFilters]
        if (value === '' || value === null || value === undefined) {
          delete params[key as keyof OrderFilters]
        }
      })

      const response = await axios.get<OrdersResponse>('/api/orders', { params })
      orders.value = response.data.data
    } catch (err) {
      const axiosError = err as any
      error.value = axiosError.response?.data?.message || 'Failed to load orders'
      console.error('Error fetching orders:', err)
    } finally {
      loading.value = false
    }
  }

  const updateOrderStatus = async (orderId: number, newStatus: OrderStatus): Promise<void> => {
    updatingOrder.value[orderId] = true

    try {
      const requestData: UpdateOrderStatusRequest = { status: newStatus }
      await axios.patch(`/api/orders/${orderId}/status`, requestData)

      // Update local order status
      const order = orders.value.find(o => o.id === orderId)
      if (order) {
        order.status = {
          value: newStatus,
          label: getStatusLabel(newStatus),
          color: getStatusColor(newStatus)
        }
      }
    } catch (err) {
      const axiosError = err as any
      error.value = axiosError.response?.data?.message || 'Failed to update order status'
      console.error('Error updating order status:', err)
      throw err
    } finally {
      updatingOrder.value[orderId] = false
    }
  }

  const deleteOrder = async (orderId: number): Promise<void> => {
    updatingOrder.value[orderId] = true

    try {
      await axios.delete(`/api/orders/${orderId}`)
      orders.value = orders.value.filter(order => order.id !== orderId)
    } catch (err) {
      const axiosError = err as any
      error.value = axiosError.response?.data?.message || 'Failed to delete order'
      console.error('Error deleting order:', err)
      throw err
    } finally {
      updatingOrder.value[orderId] = false
    }
  }

  const getStatusLabel = (status: OrderStatus): string => {
    const labels: Record<OrderStatus, string> = {
      pending: 'Pending',
      processing: 'Processing',
      fulfilled: 'Fulfilled',
      cancelled: 'Cancelled'
    }
    return labels[status] || status
  }

  const getStatusColor = (status: OrderStatus): string => {
    const colors: Record<OrderStatus, string> = {
      pending: 'yellow',
      processing: 'blue',
      fulfilled: 'green',
      cancelled: 'red'
    }
    return colors[status] || 'gray'
  }

  const canDeleteOrder = (status: OrderStatus): boolean => {
    return ['pending', 'cancelled'].includes(status)
  }

  return {
    orders,
    loading,
    error,
    updatingOrder,
    fetchOrders,
    updateOrderStatus,
    deleteOrder,
    getStatusLabel,
    getStatusColor,
    canDeleteOrder
  }
}