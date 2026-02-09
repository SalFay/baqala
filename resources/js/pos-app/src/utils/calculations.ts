/**
 * Calculate tax amount
 */
export function calculateTax(amount: number, taxRate: number): number {
  return round(amount * (taxRate / 100), 2);
}

/**
 * Calculate price including tax
 */
export function calculatePriceWithTax(price: number, taxRate: number): number {
  return round(price * (1 + taxRate / 100), 2);
}

/**
 * Calculate price excluding tax
 */
export function calculatePriceWithoutTax(priceWithTax: number, taxRate: number): number {
  return round(priceWithTax / (1 + taxRate / 100), 2);
}

/**
 * Calculate discount amount
 */
export function calculateDiscount(
  amount: number,
  discount: number,
  discountType: 'fixed' | 'percentage'
): number {
  if (discountType === 'percentage') {
    return round(amount * (discount / 100), 2);
  }
  return Math.min(round(discount, 2), amount);
}

/**
 * Calculate line total
 */
export function calculateLineTotal(
  unitPrice: number,
  quantity: number,
  discount: number = 0,
  taxRate: number = 0
): {
  subtotal: number;
  discountAmount: number;
  taxAmount: number;
  total: number;
} {
  const subtotal = round(unitPrice * quantity, 2);
  const discountAmount = round(discount, 2);
  const afterDiscount = round(subtotal - discountAmount, 2);
  const taxAmount = round(afterDiscount * (taxRate / 100), 2);
  const total = round(afterDiscount + taxAmount, 2);

  return {
    subtotal,
    discountAmount,
    taxAmount,
    total,
  };
}

/**
 * Calculate cart totals
 */
export function calculateCartTotals(
  items: Array<{
    unitPrice: number;
    quantity: number;
    discount?: number;
    taxRate?: number;
  }>,
  cartDiscount: number = 0,
  cartDiscountType: 'fixed' | 'percentage' = 'fixed',
  loyaltyDiscount: number = 0
): {
  itemsSubtotal: number;
  itemsTax: number;
  itemsDiscount: number;
  cartDiscountAmount: number;
  loyaltyDiscountAmount: number;
  total: number;
} {
  let itemsSubtotal = 0;
  let itemsTax = 0;
  let itemsDiscount = 0;

  for (const item of items) {
    const lineTotal = calculateLineTotal(
      item.unitPrice,
      item.quantity,
      item.discount || 0,
      item.taxRate || 0
    );
    itemsSubtotal += lineTotal.subtotal;
    itemsTax += lineTotal.taxAmount;
    itemsDiscount += lineTotal.discountAmount;
  }

  const afterItemDiscount = round(itemsSubtotal - itemsDiscount + itemsTax, 2);
  const cartDiscountAmount = calculateDiscount(afterItemDiscount, cartDiscount, cartDiscountType);
  const loyaltyDiscountAmount = round(loyaltyDiscount, 2);
  const total = round(Math.max(0, afterItemDiscount - cartDiscountAmount - loyaltyDiscountAmount), 2);

  return {
    itemsSubtotal: round(itemsSubtotal, 2),
    itemsTax: round(itemsTax, 2),
    itemsDiscount: round(itemsDiscount, 2),
    cartDiscountAmount: round(cartDiscountAmount, 2),
    loyaltyDiscountAmount,
    total,
  };
}

/**
 * Calculate profit margin
 */
export function calculateProfitMargin(salePrice: number, purchasePrice: number): number {
  if (salePrice === 0) return 0;
  return round(((salePrice - purchasePrice) / salePrice) * 100, 2);
}

/**
 * Calculate markup percentage
 */
export function calculateMarkup(salePrice: number, purchasePrice: number): number {
  if (purchasePrice === 0) return 0;
  return round(((salePrice - purchasePrice) / purchasePrice) * 100, 2);
}

/**
 * Calculate loyalty points for amount
 */
export function calculateLoyaltyPoints(
  amount: number,
  pointsPerUnit: number = 1, // Points per SAR
  multiplier: number = 1
): number {
  return Math.floor(amount * pointsPerUnit * multiplier);
}

/**
 * Calculate points value in currency
 */
export function calculatePointsValue(
  points: number,
  valuePerPoint: number = 0.01 // Default: 1 point = 0.01 SAR
): number {
  return round(points * valuePerPoint, 2);
}

/**
 * Round to specified decimal places
 */
export function round(value: number, decimals: number = 2): number {
  const factor = Math.pow(10, decimals);
  return Math.round(value * factor) / factor;
}

/**
 * Check if two numbers are approximately equal
 */
export function approximatelyEqual(a: number, b: number, epsilon: number = 0.01): boolean {
  return Math.abs(a - b) < epsilon;
}

/**
 * Clamp a value between min and max
 */
export function clamp(value: number, min: number, max: number): number {
  return Math.min(Math.max(value, min), max);
}

/**
 * Calculate percentage change
 */
export function calculatePercentageChange(current: number, previous: number): number {
  if (previous === 0) {
    return current > 0 ? 100 : 0;
  }
  return round(((current - previous) / previous) * 100, 2);
}
