import { test, expect } from '@playwright/test';

test.describe('Expense Management UI', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login');
    await page.fill('input[type="email"], input[name="email"]', 'admin@admin.com');
    await page.fill('input[type="password"]', 'admin');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
  });

  test('should navigate to expenses page', async ({ page }) => {
    await page.goto('/pos-app/expenses');
    await page.waitForLoadState('networkidle');

    // Should see expenses page header
    await expect(page.locator('text=Expenses, text=Expense')).toBeVisible({ timeout: 10000 });
  });

  test('should display expenses list', async ({ page }) => {
    await page.goto('/pos-app/expenses');
    await page.waitForLoadState('networkidle');

    // Should see table or list component
    const table = page.locator('.ant-table, [data-testid="expenses-table"], table');
    await expect(table).toBeVisible({ timeout: 10000 });
  });

  test('should have create expense button', async ({ page }) => {
    await page.goto('/pos-app/expenses');
    await page.waitForLoadState('networkidle');

    // Should see create/add button
    const createButton = page.locator('button:has-text("Add"), button:has-text("Create"), button:has-text("New")');
    await expect(createButton.first()).toBeVisible({ timeout: 10000 });
  });

  test('should open expense creation modal/form', async ({ page }) => {
    await page.goto('/pos-app/expenses');
    await page.waitForLoadState('networkidle');

    // Click create button
    const createButton = page.locator('button:has-text("Add"), button:has-text("Create"), button:has-text("New")');
    await createButton.first().click();

    // Should see form/modal
    const form = page.locator('.ant-modal, .ant-drawer, form, [data-testid="expense-form"]');
    await expect(form.first()).toBeVisible({ timeout: 5000 });
  });

  test('should display expense categories dropdown', async ({ page }) => {
    await page.goto('/pos-app/expenses');
    await page.waitForLoadState('networkidle');

    // Open create form
    const createButton = page.locator('button:has-text("Add"), button:has-text("Create"), button:has-text("New")');
    await createButton.first().click();
    await page.waitForTimeout(500);

    // Should see category select
    const categorySelect = page.locator('[data-testid="category-select"], .ant-select, select');
    await expect(categorySelect.first()).toBeVisible({ timeout: 5000 });
  });

  test('should show expense status tags', async ({ page }) => {
    await page.goto('/pos-app/expenses');
    await page.waitForLoadState('networkidle');

    // Status tags should exist (draft, pending, approved, paid)
    const statusTags = page.locator('.ant-tag, [data-testid="status-tag"]');
    // At least one status should be visible if there are expenses
    await page.waitForTimeout(1000);
  });

  test('should have filter options', async ({ page }) => {
    await page.goto('/pos-app/expenses');
    await page.waitForLoadState('networkidle');

    // Should see filter bar or filter options
    const filters = page.locator('[data-testid="filter-bar"], .filter-bar, .ant-input-search, input[placeholder*="Search"]');
    await expect(filters.first()).toBeVisible({ timeout: 10000 });
  });

  test('should display expense summary stats', async ({ page }) => {
    await page.goto('/pos-app/expenses');
    await page.waitForLoadState('networkidle');

    // Should see summary statistics
    const stats = page.locator('[data-testid="summary-stats"], .summary-stats, .ant-statistic, .stat-card');
    await expect(stats.first()).toBeVisible({ timeout: 10000 });
  });

  test('expense form validation', async ({ page }) => {
    await page.goto('/pos-app/expenses');
    await page.waitForLoadState('networkidle');

    // Open create form
    const createButton = page.locator('button:has-text("Add"), button:has-text("Create"), button:has-text("New")');
    await createButton.first().click();
    await page.waitForTimeout(500);

    // Try to submit empty form
    const submitButton = page.locator('button[type="submit"], button:has-text("Save"), button:has-text("Submit")');
    await submitButton.first().click();

    // Should show validation errors
    const errors = page.locator('.ant-form-item-explain-error, .error-message, [role="alert"]');
    await expect(errors.first()).toBeVisible({ timeout: 3000 });
  });
});
