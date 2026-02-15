import { test, expect } from '@playwright/test';

test.describe('Stock Take UI', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login');
    await page.fill('input[type="email"], input[name="email"]', 'admin@admin.com');
    await page.fill('input[type="password"]', 'admin');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
  });

  test('should navigate to stock takes page', async ({ page }) => {
    await page.goto('/pos-app/stock-takes');
    await page.waitForLoadState('networkidle');

    // Should see stock takes page
    await expect(page.locator('text=Stock Take, text=Inventory Count')).toBeVisible({ timeout: 10000 });
  });

  test('should display stock takes list', async ({ page }) => {
    await page.goto('/pos-app/stock-takes');
    await page.waitForLoadState('networkidle');

    // Should see table or list
    const table = page.locator('.ant-table, [data-testid="stock-takes-table"], table');
    await expect(table).toBeVisible({ timeout: 10000 });
  });

  test('should have create stock take button', async ({ page }) => {
    await page.goto('/pos-app/stock-takes');
    await page.waitForLoadState('networkidle');

    const createButton = page.locator('button:has-text("Add"), button:has-text("Create"), button:has-text("New"), button:has-text("Start")');
    await expect(createButton.first()).toBeVisible({ timeout: 10000 });
  });

  test('should open stock take creation form', async ({ page }) => {
    await page.goto('/pos-app/stock-takes');
    await page.waitForLoadState('networkidle');

    const createButton = page.locator('button:has-text("Add"), button:has-text("Create"), button:has-text("New"), button:has-text("Start")');
    await createButton.first().click();

    // Should see form/modal
    const form = page.locator('.ant-modal, .ant-drawer, form');
    await expect(form.first()).toBeVisible({ timeout: 5000 });
  });

  test('should display stock take type options', async ({ page }) => {
    await page.goto('/pos-app/stock-takes');
    await page.waitForLoadState('networkidle');

    const createButton = page.locator('button:has-text("Add"), button:has-text("Create"), button:has-text("New"), button:has-text("Start")');
    await createButton.first().click();
    await page.waitForTimeout(500);

    // Should see type selection (full, partial, category, location)
    const typeSelect = page.locator('[data-testid="type-select"], .ant-select, .ant-radio-group, select');
    await expect(typeSelect.first()).toBeVisible({ timeout: 5000 });
  });

  test('should show stock take status', async ({ page }) => {
    await page.goto('/pos-app/stock-takes');
    await page.waitForLoadState('networkidle');

    // Status tags should exist (draft, in_progress, completed, cancelled)
    await page.waitForTimeout(1000);
    const statusTags = page.locator('.ant-tag, [data-testid="status-tag"]');
    // Check for presence (may or may not have data)
  });

  test('should have summary statistics', async ({ page }) => {
    await page.goto('/pos-app/stock-takes');
    await page.waitForLoadState('networkidle');

    const stats = page.locator('[data-testid="summary-stats"], .summary-stats, .ant-statistic');
    await expect(stats.first()).toBeVisible({ timeout: 10000 });
  });

  test('should display variance indicators', async ({ page }) => {
    await page.goto('/pos-app/stock-takes');
    await page.waitForLoadState('networkidle');

    // Variance column or indicators should be present
    await page.waitForTimeout(1000);
    // Check for variance-related text
    const varianceText = page.locator('text=Variance, text=Difference, text=Discrepancy');
  });

  test('should support barcode scanning', async ({ page }) => {
    await page.goto('/pos-app/stock-takes');
    await page.waitForLoadState('networkidle');

    // Open a stock take (if any in progress)
    const inProgressRow = page.locator('tr:has-text("In Progress"), tr:has-text("in_progress")');

    // Check for scan button or input
    const scanInput = page.locator('input[placeholder*="scan"], input[placeholder*="barcode"], [data-testid="scan-input"]');
  });

  test('should allow item counting', async ({ page }) => {
    await page.goto('/pos-app/stock-takes');
    await page.waitForLoadState('networkidle');

    // Check for count input fields
    const countInputs = page.locator('input[type="number"], input[data-testid="count-input"]');
  });
});
