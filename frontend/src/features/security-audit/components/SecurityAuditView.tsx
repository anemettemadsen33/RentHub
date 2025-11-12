'use client';

import { useEffect, useState, useCallback } from 'react';
import { useTranslations } from '@/lib/i18n-temp';
import type { AuditLog, Anomaly } from '@/lib/schemas/security';
import { getAuditLogs, getAnomalies } from '../api';
import { usePrivateChannel } from '@/hooks/use-echo';

type Status = 'idle' | 'loading' | 'error';

export default function SecurityAuditView() {
  const t = useTranslations('security.audit');
  const [logs, setLogs] = useState<AuditLog[]>([]);
  const [anomalies, setAnomalies] = useState<Anomaly[]>([]);
  const [status, setStatus] = useState<Status>('idle');
  const [error, setError] = useState('');
  const [filterEvent, setFilterEvent] = useState<string>('all');
  const [userId, setUserId] = useState<number | null>(null);
  const [authToken, setAuthToken] = useState<string>('');

  useEffect(() => {
    if (typeof window === 'undefined') return;
    try {
      setAuthToken(localStorage.getItem('auth_token') || '');
      const rawUser = localStorage.getItem('user');
      if (rawUser) {
        const u = JSON.parse(rawUser);
        if (u?.id) setUserId(Number(u.id));
      }
    } catch {}
  }, []);

  const channel = usePrivateChannel(userId ? `user.${userId}` : '', authToken, !!userId);

  useEffect(() => {
    loadLogs();
    loadAnomalies();
  }, [filterEvent]);

  const handleNewAuditLog = useCallback((data: any) => {
    if (!data?.log) return;
    setLogs(prev => [data.log, ...prev]);
  }, []);

  const handleAnomalyDetected = useCallback((data: any) => {
    if (!data?.anomaly) return;
    setAnomalies(prev => [data.anomaly, ...prev]);
  }, []);

  useEffect(() => {
    if (!channel) return;
    channel.listen('security.audit.created', handleNewAuditLog);
    channel.listen('security.anomaly.detected', handleAnomalyDetected);
    return () => {
      try {
        channel.stopListening('security.audit.created');
        channel.stopListening('security.anomaly.detected');
      } catch {}
    };
  }, [channel, handleNewAuditLog, handleAnomalyDetected]);

  const loadLogs = async () => {
    setStatus('loading');
    setError('');
    try {
      const filters = filterEvent !== 'all' ? { event: filterEvent } : undefined;
      const data = await getAuditLogs(filters);
      setLogs(data);
      setStatus('idle');
    } catch (e: any) {
      setError(e?.message || 'Failed to load audit logs');
      setStatus('error');
    }
  };

  const loadAnomalies = async () => {
    try {
      const data = await getAnomalies();
      setAnomalies(data);
    } catch (e: any) {
      console.error('Failed to load anomalies', e);
    }
  };

  const getStatusBadge = (logStatus?: string) => {
    const st = logStatus || 'success';
    const colors: Record<string, string> = {
      success: 'bg-green-100 text-green-800',
      failed: 'bg-red-100 text-red-800',
      blocked: 'bg-gray-100 text-gray-700',
    };
    return (
      <span
        className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${colors[st] || colors.success}`}
      >
        {t(`status.${st}`) || st}
      </span>
    );
  };

  const getSeverityBadge = (severity?: string) => {
    const sev = severity || 'low';
    const colors: Record<string, string> = {
      low: 'bg-yellow-100 text-yellow-800',
      medium: 'bg-orange-100 text-orange-800',
      high: 'bg-red-100 text-red-800',
    };
    return (
      <span
        className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${colors[sev] || colors.low}`}
      >
        {sev.toUpperCase()}
      </span>
    );
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">
          {t('title') || 'Security Audit Log'}
        </h1>
        <p className="text-sm text-gray-600">
          {t('subtitle') || 'Monitor account activity and security events'}
        </p>
      </div>

      {anomalies.length > 0 && (
        <div className="bg-red-50 border border-red-200 rounded-lg p-4">
          <h3 className="text-lg font-semibold text-red-800 mb-2">
            {t('anomaly.badge') || 'Anomaly Detected'}
          </h3>
          <div className="space-y-2">
            {anomalies.map((anomaly) => (
              <div key={anomaly.id} className="flex justify-between items-start">
                <div>
                  <p className="font-medium text-red-700">{anomaly.description}</p>
                  <p className="text-xs text-red-600">
                    {anomaly.detectedAt
                      ? new Date(anomaly.detectedAt).toLocaleString()
                      : 'N/A'}
                  </p>
                </div>
                {getSeverityBadge(anomaly.severity)}
              </div>
            ))}
          </div>
        </div>
      )}

      <div className="flex items-center justify-between">
        <select
          value={filterEvent}
          onChange={(e) => setFilterEvent(e.target.value)}
          className="px-3 py-2 border rounded-lg"
        >
          <option value="all">{t('filters.all') || 'All Events'}</option>
          <option value="login">{t('filters.login') || 'Login Attempts'}</option>
          <option value="password_change">
            {t('filters.password') || 'Password Changes'}
          </option>
          <option value="profile_update">
            {t('filters.profile') || 'Profile Updates'}
          </option>
          <option value="suspicious">
            {t('filters.suspicious') || 'Suspicious Activity'}
          </option>
        </select>

        <button
          onClick={loadLogs}
          className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        >
          Refresh
        </button>
      </div>

      {status === 'loading' && <p>{t('loading') || 'Loading...'}</p>}
      {status === 'error' && <p className="text-red-600">{error}</p>}

      {status !== 'loading' && logs.length === 0 && (
        <div className="text-center py-12">
          <p className="text-gray-500">{t('noLogs') || 'No security events'}</p>
          <p className="text-sm text-gray-400 mt-1">
            {t('noLogsDesc') || 'Security events will appear here'}
          </p>
        </div>
      )}

      {logs.length > 0 && (
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  {t('table.timestamp') || 'Timestamp'}
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  {t('table.event') || 'Event'}
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  {t('table.ipAddress') || 'IP Address'}
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  {t('table.location') || 'Location'}
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  {t('table.device') || 'Device'}
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  {t('table.status') || 'Status'}
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {logs.map((log) => (
                <tr
                  key={log.id}
                  className={log.isAnomaly ? 'bg-red-50' : ''}
                >
                  <td className="px-6 py-4 whitespace-nowrap text-sm">
                    {log.timestamp || log.createdAt
                      ? new Date(log.timestamp || log.createdAt!).toLocaleString()
                      : 'N/A'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm">
                    <div>
                      <p className="font-medium">
                        {t(`events.${log.event}`) || log.event}
                      </p>
                      {log.description && (
                        <p className="text-xs text-gray-500">{log.description}</p>
                      )}
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-mono">
                    {log.ipAddress || '—'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm">
                    {log.location || '—'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm">
                    {log.device || '—'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm">
                    {getStatusBadge(log.status)}
                    {log.isAnomaly && (
                      <span className="ml-2 text-xs text-red-600">
                        {t('anomaly.badge') || 'Anomaly'}
                      </span>
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
