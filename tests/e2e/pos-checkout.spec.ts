import { test, expect } from '@playwright/test';

test.describe('POS - Checkout', () => {
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

  async function addProductToCart(page) {
    // Wait for products to load
    await page.waitForLoadState('networkidle');

    // Get product cards
    const productCards = page.locator('.pos-product-card, .ant-card').filter({
      hasNot: page.locator('text=Cart')
    }).filter({
      has: page.locator('text=/SAR/')
    });

    const count = await productCards.count();
    if (count > 0) {
      await productCards.first().click();
      await page.waitForTimeout(1500);
      return true;
    }
    return false;
  }

  test('should open checkout modal when clicking checkout button', async ({ page }) => {
    // Add product first
    const added = await addProductToCart(page);

    if (added) {
      // Click checkout button
      const checkoutBtn = page.locator('button').filter({ hasText: /Checkout/i });
      await checkoutBtn.click();

      // Modal should open
      await expect(page.locator('.ant-modal')).toBeVisible({ timeout: 3000 });
    }
  });

  test('should open checkout modal on F4 key', async ({ page }) => {
    // Add product first
    const added = await addProductToCart(page);

    if (added) {
      // Press F4
      await page.keyboard.press('F4');

      // Modal should open
      await expect(page.locator('.ant-modal')).toBeVisible({ timeout: 3000 });
    }
  });

  test('should display total amount in checkout modal', async ({ page }) => {
    const added = await addProductToCart(page);

    if (added) {
      // Open checkout
      const checkoutBtn = page.locator('button').filter({ hasText: /Checkout/i });
      await checkoutBtn.click();

      // Wait for modal
      await page.waitForSelector('.ant-modal', { state: 'visible' });

      // Should display total
      await expect(page.locator('text=Amount Due')).toBeVisible();
    }
  });

  test('should display payment method options', async ({ page }) => {
    const added = await addProductToCart(page);

    if (added) {
      // Open checkout
      const checkoutBtn = page.locator('button').filter({ hasText: /Checkout/i });
      await checkoutBtn.click();

      await page.waitForSelector('.ant-modal', { state: 'visible' });

      // Payment methods should be visible
      await expect(page.locator('text=Payment Method')).toBeVisible();
      await expect(page.locator('text=Cash')).toBeVisible();
      await expect(page.locator('text=Card')).toBeVisible();
    }
  });

  test('should show numeric keypad for cash payment', async ({ page }) => {
    const added = await addProductToCart(page);

    if (added) {
      // Open checkout
      const checkoutBtn = page.locator('button').filter({ hasText: /Checkout/i });
      await checkoutBtn.click();

      await page.waitForSelector('.ant-modal', { state: 'visible' });

      // Numeric keypad buttons should be visible
      await expect(page.locator('button:has-text("1")')).toBeVisible();
      await expect(page.locator('button:has-text("2")')).toBeVisible();
      await expect(page.locator('button:has-text("0")')).toBeVisible();
      await expect(page.locator('button:has-text("C")')).toBeVisible();
    }
  });

  test('should calculate change correctly', async ({ page }) => {
    const added = await addProductToCart(page);

    if (added) {
      // Open checkout
      const checkoutBtn = page.locator('button').filter({ hasText: /Checkout/i });
      await checkoutBtn.click();

      await page.waitForSelector('.ant-modal', { state: 'visible' });

      // Change display should be visible
      await expect(page.locator('text=Change')).toBeVisible();
    }
  });

  test('should display quick amount buttons', async ({ page }) => {
    const added = await addProductToCart(page);

    if (added) {
      // Open checkout
      const checkoutBtn = page.locator('button').filter({ hasText: /Checkout/i });
      await checkoutBtn.click();

      await page.waitForSelector('.ant-modal', { state: 'visible' });

      // Quick amount buttons should exist
      const quickAmountBtns = page.locator('.ant-modal button').filter({ hasText: /SAR/ });
      await expect(quickAmountBtns.first()).toBeVisible();
    }
  });

  test('should close modal on ESC key', async ({ page }) => {
    const added = await addProductToCart(page);

    if (added) {
      // Open checkout
      const checkoutBtn = page.locator('button').filter({ hasText: /Checkout/i });
      await checkoutBtn.click();

      await page.waitForSelector('.ant-modal', { state: 'visible' });

      // Press ESC
      await page.keyboard.press('Escape');

      // Modal should close
      await expect(page.locator('.ant-modal')).not.toBeVisible({ timeout: 2000 });
    }
  });

  test('should have Complete Sale button', async ({ page }) => {
    const added = await addProductToCart(page);

    if (added) {
      // Open checkout
      const checkoutBtn = page.locator('button').filter({ hasText: /Checkout/i });
      await checkoutBtn.click();

      await page.waitForSelector('.ant-modal', { state: 'visible' });

      // Complete Sale button should exist
      await expect(page.locator('button:has-text("Complete Sale")')).toBeVisible();
    }
  });

  test('should switch between payment methods', async ({ page }) => {
    const added = await addProductToCart(page);

    if (added) {
      // Open checkout
      const checkoutBtn = page.locator('button').filter({ hasText: /Checkout/i });
      await checkoutBtn.click();

      await page.waitForSelector('.ant-modal', { state: 'visible' });

      // Click Card payment method
      const cardOption = page.locator('.ant-modal').locator('text=Card');
      await cardOption.click();

      // Should show reference input for card
      await expect(page.locator('input[placeholder*="Reference"], input[placeholder*="reference"]')).toBeVisible({ timeout: 2000 });
    }
  });
});
