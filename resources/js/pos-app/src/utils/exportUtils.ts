import * as XLSX from 'xlsx';
import { jsPDF } from 'jspdf';

/**
 * Export data to Excel file
 */
export function exportToExcel<T extends Record<string, any>>(
  data: T[],
  filename: string,
  headers?: { key: keyof T; label: string }[]
) {
  // Transform data if headers are provided
  const exportData = headers
    ? data.map((row) =>
        headers.reduce((acc, { key, label }) => {
          acc[label] = row[key];
          return acc;
        }, {} as Record<string, any>)
      )
    : data;

  const worksheet = XLSX.utils.json_to_sheet(exportData);
  const workbook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Data');

  // Auto-size columns
  const maxWidths: number[] = [];
  const rows = XLSX.utils.sheet_to_json(worksheet, { header: 1 }) as any[][];

  rows.forEach((row) => {
    row.forEach((cell, colIndex) => {
      const cellLength = String(cell).length;
      maxWidths[colIndex] = Math.max(maxWidths[colIndex] || 10, cellLength);
    });
  });

  worksheet['!cols'] = maxWidths.map((w) => ({ wch: Math.min(w + 2, 50) }));

  XLSX.writeFile(workbook, `${filename}.xlsx`);
}

/**
 * Export data to CSV file
 */
export function exportToCsv<T extends Record<string, any>>(
  data: T[],
  filename: string,
  headers?: { key: keyof T; label: string }[]
) {
  // Build header row
  const headerLabels = headers
    ? headers.map((h) => h.label)
    : Object.keys(data[0] || {});

  const headerKeys = headers ? headers.map((h) => h.key) : Object.keys(data[0] || {});

  // Build CSV content
  const csvRows = [
    headerLabels.join(','),
    ...data.map((row) =>
      headerKeys.map((key) => {
        const value = row[key as keyof T];
        // Escape values with commas or quotes
        if (typeof value === 'string' && (value.includes(',') || value.includes('"'))) {
          return `"${value.replace(/"/g, '""')}"`;
        }
        return value ?? '';
      }).join(',')
    ),
  ];

  const csvContent = csvRows.join('\n');
  downloadFile(csvContent, `${filename}.csv`, 'text/csv;charset=utf-8;');
}

/**
 * Export data to PDF file
 */
export function exportToPdf(
  title: string,
  content: {
    headers: string[];
    rows: (string | number)[][];
  },
  filename: string,
  options: {
    orientation?: 'portrait' | 'landscape';
    pageSize?: 'a4' | 'letter';
  } = {}
) {
  const { orientation = 'portrait', pageSize = 'a4' } = options;

  const doc = new jsPDF({
    orientation,
    unit: 'mm',
    format: pageSize,
  });

  // Add title
  doc.setFontSize(16);
  doc.text(title, 14, 15);

  // Add date
  doc.setFontSize(10);
  doc.text(`Generated: ${new Date().toLocaleDateString()}`, 14, 22);

  // Add table
  const startY = 30;
  const cellPadding = 3;
  const cellHeight = 8;
  const pageWidth = doc.internal.pageSize.getWidth();
  const pageHeight = doc.internal.pageSize.getHeight();
  const marginLeft = 14;
  const marginRight = 14;
  const tableWidth = pageWidth - marginLeft - marginRight;
  const columnWidth = tableWidth / content.headers.length;

  let y = startY;

  // Draw header
  doc.setFillColor(66, 139, 202);
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(10);

  content.headers.forEach((header, i) => {
    doc.rect(marginLeft + i * columnWidth, y, columnWidth, cellHeight, 'F');
    doc.text(header, marginLeft + i * columnWidth + cellPadding, y + cellHeight - cellPadding);
  });

  y += cellHeight;
  doc.setTextColor(0, 0, 0);
  doc.setFontSize(9);

  // Draw rows
  content.rows.forEach((row) => {
    // Check for page break
    if (y + cellHeight > pageHeight - 20) {
      doc.addPage();
      y = 20;
    }

    row.forEach((cell, i) => {
      doc.rect(marginLeft + i * columnWidth, y, columnWidth, cellHeight);
      const text = String(cell).substring(0, 20); // Truncate long text
      doc.text(text, marginLeft + i * columnWidth + cellPadding, y + cellHeight - cellPadding);
    });

    y += cellHeight;
  });

  doc.save(`${filename}.pdf`);
}

/**
 * Download a file
 */
function downloadFile(content: string, filename: string, mimeType: string) {
  const blob = new Blob([content], { type: mimeType });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}

/**
 * Print a table of data
 */
export function printTable(
  title: string,
  headers: string[],
  rows: (string | number)[][]
) {
  const tableHtml = `
    <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
      <thead>
        <tr style="background-color: #f0f0f0;">
          ${headers.map((h) => `<th>${h}</th>`).join('')}
        </tr>
      </thead>
      <tbody>
        ${rows.map((row) => `<tr>${row.map((cell) => `<td>${cell}</td>`).join('')}</tr>`).join('')}
      </tbody>
    </table>
  `;

  const printWindow = window.open('', '_blank');
  if (printWindow) {
    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>${title}</title>
        <style>
          body { font-family: Arial, sans-serif; padding: 20px; }
          h1 { font-size: 18px; margin-bottom: 10px; }
          table { font-size: 12px; }
          th { text-align: left; }
          @media print {
            body { padding: 0; }
          }
        </style>
      </head>
      <body>
        <h1>${title}</h1>
        <p>Generated: ${new Date().toLocaleDateString()}</p>
        ${tableHtml}
      </body>
      </html>
    `);
    printWindow.document.close();
    printWindow.print();
  }
}
