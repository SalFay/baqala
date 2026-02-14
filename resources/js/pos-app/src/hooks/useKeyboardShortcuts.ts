import { useEffect, useCallback, useRef } from 'react';

type KeyHandler = () => void;

interface ShortcutConfig {
  key: string;
  handler: KeyHandler;
  ctrlKey?: boolean;
  altKey?: boolean;
  shiftKey?: boolean;
  preventDefault?: boolean;
  description?: string;
}

/**
 * Hook for managing keyboard shortcuts
 * Used primarily in the POS terminal
 */
export function useKeyboardShortcuts(shortcuts: ShortcutConfig[], enabled: boolean = true) {
  const handleKeyDown = useCallback(
    (event: KeyboardEvent) => {
      if (!enabled) return;

      // Ignore if user is typing in an input (except for function keys and escape)
      const target = event.target as HTMLElement;
      const isFunctionKey = event.key.startsWith('F') && event.key.length <= 3;
      const isEscape = event.key === 'Escape';
      const isAsterisk = event.key === '*';

      if (
        target.tagName === 'INPUT' ||
        target.tagName === 'TEXTAREA' ||
        target.isContentEditable
      ) {
        if (!isFunctionKey && !isEscape && !isAsterisk) {
          return;
        }
      }

      for (const shortcut of shortcuts) {
        const ctrlMatch = shortcut.ctrlKey === undefined ? !event.ctrlKey : shortcut.ctrlKey === event.ctrlKey;
        const altMatch = shortcut.altKey === undefined ? !event.altKey : shortcut.altKey === event.altKey;
        const shiftMatch = shortcut.shiftKey === undefined || shortcut.shiftKey === event.shiftKey;
        const keyMatch = event.key.toLowerCase() === shortcut.key.toLowerCase();

        if (keyMatch && ctrlMatch && altMatch && shiftMatch) {
          if (shortcut.preventDefault !== false) {
            event.preventDefault();
            event.stopPropagation();
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
      window.addEventListener('keydown', handleKeyDown, { capture: true });
      return () => window.removeEventListener('keydown', handleKeyDown, { capture: true });
    }
  }, [handleKeyDown, enabled]);
}

/**
 * POS-specific keyboard shortcuts configuration
 */
export const posShortcuts = {
  // F1 - Help overlay
  HELP: 'F1',
  // F2 - Hold order
  HOLD_ORDER: 'F2',
  // F3 - Recall held order
  RECALL_ORDER: 'F3',
  // F4 - Apply discount
  APPLY_DISCOUNT: 'F4',
  // F5 - Customer lookup
  CUSTOMER_LOOKUP: 'F5',
  // F6 - Reserved
  RESERVED_F6: 'F6',
  // F7 - Process return
  PROCESS_RETURN: 'F7',
  // F8 - Void last item
  VOID_LAST: 'F8',
  // F9 - Checkout / Payment
  CHECKOUT: 'F9',
  // F10 - Reserved
  RESERVED_F10: 'F10',
  // F11 - Fullscreen
  FULLSCREEN: 'F11',
  // F12 - Dev tools (do not intercept)
  // Escape - Cancel current action
  CANCEL: 'Escape',
  // * - Focus search
  FOCUS_SEARCH: '*',
  // + - Increase quantity
  INCREASE_QTY: '+',
  // - - Decrease quantity
  DECREASE_QTY: '-',
  // Delete - Remove selected item
  REMOVE_ITEM: 'Delete',
  // Ctrl+S - Save/Quick save
  SAVE: 's',
  // Ctrl+P - Print
  PRINT: 'p',
  // Ctrl+Z - Undo
  UNDO: 'z',
};

/**
 * POS shortcut descriptions for help overlay
 */
export const shortcutDescriptions: Record<string, string> = {
  F1: 'Show help overlay',
  F2: 'Hold current order',
  F3: 'Recall held order',
  F4: 'Apply discount',
  F5: 'Select/Change customer',
  F7: 'Process return',
  F8: 'Void last item',
  F9: 'Checkout/Payment',
  Escape: 'Cancel/Go back',
  '*': 'Focus search bar',
  '+': 'Increase quantity',
  '-': 'Decrease quantity',
  Delete: 'Remove selected item',
  'Ctrl+P': 'Print receipt',
  'Ctrl+S': 'Quick save',
};

/**
 * Helper to create POS shortcuts array
 */
export function createPosShortcuts(handlers: {
  onHelp?: () => void;
  onHold?: () => void;
  onRecall?: () => void;
  onApplyDiscount?: () => void;
  onCustomerLookup?: () => void;
  onProcessReturn?: () => void;
  onVoidLast?: () => void;
  onCheckout?: () => void;
  onCancel?: () => void;
  onFocusSearch?: () => void;
  onIncreaseQty?: () => void;
  onDecreaseQty?: () => void;
  onRemoveItem?: () => void;
  onPrint?: () => void;
  onSave?: () => void;
  onUndo?: () => void;
  onFullscreen?: () => void;
}): ShortcutConfig[] {
  const shortcuts: ShortcutConfig[] = [];

  if (handlers.onHelp) {
    shortcuts.push({
      key: posShortcuts.HELP,
      handler: handlers.onHelp,
      description: 'Help',
    });
  }
  if (handlers.onHold) {
    shortcuts.push({
      key: posShortcuts.HOLD_ORDER,
      handler: handlers.onHold,
      description: 'Hold Order',
    });
  }
  if (handlers.onRecall) {
    shortcuts.push({
      key: posShortcuts.RECALL_ORDER,
      handler: handlers.onRecall,
      description: 'Recall Order',
    });
  }
  if (handlers.onApplyDiscount) {
    shortcuts.push({
      key: posShortcuts.APPLY_DISCOUNT,
      handler: handlers.onApplyDiscount,
      description: 'Apply Discount',
    });
  }
  if (handlers.onCustomerLookup) {
    shortcuts.push({
      key: posShortcuts.CUSTOMER_LOOKUP,
      handler: handlers.onCustomerLookup,
      description: 'Customer Lookup',
    });
  }
  if (handlers.onProcessReturn) {
    shortcuts.push({
      key: posShortcuts.PROCESS_RETURN,
      handler: handlers.onProcessReturn,
      description: 'Process Return',
    });
  }
  if (handlers.onVoidLast) {
    shortcuts.push({
      key: posShortcuts.VOID_LAST,
      handler: handlers.onVoidLast,
      description: 'Void Last Item',
    });
  }
  if (handlers.onCheckout) {
    shortcuts.push({
      key: posShortcuts.CHECKOUT,
      handler: handlers.onCheckout,
      description: 'Checkout',
    });
  }
  if (handlers.onCancel) {
    shortcuts.push({
      key: posShortcuts.CANCEL,
      handler: handlers.onCancel,
      description: 'Cancel',
    });
  }
  if (handlers.onFocusSearch) {
    shortcuts.push({
      key: posShortcuts.FOCUS_SEARCH,
      handler: handlers.onFocusSearch,
      description: 'Focus Search',
    });
  }
  if (handlers.onIncreaseQty) {
    shortcuts.push({
      key: posShortcuts.INCREASE_QTY,
      handler: handlers.onIncreaseQty,
      description: 'Increase Quantity',
    });
  }
  if (handlers.onDecreaseQty) {
    shortcuts.push({
      key: posShortcuts.DECREASE_QTY,
      handler: handlers.onDecreaseQty,
      description: 'Decrease Quantity',
    });
  }
  if (handlers.onRemoveItem) {
    shortcuts.push({
      key: posShortcuts.REMOVE_ITEM,
      handler: handlers.onRemoveItem,
      description: 'Remove Item',
    });
  }
  if (handlers.onPrint) {
    shortcuts.push({
      key: posShortcuts.PRINT,
      handler: handlers.onPrint,
      ctrlKey: true,
      description: 'Print',
    });
  }
  if (handlers.onSave) {
    shortcuts.push({
      key: posShortcuts.SAVE,
      handler: handlers.onSave,
      ctrlKey: true,
      description: 'Save',
    });
  }
  if (handlers.onUndo) {
    shortcuts.push({
      key: posShortcuts.UNDO,
      handler: handlers.onUndo,
      ctrlKey: true,
      description: 'Undo',
    });
  }
  if (handlers.onFullscreen) {
    shortcuts.push({
      key: posShortcuts.FULLSCREEN,
      handler: handlers.onFullscreen,
      description: 'Toggle Fullscreen',
    });
  }

  return shortcuts;
}

/**
 * Hook for barcode scanner input
 * Captures rapid keystrokes and treats them as barcode input
 */
export function useBarcodeInput(onBarcode: (barcode: string) => void, enabled: boolean = true) {
  const bufferRef = useRef<string>('');
  const timeoutRef = useRef<NodeJS.Timeout | null>(null);
  const lastKeyTimeRef = useRef<number>(0);
  const scanThreshold = 50; // Max ms between keystrokes for barcode
  const minLength = 4; // Minimum barcode length

  const handleKeyPress = useCallback((event: KeyboardEvent) => {
    if (!enabled) return;

    // Ignore if in input field (handled differently)
    const target = event.target as HTMLElement;
    if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA') {
      return;
    }

    const now = Date.now();
    const timeSinceLastKey = now - lastKeyTimeRef.current;
    lastKeyTimeRef.current = now;

    // If too much time has passed, reset buffer
    if (timeSinceLastKey > scanThreshold && bufferRef.current.length > 0) {
      bufferRef.current = '';
    }

    // Clear existing timeout
    if (timeoutRef.current) {
      clearTimeout(timeoutRef.current);
    }

    // Add character to buffer if it's a valid barcode character
    if (event.key.length === 1 && /[a-zA-Z0-9]/.test(event.key)) {
      bufferRef.current += event.key;
      event.preventDefault();
    }

    // Handle Enter key - submit barcode
    if (event.key === 'Enter' && bufferRef.current.length >= minLength) {
      onBarcode(bufferRef.current);
      bufferRef.current = '';
      event.preventDefault();
      return;
    }

    // Set timeout to clear buffer if no more input
    timeoutRef.current = setTimeout(() => {
      if (bufferRef.current.length >= minLength) {
        onBarcode(bufferRef.current);
      }
      bufferRef.current = '';
    }, 100);
  }, [enabled, onBarcode]);

  useEffect(() => {
    if (enabled) {
      window.addEventListener('keypress', handleKeyPress);
      return () => {
        window.removeEventListener('keypress', handleKeyPress);
        if (timeoutRef.current) {
          clearTimeout(timeoutRef.current);
        }
      };
    }
  }, [handleKeyPress, enabled]);
}
