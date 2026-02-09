import { useCallback, useRef } from 'react';

interface PrintOptions {
  title?: string;
  styles?: string;
  onBeforePrint?: () => void;
  onAfterPrint?: () => void;
}

/**
 * Hook for printing content
 * Supports both inline printing and iframe-based printing
 */
export function usePrint() {
  const frameRef = useRef<HTMLIFrameElement | null>(null);

  /**
   * Print content using a hidden iframe
   */
  const printContent = useCallback(
    (content: string, options: PrintOptions = {}) => {
      const { title = 'Print', styles = '', onBeforePrint, onAfterPrint } = options;

      // Create iframe if it doesn't exist
      if (!frameRef.current) {
        const frame = document.createElement('iframe');
        frame.style.position = 'absolute';
        frame.style.top = '-10000px';
        frame.style.left = '-10000px';
        frame.style.width = '0';
        frame.style.height = '0';
        document.body.appendChild(frame);
        frameRef.current = frame;
      }

      const frame = frameRef.current;
      const doc = frame.contentDocument || frame.contentWindow?.document;

      if (!doc) {
        console.error('Could not access iframe document');
        return;
      }

      // Build the print document
      const html = `
        <!DOCTYPE html>
        <html>
        <head>
          <title>${title}</title>
          <style>
            @media print {
              body { margin: 0; padding: 0; }
            }
            ${getDefaultPrintStyles()}
            ${styles}
          </style>
        </head>
        <body>
          ${content}
        </body>
        </html>
      `;

      doc.open();
      doc.write(html);
      doc.close();

      // Wait for content to load
      frame.onload = () => {
        try {
          onBeforePrint?.();
          frame.contentWindow?.focus();
          frame.contentWindow?.print();
          onAfterPrint?.();
        } catch (error) {
          console.error('Print failed:', error);
        }
      };
    },
    []
  );

  /**
   * Print a specific element by reference
   */
  const printElement = useCallback(
    (element: HTMLElement | null, options: PrintOptions = {}) => {
      if (!element) {
        console.error('No element to print');
        return;
      }

      printContent(element.innerHTML, options);
    },
    [printContent]
  );

  /**
   * Open print dialog for the current page
   */
  const printPage = useCallback(() => {
    window.print();
  }, []);

  return {
    printContent,
    printElement,
    printPage,
  };
}

/**
 * Default print styles for receipts
 */
function getDefaultPrintStyles(): string {
  return `
    body {
      font-family: 'Courier New', monospace;
      font-size: 12px;
      line-height: 1.4;
    }

    .receipt {
      width: 80mm;
      max-width: 100%;
      margin: 0 auto;
    }

    .receipt-header {
      text-align: center;
      margin-bottom: 10px;
    }

    .receipt-title {
      font-size: 16px;
      font-weight: bold;
    }

    .receipt-info {
      font-size: 10px;
    }

    .receipt-divider {
      border-top: 1px dashed #000;
      margin: 8px 0;
    }

    .receipt-items {
      width: 100%;
    }

    .receipt-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 4px;
    }

    .receipt-item-name {
      flex: 1;
    }

    .receipt-item-qty {
      width: 30px;
      text-align: center;
    }

    .receipt-item-price {
      width: 60px;
      text-align: right;
    }

    .receipt-totals {
      margin-top: 10px;
    }

    .receipt-total-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 2px;
    }

    .receipt-grand-total {
      font-weight: bold;
      font-size: 14px;
      border-top: 1px solid #000;
      padding-top: 4px;
      margin-top: 4px;
    }

    .receipt-footer {
      text-align: center;
      margin-top: 10px;
      font-size: 10px;
    }

    .receipt-qr {
      text-align: center;
      margin-top: 10px;
    }

    .receipt-qr img {
      max-width: 100px;
    }

    @media print {
      @page {
        size: 80mm auto;
        margin: 0;
      }
    }
  `;
}

/**
 * Hook for receipt-specific printing
 */
export function useReceiptPrint() {
  const { printContent } = usePrint();

  const printReceipt = useCallback(
    (receiptHtml: string, shopName?: string) => {
      printContent(receiptHtml, {
        title: `Receipt - ${shopName || 'Baqala POS'}`,
        styles: getDefaultPrintStyles(),
      });
    },
    [printContent]
  );

  return { printReceipt };
}
