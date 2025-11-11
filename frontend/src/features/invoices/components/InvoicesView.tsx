'use client';

import { useEffect, useState, useCallback, useMemo } from 'react';
import { useTranslations } from 'next-intl';
import type { Invoice } from '@/lib/schemas/invoice';
import { listInvoices, downloadInvoice, resendInvoice } from '../api';
import { usePrivateChannel } from '@/hooks/use-echo';
import { notify } from '@/lib/notify';

type Status = 'idle' | 'loading' | 'error';

export default function InvoicesView() {
  const t = useTranslations('invoices');
  const [items, setItems] = useState<Invoice[]>([]);
  const [status, setStatus] = useState<Status>('idle');
  const [error, setError] = useState('');
  const [filterStatus, setFilterStatus] = useState<string>('all');
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedIds, setSelectedIds] = useState<number[]>([]);
  const [isExporting, setIsExporting] = useState(false);
  const [userId, setUserId] = useState<number | null>(null);
  const [authToken, setAuthToken] = useState<string>('');

  // Load auth context from localStorage (client-side only)
  useEffect(() => {
    if (typeof window === 'undefined') return;
    try {
      const token = localStorage.getItem('auth_token') || '';
      setAuthToken(token);
      const rawUser = localStorage.getItem('user');
      if (rawUser) {
        const u = JSON.parse(rawUser);
        if (u?.id) setUserId(Number(u.id));
      }
    } catch {}
  }, []);

  const channel = usePrivateChannel(userId ? `user.${userId}` : '', authToken, !!userId);

  useEffect(() => {
    loadInvoices();
  }, []);

  // Realtime handlers
  const handleInvoiceCreated = useCallback((data: any) => {
    if (!data?.invoice) return;
    setItems(prev => {
      if (prev.some(i => i.id === data.invoice.id)) return prev;
      return [data.invoice, ...prev];
    });
    notify.success?.({ title: t('title'), description: t('toasts.downloaded') || 'Invoice created' });
  }, [t]);

  const handleInvoiceUpdated = useCallback((data: any) => {
    if (!data?.invoice) return;
    setItems(prev => prev.map(i => i.id === data.invoice.id ? { ...i, ...data.invoice } : i));
  }, []);

  const handleInvoiceStatus = useCallback((data: any) => {
    if (!data?.invoice) return;
    setItems(prev => prev.map(i => i.id === data.invoice.id ? { ...i, status: data.invoice.status } : i));
  }, []);

  useEffect(() => {
    if (!channel) return;
    channel.listen('invoice.created', handleInvoiceCreated);
    channel.listen('invoice.updated', handleInvoiceUpdated);
    channel.listen('invoice.status.updated', handleInvoiceStatus);
    return () => {
      try {
        channel.stopListening('invoice.created');
        channel.stopListening('invoice.updated');
        channel.stopListening('invoice.status.updated');
      } catch {}
    };
  }, [channel, handleInvoiceCreated, handleInvoiceUpdated, handleInvoiceStatus]);

  const loadInvoices = async () => {
    setStatus('loading');
    setError('');
    try {
      const data = await listInvoices();
      setItems(data);
      setStatus('idle');
    } catch (e: any) {
      setError(e?.message || 'Failed to load invoices');
      setStatus('error');
    }
  };

  const handleDownload = async (id: number, invoiceNumber?: string) => {
    try {
      const blob = await downloadInvoice(id);
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `invoice_${invoiceNumber || id}.pdf`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);
    } catch (e: any) {
      alert(e?.message || t('toasts.downloadFailed') || 'Download failed');
    }
  };

  const handleResend = async (id: number) => {
    if (!confirm(t('confirmResend') || 'Resend this invoice to your email?')) return;
    try {
      await resendInvoice(id);
      alert(t('toasts.resent') || 'Invoice resent successfully');
    } catch (e: any) {
      alert(e?.message || 'Failed to resend invoice');
    }
  };

  const getStatusBadge = (invoiceStatus?: string) => {
    const st = invoiceStatus || 'pending';
    const colors: Record<string, string> = {
      paid: 'bg-green-100 text-green-800',
      pending: 'bg-yellow-100 text-yellow-800',
      overdue: 'bg-red-100 text-red-800',
      cancelled: 'bg-gray-100 text-gray-700',
    };
    return (
      <span
        className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${colors[st] || colors.pending}`}
      >
        {t(`status.${st}`) || st}
      </span>
    );
  };

  const filteredItems = useMemo(() => {
    let data = items;
    if (filterStatus !== 'all') {
      data = data.filter(inv => inv.status === filterStatus);
    }
    if (searchQuery.trim()) {
      const q = searchQuery.trim().toLowerCase();
      data = data.filter(inv =>
        (inv.invoiceNumber || String(inv.id)).toLowerCase().includes(q) ||
        (inv.propertyTitle || '').toLowerCase().includes(q)
      );
    }
    return data;
  }, [items, filterStatus, searchQuery]);

  const statusCounts = useMemo(() => {
    const counts: Record<string, number> = { paid: 0, pending: 0, overdue: 0, cancelled: 0 };
    for (const inv of items) {
      if (inv.status && counts[inv.status] !== undefined) counts[inv.status] += 1;
    }
    return counts;
  }, [items]);

  const allSelected = selectedIds.length > 0 && filteredItems.every(i => selectedIds.includes(i.id));

  const toggleSelect = (id: number) => {
    setSelectedIds(prev => prev.includes(id) ? prev.filter(x => x !== id) : [...prev, id]);
  };

  const toggleSelectAll = () => {
    if (allSelected) {
      setSelectedIds([]);
    } else {
      setSelectedIds(filteredItems.map(i => i.id));
    }
  };

  const exportCsv = () => {
    try {
      setIsExporting(true);
      const rows = [
        ['Invoice','Date','Property','Amount','Currency','Status']
      ];
      const exportSet = selectedIds.length ? items.filter(i => selectedIds.includes(i.id)) : filteredItems;
      for (const inv of exportSet) {
        rows.push([
          inv.invoiceNumber || `#${inv.id}`,
          inv.issuedAt ? new Date(inv.issuedAt).toISOString() : '',
          inv.propertyTitle || `Booking #${inv.bookingId || ''}`,
          String(inv.amount),
          inv.currency || 'USD',
          inv.status || ''
        ]);
      }
      const csv = rows.map(r => r.map(cell => '"'+String(cell).replace(/"/g,'""')+'"').join(',')).join('\n');
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `invoices_export_${Date.now()}.csv`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
      notify.success?.({ title: t('title'), description: t('toasts.exported') || 'Export complete' });
    } catch (e: any) {
      notify.error?.({ title: t('title'), description: e?.message || 'Export failed' });
    } finally {
      setIsExporting(false);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div className="space-y-1">
          <h1 className="text-2xl font-bold tracking-tight">{t('title') || 'Invoices'}</h1>
          <p className="text-sm text-muted-foreground">
            {t('subtitle') || 'View, filter, export and manage your invoices'}
          </p>
        </div>
        <div className="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
          <input
            type="text"
            placeholder={t('searchPlaceholder') || 'Search invoices...'}
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="px-3 py-2 border rounded-lg w-full sm:w-48 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <select
            value={filterStatus}
            onChange={(e) => setFilterStatus(e.target.value)}
            className="px-3 py-2 border rounded-lg w-full sm:w-40 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="all">{t('filters.all') || 'All'}</option>
            <option value="paid">{t('filters.paid') || 'Paid'}</option>
            <option value="pending">{t('filters.pending') || 'Pending'}</option>
            <option value="overdue">{t('status.overdue') || 'Overdue'}</option>
            <option value="cancelled">{t('status.cancelled') || 'Cancelled'}</option>
          </select>
          <button
            onClick={exportCsv}
            disabled={isExporting || status === 'loading'}
            className="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50"
          >
            {isExporting ? (t('actions.exporting') || 'Exporting…') : (t('actions.exportCsv') || 'Export CSV')}
          </button>
        </div>
      </div>

      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        <SummaryCard label={t('status.paid') || 'Paid'} value={statusCounts.paid} color="text-green-600" />
        <SummaryCard label={t('status.pending') || 'Pending'} value={statusCounts.pending} color="text-yellow-600" />
        <SummaryCard label={t('status.overdue') || 'Overdue'} value={statusCounts.overdue} color="text-red-600" />
        <SummaryCard label={t('status.cancelled') || 'Cancelled'} value={statusCounts.cancelled} color="text-gray-600" />
      </div>

      {status === 'loading' && (
        <div className="space-y-2">
          {Array.from({ length: 5 }).map((_, i) => (
            <div key={i} className="animate-pulse h-10 bg-gray-100 rounded" />
          ))}
        </div>
      )}
      {status === 'error' && <p className="text-red-600">{error}</p>}

      {status !== 'loading' && filteredItems.length === 0 && (
        <div className="text-center py-12">
          <p className="text-gray-500">{t('noInvoices') || 'No invoices yet'}</p>
          <p className="text-sm text-gray-400 mt-1">
            {t('noInvoicesDesc') || 'Your invoices will appear here after booking'}
          </p>
        </div>
      )}

      {filteredItems.length > 0 && status !== 'loading' && (
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <input
                    type="checkbox"
                    checked={allSelected}
                    aria-label="Select all"
                    onChange={toggleSelectAll}
                    className="accent-blue-600" />
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {t('table.invoice') || 'Invoice'}
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {t('table.date') || 'Date'}
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {t('table.property') || 'Property'}
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {t('table.amount') || 'Amount'}
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {t('table.status') || 'Status'}
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {t('table.actions') || 'Actions'}
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {filteredItems.map((invoice) => (
                <tr key={invoice.id} className={selectedIds.includes(invoice.id) ? 'bg-blue-50' : ''}>
                  <td className="px-3 py-4 whitespace-nowrap">
                    <input
                      type="checkbox"
                      checked={selectedIds.includes(invoice.id)}
                      onChange={() => toggleSelect(invoice.id)}
                      className="accent-blue-600"
                      aria-label={`Select invoice ${invoice.invoiceNumber || invoice.id}`}
                    />
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-mono">
                    {invoice.invoiceNumber || `#${invoice.id}`}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm">
                    {invoice.issuedAt
                      ? new Date(invoice.issuedAt).toLocaleDateString()
                      : 'N/A'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm">
                    {invoice.propertyTitle || `Booking #${invoice.bookingId || '—'}`}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm">
                    {invoice.amount} {invoice.currency || 'USD'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm">
                    {getStatusBadge(invoice.status)}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                    <button
                      onClick={() => handleDownload(invoice.id, invoice.invoiceNumber)}
                      className="inline-flex items-center gap-1 px-2 py-1 rounded border text-xs font-medium hover:bg-blue-50 text-blue-700 border-blue-200"
                    >
                      {t('actions.download') || 'Download'}
                    </button>
                    <button
                      onClick={() => handleResend(invoice.id)}
                      className="inline-flex items-center gap-1 px-2 py-1 rounded border text-xs font-medium hover:bg-indigo-50 text-indigo-700 border-indigo-200"
                    >
                      {t('actions.resend') || 'Resend'}
                    </button>
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

function SummaryCard({ label, value, color }: { label: string; value: number; color?: string }) {
  return (
    <div className="border rounded-lg p-4 bg-white shadow-sm flex flex-col gap-1">
      <span className="text-xs uppercase tracking-wide text-gray-500">{label}</span>
      <span className={`text-2xl font-semibold ${color || 'text-gray-800'}`}>{value}</span>
    </div>
  );
}
