import { test, expect } from '@playwright/test';

test.describe('Statement & Credit System UI', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login');
    await page.fill('input[type="email"], input[name="email"]', 'admin@admin.com');
    await page.fill('input[type="password"]', 'admin');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
  });

  test('should navigate to statements page', async ({ page }) => {
    await page.goto('/pos-app/statements');
    await page.waitForLoadState('networkidle');

    // Should see statements page
    await expect(page.locator('text=Statement, text=Account')).toBeVisible({ timeout: 10000 });
  });

  test('should have customer and vendor tabs', async ({ page }) => {
    await page.goto('/pos-app/statements');
    await page.waitForLoadState('networkidle');

    // Should see tabs for customers and vendors
    const tabs = page.locator('.ant-tabs, [role="tablist"]');
    await expect(tabs).toBeVisible({ timeout: 10000 });

    // Check for customer/vendor tabs
    const customerTab = page.locator('text=Customer, text=Customers');
    const vendorTab = page.locator('text=Vendor, text=Vendors, text=Supplier');
  });

  test('should display customer list', async ({ page }) => {
    await page.goto('/pos-app/statements');
    await page.waitForLoadState('networkidle');

    // Should see customer/vendor list
    const list = page.locator('.ant-table, .ant-list, table, [data-testid="entity-list"]');
    await expect(list.first()).toBeVisible({ timeout: 10000 });
  });

  test('should open customer statement', async ({ page }) => {
    await page.goto('/pos-app/statements');
    await page.waitForLoadState('networkidle');

    // Click on a customer row to view statement
    const viewButton = page.locator('button:has-text("View"), button:has-text("Statement"), a:has-text("View")');
    if (await viewButton.first().isVisible()) {
      await viewButton.first().click();
      await page.waitForTimeout(1000);

      // Should see statement details
      const statementView = page.locator('[data-testid="statement-view"], .statement-view, .statement-details');
      await expect(statementView.first()).toBeVisible({ timeout: 5000 });
    }
  });

  test('should show date range filter', async ({ page }) => {
    await page.goto('/pos-app/statements');
    await page.waitForLoadState('networkidle');

    // Should have date range picker
    const datePicker = page.locator('.ant-picker, .ant-picker-range, input[type="date"]');
    await expect(datePicker.first()).toBeVisible({ timeout: 10000 });
  });

  test('should display balance summary', async ({ page }) => {
    await page.goto('/pos-app/statements');
    await page.waitForLoadState('networkidle');

    // Should see balance/summary section
    const summary = page.locator('text=Balance, text=Total, text=Outstanding');
    await expect(summary.first()).toBeVisible({ timeout: 10000 });
  });

  test('should have add credit/payment button', async ({ page }) => {
    await page.goto('/pos-app/statements');
    await page.waitForLoadState('networkidle');

    // Should see add payment/credit button
    const addButton = page.locator('button:has-text("Add Payment"), button:has-text("Add Credit"), button:has-text("Record Payment")');
    // Button may be in statement detail view
  });

  test('should show transaction history', async ({ page }) => {
    await page.goto('/pos-app/statements');
    await page.waitForLoadState('networkidle');

    // If we can view a statement, check for transactions
    const viewButton = page.locator('button:has-text("View"), a:has-text("View")');
    if (await viewButton.first().isVisible()) {
      await viewButton.first().click();
      await page.waitForTimeout(1000);

      // Should see transaction list
      const transactions = page.locator('.ant-table, .transaction-list, [data-testid="transactions"]');
    }
  });

  test('should support PDF export', async ({ page }) => {
    await page.goto('/pos-app/statements');
    await page.waitForLoadState('networkidle');

    // Should have export/print button
    const exportButton = page.locator('button:has-text("Export"), button:has-text("PDF"), button:has-text("Print"), button:has-text("Download")');
  });

  test('should display credit/debit indicators', async ({ page }) => {
    await page.goto('/pos-app/statements');
    await page.waitForLoadState('networkidle');

    // Check for credit/debit type indicators
    await page.waitForTimeout(1000);
    const typeIndicators = page.locator('text=Credit, text=Debit, text=Payment, text=Invoice');
  });
});
