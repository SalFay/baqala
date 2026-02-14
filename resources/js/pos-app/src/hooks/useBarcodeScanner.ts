import { useState, useEffect, useCallback, useRef } from 'react';

interface UseBarcodeScannerOptions {
  onScan: (barcode: string) => void;
  minLength?: number;
  maxLength?: number;
  timeout?: number;
  enabled?: boolean;
}

/**
 * Hook for handling barcode scanner input
 *
 * Barcode scanners typically work by sending keystrokes very quickly.
 * This hook detects rapid sequential input and treats it as a barcode scan.
 */
export function useBarcodeScanner({
  onScan,
  minLength = 4,
  maxLength = 50,
  timeout = 100,
  enabled = true,
}: UseBarcodeScannerOptions) {
  const [buffer, setBuffer] = useState('');
  const [isScanning, setIsScanning] = useState(false);
  const timeoutRef = useRef<NodeJS.Timeout | null>(null);
  const lastKeyTime = useRef<number>(0);

  const resetBuffer = useCallback(() => {
    setBuffer('');
    setIsScanning(false);
    if (timeoutRef.current) {
      clearTimeout(timeoutRef.current);
      timeoutRef.current = null;
    }
  }, []);

  const processBuffer = useCallback(
    (currentBuffer: string) => {
      if (currentBuffer.length >= minLength && currentBuffer.length <= maxLength) {
        onScan(currentBuffer);
      }
      resetBuffer();
    },
    [minLength, maxLength, onScan, resetBuffer]
  );

  useEffect(() => {
    if (!enabled) {
      resetBuffer();
      return;
    }

    const handleKeyDown = (event: KeyboardEvent) => {
      const now = Date.now();
      const timeDiff = now - lastKeyTime.current;
      lastKeyTime.current = now;

      // Ignore if user is typing in a specific input (not barcode input)
      const target = event.target as HTMLElement;
      if (target.tagName === 'INPUT') {
        if (!(target as HTMLInputElement).dataset.barcodeInput) {
          return; // Skip normal inputs without barcode-input attribute
        }
      } else if (target.tagName === 'TEXTAREA' || target.isContentEditable) {
        return; // Skip textareas and contenteditable elements
      }

      // Handle Enter key - process buffer if we have content
      if (event.key === 'Enter') {
        if (buffer.length >= minLength) {
          event.preventDefault();
          processBuffer(buffer);
        }
        return;
      }

      // Only accept printable characters
      if (event.key.length !== 1) {
        return;
      }

      // If time between keys is very short, it's likely a scanner
      const isRapidInput = timeDiff < timeout;

      // Start new buffer if this is a new scan
      if (!isScanning && isRapidInput) {
        setIsScanning(true);
      }

      // Add to buffer
      setBuffer((prev) => {
        const newBuffer = prev + event.key;

        // Clear any existing timeout
        if (timeoutRef.current) {
          clearTimeout(timeoutRef.current);
        }

        // Set timeout to process buffer after input stops
        timeoutRef.current = setTimeout(() => {
          if (newBuffer.length >= minLength) {
            processBuffer(newBuffer);
          } else {
            resetBuffer();
          }
        }, timeout * 2);

        // Limit buffer size
        if (newBuffer.length > maxLength) {
          resetBuffer();
          return '';
        }

        return newBuffer;
      });
    };

    window.addEventListener('keydown', handleKeyDown);

    return () => {
      window.removeEventListener('keydown', handleKeyDown);
      if (timeoutRef.current) {
        clearTimeout(timeoutRef.current);
      }
    };
  }, [enabled, buffer, isScanning, minLength, maxLength, timeout, processBuffer, resetBuffer]);

  return {
    buffer,
    isScanning,
    resetBuffer,
  };
}

/**
 * Simplified barcode input hook for dedicated barcode input fields
 */
export function useBarcodeInput(onScan: (barcode: string) => void) {
  const [value, setValue] = useState('');
  const inputRef = useRef<HTMLInputElement>(null);

  const handleKeyDown = useCallback(
    (event: React.KeyboardEvent<HTMLInputElement>) => {
      if (event.key === 'Enter' && value.trim()) {
        event.preventDefault();
        onScan(value.trim());
        setValue('');
      }
    },
    [value, onScan]
  );

  const handleChange = useCallback((event: React.ChangeEvent<HTMLInputElement>) => {
    setValue(event.target.value);
  }, []);

  const focus = useCallback(() => {
    inputRef.current?.focus();
  }, []);

  const clear = useCallback(() => {
    setValue('');
  }, []);

  return {
    value,
    setValue,
    inputRef,
    inputProps: {
      ref: inputRef,
      value,
      onChange: handleChange,
      onKeyDown: handleKeyDown,
      'data-barcode-input': 'true',
    },
    focus,
    clear,
  };
}
