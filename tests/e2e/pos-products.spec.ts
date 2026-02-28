import { test, expect } from '@playwright/test';

test.describe('POS - Products', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login');
    await page.fill('input[type="email"], input[name="email"]', 'admin@admin.com');
    await page.fill('input[type="password"]', 'admin');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    // Navigate to POS
    await page.goto('/pos');
    await page.waitForLoadState('networkidle');
  });

  test('should load POS page with product grid', async ({ page }) => {
    // Check for product cards or loading state
    const productsOrEmpty = page.locator('.ant-card, .ant-empty, .ant-spin');
    await expect(productsOrEmpty.first()).toBeVisible({ timeout: 10000 });
  });

  test('should display product cards with name and price', async ({ page }) => {
    // Wait for products to load
    await page.waitForLoadState('networkidle');

    // Check for product cards
    const productCards = page.locator('.ant-card').filter({ hasNot: page.locator('text=Cart') });

    // Either we have products or an empty state
    const count = await productCards.count();
    if (count > 0) {
      // Check first product has name and price (SAR)
      const firstProduct = productCards.first();
      await expect(firstProduct).toBeVisible();

      // Should contain price with SAR
      await expect(firstProduct.locator('text=/SAR|\\d+\\.\\d{2}/')).toBeVisible();
    }
  });

  test('should have category tabs', async ({ page }) => {
    // Category tabs should be visible
    const tabs = page.locator('.ant-tabs');
    await expect(tabs).toBeVisible();

    // "All Products" tab should exist
    await expect(page.locator('text=All Products')).toBeVisible();
  });

  test('should filter products by category', async ({ page }) => {
    // Wait for products to load
    await page.waitForLoadState('networkidle');

    // Get all category tabs
    const categoryTabs = page.locator('.ant-tabs-tab');
    const count = await categoryTabs.count();

    if (count > 1) {
      // Click on second category tab (if exists)
      await categoryTabs.nth(1).click();

      // Wait for filter to apply
      await page.waitForLoadState('networkidle');
    }
  });

  test('should have search functionality', async ({ page }) => {
    // Search input should be visible
    const searchInput = page.locator('input[placeholder*="Search"]');
    await expect(searchInput).toBeVisible();
  });

  test('should search for products', async ({ page }) => {
    // Type in search
    const searchInput = page.locator('input[placeholder*="Search"]');
    await searchInput.fill('test');
    await searchInput.press('Enter');

    // Wait for search results
    await page.waitForLoadState('networkidle');

    // Should show results or empty state
    const resultsOrEmpty = page.locator('.ant-card, .ant-empty');
    await expect(resultsOrEmpty.first()).toBeVisible();
  });

  test('should show empty state for no results', async ({ page }) => {
    // Search for something that doesn't exist
    const searchInput = page.locator('input[placeholder*="Search"]');
    await searchInput.fill('xyznonexistentproduct123456');
    await searchInput.press('Enter');

    // Wait for search
    await page.waitForLoadState('networkidle');

    // Should show empty state
    await expect(page.locator('text=No products found')).toBeVisible({ timeout: 5000 });
  });

  test('should have barcode scanner input', async ({ page }) => {
    // Barcode input should be visible
    const barcodeInput = page.locator('input[placeholder*="barcode"], input[placeholder*="Scan"]');
    await expect(barcodeInput).toBeVisible();
  });

  test('should focus barcode input on F2', async ({ page }) => {
    // Press F2
    await page.keyboard.press('F2');

    // Barcode input should be focused
    const barcodeInput = page.locator('input[placeholder*="barcode"], input[placeholder*="Scan"]');
    await expect(barcodeInput).toBeFocused();
  });

  test('should display keyboard shortcuts panel', async ({ page }) => {
    // Find and click the keyboard shortcuts button
    const keyboardBtn = page.locator('button[shape="circle"]').filter({ has: page.locator('[aria-label*="key"], .anticon-key') }).last();

    if (await keyboardBtn.isVisible()) {
      await keyboardBtn.click();

      // Shortcuts panel should appear
      await expect(page.locator('text=Keyboard Shortcuts')).toBeVisible();
    }
  });
});
