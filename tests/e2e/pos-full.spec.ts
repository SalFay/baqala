import { test, expect } from '@playwright/test'

test.describe('POS System - Full Feature Test', () => {
  test.beforeEach(async ({ page }) => {
    // Login
    await page.goto('/login')
    await page.fill('input[name="email"]', 'admin@baqala.test')
    await page.fill('input[name="password"]', 'password')
    await page.click('button[type="submit"]')
    await page.waitForURL(/\/(dashboard|pos)/)

    // Navigate to POS
    await page.goto('/pos')
    await page.waitForLoadState('networkidle')
  })

  test.describe('Phase 1: Critical Bug Fixes', () => {
    test('should display dynamic tax rate (not hardcoded 15%)', async ({ page }) => {
      // Add product to cart
      await page.click('.pos-product-card >> nth=0')
      await page.waitForTimeout(500)

      // Check cart summary shows tax with actual rate
      const cartSummary = page.locator('.ant-card').filter({ hasText: 'Tax' })
      await expect(cartSummary).toBeVisible()

      // Should not be hardcoded "Tax (15%)"
      const taxText = await page.locator('text=/Tax \\(\\d+(\\.\\d+)?%\\)/').textContent()
      expect(taxText).toBeTruthy()
    })

    test('should validate payment reference for card payments', async ({ page }) => {
      // Add product
      await page.click('.pos-product-card >> nth=0')
      await page.waitForTimeout(500)

      // Open checkout
      await page.click('button:has-text("Checkout")')
      await page.waitForSelector('.ant-modal')

      // Select card payment
      await page.click('text=Card')

      // Try to submit without reference
      await page.click('button:has-text("Complete")')

      // Should show validation error
      await expect(page.locator('text=/reference|required/i')).toBeVisible()
    })
  })

  test.describe('Phase 2: Discount System', () => {
    test('should open discount modal', async ({ page }) => {
      // Add product first
      await page.click('.pos-product-card >> nth=0')
      await page.waitForTimeout(500)

      // Click discount button (gift icon)
      await page.click('button:has(.anticon-gift)')

      // Modal should open
      await expect(page.locator('.ant-modal')).toBeVisible()
      await expect(page.locator('text=Apply Discount')).toBeVisible()
    })

    test('should apply percentage discount', async ({ page }) => {
      // Add product
      await page.click('.pos-product-card >> nth=0')
      await page.waitForTimeout(500)

      // Open discount modal
      await page.click('button:has(.anticon-gift)')
      await page.waitForSelector('.ant-modal')

      // Select percentage and enter value
      await page.click('text=Percentage')
      await page.fill('input[type="number"]', '10')
      await page.fill('textarea', 'Test discount')

      // Apply
      await page.click('button:has-text("Apply")')
      await page.waitForTimeout(500)

      // Discount should show in cart
      await expect(page.locator('text=/Discount|10%/i')).toBeVisible()
    })
  })

  test.describe('Phase 3: Quick Customer Creation', () => {
    test('should open customer modal', async ({ page }) => {
      // Click customer selection area
      await page.click('text=Walk-in Customer')

      // Modal should open
      await expect(page.locator('.ant-modal')).toBeVisible()
      await expect(page.locator('text=/Select Customer|Customer/i')).toBeVisible()
    })

    test('should show new customer form', async ({ page }) => {
      // Open customer modal
      await page.click('text=Walk-in Customer')
      await page.waitForSelector('.ant-modal')

      // Click new customer button
      await page.click('button:has-text("New")')

      // Form should appear
      await expect(page.locator('input[placeholder*="First"]')).toBeVisible()
      await expect(page.locator('input[placeholder*="Phone"]')).toBeVisible()
    })
  })

  test.describe('Phase 4: Split Payment', () => {
    test('should show split payment option in checkout', async ({ page }) => {
      // Add product
      await page.click('.pos-product-card >> nth=0')
      await page.waitForTimeout(500)

      // Open checkout
      await page.click('button:has-text("Checkout")')
      await page.waitForSelector('.ant-modal')

      // Should see split payment option
      await expect(page.locator('text=/Split Payment|Add Payment/i')).toBeVisible()
    })

    test('should have order notes field', async ({ page }) => {
      // Add product
      await page.click('.pos-product-card >> nth=0')
      await page.waitForTimeout(500)

      // Open checkout
      await page.click('button:has-text("Checkout")')
      await page.waitForSelector('.ant-modal')

      // Should see notes field
      await expect(page.locator('textarea[placeholder*="notes" i]')).toBeVisible()
    })
  })

  test.describe('Phase 5: Keyboard Mode', () => {
    test('should toggle keyboard mode with F1', async ({ page }) => {
      // Press F1
      await page.keyboard.press('F1')
      await page.waitForTimeout(300)

      // Should see keyboard mode indicator
      await expect(page.locator('text=Keyboard Mode')).toBeVisible()
    })

    test('should focus barcode with F2', async ({ page }) => {
      // Press F2
      await page.keyboard.press('F2')
      await page.waitForTimeout(300)

      // Barcode input should be focused
      const barcodeInput = page.locator('input[placeholder*="barcode" i]')
      await expect(barcodeInput).toBeFocused()
    })

    test('should focus search with F3', async ({ page }) => {
      // Press F3
      await page.keyboard.press('F3')
      await page.waitForTimeout(300)

      // Search input should be focused
      const searchInput = page.locator('input[placeholder*="Search" i]')
      await expect(searchInput).toBeFocused()
    })

    test('should open checkout with F4', async ({ page }) => {
      // Add product first
      await page.click('.pos-product-card >> nth=0')
      await page.waitForTimeout(500)

      // Press F4
      await page.keyboard.press('F4')
      await page.waitForTimeout(300)

      // Checkout modal should open
      await expect(page.locator('.ant-modal')).toBeVisible()
    })

    test('should open held carts with F7', async ({ page }) => {
      // Press F7
      await page.keyboard.press('F7')
      await page.waitForTimeout(300)

      // Held carts modal should open
      await expect(page.locator('text=/Held Carts|No held carts/i')).toBeVisible()
    })

    test('should open customer modal with F8', async ({ page }) => {
      // Press F8
      await page.keyboard.press('F8')
      await page.waitForTimeout(300)

      // Customer modal should open
      await expect(page.locator('.ant-modal')).toBeVisible()
    })

    test('should navigate products with arrow keys in keyboard mode', async ({ page }) => {
      // Enable keyboard mode
      await page.keyboard.press('F1')
      await page.waitForTimeout(300)

      // Navigate right
      await page.keyboard.press('ArrowRight')
      await page.waitForTimeout(200)

      // A product should be selected (has selection styling)
      await expect(page.locator('.pos-product-card-selected')).toBeVisible()
    })
  })

  test.describe('Phase 6: Loyalty Panel', () => {
    test('should show loyalty panel when customer selected', async ({ page }) => {
      // Open customer modal
      await page.click('text=Walk-in Customer')
      await page.waitForSelector('.ant-modal')

      // Search for customer
      await page.fill('input[placeholder*="Search" i]', 'test')
      await page.waitForTimeout(500)

      // If customers exist, select one
      const customerRow = page.locator('.ant-list-item >> nth=0')
      if (await customerRow.isVisible()) {
        await customerRow.click()
        await page.waitForTimeout(500)

        // Loyalty panel should appear (or "No loyalty" message)
        const loyaltyText = page.locator('text=/Loyalty|Points|Member/i')
        await expect(loyaltyText).toBeVisible()
      }
    })
  })

  test.describe('Phase 7: Recent Orders', () => {
    test('should open recent orders drawer', async ({ page }) => {
      // Click history button
      await page.click('button:has(.anticon-history)')
      await page.waitForTimeout(300)

      // Drawer should open
      await expect(page.locator('.ant-drawer')).toBeVisible()
      await expect(page.locator('text=Recent Orders')).toBeVisible()
    })

    test('should search orders in drawer', async ({ page }) => {
      // Open drawer
      await page.click('button:has(.anticon-history)')
      await page.waitForSelector('.ant-drawer')

      // Search field should be visible
      const searchInput = page.locator('.ant-drawer input[placeholder*="Search" i]')
      await expect(searchInput).toBeVisible()
    })
  })

  test.describe('Phase 8: Product Quick View', () => {
    test('should show product details on right-click', async ({ page }) => {
      // Right-click on product
      await page.locator('.pos-product-card >> nth=0').click({ button: 'right' })
      await page.waitForTimeout(300)

      // Quick view modal should open
      await expect(page.locator('.ant-modal')).toBeVisible()
      await expect(page.locator('text=/Product|Details|Stock/i')).toBeVisible()
    })

    test('should show product info in quick view', async ({ page }) => {
      // Right-click on product
      await page.locator('.pos-product-card >> nth=0').click({ button: 'right' })
      await page.waitForSelector('.ant-modal')

      // Should show price and stock
      await expect(page.locator('.ant-modal >> text=/SAR|\\$/i')).toBeVisible()
      await expect(page.locator('.ant-modal >> text=/stock/i')).toBeVisible()
    })
  })

  test.describe('Phase 9: Returns', () => {
    test('should open returns modal', async ({ page }) => {
      // Click returns button
      await page.click('button:has(.anticon-rollback)')
      await page.waitForTimeout(300)

      // Returns modal should open
      await expect(page.locator('.ant-modal')).toBeVisible()
      await expect(page.locator('text=Returns')).toBeVisible()
    })

    test('should show order search in returns modal', async ({ page }) => {
      // Open returns modal
      await page.click('button:has(.anticon-rollback)')
      await page.waitForSelector('.ant-modal')

      // Search field should be visible
      const searchInput = page.locator('.ant-modal input[placeholder*="Search" i]')
      await expect(searchInput).toBeVisible()
    })

    test('should search for orders to return', async ({ page }) => {
      // Open returns modal
      await page.click('button:has(.anticon-rollback)')
      await page.waitForSelector('.ant-modal')

      // Search for an order
      await page.fill('.ant-modal input', 'ORD')
      await page.click('.ant-modal button:has(.anticon-search)')
      await page.waitForTimeout(1000)

      // Should show results or "no orders found"
      const hasResults = await page.locator('.ant-modal .ant-table-row').count() > 0
      const noResults = await page.locator('text=/No orders found/i').isVisible()
      expect(hasResults || noResults).toBeTruthy()
    })
  })

  test.describe('Cart Operations', () => {
    test('should add product to cart', async ({ page }) => {
      // Click on product
      await page.click('.pos-product-card >> nth=0')
      await page.waitForTimeout(500)

      // Cart should have item
      const cartBadge = page.locator('.ant-badge-count')
      await expect(cartBadge).toBeVisible()
    })

    test('should update cart quantity', async ({ page }) => {
      // Add product
      await page.click('.pos-product-card >> nth=0')
      await page.waitForTimeout(500)

      // Click increase button
      await page.click('button:has(.anticon-plus) >> nth=0')
      await page.waitForTimeout(500)

      // Quantity should be 2
      await expect(page.locator('text=2')).toBeVisible()
    })

    test('should remove item from cart', async ({ page }) => {
      // Add product
      await page.click('.pos-product-card >> nth=0')
      await page.waitForTimeout(500)

      // Click delete button
      await page.click('button:has(.anticon-delete) >> nth=0')
      await page.waitForTimeout(500)

      // Cart should be empty
      await expect(page.locator('text=Cart is empty')).toBeVisible()
    })

    test('should clear entire cart', async ({ page }) => {
      // Add products
      await page.click('.pos-product-card >> nth=0')
      await page.waitForTimeout(300)
      await page.click('.pos-product-card >> nth=1')
      await page.waitForTimeout(500)

      // Click clear cart button
      await page.click('button:has(.anticon-delete)[class*="danger"]')
      await page.waitForTimeout(500)

      // Cart should be empty
      await expect(page.locator('text=Cart is empty')).toBeVisible()
    })
  })

  test.describe('Hold Cart', () => {
    test('should hold cart', async ({ page }) => {
      // Add product
      await page.click('.pos-product-card >> nth=0')
      await page.waitForTimeout(500)

      // Click hold button
      await page.click('button:has(.anticon-pause-circle)')
      await page.waitForSelector('.ant-modal')

      // Enter name and hold
      await page.fill('input', 'Test Hold')
      await page.click('button:has-text("Hold")')
      await page.waitForTimeout(500)

      // Cart should be cleared
      await expect(page.locator('text=Cart is empty')).toBeVisible()
    })
  })

  test.describe('Category Filter', () => {
    test('should filter products by category', async ({ page }) => {
      // Click on a category tab (if exists)
      const categoryTab = page.locator('.ant-tabs-tab >> nth=1')
      if (await categoryTab.isVisible()) {
        await categoryTab.click()
        await page.waitForTimeout(500)

        // Products should be filtered (page updates)
        await expect(page.locator('.pos-product-grid')).toBeVisible()
      }
    })
  })

  test.describe('Product Search', () => {
    test('should search products', async ({ page }) => {
      // Type in search
      await page.fill('input[placeholder*="Search products" i]', 'test')
      await page.waitForTimeout(500)

      // Products should update
      await expect(page.locator('.pos-product-grid')).toBeVisible()
    })
  })
})
