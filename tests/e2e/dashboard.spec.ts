import { test, expect } from '@playwright/test';

test.describe('Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login');
    await page.fill('input[type="email"], input[name="email"]', 'admin@admin.com');
    await page.fill('input[type="password"]', 'admin');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    // Navigate to dashboard
    await page.goto('/dashboard');
    await page.waitForLoadState('networkidle');
  });

  test('should display dashboard with all sections', async ({ page }) => {
    // Check page title
    await expect(page).toHaveTitle(/Dashboard/);

    // Check stat cards are visible
    await expect(page.locator('text=Today\'s Sales')).toBeVisible();
    await expect(page.locator('text=Today\'s Orders')).toBeVisible();
    await expect(page.locator('text=Monthly Revenue')).toBeVisible();
    await expect(page.locator('text=Total Customers')).toBeVisible();
  });

  test('should display gradient stat cards with data', async ({ page }) => {
    // All 4 stat cards should be visible
    const statCards = page.locator('.ant-card').filter({ has: page.locator('text=/SAR|transactions|registered/') });
    await expect(statCards.first()).toBeVisible();
  });

  test('should display sales overview chart section', async ({ page }) => {
    // Sales Overview section
    await expect(page.locator('text=Sales Overview')).toBeVisible();

    // Should show chart or empty state
    const chartOrEmpty = page.locator('.ant-card').filter({ has: page.locator('text=Sales Overview') });
    await expect(chartOrEmpty).toBeVisible();
  });

  test('should display top products section', async ({ page }) => {
    // Top Products section
    await expect(page.locator('text=Top Products')).toBeVisible();
  });

  test('should display recent orders table', async ({ page }) => {
    // Recent Orders section
    await expect(page.locator('text=Recent Orders')).toBeVisible();

    // Table headers
    const tableHeaders = page.locator('.ant-table-thead');
    await expect(tableHeaders.first()).toBeVisible();
  });

  test('should display low stock alerts table', async ({ page }) => {
    // Low Stock section
    await expect(page.locator('text=Low Stock Alerts')).toBeVisible();
  });

  test('should display quick actions section', async ({ page }) => {
    // Quick Actions section
    await expect(page.locator('text=Quick Actions')).toBeVisible();

    // Quick action buttons
    await expect(page.locator('button:has-text("New Sale"), a:has-text("New Sale")')).toBeVisible();
    await expect(page.locator('button:has-text("Products"), a:has-text("Products")')).toBeVisible();
    await expect(page.locator('button:has-text("Customers"), a:has-text("Customers")')).toBeVisible();
    await expect(page.locator('button:has-text("Orders"), a:has-text("Orders")')).toBeVisible();
  });

  test('should display secondary metrics', async ({ page }) => {
    // Secondary metrics
    await expect(page.locator('text=Avg. Order Value')).toBeVisible();
    await expect(page.locator('text=Low Stock Items')).toBeVisible();
    await expect(page.locator('text=Out of Stock')).toBeVisible();
  });

  test('should have date range picker', async ({ page }) => {
    // Date range picker should be visible
    const rangePicker = page.locator('.ant-picker-range');
    await expect(rangePicker).toBeVisible();
  });

  test('should filter data when date range changes', async ({ page }) => {
    // Click on date range picker
    const rangePicker = page.locator('.ant-picker-range');
    await rangePicker.click();

    // Wait for picker panel
    await page.waitForSelector('.ant-picker-panel', { state: 'visible' });

    // Select a date (first available date in current month)
    const today = page.locator('.ant-picker-cell-today');
    if (await today.isVisible()) {
      await today.click();
      await today.click(); // Click twice for range
    }
  });

  test('should navigate to POS from quick actions', async ({ page }) => {
    // Click New Sale button
    await page.click('button:has-text("New Sale"), a:has-text("New Sale")');

    // Should navigate to POS
    await page.waitForURL('**/pos**');
    await expect(page).toHaveURL(/pos/);
  });

  test('should navigate to Products from quick actions', async ({ page }) => {
    // Click Products button
    await page.click('button:has-text("Products"), a:has-text("Products")');

    // Should navigate to products
    await page.waitForURL('**/products**');
    await expect(page).toHaveURL(/products/);
  });
});
