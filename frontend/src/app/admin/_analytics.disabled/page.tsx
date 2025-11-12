"use client";

import { useEffect, useMemo, useState, useCallback } from 'react';
import { DashboardSkeleton } from '@/components/skeletons';
import apiClient from '@/lib/api-client';
import { useAuth } from '@/contexts/auth-context';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { RateLimiterWidget } from '@/components/admin/rate-limiter-widget';
import {
  LineChart,
  Line,
  CartesianGrid,
  XAxis,
  YAxis,
  Tooltip,
  ResponsiveContainer,
  BarChart,
  Bar,
  PieChart,
  Pie,
  Cell,
  Legend,
} from 'recharts';

interface AnalyticsEvent {
  type: string;
  payload?: any;
  timestamp: string;
  user?: { id: string | number; role?: string } | null;
}

export default function AdminAnalyticsPage() {
  const { user } = useAuth();
  const [events, setEvents] = useState<AnalyticsEvent[]>([]);
  const [loading, setLoading] = useState(false);
  const [summary, setSummary] = useState<Record<string, Record<string, number>>>({});
  const [typeFilter, setTypeFilter] = useState<string>('');
  const [days, setDays] = useState<number>(1);
  const [limit, setLimit] = useState<number>(200);

  const isAdmin = (user as any)?.role === 'admin' || (user as any)?.roles?.includes?.('admin');

  const fetchEvents = async () => {
    setLoading(true);
    try {
      const { data } = await apiClient.get('/analytics/events', {
        params: { days, limit, type: typeFilter || undefined },
      });
      setEvents(data.events || []);
      const sumRes = await apiClient.get('/analytics/events/summary', { params: { days } });
      setSummary(sumRes.data.summary || {});
    } catch (e) {
      // noop
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (isAdmin) fetchEvents();
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isAdmin]);

  // Funnel order representing conversion steps
  const funnelSteps: { key: string; label: string }[] = useMemo(() => [
    { key: 'pageview', label: 'Page Views' },
    { key: 'search_performed', label: 'Searches' },
    { key: 'filters_applied', label: 'Filters Applied' },
    { key: 'wishlist_toggled', label: 'Wishlist Toggles' },
    { key: 'booking_submitted', label: 'Bookings' },
  ], []);

  const aggregateTotal = useCallback((type: string) =>
    Object.values(summary).reduce((acc, counts) => acc + (counts[type] || 0), 0),
  [summary]);

  // Derive chart data from summary (object keyed by day -> counts object)
  const daySeries = useMemo(() => {
    return Object.entries(summary)
      .sort((a, b) => new Date(a[0]).getTime() - new Date(b[0]).getTime())
      .map(([day, counts]) => ({
        day,
        pageview: counts.pageview || 0,
        booking_submitted: counts.booking_submitted || 0,
        wishlist_toggled: counts.wishlist_toggled || 0,
        search_performed: counts.search_performed || 0,
        filters_applied: counts.filters_applied || 0,
      }));
  }, [summary]);

  const funnelData = useMemo(() => {
    return funnelSteps.map(step => ({
      step: step.label,
      value: aggregateTotal(step.key),
    }));
  }, [funnelSteps, aggregateTotal]);

  const distributionData = useMemo(() => {
    return funnelSteps.map(step => ({
      name: step.label,
      value: aggregateTotal(step.key),
    }));
  }, [funnelSteps, aggregateTotal]);

  if (!isAdmin) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-8">
          <Card>
            <CardContent className="py-8 text-center text-gray-600">
              You do not have permission to view this page.
            </CardContent>
          </Card>
        </div>
      </MainLayout>
    );
  }

  if (loading && events.length === 0 && Object.keys(summary).length === 0) {
    return (
      <MainLayout>
        <DashboardSkeleton />
      </MainLayout>
    );
  }

  const COLORS = ['#2563eb', '#10b981', '#f59e0b', '#ec4899', '#9333ea'];

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8 space-y-6">
        <div className="flex items-end gap-3">
          <div>
            <label className="block text-xs font-medium text-gray-600">Type</label>
            <Input value={typeFilter} onChange={(e) => setTypeFilter(e.target.value)} placeholder="e.g. booking_submitted" />
          </div>
          <div>
            <label className="block text-xs font-medium text-gray-600">Days</label>
            <Input type="number" min={1} max={30} value={days} onChange={(e) => setDays(parseInt(e.target.value || '1'))} />
          </div>
          <div>
            <label className="block text-xs font-medium text-gray-600">Limit</label>
            <Input type="number" min={50} max={2000} value={limit} onChange={(e) => setLimit(parseInt(e.target.value || '200'))} />
          </div>
          <div className="pb-0.5">
            <Button onClick={fetchEvents} disabled={loading}>{loading ? 'Loading...' : 'Refresh'}</Button>
          </div>
        </div>

        {/* Time Series */}
        <Card>
          <CardContent className="p-4 space-y-4">
            <h2 className="font-semibold">Time Series (last {days} days)</h2>
            {daySeries.length === 0 ? (
              <div className="text-sm text-gray-500">No data</div>
            ) : (
              <div className="h-72">
                <ResponsiveContainer width="100%" height="100%">
                  <LineChart data={daySeries} margin={{ top: 10, right: 20, left: 0, bottom: 0 }}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="day" tick={{ fontSize: 12 }} />
                    <YAxis tick={{ fontSize: 12 }} />
                    <Tooltip />
                    <Legend />
                    <Line type="monotone" dataKey="pageview" stroke="#2563eb" strokeWidth={2} dot={false} name="Page Views" />
                    <Line type="monotone" dataKey="search_performed" stroke="#10b981" strokeWidth={2} dot={false} name="Searches" />
                    <Line type="monotone" dataKey="filters_applied" stroke="#f59e0b" strokeWidth={2} dot={false} name="Filters" />
                    <Line type="monotone" dataKey="wishlist_toggled" stroke="#ec4899" strokeWidth={2} dot={false} name="Wishlist" />
                    <Line type="monotone" dataKey="booking_submitted" stroke="#9333ea" strokeWidth={2} dot={false} name="Bookings" />
                  </LineChart>
                </ResponsiveContainer>
              </div>
            )}
          </CardContent>
        </Card>

        {/* Conversion Funnel (Bar) */}
        <Card>
          <CardContent className="p-4 space-y-4">
            <h2 className="font-semibold">Conversion Funnel (aggregate)</h2>
            {funnelData.every(d => d.value === 0) ? (
              <div className="text-sm text-gray-500">No data</div>
            ) : (
              <div className="h-64">
                <ResponsiveContainer width="100%" height="100%">
                  <BarChart data={funnelData} margin={{ top: 10, right: 20, left: 0, bottom: 0 }}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="step" tick={{ fontSize: 12 }} interval={0} />
                    <YAxis />
                    <Tooltip />
                    <Bar dataKey="value" name="Count">
                      {funnelData.map((entry, index) => (
                        <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                      ))}
                    </Bar>
                  </BarChart>
                </ResponsiveContainer>
              </div>
            )}
          </CardContent>
        </Card>

        {/* Event Distribution (Pie) */}
        <Card>
          <CardContent className="p-4 space-y-4">
            <h2 className="font-semibold">Event Type Distribution (aggregate)</h2>
            {distributionData.every(d => d.value === 0) ? (
              <div className="text-sm text-gray-500">No data</div>
            ) : (
              <div className="h-64">
                <ResponsiveContainer width="100%" height="100%">
                  <PieChart>
                    <Tooltip />
                    <Legend />
                    <Pie
                      data={distributionData}
                      dataKey="value"
                      nameKey="name"
                      outerRadius={110}
                      innerRadius={40}
                      label={(d) => `${d.name}: ${d.value}`}
                    >
                      {distributionData.map((entry, index) => (
                        <Cell key={`slice-${index}`} fill={COLORS[index % COLORS.length]} />
                      ))}
                    </Pie>
                  </PieChart>
                </ResponsiveContainer>
              </div>
            )}
          </CardContent>
        </Card>

        {/* Rate Limiter Widget */}
        <RateLimiterWidget />

        <Card>
          <CardContent className="p-4">
            <h2 className="font-semibold mb-3">Raw Summary Table (last {days} days)</h2>
            <div className="overflow-x-auto">
              <table className="min-w-full text-sm">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="text-left p-2">Day</th>
                    {funnelSteps.map(s => (
                      <th key={s.key} className="text-left p-2">{s.key}</th>
                    ))}
                  </tr>
                </thead>
                <tbody>
                  {Object.keys(summary).length === 0 ? (
                    <tr><td colSpan={funnelSteps.length + 1} className="p-4 text-center text-gray-500">No data</td></tr>
                  ) : (
                    daySeries.map(row => (
                      <tr key={row.day} className="border-t">
                        <td className="p-2 whitespace-nowrap">{row.day}</td>
                        {funnelSteps.map(s => (
                          <td key={s.key} className="p-2">{(row as any)[s.key] || 0}</td>
                        ))}
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-0">
            <div className="overflow-x-auto">
              <table className="min-w-full text-sm">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="text-left p-3">Time</th>
                    <th className="text-left p-3">Type</th>
                    <th className="text-left p-3">User</th>
                    <th className="text-left p-3">Payload</th>
                  </tr>
                </thead>
                <tbody>
                  {events.length === 0 ? (
                    <tr><td colSpan={4} className="p-6 text-center text-gray-500">No events</td></tr>
                  ) : (
                    [...events].reverse().map((ev, idx) => (
                      <tr key={idx} className="border-t">
                        <td className="p-3 whitespace-nowrap">{new Date(ev.timestamp).toLocaleString()}</td>
                        <td className="p-3 font-medium">{ev.type}</td>
                        <td className="p-3">{ev.user ? `${ev.user.id}${ev.user.role ? ' ('+ev.user.role+')' : ''}` : 'anon'}</td>
                        <td className="p-3"><pre className="whitespace-pre-wrap text-xs text-gray-600">{JSON.stringify(ev.payload, null, 2)}</pre></td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>

        <div>
          <a
            href={`/api/v1/analytics/events/export?days=${days}${typeFilter ? `&type=${encodeURIComponent(typeFilter)}` : ''}`}
            target="_blank"
            rel="noopener noreferrer"
          >
            <Button variant="outline">Download CSV</Button>
          </a>
        </div>
      </div>
    </MainLayout>
  );
}
