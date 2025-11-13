import { beforeAll } from 'vitest'

// Mock axios for tests
import axios from 'axios'
import MockAdapter from 'axios-mock-adapter'

const mock = new MockAdapter(axios)

// Global test setup
beforeAll(() => {
  // Setup global mocks here if needed
})