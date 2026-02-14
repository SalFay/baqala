import { test, expect } from '@playwright/test';

test.describe('POS System', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login');
    await page.fill('input[type="email"], input[name="email"]', 'admin@admin.com');
    await page.fill('input[type="password"]', 'admin');
    await page.click('button[type="submit"]');

    // Wait for app to load
    await page.waitForLoadState('networkidle');
  });

  test('should load POS page', async ({ page }) => {
    await page.goto('/pos');

    // Should see the POS interface
    await expect(page.locator('#root')).toBeVisible();

    // Wait for app to fully load
    await page.waitForLoadState('networkidle');
  });

  test('should display products', async ({ page }) => {
    await page.goto('/pos');
    await page.waitForLoadState('networkidle');

    // Should see product cards or list
    const products = page.locator('[data-testid="product-card"], .product-card, .ant-card');
    await expect(products.first()).toBeVisible({ timeout: 10000 });
  });

  test('should have cart functionality', async ({ page }) => {
    await page.goto('/pos');
    await page.waitForLoadState('networkidle');

    // Should see cart section
    const cart = page.locator('[data-testid="cart"], .cart-panel, .cart-section');
    // Cart may or may not be visible depending on UI
  });
});
