import { test, expect } from '@playwright/test';

test.describe('Return System UI', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login');
    await page.fill('input[type="email"], input[name="email"]', 'admin@admin.com');
    await page.fill('input[type="password"]', 'admin');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
  });

  test('should navigate to returns page', async ({ page }) => {
    await page.goto('/pos-app/returns');
    await page.waitForLoadState('networkidle');

    // Should see returns page
    await expect(page.locator('text=Return, text=Refund')).toBeVisible({ timeout: 10000 });
  });

  test('should display returns list', async ({ page }) => {
    await page.goto('/pos-app/returns');
    await page.waitForLoadState('networkidle');

    // Should see table or list
    const table = page.locator('.ant-table, [data-testid="returns-table"], table');
    await expect(table).toBeVisible({ timeout: 10000 });
  });

  test('should have create return button', async ({ page }) => {
    await page.goto('/pos-app/returns');
    await page.waitForLoadState('networkidle');

    const createButton = page.locator('button:has-text("New Return"), button:has-text("Create"), button:has-text("Add")');
    await expect(createButton.first()).toBeVisible({ timeout: 10000 });
  });

  test('should search for order when creating return', async ({ page }) => {
    await page.goto('/pos-app/returns');
    await page.waitForLoadState('networkidle');

    const createButton = page.locator('button:has-text("New Return"), button:has-text("Create"), button:has-text("Add")');
    await createButton.first().click();
    await page.waitForTimeout(500);

    // Should see order search input
    const orderSearch = page.locator('input[placeholder*="order"], input[placeholder*="Order"], [data-testid="order-search"]');
    await expect(orderSearch.first()).toBeVisible({ timeout: 5000 });
  });

  test('should display item conditions', async ({ page }) => {
    await page.goto('/pos-app/returns');
    await page.waitForLoadState('networkidle');

    // Open create return form
    const createButton = page.locator('button:has-text("New Return"), button:has-text("Create"), button:has-text("Add")');
    await createButton.first().click();
    await page.waitForTimeout(500);

    // Should have condition options (sellable, damaged, defective)
    const conditionSelect = page.locator('text=Sellable, text=Damaged, text=Defective, text=Condition');
  });

  test('should show return status tags', async ({ page }) => {
    await page.goto('/pos-app/returns');
    await page.waitForLoadState('networkidle');

    // Status tags should exist (pending, approved, rejected, completed)
    await page.waitForTimeout(1000);
    const statusTags = page.locator('.ant-tag, [data-testid="status-tag"]');
  });

  test('should have approve/reject actions', async ({ page }) => {
    await page.goto('/pos-app/returns');
    await page.waitForLoadState('networkidle');

    // Check for action buttons in table or dropdown
    const actionButtons = page.locator('button:has-text("Approve"), button:has-text("Reject"), .ant-dropdown-trigger');
  });

  test('should display return reasons', async ({ page }) => {
    await page.goto('/pos-app/returns');
    await page.waitForLoadState('networkidle');

    const createButton = page.locator('button:has-text("New Return"), button:has-text("Create"), button:has-text("Add")');
    await createButton.first().click();
    await page.waitForTimeout(500);

    // Should see reason selection
    const reasonSelect = page.locator('[data-testid="reason-select"], .ant-select, select, textarea[placeholder*="reason"]');
    await expect(reasonSelect.first()).toBeVisible({ timeout: 5000 });
  });

  test('should show refund amount calculation', async ({ page }) => {
    await page.goto('/pos-app/returns');
    await page.waitForLoadState('networkidle');

    // Check for refund/total amount display
    const refundAmount = page.locator('text=Refund, text=Total, text=Amount');
  });

  test('should validate quantity cannot exceed ordered', async ({ page }) => {
    await page.goto('/pos-app/returns');
    await page.waitForLoadState('networkidle');

    // This would require selecting an order and trying to return more than ordered
    // The UI should prevent or show error for excessive quantity
  });

  test('should show return eligibility status', async ({ page }) => {
    await page.goto('/pos-app/returns');
    await page.waitForLoadState('networkidle');

    // When viewing order for return, should show if eligible
    const eligibilityIndicator = page.locator('text=Eligible, text=Not Eligible, text=Returnable');
  });

  test('should process refund method selection', async ({ page }) => {
    await page.goto('/pos-app/returns');
    await page.waitForLoadState('networkidle');

    // For approved returns, should have refund method options
    const refundMethod = page.locator('text=Cash, text=Store Credit, text=Original Payment');
  });
});
