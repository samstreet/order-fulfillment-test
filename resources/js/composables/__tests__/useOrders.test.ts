import { describe, it, expect, beforeEach, vi } from 'vitest'
import axios from 'axios'
import MockAdapter from 'axios-mock-adapter'
import { useOrders } from '../useOrders'
import type { Order, OrderStatus } from '../../types/api'

// Mock axios
const mock = new MockAdapter(axios)

// Mock data
const mockOrder: Order = {
  id: 1,
  order_number: 'ORD-001',
  customer_name: 'John Doe',
  customer_email: 'john@example.com',
  status: {
    value: 'pending' as OrderStatus,
    label: 'Pending',
    color: 'yellow'
  },
  total_amount: {
    value: 150.00,
    formatted: '$150.00'
  },
  items_count: {
    value: 2,
    formatted: '2 items'
  },
  notes: null,
  ordered_at: '2024-01-01T10:00:00.000000Z',
  fulfilled_at: null,
  created_at: '2024-01-01T10:00:00.000000Z',
  updated_at: '2024-01-01T10:00:00.000000Z',
}

describe('useOrders', () => {
  let ordersComposable: ReturnType<typeof useOrders>

  beforeEach(() => {
    mock.reset()
    ordersComposable = useOrders()
  })

  describe('fetchOrders', () => {
    it('fetches orders successfully', async () => {
      const mockResponse = {
        data: [mockOrder],
        meta: {
          current_page: 1,
          per_page: 15,
          total: 1,
          last_page: 1,
        }
      }

      mock.onGet('/api/orders').reply(200, mockResponse)

      await ordersComposable.fetchOrders()

      expect(ordersComposable.orders.value).toHaveLength(1)
      expect(ordersComposable.orders.value[0].id).toBe(1)
      expect(ordersComposable.orders.value[0].order_number).toBe('ORD-001')
      expect(ordersComposable.loading.value).toBe(false)
      expect(ordersComposable.error.value).toBe(null)
    })

    it('handles API errors', async () => {
      mock.onGet('/api/orders').reply(500, {
        message: 'Internal server error'
      })

      await ordersComposable.fetchOrders()

      expect(ordersComposable.orders.value).toEqual([])
      expect(ordersComposable.loading.value).toBe(false)
      expect(ordersComposable.error.value).toBe('Internal server error')
    })

    it('applies filters correctly', async () => {
      const filters = {
        status: 'pending' as OrderStatus,
        search: 'John',
        page: 1,
        per_page: 10
      }

      mock.onGet('/api/orders', {
        params: {
          status: 'pending',
          search: 'John',
          page: 1,
          per_page: 10
        }
      }).reply(200, {
        data: [mockOrder],
        meta: {
          current_page: 1,
          per_page: 10,
          total: 1,
          last_page: 1,
        }
      })

      await ordersComposable.fetchOrders(filters)

      expect(ordersComposable.orders.value).toHaveLength(1)
    })

    it('removes empty filters', async () => {
      const filters = {
        status: '' as OrderStatus,
        search: '',
        page: 1,
        per_page: 15
      }

      mock.onGet('/api/orders', {
        params: {
          page: 1,
          per_page: 15
        }
      }).reply(200, {
        data: [mockOrder],
        meta: {
          current_page: 1,
          per_page: 15,
          total: 1,
          last_page: 1,
        }
      })

      await ordersComposable.fetchOrders(filters)

      expect(ordersComposable.orders.value).toHaveLength(1)
    })
  })

  describe('updateOrderStatus', () => {
    it('updates order status successfully', async () => {
      // Set up initial order
      ordersComposable.orders.value = [{ ...mockOrder }]

      const updatedOrder = {
        ...mockOrder,
        status: {
          value: 'processing' as OrderStatus,
          label: 'Processing',
          color: 'blue'
        }
      }

      mock.onPatch('/api/orders/1/status').reply(200, {
        data: updatedOrder
      })

      await ordersComposable.updateOrderStatus(1, 'processing')

      expect(ordersComposable.orders.value[0].status.value).toBe('processing')
      expect(ordersComposable.orders.value[0].status.label).toBe('Processing')
      expect(ordersComposable.updatingOrder.value[1]).toBe(false)
    })

    it('handles update errors', async () => {
      ordersComposable.orders.value = [{ ...mockOrder }]

      mock.onPatch('/api/orders/1/status').reply(422, {
        message: 'Invalid status transition'
      })

      await expect(ordersComposable.updateOrderStatus(1, 'fulfilled')).rejects.toThrow()

      expect(ordersComposable.error.value).toBe('Invalid status transition')
      expect(ordersComposable.updatingOrder.value[1]).toBe(false)
    })

    it('sets updating state correctly', async () => {
      ordersComposable.orders.value = [{ ...mockOrder }]

      mock.onPatch('/api/orders/1/status').reply(() =>
        new Promise(resolve => setTimeout(() => resolve([200, { data: mockOrder }]), 100))
      )

      const updatePromise = ordersComposable.updateOrderStatus(1, 'processing')

      expect(ordersComposable.updatingOrder.value[1]).toBe(true)

      await updatePromise

      expect(ordersComposable.updatingOrder.value[1]).toBe(false)
    })
  })

  describe('deleteOrder', () => {
    it('deletes order successfully', async () => {
      ordersComposable.orders.value = [{ ...mockOrder }, {
        ...mockOrder,
        id: 2,
        order_number: 'ORD-002'
      }]

      mock.onDelete('/api/orders/1').reply(200, {
        message: 'Order deleted successfully'
      })

      await ordersComposable.deleteOrder(1)

      expect(ordersComposable.orders.value).toHaveLength(1)
      expect(ordersComposable.orders.value[0].id).toBe(2)
      expect(ordersComposable.updatingOrder.value[1]).toBe(false)
    })

    it('handles delete errors', async () => {
      ordersComposable.orders.value = [{ ...mockOrder }]

      mock.onDelete('/api/orders/1').reply(422, {
        message: 'Cannot delete processing order'
      })

      await expect(ordersComposable.deleteOrder(1)).rejects.toThrow()

      expect(ordersComposable.error.value).toBe('Cannot delete processing order')
      expect(ordersComposable.orders.value).toHaveLength(1) // Order should still be there
      expect(ordersComposable.updatingOrder.value[1]).toBe(false)
    })
  })

  describe('utility functions', () => {
    it('returns correct status label', () => {
      expect(ordersComposable.getStatusLabel('pending')).toBe('Pending')
      expect(ordersComposable.getStatusLabel('processing')).toBe('Processing')
      expect(ordersComposable.getStatusLabel('fulfilled')).toBe('Fulfilled')
      expect(ordersComposable.getStatusLabel('cancelled')).toBe('Cancelled')
    })

    it('returns correct status color', () => {
      expect(ordersComposable.getStatusColor('pending')).toBe('yellow')
      expect(ordersComposable.getStatusColor('processing')).toBe('blue')
      expect(ordersComposable.getStatusColor('fulfilled')).toBe('green')
      expect(ordersComposable.getStatusColor('cancelled')).toBe('red')
    })

    it('determines deletable orders correctly', () => {
      expect(ordersComposable.canDeleteOrder('pending')).toBe(true)
      expect(ordersComposable.canDeleteOrder('cancelled')).toBe(true)
      expect(ordersComposable.canDeleteOrder('processing')).toBe(false)
      expect(ordersComposable.canDeleteOrder('fulfilled')).toBe(false)
    })
  })

  describe('initial state', () => {
    it('initializes with correct default values', () => {
      expect(ordersComposable.orders.value).toEqual([])
      expect(ordersComposable.loading.value).toBe(false)
      expect(ordersComposable.error.value).toBe(null)
      expect(ordersComposable.updatingOrder.value).toEqual({})
    })
  })
})