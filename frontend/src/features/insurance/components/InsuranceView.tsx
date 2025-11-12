'use client';

import { useEffect, useState, useCallback } from 'react';
import { useTranslations } from '@/lib/i18n-temp';
import type { InsurancePlan, Claim } from '@/lib/schemas/insurance';
import { getAvailablePlans, getUserClaims, submitClaim } from '../api';
import { usePrivateChannel } from '@/hooks/use-echo';
import { notify } from '@/lib/notify';

type Status = 'idle' | 'loading' | 'error';

export default function InsuranceView() {
  const t = useTranslations('insurance');
  const [plans, setPlans] = useState<InsurancePlan[]>([]);
  const [claims, setClaims] = useState<Claim[]>([]);
  const [status, setStatus] = useState<Status>('idle');
  const [error, setError] = useState('');
  const [activeTab, setActiveTab] = useState<'plans' | 'claims'>('plans');
  const [claimForm, setClaimForm] = useState({
    insuranceId: 0,
    description: '',
    amount: 0,
  });
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
    loadData();
  }, [activeTab]);

  // Realtime claim updates
  const handleClaimSubmitted = useCallback((data: any) => {
    if (!data?.claim) return;
    setClaims(prev => {
      if (prev.some(c => c.id === data.claim.id)) return prev;
      return [data.claim, ...prev];
    });
    notify.success?.({ title: t('claims.title'), description: t('toasts.claimSubmitted') || 'Claim submitted' });
  }, [t]);

  const handleClaimUpdated = useCallback((data: any) => {
    if (!data?.claim) return;
    setClaims(prev => prev.map(c => c.id === data.claim.id ? { ...c, ...data.claim } : c));
  }, []);

  useEffect(() => {
    if (!channel) return;
    channel.listen('insurance.claim.submitted', handleClaimSubmitted);
    channel.listen('insurance.claim.updated', handleClaimUpdated);
    return () => {
      try {
        channel.stopListening('insurance.claim.submitted');
        channel.stopListening('insurance.claim.updated');
      } catch {}
    };
  }, [channel, handleClaimSubmitted, handleClaimUpdated]);

  const loadData = async () => {
    setStatus('loading');
    setError('');
    try {
      if (activeTab === 'plans') {
        const data = await getAvailablePlans();
        setPlans(data);
      } else {
        const data = await getUserClaims();
        setClaims(data);
      }
      setStatus('idle');
    } catch (e: any) {
      setError(e?.message || 'Failed to load data');
      setStatus('error');
    }
  };

  const handleSubmitClaim = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!claimForm.insuranceId || !claimForm.description) {
      alert(t('claims.fillFields') || 'Please fill all required fields');
      return;
    }

    try {
      const newClaim = await submitClaim(claimForm);
      setClaims((prev) => [newClaim, ...prev]);
      setClaimForm({ insuranceId: 0, description: '', amount: 0 });
      alert(t('toasts.claimSubmitted') || 'Claim submitted successfully');
    } catch (e: any) {
      alert(e?.message || t('toasts.claimFailed') || 'Failed to submit claim');
    }
  };

  const getClaimStatusBadge = (claimStatus?: string) => {
    const st = claimStatus || 'pending';
    const colors: Record<string, string> = {
      pending: 'bg-yellow-100 text-yellow-800',
      approved: 'bg-green-100 text-green-800',
      rejected: 'bg-red-100 text-red-800',
    };
    return (
      <span
        className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${colors[st] || colors.pending}`}
      >
        {t(`claims.${st}`) || st}
      </span>
    );
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">{t('title') || 'Travel Insurance'}</h1>
        <p className="text-sm text-gray-600">
          {t('subtitle') || 'Protect your bookings with comprehensive coverage'}
        </p>
      </div>

      <div className="border-b border-gray-200">
        <nav className="-mb-px flex space-x-8">
          <button
            onClick={() => setActiveTab('plans')}
            className={`py-2 px-1 border-b-2 font-medium text-sm ${
              activeTab === 'plans'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            {t('plansTab') || 'Insurance Plans'}
          </button>
          <button
            onClick={() => setActiveTab('claims')}
            className={`py-2 px-1 border-b-2 font-medium text-sm ${
              activeTab === 'claims'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            {t('claims.title') || 'File a Claim'}
          </button>
        </nav>
      </div>

      {status === 'loading' && <p>{t('loading') || 'Loading...'}</p>}
      {status === 'error' && <p className="text-red-600">{error}</p>}

      {activeTab === 'plans' && status !== 'loading' && (
        <div>
          {plans.length === 0 && (
            <div className="text-center py-12">
              <p className="text-gray-500">{t('noInsurance') || 'No available plans'}</p>
              <p className="text-sm text-gray-400 mt-1">
                {t('noInsuranceDesc') || 'Check back later for insurance options'}
              </p>
            </div>
          )}

          <div className="grid md:grid-cols-3 gap-4">
            {plans.map((plan) => (
              <div key={plan.id} className="border rounded-lg p-6 space-y-3">
                <h3 className="text-lg font-semibold">
                  {t(`plans.${plan.type}`) || plan.name}
                </h3>
                <p className="text-2xl font-bold">
                  {plan.price} {plan.currency || 'USD'}
                </p>
                <p className="text-sm text-gray-600">{plan.description || ''}</p>
                {plan.coverages && plan.coverages.length > 0 && (
                  <ul className="text-sm space-y-1">
                    {plan.coverages.map((cov, idx) => (
                      <li key={idx} className="flex items-start">
                        <span className="text-green-600 mr-2">✓</span>
                        {cov}
                      </li>
                    ))}
                  </ul>
                )}
                <button className="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                  {t('addToBooking') || 'Add to Booking'}
                </button>
              </div>
            ))}
          </div>
        </div>
      )}

      {activeTab === 'claims' && status !== 'loading' && (
        <div className="space-y-6">
          <form onSubmit={handleSubmitClaim} className="border rounded-lg p-6 space-y-4">
            <h3 className="text-lg font-semibold">{t('claims.submit') || 'Submit Claim'}</h3>
            <div>
              <label className="block text-sm font-medium mb-1">
                Insurance ID <span className="text-red-500">*</span>
              </label>
              <input
                type="number"
                value={claimForm.insuranceId || ''}
                onChange={(e) =>
                  setClaimForm({ ...claimForm, insuranceId: Number(e.target.value) })
                }
                className="w-full px-3 py-2 border rounded"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium mb-1">
                Description <span className="text-red-500">*</span>
              </label>
              <textarea
                value={claimForm.description}
                onChange={(e) =>
                  setClaimForm({ ...claimForm, description: e.target.value })
                }
                rows={4}
                className="w-full px-3 py-2 border rounded"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium mb-1">Amount (optional)</label>
              <input
                type="number"
                step="0.01"
                value={claimForm.amount || ''}
                onChange={(e) =>
                  setClaimForm({ ...claimForm, amount: Number(e.target.value) })
                }
                className="w-full px-3 py-2 border rounded"
              />
            </div>
            <button
              type="submit"
              className="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              {t('claims.submit') || 'Submit Claim'}
            </button>
          </form>

          {claims.length === 0 && (
            <p className="text-gray-500 text-center">
              {t('claims.noClaims') || 'No claims submitted yet'}
            </p>
          )}

          {claims.length > 0 && (
            <div className="space-y-4">
              <h3 className="text-lg font-semibold">{t('claims.status') || 'Your Claims'}</h3>
              <div className="divide-y">
                {claims.map((claim) => (
                  <div key={claim.id} className="py-4 flex justify-between items-start">
                    <div>
                      <p className="font-medium">Claim #{claim.id}</p>
                      <p className="text-sm text-gray-600">{claim.description}</p>
                      <p className="text-sm text-gray-500">
                        Submitted:{' '}
                        {claim.submittedAt
                          ? new Date(claim.submittedAt).toLocaleDateString()
                          : 'N/A'}
                      </p>
                    </div>
                    <div className="text-right">
                      {getClaimStatusBadge(claim.status)}
                      {claim.amount && (
                        <p className="text-sm font-medium mt-1">${claim.amount}</p>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      )}
    </div>
  );
}
