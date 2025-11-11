import { jsPDF } from 'jspdf';

export interface InvoiceData {
  invoiceNumber: string;
  date: string;
  dueDate?: string;
  
  // Company Info
  companyName: string;
  companyAddress: string;
  companyEmail: string;
  companyPhone: string;
  
  // Customer Info
  customerName: string;
  customerEmail: string;
  customerAddress?: string;
  
  // Items
  items: {
    description: string;
    quantity: number;
    price: number;
    total: number;
  }[];
  
  // Totals
  subtotal: number;
  tax?: number;
  taxRate?: number;
  total: number;
  
  // Discounts
  referralDiscountAmount?: number; // absolute amount discounted by referral
  loyaltyDiscountAmount?: number;  // absolute amount discounted by loyalty program
  referralDiscountPercent?: number; // percent for referral if applicable
  loyaltyDiscountPercent?: number;  // percent for loyalty if applicable
  
  // Payment Info
  paymentMethod?: string;
  bankDetails?: {
    bankName: string;
    accountName: string;
    accountNumber: string;
    iban?: string;
    swift?: string;
  };
  
  // Notes
  notes?: string;
}

export const generateInvoicePDF = (data: InvoiceData): void => {
  const doc = new jsPDF();
  
  // Set font
  doc.setFont('helvetica');
  
  // Header - Company Info
  doc.setFontSize(24);
  doc.setTextColor(37, 99, 235); // Blue
  doc.text('INVOICE', 20, 20);
  
  doc.setFontSize(10);
  doc.setTextColor(0, 0, 0);
  doc.text(data.companyName, 20, 30);
  doc.text(data.companyAddress, 20, 35);
  doc.text(data.companyEmail, 20, 40);
  doc.text(data.companyPhone, 20, 45);
  
  // Invoice Details
  doc.setFontSize(10);
  doc.text(`Invoice #: ${data.invoiceNumber}`, 140, 30);
  doc.text(`Date: ${data.date}`, 140, 35);
  if (data.dueDate) {
    doc.text(`Due Date: ${data.dueDate}`, 140, 40);
  }
  
  // Bill To
  doc.setFontSize(12);
  doc.setFont('helvetica', 'bold');
  doc.text('Bill To:', 20, 60);
  
  doc.setFontSize(10);
  doc.setFont('helvetica', 'normal');
  doc.text(data.customerName, 20, 67);
  doc.text(data.customerEmail, 20, 72);
  if (data.customerAddress) {
    doc.text(data.customerAddress, 20, 77);
  }
  
  // Table Header
  let yPos = 95;
  doc.setFillColor(37, 99, 235); // Blue
  doc.rect(20, yPos, 170, 8, 'F');
  
  doc.setTextColor(255, 255, 255); // White
  doc.setFont('helvetica', 'bold');
  doc.text('Description', 25, yPos + 5);
  doc.text('Qty', 130, yPos + 5);
  doc.text('Price', 150, yPos + 5);
  doc.text('Total', 175, yPos + 5, { align: 'right' });
  
  // Table Items
  doc.setTextColor(0, 0, 0);
  doc.setFont('helvetica', 'normal');
  yPos += 12;
  
  data.items.forEach((item) => {
    doc.text(item.description, 25, yPos);
    doc.text(item.quantity.toString(), 130, yPos);
    doc.text(`$${item.price.toFixed(2)}`, 150, yPos);
    doc.text(`$${item.total.toFixed(2)}`, 185, yPos, { align: 'right' });
    yPos += 7;
  });
  
  // Line
  yPos += 5;
  doc.line(20, yPos, 190, yPos);
  yPos += 10;
  
  // Totals
  doc.text('Subtotal:', 140, yPos);
  doc.text(`$${data.subtotal.toFixed(2)}`, 185, yPos, { align: 'right' });
  yPos += 7;

  // Discounts (if any)
  const referralLine = (data.referralDiscountAmount && data.referralDiscountAmount > 0);
  const loyaltyLine = (data.loyaltyDiscountAmount && data.loyaltyDiscountAmount > 0);
  if (referralLine) {
    doc.text('Referral Discount:', 140, yPos);
    doc.text(`-$${data.referralDiscountAmount!.toFixed(2)}`, 185, yPos, { align: 'right' });
    yPos += 7;
  }
  if (loyaltyLine) {
    doc.text('Loyalty Discount:', 140, yPos);
    doc.text(`-$${data.loyaltyDiscountAmount!.toFixed(2)}`, 185, yPos, { align: 'right' });
    yPos += 7;
  }
  
  if (data.tax && data.taxRate) {
    doc.text(`Tax (${data.taxRate}%):`, 140, yPos);
    doc.text(`$${data.tax.toFixed(2)}`, 185, yPos, { align: 'right' });
    yPos += 7;
  }
  
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(12);
  doc.text('Total:', 140, yPos);
  doc.text(`$${data.total.toFixed(2)}`, 185, yPos, { align: 'right' });
  
  // Payment Method
  if (data.paymentMethod) {
    yPos += 15;
    doc.setFontSize(10);
    doc.text(`Payment Method: ${data.paymentMethod}`, 20, yPos);
  }
  
  // Bank Details (if bank transfer)
  if (data.bankDetails) {
    yPos += 10;
    doc.setFont('helvetica', 'bold');
    doc.text('Bank Transfer Details:', 20, yPos);
    yPos += 7;
    
    doc.setFont('helvetica', 'normal');
    doc.text(`Bank: ${data.bankDetails.bankName}`, 20, yPos);
    yPos += 5;
    doc.text(`Account Name: ${data.bankDetails.accountName}`, 20, yPos);
    yPos += 5;
    doc.text(`Account Number: ${data.bankDetails.accountNumber}`, 20, yPos);
    
    if (data.bankDetails.iban) {
      yPos += 5;
      doc.text(`IBAN: ${data.bankDetails.iban}`, 20, yPos);
    }
    
    if (data.bankDetails.swift) {
      yPos += 5;
      doc.text(`SWIFT/BIC: ${data.bankDetails.swift}`, 20, yPos);
    }
  }
  
  // Notes
  if (data.notes) {
    yPos += 15;
    doc.setFont('helvetica', 'bold');
    doc.text('Notes:', 20, yPos);
    yPos += 7;
    
    doc.setFont('helvetica', 'normal');
    const splitNotes = doc.splitTextToSize(data.notes, 170);
    doc.text(splitNotes, 20, yPos);
  }
  
  // Footer
  doc.setFontSize(8);
  doc.setTextColor(128, 128, 128);
  doc.text('Thank you for your business!', 105, 280, { align: 'center' });
  
  // Save
  doc.save(`invoice-${data.invoiceNumber}.pdf`);
};

