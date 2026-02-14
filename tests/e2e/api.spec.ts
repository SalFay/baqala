import { test, expect } from '@playwright/test';

test.describe('API Endpoints', () => {
  let authToken: string;

  test.beforeAll(async ({ request }) => {
    // Get auth token
    const response = await request.post('/api/auth/login', {
      data: {
        email: 'admin@admin.com',
        password: 'admin',
      },
    });

    expect(response.ok()).toBeTruthy();
    const body = await response.json();
    authToken = body.token || body.access_token || body.data?.token;
  });

  test('should return products', async ({ request }) => {
    const response = await request.get('/api/products', {
      headers: {
        Authorization: `Bearer ${authToken}`,
      },
    });

    expect(response.ok()).toBeTruthy();
    const body = await response.json();
    expect(body.data || body).toBeDefined();
  });

  test('should return categories', async ({ request }) => {
    const response = await request.get('/api/categories', {
      headers: {
        Authorization: `Bearer ${authToken}`,
      },
    });

    expect(response.ok()).toBeTruthy();
    const body = await response.json();
    expect(body.data || body).toBeDefined();
  });

  test('should return customers', async ({ request }) => {
    const response = await request.get('/api/customers', {
      headers: {
        Authorization: `Bearer ${authToken}`,
      },
    });

    expect(response.ok()).toBeTruthy();
    const body = await response.json();
    expect(body.data || body).toBeDefined();
  });

  test('should return dropdown options', async ({ request }) => {
    const response = await request.post('/api/dropdown', {
      headers: {
        Authorization: `Bearer ${authToken}`,
      },
      data: {
        type: 'products',
        q: '',
      },
    });

    expect(response.ok()).toBeTruthy();
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
  });

  test('should get current cart', async ({ request }) => {
    const response = await request.get('/api/cart', {
      headers: {
        Authorization: `Bearer ${authToken}`,
      },
    });

    expect(response.ok()).toBeTruthy();
    const body = await response.json();
    expect(body).toBeDefined();
  });

  test('should return dashboard stats', async ({ request }) => {
    const response = await request.get('/api/dashboard/stats', {
      headers: {
        Authorization: `Bearer ${authToken}`,
      },
    });

    expect(response.ok()).toBeTruthy();
    const body = await response.json();
    expect(body).toBeDefined();
  });
});
