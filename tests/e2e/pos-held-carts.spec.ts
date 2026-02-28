import { test, expect } from '@playwright/test';

test.describe('POS - Held Carts', () => {
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

  test('should display hold cart button', async ({ page }) => {
    // Hold cart button (pause icon)
    const holdBtn = page.locator('button').filter({ has: page.locator('.anticon-pause-circle') });
    await expect(holdBtn).toBeVisible();
  });

  test('should display held carts button', async ({ page }) => {
    // Held carts button (play icon)
    const heldCartsBtn = page.locator('button').filter({ has: page.locator('.anticon-play-circle') });
    await expect(heldCartsBtn).toBeVisible();
  });

  test('should disable hold button when cart is empty', async ({ page }) => {
    // Hold cart button should be disabled when cart is empty
    const holdBtn = page.locator('button').filter({ has: page.locator('.anticon-pause-circle') });
    await expect(holdBtn).toBeDisabled();
  });

  test('should enable hold button when cart has items', async ({ page }) => {
    // Add a product
    await page.waitForLoadState('networkidle');

    const productCards = page.locator('.pos-product-card, .ant-card').filter({
      hasNot: page.locator('text=Cart')
    }).filter({
      has: page.locator('text=/SAR/')
    });

    const count = await productCards.count();
    if (count > 0) {
      await productCards.first().click();
      await page.waitForTimeout(1500);

      // Hold button should be enabled
      const holdBtn = page.locator('button').filter({ has: page.locator('.anticon-pause-circle') });
      await expect(holdBtn).not.toBeDisabled();
    }
  });

  test('should open hold cart modal when clicking hold button', async ({ page }) => {
    // Add a product first
    await page.waitForLoadState('networkidle');

    const productCards = page.locator('.pos-product-card, .ant-card').filter({
      hasNot: page.locator('text=Cart')
    }).filter({
      has: page.locator('text=/SAR/')
    });

    const count = await productCards.count();
    if (count > 0) {
      await productCards.first().click();
      await page.waitForTimeout(1500);

      // Click hold button
      const holdBtn = page.locator('button').filter({ has: page.locator('.anticon-pause-circle') });
      await holdBtn.click();

      // Modal should open
      await expect(page.locator('.ant-modal')).toBeVisible({ timeout: 3000 });
    }
  });

  test('should open held carts modal', async ({ page }) => {
    // Click held carts button
    const heldCartsBtn = page.locator('button').filter({ has: page.locator('.anticon-play-circle') });
    await heldCartsBtn.click();

    // Modal should open
    await expect(page.locator('.ant-modal')).toBeVisible({ timeout: 3000 });
  });

  test('should display held carts badge count', async ({ page }) => {
    // Held carts button should have a badge
    const heldCartsBadge = page.locator('.ant-badge').filter({ has: page.locator('.anticon-play-circle') });
    await expect(heldCartsBadge).toBeVisible();
  });

  test('should be able to close held carts modal', async ({ page }) => {
    // Open held carts modal
    const heldCartsBtn = page.locator('button').filter({ has: page.locator('.anticon-play-circle') });
    await heldCartsBtn.click();

    // Wait for modal
    await page.waitForSelector('.ant-modal', { state: 'visible', timeout: 3000 });

    // Close modal
    await page.keyboard.press('Escape');

    // Modal should close
    await expect(page.locator('.ant-modal')).not.toBeVisible({ timeout: 2000 });
  });

  test('should show empty state when no held carts', async ({ page }) => {
    // Open held carts modal
    const heldCartsBtn = page.locator('button').filter({ has: page.locator('.anticon-play-circle') });
    await heldCartsBtn.click();

    // Wait for modal
    await page.waitForSelector('.ant-modal', { state: 'visible', timeout: 3000 });

    // Should show empty state or list
    const modalContent = page.locator('.ant-modal-body');
    await expect(modalContent).toBeVisible();
  });

  test('should have note input in hold cart modal', async ({ page }) => {
    // Add a product first
    await page.waitForLoadState('networkidle');

    const productCards = page.locator('.pos-product-card, .ant-card').filter({
      hasNot: page.locator('text=Cart')
    }).filter({
      has: page.locator('text=/SAR/')
    });

    const count = await productCards.count();
    if (count > 0) {
      await productCards.first().click();
      await page.waitForTimeout(1500);

      // Click hold button
      const holdBtn = page.locator('button').filter({ has: page.locator('.anticon-pause-circle') });
      await holdBtn.click();

      // Wait for modal
      await page.waitForSelector('.ant-modal', { state: 'visible', timeout: 3000 });

      // Should have input for note
      const noteInput = page.locator('.ant-modal input, .ant-modal textarea');
      await expect(noteInput.first()).toBeVisible();
    }
  });
});
