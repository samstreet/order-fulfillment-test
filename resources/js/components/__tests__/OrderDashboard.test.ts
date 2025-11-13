import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import axios from 'axios'
import MockAdapter from 'axios-mock-adapter'
import OrderDashboard from '../OrderDashboard.vue'
import type { Order, OrderStatus } from '../../types/api'

// Mock axios
const mock = new MockAdapter(axios)

// Mock data
const mockOrders: Order[] = [
  {
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
  },
  {
    id: 2,
    order_number: 'ORD-002',
    customer_name: 'Jane Smith',
    customer_email: 'jane@example.com',
    status: {
      value: 'fulfilled' as OrderStatus,
      label: 'Fulfilled',
      color: 'green'
    },
    total_amount: {
      value: 75.50,
      formatted: '$75.50'
    },
    items_count: {
      value: 1,
      formatted: '1 item'
    },
    notes: 'Express delivery',
    ordered_at: '2024-01-02T14:30:00.000000Z',
    fulfilled_at: '2024-01-03T09:15:00.000000Z',
    created_at: '2024-01-02T14:30:00.000000Z',
    updated_at: '2024-01-03T09:15:00.000000Z',
  }
]

describe('OrderDashboard', () => {
  let wrapper: any

  beforeEach(async () => {
    mock.reset()

    // Mock the initial API call
    mock.onGet('/api/orders').reply(200, {
      data: mockOrders,
      meta: {
        current_page: 1,
        per_page: 15,
        total: 2,
        last_page: 1,
      }
    })

    wrapper = mount(OrderDashboard, {
      global: {
        stubs: {
          // Stub any components that might not be available in test environment
        }
      }
    })

    // Wait for the component to mount and complete the initial API call
    await wrapper.vm.$nextTick()
    // Wait for the async typedFetchOrders call to complete
    await new Promise(resolve => setTimeout(resolve, 0))
  })

  it('renders the dashboard correctly', () => {
    expect(wrapper.text()).toContain('Order Management')
    expect(wrapper.text()).toContain('Manage and track all orders in your system')
  })



  it('displays orders after loading', async () => {
    // Wait for data to be loaded
    await new Promise(resolve => setTimeout(resolve, 50))
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('ORD-001')
    expect(wrapper.text()).toContain('John Doe')
    expect(wrapper.text()).toContain('ORD-002')
    expect(wrapper.text()).toContain('Jane Smith')
  })

  it('displays order status correctly', async () => {
    // Wait for data to be loaded
    await new Promise(resolve => setTimeout(resolve, 50))
    await wrapper.vm.$nextTick()

    const statusElements = wrapper.findAll('.inline-flex')
    expect(statusElements.length).toBeGreaterThan(0)

    // Check that status labels are displayed
    expect(wrapper.text()).toContain('Pending')
    expect(wrapper.text()).toContain('Fulfilled')
  })

  it('displays formatted prices correctly', async () => {
    // Wait for data to be loaded
    await new Promise(resolve => setTimeout(resolve, 50))
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('$150.00')
    expect(wrapper.text()).toContain('$75.50')
  })

  it('displays item counts correctly', async () => {
    // Wait for data to be loaded
    await new Promise(resolve => setTimeout(resolve, 50))
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('2 items')
    expect(wrapper.text()).toContain('1 item')
  })

  it('filters orders by status', async () => {
    // Wait for initial data to load
    await new Promise(resolve => setTimeout(resolve, 50))
    await wrapper.vm.$nextTick()

    // Mock filtered response - need to handle the query params properly
    mock.onGet('/api/orders').reply((config) => {
      const params = new URLSearchParams(config.params)
      if (params.get('status') === 'pending') {
        return [200, {
          data: [mockOrders[0]],
          meta: {
            current_page: 1,
            per_page: 15,
            total: 1,
            last_page: 1,
          }
        }]
      }
      return [200, {
        data: mockOrders,
        meta: {
          current_page: 1,
          per_page: 15,
          total: 2,
          last_page: 1,
        }
      }]
    })

    // Find status filter select (first select in the filters section)
    const selects = wrapper.findAll('select')
    const statusSelect = selects[0] // Status filter is the first select
    expect(statusSelect.exists()).toBe(true)

    await statusSelect.setValue('pending')

    // Wait for API call and component update
    await new Promise(resolve => setTimeout(resolve, 100))
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('ORD-001')
    expect(wrapper.text()).not.toContain('ORD-002')
  })

  it('searches orders by text', async () => {
    // Wait for initial data to load
    await new Promise(resolve => setTimeout(resolve, 50))
    await wrapper.vm.$nextTick()

    // Mock search response - handle query params dynamically
    mock.onGet('/api/orders').reply((config) => {
      const params = new URLSearchParams(config.params)
      if (params.get('search') === 'John') {
        return [200, {
          data: [mockOrders[0]],
          meta: {
            current_page: 1,
            per_page: 15,
            total: 1,
            last_page: 1,
          }
        }]
      }
      return [200, {
        data: mockOrders,
        meta: {
          current_page: 1,
          per_page: 15,
          total: 2,
          last_page: 1,
        }
      }]
    })

    // Find search input
    const searchInput = wrapper.find('input[type="text"]')
    expect(searchInput.exists()).toBe(true)

    await searchInput.setValue('John')

    // Wait for debounced search (300ms delay in component)
    await new Promise(resolve => setTimeout(resolve, 350))
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('ORD-001')
    expect(wrapper.text()).not.toContain('ORD-002')
  })

  it('updates order status successfully', async () => {
    // Wait for initial data to load
    await new Promise(resolve => setTimeout(resolve, 50))
    await wrapper.vm.$nextTick()

    // Mock status update
    mock.onPatch('/api/orders/1/status').reply(200, {
      data: {
        ...mockOrders[0],
        status: {
          value: 'processing' as OrderStatus,
          label: 'Processing',
          color: 'blue'
        }
      }
    })

    // Find all status selects (skip the filter selects, get the ones in the table)
    const allSelects = wrapper.findAll('select')
    // The status selects in the table rows come after the filter selects (2 filter selects)
    const tableStatusSelects = allSelects.slice(2)

    expect(tableStatusSelects.length).toBeGreaterThan(0)

    // Find the select for the first order (should be pending)
    const firstOrderSelect = tableStatusSelects[0]
    expect(firstOrderSelect.element.value).toBe('pending')

    await firstOrderSelect.setValue('processing')

    // Wait for API call and component update
    await new Promise(resolve => setTimeout(resolve, 100))
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Processing')
  })

  it('handles API errors gracefully', async () => {
    mock.reset()
    mock.onGet('/api/orders').reply(500, {
      message: 'Internal server error'
    })

    const errorWrapper = mount(OrderDashboard)
    await errorWrapper.vm.$nextTick()
    // Wait for the error to be processed
    await new Promise(resolve => setTimeout(resolve, 50))

    expect(errorWrapper.text()).toContain('Failed to load orders')
    expect(errorWrapper.text()).toContain('Internal server error')
  })

  it('shows pagination when there are multiple pages', async () => {
    mock.reset()
    mock.onGet('/api/orders').reply(200, {
      data: mockOrders,
      meta: {
        current_page: 1,
        per_page: 10,
        total: 25,
        last_page: 3,
        from: 1,
        to: 2
      }
    })

    const paginationWrapper = mount(OrderDashboard)
    await paginationWrapper.vm.$nextTick()
    // Wait for data to load
    await new Promise(resolve => setTimeout(resolve, 50))

    expect(paginationWrapper.text()).toContain('Showing 1 to 2 of 25 results')
  })

  it('formats dates correctly', async () => {
    // Wait for data to load
    await new Promise(resolve => setTimeout(resolve, 50))
    await wrapper.vm.$nextTick()

    // The component formats dates as "Jan 1, 2024, 10:00 AM"
    expect(wrapper.text()).toContain('Jan 1, 2024')
    expect(wrapper.text()).toContain('Jan 2, 2024')
  })
})