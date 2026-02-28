import { test, expect } from '@playwright/test';

test.describe('Dashboard API', () => {
  let authCookie: string;

  test.beforeAll(async ({ browser }) => {
    // Login once to get auth cookie
    const context = await browser.newContext();
    const page = await context.newPage();

    await page.goto('/login');
    await page.fill('input[type="email"], input[name="email"]', 'admin@admin.com');
    await page.fill('input[type="password"]', 'admin');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    // Get cookies
    const cookies = await context.cookies();
    authCookie = cookies.map(c => `${c.name}=${c.value}`).join('; ');

    await context.close();
  });

  test('should return dashboard page with stats data', async ({ request }) => {
    // Request dashboard page with auth
    const response = await request.get('/dashboard', {
      headers: {
        'Cookie': authCookie,
        'Accept': 'text/html,application/xhtml+xml',
      },
    });

    expect(response.ok()).toBeTruthy();
    expect(response.status()).toBe(200);

    const html = await response.text();
    // Should contain Inertia page data
    expect(html).toContain('data-page');
  });

  test('should include stats in dashboard response', async ({ page }) => {
    // Login
    await page.goto('/login');
    await page.fill('input[type="email"], input[name="email"]', 'admin@admin.com');
    await page.fill('input[type="password"]', 'admin');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    // Go to dashboard
    await page.goto('/dashboard');

    // Wait for page load
    await page.waitForLoadState('networkidle');

    // Check that stats are rendered
    await expect(page.locator('text=Today\'s Sales')).toBeVisible();
    await expect(page.locator('text=Today\'s Orders')).toBeVisible();
  });

  test('should support date filtering', async ({ page }) => {
    // Login
    await page.goto('/login');
    await page.fill('input[type="email"], input[name="email"]', 'admin@admin.com');
    await page.fill('input[type="password"]', 'admin');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    // Go to dashboard with date params
    await page.goto('/dashboard?start_date=2024-01-01&end_date=2024-01-31');

    // Should load without error
    await page.waitForLoadState('networkidle');
    await expect(page.locator('text=Dashboard')).toBeVisible();
  });

  test('should return sales chart data', async ({ page }) => {
    // Login
    await page.goto('/login');
    await page.fill('input[type="email"], input[name="email"]', 'admin@admin.com');
    await page.fill('input[type="password"]', 'admin');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    // Go to dashboard
    await page.goto('/dashboard');
    await page.waitForLoadState('networkidle');

    // Sales Overview section should exist
    await expect(page.locator('text=Sales Overview')).toBeVisible();
  });

  test('should return top products data', async ({ page }) => {
    // Login
    await page.goto('/login');
    await page.fill('input[type="email"], input[name="email"]', 'admin@admin.com');
    await page.fill('input[type="password"]', 'admin');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    // Go to dashboard
    await page.goto('/dashboard');
    await page.waitForLoadState('networkidle');

    // Top Products section should exist
    await expect(page.locator('text=Top Products')).toBeVisible();
  });

  test('should return recent orders data', async ({ page }) => {
    // Login
    await page.goto('/login');
    await page.fill('input[type="email"], input[name="email"]', 'admin@admin.com');
    await page.fill('input[type="password"]', 'admin');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    // Go to dashboard
    await page.goto('/dashboard');
    await page.waitForLoadState('networkidle');

    // Recent Orders section should exist
    await expect(page.locator('text=Recent Orders')).toBeVisible();
  });

  test('should return low stock data', async ({ page }) => {
    // Login
    await page.goto('/login');
    await page.fill('input[type="email"], input[name="email"]', 'admin@admin.com');
    await page.fill('input[type="password"]', 'admin');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    // Go to dashboard
    await page.goto('/dashboard');
    await page.waitForLoadState('networkidle');

    // Low Stock section should exist
    await expect(page.locator('text=Low Stock')).toBeVisible();
  });
});