export const previewInvoicePDF = (data: InvoiceData): void => {
  const doc = new jsPDF();
  
  // Same generation logic as above
  doc.setFont('helvetica');
  
  doc.setFontSize(24);
  doc.setTextColor(37, 99, 235);
  doc.text('INVOICE', 20, 20);
  
  doc.setFontSize(10);
  doc.setTextColor(0, 0, 0);
  doc.text(data.companyName, 20, 30);
  doc.text(data.companyAddress, 20, 35);
  doc.text(data.companyEmail, 20, 40);
  doc.text(data.companyPhone, 20, 45);
  
  doc.setFontSize(10);
  doc.text(`Invoice #: ${data.invoiceNumber}`, 140, 30);
  doc.text(`Date: ${data.date}`, 140, 35);
  if (data.dueDate) {
    doc.text(`Due Date: ${data.dueDate}`, 140, 40);
  }
  
  doc.setFontSize(12);
  doc.setFont('helvetica', 'bold');
  doc.text('Bill To:', 20, 60);
  
  doc.setFontSize(10);
  doc.setFont('helvetica', 'normal');
  doc.text(data.customerName, 20, 67);
  doc.text(data.customerEmail, 20, 72);
  if (data.customerAddress) {
    doc.text(data.customerAddress, 20, 77);
  }
  
  let yPos = 95;
  doc.setFillColor(37, 99, 235);
  doc.rect(20, yPos, 170, 8, 'F');
  
  doc.setTextColor(255, 255, 255);
  doc.setFont('helvetica', 'bold');
  doc.text('Description', 25, yPos + 5);
  doc.text('Qty', 130, yPos + 5);
  doc.text('Price', 150, yPos + 5);
  doc.text('Total', 175, yPos + 5, { align: 'right' });
  
  doc.setTextColor(0, 0, 0);
  doc.setFont('helvetica', 'normal');
  yPos += 12;
  
  data.items.forEach((item) => {
    doc.text(item.description, 25, yPos);
    doc.text(item.quantity.toString(), 130, yPos);
    doc.text(`$${item.price.toFixed(2)}`, 150, yPos);
    doc.text(`$${item.total.toFixed(2)}`, 185, yPos, { align: 'right' });
    yPos += 7;
  });
  
  yPos += 5;
  doc.line(20, yPos, 190, yPos);
  yPos += 10;
  
  doc.text('Subtotal:', 140, yPos);
  doc.text(`$${data.subtotal.toFixed(2)}`, 185, yPos, { align: 'right' });
  yPos += 7;
  
  if (data.tax && data.taxRate) {
    doc.text(`Tax (${data.taxRate}%):`, 140, yPos);
    doc.text(`$${data.tax.toFixed(2)}`, 185, yPos, { align: 'right' });
    yPos += 7;
  }
  
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(12);
  doc.text('Total:', 140, yPos);
  doc.text(`$${data.total.toFixed(2)}`, 185, yPos, { align: 'right' });
  
  if (data.paymentMethod) {
    yPos += 15;
    doc.setFontSize(10);
    doc.text(`Payment Method: ${data.paymentMethod}`, 20, yPos);
  }
  
  if (data.bankDetails) {
    yPos += 10;
    doc.setFont('helvetica', 'bold');
    doc.text('Bank Transfer Details:', 20, yPos);
    yPos += 7;
    
    doc.setFont('helvetica', 'normal');
    doc.text(`Bank: ${data.bankDetails.bankName}`, 20, yPos);
    yPos += 5;
    doc.text(`Account Name: ${data.bankDetails.accountName}`, 20, yPos);
    yPos += 5;
    doc.text(`Account Number: ${data.bankDetails.accountNumber}`, 20, yPos);
    
    if (data.bankDetails.iban) {
      yPos += 5;
      doc.text(`IBAN: ${data.bankDetails.iban}`, 20, yPos);
    }
    
    if (data.bankDetails.swift) {
      yPos += 5;
      doc.text(`SWIFT/BIC: ${data.bankDetails.swift}`, 20, yPos);
    }
  }
  
  if (data.notes) {
    yPos += 15;
    doc.setFont('helvetica', 'bold');
    doc.text('Notes:', 20, yPos);
    yPos += 7;
    
    doc.setFont('helvetica', 'normal');
    const splitNotes = doc.splitTextToSize(data.notes, 170);
    doc.text(splitNotes, 20, yPos);
  }
  
  doc.setFontSize(8);
  doc.setTextColor(128, 128, 128);
  doc.text('Thank you for your business!', 105, 280, { align: 'center' });
  
  // Open in new window instead of download
  window.open(doc.output('bloburl'), '_blank');
};
