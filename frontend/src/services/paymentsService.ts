import { apiClient } from '@/lib/api-client';

export interface PaymentIntent {
  clientSecret: string;
  amount: number;
  currency: string;
  booking_id: string;
}

export interface CreatePaymentData {
  booking_id: string;
  amount: number;
  payment_method: 'bank_transfer' | 'paypal' | 'cash' | 'stripe' | 'card';
  type?: 'full' | 'deposit' | 'balance';
  bank_reference?: string;
  stripe_payment_intent_id?: string;
  notes?: string;
}

export interface Payment {
  id: string;
  booking_id: string;
  amount: number;
  status: 'pending' | 'processing' | 'completed' | 'failed' | 'refunded';
  payment_method: string;
  type: string;
  created_at: string;
}

class PaymentsService {
  /**
   * Create a Stripe Payment Intent for a booking
   */
  async createPaymentIntent(bookingId: string): Promise<PaymentIntent> {
    const response = await apiClient.post<PaymentIntent>('/payments/create-intent', {
      booking_id: bookingId,
    });
    return response.data;
  }

  /**
   * Create a payment record after successful payment
   */
  async createPayment(data: CreatePaymentData): Promise<Payment> {
    const response = await apiClient.post<Payment>('/payments', data);
    return response.data;
  }

  /**
   * Get payment details
   */
  async getPayment(paymentId: string): Promise<Payment> {
    const response = await apiClient.get<Payment>(`/payments/${paymentId}`);
    return response.data;
  }

  /**
   * Get all payments for the current user
   */
  async getPayments(): Promise<Payment[]> {
    const response = await apiClient.get<{ success: boolean; data: Payment[] }>(
      '/payments'
    );
    return response.data.data;
  }

  /**
   * Update payment status
   */
  async updateStatus(
    paymentId: string,
    status: 'processing' | 'completed' | 'failed',
    transactionId?: string,
    failureReason?: string
  ): Promise<Payment> {
    const response = await apiClient.post<Payment>(`/payments/${paymentId}/status`, {
      status,
      transaction_id: transactionId,
      failure_reason: failureReason,
    });
    return response.data;
  }

  /**
   * Confirm payment
   */
  async confirmPayment(paymentId: string): Promise<Payment> {
    const response = await apiClient.post<Payment>(`/payments/${paymentId}/confirm`);
    return response.data;
  }

  /**
   * Refund payment
   */
  async refundPayment(paymentId: string, reason?: string): Promise<Payment> {
    const response = await apiClient.post<Payment>(`/payments/${paymentId}/refund`, {
      reason,
    });
    return response.data;
  }
}

export const paymentsService = new PaymentsService();
