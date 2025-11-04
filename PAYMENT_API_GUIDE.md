# Payment System API Guide

Complete guide for integrating the Payment System with your Next.js frontend.

---

## 📋 Table of Contents

1. [Payment Endpoints](#payment-endpoints)
2. [Invoice Endpoints](#invoice-endpoints)
3. [Usage Examples](#usage-examples)
4. [TypeScript Types](#typescript-types)
5. [Error Handling](#error-handling)

---

## 🔐 Authentication

All payment endpoints require authentication. Include the Bearer token in headers:

```typescript
headers: {
  'Authorization': `Bearer ${token}`,
  'Content-Type': 'application/json',
}
```

---

## 💳 Payment Endpoints

### 1. Get User Payments

**GET** `/api/v1/payments`

Returns paginated list of user's payments.

```typescript
const getPayments = async () => {
  const response = await axios.get('/api/v1/payments', {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });
  return response.data;
};

// Response
{
  "data": [
    {
      "id": 1,
      "payment_number": "PAY2025110001",
      "booking_id": 1,
      "invoice_id": 1,
      "amount": "150.00",
      "currency": "EUR",
      "type": "full",
      "status": "completed",
      "payment_method": "bank_transfer",
      "completed_at": "2025-11-02T10:30:00.000000Z",
      "booking": {
        "id": 1,
        "property": {
          "id": 1,
          "title": "Luxury Apartment"
        }
      }
    }
  ],
  "links": {...},
  "meta": {...}
}
```

---

### 2. Create Payment

**POST** `/api/v1/payments`

Create a new payment for a booking.

```typescript
interface CreatePaymentData {
  booking_id: number;
  amount: number;
  payment_method: 'bank_transfer' | 'paypal' | 'cash';
  type: 'full' | 'deposit' | 'balance';
  bank_reference?: string;
  notes?: string;
}

const createPayment = async (data: CreatePaymentData) => {
  const response = await axios.post('/api/v1/payments', data, {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });
  return response.data;
};

// Example Usage
const payment = await createPayment({
  booking_id: 1,
  amount: 150.00,
  payment_method: 'bank_transfer',
  type: 'full',
  bank_reference: 'TRX123456789',
  notes: 'Payment for booking #1',
});

// Response
{
  "message": "Payment initiated successfully",
  "payment": {
    "id": 1,
    "payment_number": "PAY2025110001",
    "booking_id": 1,
    "invoice_id": 1,
    "amount": "150.00",
    "status": "pending",
    // ... other fields
  }
}
```

---

### 3. Get Payment Details

**GET** `/api/v1/payments/{id}`

Get detailed information about a specific payment.

```typescript
const getPayment = async (paymentId: number) => {
  const response = await axios.get(`/api/v1/payments/${paymentId}`, {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });
  return response.data;
};

// Response
{
  "id": 1,
  "payment_number": "PAY2025110001",
  "booking": {...},
  "invoice": {...},
  "amount": "150.00",
  "status": "completed",
  // ... all fields
}
```

---

### 4. Update Payment Status

**POST** `/api/v1/payments/{id}/status`

Update the status of a payment (admin or owner).

```typescript
interface UpdatePaymentStatusData {
  status: 'processing' | 'completed' | 'failed';
  transaction_id?: string;
  failure_reason?: string; // Required if status is 'failed'
}

const updatePaymentStatus = async (
  paymentId: number, 
  data: UpdatePaymentStatusData
) => {
  const response = await axios.post(
    `/api/v1/payments/${paymentId}/status`,
    data,
    {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    }
  );
  return response.data;
};

// Example: Mark as completed
await updatePaymentStatus(1, {
  status: 'completed',
  transaction_id: 'STRIPE_CH_123456',
});

// Example: Mark as failed
await updatePaymentStatus(1, {
  status: 'failed',
  failure_reason: 'Insufficient funds',
});
```

---

## 📄 Invoice Endpoints

### 1. Get User Invoices

**GET** `/api/v1/invoices`

Returns paginated list of user's invoices.

```typescript
const getInvoices = async () => {
  const response = await axios.get('/api/v1/invoices', {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });
  return response.data;
};

// Response
{
  "data": [
    {
      "id": 1,
      "invoice_number": "2025110001",
      "booking_id": 1,
      "total_amount": "150.00",
      "status": "sent",
      "invoice_date": "2025-11-02",
      "due_date": "2025-11-09",
      "pdf_path": "invoices/2025110001.pdf",
      "sent_at": "2025-11-02T10:00:00.000000Z",
      "booking": {...},
      "bank_account": {...}
    }
  ],
  "links": {...},
  "meta": {...}
}
```

---

### 2. Get Invoice Details

**GET** `/api/v1/invoices/{id}`

Get detailed information about a specific invoice.

```typescript
const getInvoice = async (invoiceId: number) => {
  const response = await axios.get(`/api/v1/invoices/${invoiceId}`, {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });
  return response.data;
};

// Response
{
  "id": 1,
  "invoice_number": "2025110001",
  "booking": {
    "id": 1,
    "property": {
      "id": 1,
      "title": "Luxury Apartment"
    }
  },
  "bank_account": {
    "account_name": "RentHub Ltd",
    "iban": "RO49 AAAA 1B31 0075 9384 0000",
    "bic_swift": "AAAROBU",
    "bank_name": "ING Bank Romania"
  },
  "payments": [...],
  "subtotal": "120.00",
  "cleaning_fee": "20.00",
  "security_deposit": "10.00",
  "total_amount": "150.00",
  "status": "sent"
}
```

---

### 3. Download Invoice PDF

**GET** `/api/v1/invoices/{id}/download`

Download the invoice as a PDF file.

```typescript
const downloadInvoice = async (invoiceId: number) => {
  const response = await axios.get(
    `/api/v1/invoices/${invoiceId}/download`,
    {
      responseType: 'blob',
      headers: {
        Authorization: `Bearer ${token}`,
      },
    }
  );

  // Create download link
  const url = window.URL.createObjectURL(new Blob([response.data]));
  const link = document.createElement('a');
  link.href = url;
  link.setAttribute('download', `invoice-${invoiceId}.pdf`);
  document.body.appendChild(link);
  link.click();
  link.remove();
  window.URL.revokeObjectURL(url);
};
```

---

### 4. Resend Invoice Email

**POST** `/api/v1/invoices/{id}/resend`

Resend the invoice email with PDF attachment.

```typescript
const resendInvoice = async (invoiceId: number) => {
  const response = await axios.post(
    `/api/v1/invoices/${invoiceId}/resend`,
    {},
    {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    }
  );
  return response.data;
};

// Response
{
  "message": "Invoice resent successfully",
  "invoice": {
    "id": 1,
    "sent_at": "2025-11-02T15:30:00.000000Z",
    "send_count": 2
  }
}
```

---

## 📦 TypeScript Types

```typescript
// Payment Types
export interface Payment {
  id: number;
  payment_number: string;
  booking_id: number;
  invoice_id: number | null;
  user_id: number;
  amount: string;
  currency: string;
  type: 'full' | 'deposit' | 'balance' | 'refund';
  status: 'pending' | 'processing' | 'completed' | 'failed' | 'refunded';
  payment_method: 'bank_transfer' | 'paypal' | 'cash';
  payment_gateway?: string;
  transaction_id?: string;
  gateway_reference?: string;
  bank_reference?: string;
  bank_receipt?: string;
  initiated_at?: string;
  completed_at?: string;
  failed_at?: string;
  refunded_at?: string;
  failure_reason?: string;
  notes?: string;
  metadata?: Record<string, any>;
  created_at: string;
  updated_at: string;
  booking?: Booking;
  invoice?: Invoice;
}

// Invoice Types
export interface Invoice {
  id: number;
  invoice_number: string;
  booking_id: number;
  user_id: number;
  property_id: number;
  bank_account_id: number | null;
  invoice_date: string;
  due_date: string;
  status: 'draft' | 'sent' | 'paid' | 'cancelled' | 'overdue';
  subtotal: string;
  cleaning_fee: string;
  security_deposit: string;
  taxes: string;
  total_amount: string;
  currency: string;
  customer_name: string;
  customer_email: string;
  customer_phone?: string;
  customer_address?: string;
  property_title: string;
  property_address?: string;
  paid_at?: string;
  payment_method?: string;
  payment_reference?: string;
  pdf_path?: string;
  sent_at?: string;
  send_count: number;
  notes?: string;
  created_at: string;
  updated_at: string;
  booking?: Booking;
  bank_account?: BankAccount;
  payments?: Payment[];
}

// Bank Account Types
export interface BankAccount {
  id: number;
  user_id: number | null;
  account_name: string;
  account_holder_name: string;
  iban: string;
  bic_swift: string;
  bank_name: string;
  bank_address?: string;
  currency: string;
  is_default: boolean;
  is_active: boolean;
  account_type: 'business' | 'personal';
  notes?: string;
  formatted_iban?: string;
  created_at: string;
  updated_at: string;
}
```

---

## 🎨 React Components Examples

### Payment Form Component

```typescript
import { useState } from 'react';
import axios from 'axios';

interface PaymentFormProps {
  bookingId: number;
  amount: number;
  onSuccess: (payment: Payment) => void;
}

export const PaymentForm: React.FC<PaymentFormProps> = ({
  bookingId,
  amount,
  onSuccess,
}) => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    const formData = new FormData(e.currentTarget);

    try {
      const response = await axios.post('/api/v1/payments', {
        booking_id: bookingId,
        amount: amount,
        payment_method: formData.get('payment_method'),
        type: 'full',
        bank_reference: formData.get('bank_reference'),
        notes: formData.get('notes'),
      }, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });

      onSuccess(response.data.payment);
    } catch (err: any) {
      setError(err.response?.data?.error || 'Failed to create payment');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div>
        <label>Payment Method</label>
        <select name="payment_method" required>
          <option value="bank_transfer">Bank Transfer</option>
          <option value="paypal">PayPal</option>
          <option value="cash">Cash</option>
        </select>
      </div>

      <div>
        <label>Bank Reference (Optional)</label>
        <input type="text" name="bank_reference" />
      </div>

      <div>
        <label>Notes (Optional)</label>
        <textarea name="notes" />
      </div>

      {error && <p className="text-red-500">{error}</p>}

      <button type="submit" disabled={loading}>
        {loading ? 'Processing...' : `Pay ${amount} EUR`}
      </button>
    </form>
  );
};
```

---

### Invoice List Component

```typescript
import { useEffect, useState } from 'react';
import axios from 'axios';

export const InvoiceList: React.FC = () => {
  const [invoices, setInvoices] = useState<Invoice[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchInvoices = async () => {
      try {
        const response = await axios.get('/api/v1/invoices', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        });
        setInvoices(response.data.data);
      } catch (error) {
        console.error('Failed to fetch invoices', error);
      } finally {
        setLoading(false);
      }
    };

    fetchInvoices();
  }, []);

  const handleDownload = async (invoiceId: number) => {
    try {
      const response = await axios.get(
        `/api/v1/invoices/${invoiceId}/download`,
        {
          responseType: 'blob',
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        }
      );

      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `invoice-${invoiceId}.pdf`);
      document.body.appendChild(link);
      link.click();
      link.remove();
    } catch (error) {
      console.error('Failed to download invoice', error);
    }
  };

  const handleResend = async (invoiceId: number) => {
    try {
      await axios.post(
        `/api/v1/invoices/${invoiceId}/resend`,
        {},
        {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        }
      );
      alert('Invoice resent successfully!');
    } catch (error) {
      console.error('Failed to resend invoice', error);
    }
  };

  if (loading) return <div>Loading...</div>;

  return (
    <div className="space-y-4">
      <h2 className="text-2xl font-bold">My Invoices</h2>
      
      {invoices.map((invoice) => (
        <div key={invoice.id} className="border p-4 rounded">
          <div className="flex justify-between items-center">
            <div>
              <h3 className="font-semibold">#{invoice.invoice_number}</h3>
              <p className="text-sm text-gray-600">
                {invoice.property_title}
              </p>
              <p className="text-lg font-bold">
                {invoice.total_amount} {invoice.currency}
              </p>
              <span className={`badge badge-${invoice.status}`}>
                {invoice.status}
              </span>
            </div>
            
            <div className="space-x-2">
              <button
                onClick={() => handleDownload(invoice.id)}
                className="btn btn-primary"
              >
                Download PDF
              </button>
              
              <button
                onClick={() => handleResend(invoice.id)}
                className="btn btn-secondary"
              >
                Resend Email
              </button>
            </div>
          </div>
        </div>
      ))}
    </div>
  );
};
```

---

## ⚠️ Error Handling

### Common Error Responses

```typescript
// 401 Unauthorized
{
  "message": "Unauthenticated."
}

// 403 Forbidden
{
  "error": "Unauthorized"
}

// 422 Validation Error
{
  "errors": {
    "booking_id": ["The booking id field is required."],
    "amount": ["The amount must be a number."]
  }
}

// 500 Server Error
{
  "error": "Failed to create payment: [error message]"
}
```

### Error Handler Example

```typescript
const handleApiError = (error: any) => {
  if (error.response) {
    // Server responded with error
    const status = error.response.status;
    const data = error.response.data;

    switch (status) {
      case 401:
        // Redirect to login
        router.push('/login');
        break;
      case 403:
        alert('You do not have permission to perform this action');
        break;
      case 422:
        // Handle validation errors
        const errors = data.errors;
        Object.keys(errors).forEach(key => {
          alert(errors[key][0]);
        });
        break;
      case 500:
        alert(data.error || 'Server error occurred');
        break;
      default:
        alert('An error occurred');
    }
  } else {
    // Network error
    alert('Network error. Please check your connection.');
  }
};
```

---

## 🎯 Best Practices

1. **Always handle errors gracefully**
2. **Show loading states during API calls**
3. **Validate data on frontend before sending**
4. **Use TypeScript types for type safety**
5. **Store token securely (httpOnly cookies preferred)**
6. **Implement request retry logic for failed requests**
7. **Show success messages after operations**
8. **Cache invoice PDFs locally if needed**

---

## 📞 Support

For API issues or questions:
- Check backend logs: `storage/logs/laravel.log`
- Review error responses
- Contact backend team

---

**API Version**: 1.0.0  
**Last Updated**: November 2, 2025
