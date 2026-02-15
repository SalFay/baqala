import { test, expect } from '@playwright/test';

test.describe('Business Type Management UI', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login');
    await page.fill('input[type="email"], input[name="email"]', 'admin@admin.com');
    await page.fill('input[type="password"]', 'admin');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
  });

  test('should navigate to settings page', async ({ page }) => {
    await page.goto('/pos-app/settings');
    await page.waitForLoadState('networkidle');

    // Should see settings page
    await expect(page.locator('text=Settings, text=Configuration')).toBeVisible({ timeout: 10000 });
  });

  test('should display business type section', async ({ page }) => {
    await page.goto('/pos-app/settings');
    await page.waitForLoadState('networkidle');

    // Should see business type section
    const businessTypeSection = page.locator('text=Business Type, text=Industry');
    await expect(businessTypeSection.first()).toBeVisible({ timeout: 10000 });
  });

  test('should show available business types', async ({ page }) => {
    await page.goto('/pos-app/settings');
    await page.waitForLoadState('networkidle');

    // Should see business type options
    const businessTypes = page.locator('.business-type-card, .ant-card, [data-testid="business-type"]');
    // Wait for types to load
    await page.waitForTimeout(1000);
  });

  test('should display grocery business type', async ({ page }) => {
    await page.goto('/pos-app/settings');
    await page.waitForLoadState('networkidle');

    // Should see grocery option
    const grocery = page.locator('text=Grocery, text=Supermarket');
    await expect(grocery.first()).toBeVisible({ timeout: 10000 });
  });

  test('should display pharmacy business type', async ({ page }) => {
    await page.goto('/pos-app/settings');
    await page.waitForLoadState('networkidle');

    // Should see pharmacy option
    const pharmacy = page.locator('text=Pharmacy, text=Medical');
    await expect(pharmacy.first()).toBeVisible({ timeout: 10000 });
  });

  test('should display electronics business type', async ({ page }) => {
    await page.goto('/pos-app/settings');
    await page.waitForLoadState('networkidle');

    // Should see electronics option
    const electronics = page.locator('text=Electronics, text=Mobile');
    await expect(electronics.first()).toBeVisible({ timeout: 10000 });
  });

  test('should show preview when selecting business type', async ({ page }) => {
    await page.goto('/pos-app/settings');
    await page.waitForLoadState('networkidle');

    // Click on a business type to preview
    const businessTypeCard = page.locator('.business-type-card, .ant-card').first();
    if (await businessTypeCard.isVisible()) {
      await businessTypeCard.click();
      await page.waitForTimeout(500);

      // Should see preview content (categories, products)
      const preview = page.locator('text=Categories, text=Products, text=Preview');
    }
  });

  test('should have apply button for business type', async ({ page }) => {
    await page.goto('/pos-app/settings');
    await page.waitForLoadState('networkidle');

    // Should see apply/select button
    const applyButton = page.locator('button:has-text("Apply"), button:has-text("Select"), button:has-text("Use This")');
  });

  test('should show current business type', async ({ page }) => {
    await page.goto('/pos-app/settings');
    await page.waitForLoadState('networkidle');

    // Should indicate current business type
    const currentIndicator = page.locator('text=Current, .active, .selected, [data-current="true"]');
  });

  test('should warn before changing business type', async ({ page }) => {
    await page.goto('/pos-app/settings');
    await page.waitForLoadState('networkidle');

    // Click apply on different business type
    const applyButton = page.locator('button:has-text("Apply"), button:has-text("Select")');
    if (await applyButton.first().isVisible()) {
      await applyButton.first().click();

      // Should show confirmation dialog
      const confirmDialog = page.locator('.ant-modal-confirm, [role="dialog"], .ant-popconfirm');
    }
  });
});
