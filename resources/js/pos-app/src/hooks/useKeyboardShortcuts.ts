import { useEffect, useCallback } from 'react';

type KeyHandler = () => void;

interface ShortcutConfig {
  key: string;
  handler: KeyHandler;
  ctrlKey?: boolean;
  altKey?: boolean;
  shiftKey?: boolean;
  preventDefault?: boolean;
}

/**
 * Hook for managing keyboard shortcuts
 * Used primarily in the POS terminal
 */
export function useKeyboardShortcuts(shortcuts: ShortcutConfig[], enabled: boolean = true) {
  const handleKeyDown = useCallback(
    (event: KeyboardEvent) => {
      if (!enabled) return;

      // Ignore if user is typing in an input
      const target = event.target as HTMLElement;
      if (
        target.tagName === 'INPUT' ||
        target.tagName === 'TEXTAREA' ||
        target.isContentEditable
      ) {
        // Allow specific shortcuts even in inputs (like Escape)
        if (!['Escape', 'F1', 'F2', 'F3', 'F4', 'F5', 'F6', 'F7', 'F8', 'F9', 'F10', 'F11', 'F12'].includes(event.key)) {
          return;
        }
      }

      for (const shortcut of shortcuts) {
        const ctrlMatch = shortcut.ctrlKey === undefined || shortcut.ctrlKey === event.ctrlKey;
        const altMatch = shortcut.altKey === undefined || shortcut.altKey === event.altKey;
        const shiftMatch = shortcut.shiftKey === undefined || shortcut.shiftKey === event.shiftKey;
        const keyMatch = event.key.toLowerCase() === shortcut.key.toLowerCase();

        if (keyMatch && ctrlMatch && altMatch && shiftMatch) {
          if (shortcut.preventDefault !== false) {
            event.preventDefault();
          }
          shortcut.handler();
          return;
        }
      }
    },
    [shortcuts, enabled]
  );

  useEffect(() => {
    if (enabled) {
      window.addEventListener('keydown', handleKeyDown);
      return () => window.removeEventListener('keydown', handleKeyDown);
    }
  }, [handleKeyDown, enabled]);
}

/**
 * POS-specific keyboard shortcuts configuration
 */
export const posShortcuts = {
  // F2 - Hold order
  HOLD_ORDER: 'F2',
  // F3 - Recall held order
  RECALL_ORDER: 'F3',
  // F4 - Clear cart
  CLEAR_CART: 'F4',
  // F5 - Customer lookup
  CUSTOMER_LOOKUP: 'F5',
  // F6 - Apply discount
  APPLY_DISCOUNT: 'F6',
  // F7 - Product search
  PRODUCT_SEARCH: 'F7',
  // F9 - Checkout / Payment
  CHECKOUT: 'F9',
  // Escape - Cancel current action
  CANCEL: 'Escape',
  // + - Increase quantity
  INCREASE_QTY: '+',
  // - - Decrease quantity
  DECREASE_QTY: '-',
  // Delete - Remove selected item
  REMOVE_ITEM: 'Delete',
};

/**
 * Helper to create POS shortcuts array
 */
export function createPosShortcuts(handlers: {
  onHold?: () => void;
  onRecall?: () => void;
  onClearCart?: () => void;
  onCustomerLookup?: () => void;
  onApplyDiscount?: () => void;
  onProductSearch?: () => void;
  onCheckout?: () => void;
  onCancel?: () => void;
  onIncreaseQty?: () => void;
  onDecreaseQty?: () => void;
  onRemoveItem?: () => void;
}): ShortcutConfig[] {
  const shortcuts: ShortcutConfig[] = [];

  if (handlers.onHold) {
    shortcuts.push({ key: posShortcuts.HOLD_ORDER, handler: handlers.onHold });
  }
  if (handlers.onRecall) {
    shortcuts.push({ key: posShortcuts.RECALL_ORDER, handler: handlers.onRecall });
  }
  if (handlers.onClearCart) {
    shortcuts.push({ key: posShortcuts.CLEAR_CART, handler: handlers.onClearCart });
  }
  if (handlers.onCustomerLookup) {
    shortcuts.push({ key: posShortcuts.CUSTOMER_LOOKUP, handler: handlers.onCustomerLookup });
  }
  if (handlers.onApplyDiscount) {
    shortcuts.push({ key: posShortcuts.APPLY_DISCOUNT, handler: handlers.onApplyDiscount });
  }
  if (handlers.onProductSearch) {
    shortcuts.push({ key: posShortcuts.PRODUCT_SEARCH, handler: handlers.onProductSearch });
  }
  if (handlers.onCheckout) {
    shortcuts.push({ key: posShortcuts.CHECKOUT, handler: handlers.onCheckout });
  }
  if (handlers.onCancel) {
    shortcuts.push({ key: posShortcuts.CANCEL, handler: handlers.onCancel });
  }
  if (handlers.onIncreaseQty) {
    shortcuts.push({ key: posShortcuts.INCREASE_QTY, handler: handlers.onIncreaseQty });
  }
  if (handlers.onDecreaseQty) {
    shortcuts.push({ key: posShortcuts.DECREASE_QTY, handler: handlers.onDecreaseQty });
  }
  if (handlers.onRemoveItem) {
    shortcuts.push({ key: posShortcuts.REMOVE_ITEM, handler: handlers.onRemoveItem });
  }

  return shortcuts;
}
