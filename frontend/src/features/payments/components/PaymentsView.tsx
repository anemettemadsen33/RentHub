'use client';

import { useEffect, useState } from 'react';
import { useTranslations } from 'next-intl';
import type { Payment } from '@/lib/schemas/payment';
import { usePrivateChannel } from '@/hooks/use-echo';
import { listPayments, refundPayment } from '../api';

type Status = 'idle' | 'loading' | 'error';

export default function PaymentsView() {
  const t = useTranslations('payments');
  const [items, setItems] = useState<Payment[]>([]);
  const [status, setStatus] = useState<Status>('idle');
  const [error, setError] = useState('');
  const [filterStatus, setFilterStatus] = useState<string>('all');

  // Get user ID for private channel
  const userId = typeof window !== 'undefined' 
    ? JSON.parse(localStorage.getItem('user') || '{}')?.id 
    : null;
  const authToken = typeof window !== 'undefined'
    ? localStorage.getItem('auth_token') || ''
    : '';

  // Subscribe to payment updates
  const channel = usePrivateChannel(
    userId ? `user.${userId}` : '',
    authToken,
    !!userId
  );

  // Listen for payment status updates
  useEffect(() => {
    if (!channel) return;

    const handlePaymentStatusUpdated = (data: { payment: Payment }) => {
      setItems((prev) => prev.map(p => 
        p.id === data.payment.id ? data.payment : p
      ));
      
      // Show toast notification
      if (typeof window !== 'undefined' && data.payment.status) {
        const message = t(`statusUpdated.${data.payment.status}`) || 
          `Payment status updated to ${data.payment.status}`;
        // Simple alert for now - could be replaced with toast library
        console.log('[Payment Update]', message);
      }
    };

    channel.listen('payment.status.updated', handlePaymentStatusUpdated);

    return () => {
      channel.stopListening('payment.status.updated');
    };
  }, [channel, t]);

  useEffect(() => {
    loadPayments();
  }, []);

  const loadPayments = async () => {
    setStatus('loading');
    setError('');
    try {
      const data = await listPayments();
      setItems(data);
      setStatus('idle');
    } catch (e: any) {
      setError(e?.message || 'Failed to load payments');
      setStatus('error');
    }
  };

  const handleRefund = async (id: number) => {
    if (!confirm(t('confirmRefund') || 'Are you sure you want to request a refund?'))
      return;

    const oldItems = [...items];
    // Optimistic update
    setItems((prev) =>
      prev.map((p) => (p.id === id ? { ...p, status: 'refunded' } : p))
    );

    try {
      const updated = await refundPayment(id);
      setItems((prev) =>
        prev.map((p) => (p.id === id ? updated : p))
      );
    } catch (e: any) {
      setItems(oldItems);
      alert(e?.message || 'Failed to refund payment');
    }
  };

  const getStatusBadge = (paymentStatus?: string) => {
    const st = paymentStatus || 'pending';
    const colors: Record<string, string> = {
      pending: 'bg-yellow-100 text-yellow-800',
      completed: 'bg-green-100 text-green-800',
      failed: 'bg-red-100 text-red-800',
      refunded: 'bg-gray-100 text-gray-800',
      cancelled: 'bg-gray-100 text-gray-700',
    };
    return (
      <span
        className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${colors[st] || colors.pending}`}
      >
        {t(st) || st}
      </span>
    );
  };

  const filteredItems =
    filterStatus === 'all'
      ? items
      : items.filter((p) => p.status === filterStatus);

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">{t('title') || 'Payments'}</h1>
        <select
          value={filterStatus}
          onChange={(e) => setFilterStatus(e.target.value)}
          className="px-3 py-2 border rounded-lg"
        >
          <option value="all">{t('all') || 'All'}</option>
          <option value="pending">{t('pending') || 'Pending'}</option>
          <option value="completed">{t('completed') || 'Completed'}</option>
          <option value="failed">{t('failed') || 'Failed'}</option>
          <option value="refunded">{t('refunded') || 'Refunded'}</option>
          <option value="cancelled">{t('cancelled') || 'Cancelled'}</option>
        </select>
      </div>

      {status === 'loading' && <p>{t('loading') || 'Loading...'}</p>}
      {status === 'error' && <p className="text-red-600">{error}</p>}

      {status !== 'loading' && filteredItems.length === 0 && (
        <p className="text-gray-500">{t('noPayments') || 'No payments found.'}</p>
      )}

      {filteredItems.length > 0 && (
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {t('id') || 'ID'}
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {t('amount') || 'Amount'}
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {t('status') || 'Status'}
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {t('method') || 'Method'}
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {t('createdAt') || 'Date'}
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {t('actions') || 'Actions'}
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {filteredItems.map((payment) => (
                <tr key={payment.id}>
                  <td className="px-6 py-4 whitespace-nowrap text-sm">
                    {payment.id}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm">
                    {payment.amount} {payment.currency || 'USD'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm">
                    {getStatusBadge(payment.status)}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm">
                    {payment.method || 'N/A'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm">
                    {payment.createdAt
                      ? new Date(payment.createdAt).toLocaleDateString()
                      : 'N/A'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                    {payment.status === 'completed' && (
                      <button
                        onClick={() => handleRefund(payment.id)}
                        className="text-blue-600 hover:underline"
                      >
                        {t('refund') || 'Refund'}
                      </button>
                    )}
                    {payment.status !== 'completed' && (
                      <span className="text-gray-400">{t('noActions') || '—'}</span>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
