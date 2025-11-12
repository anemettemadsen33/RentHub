'use client';

import { useEffect, useState, useCallback } from 'react';
import { useTranslations } from '@/lib/i18n-temp';
import type { VerificationStatus } from '@/lib/schemas/verification';
import {
  getVerificationStatus,
  submitIdVerification,
  submitAddressVerification,
  sendPhoneVerification,
  verifyPhoneCode,
  requestBackgroundCheck,
} from '../api';
import { usePrivateChannel } from '@/hooks/use-echo';
import { notify } from '@/lib/notify';

type Status = 'idle' | 'loading' | 'error';
type Step = 'identity' | 'address' | 'phone' | 'background';

export default function ProfileVerificationView() {
  const t = useTranslations('verification');
  const [verificationStatus, setVerificationStatus] = useState<VerificationStatus | null>(null);
  const [status, setStatus] = useState<Status>('idle');
  const [error, setError] = useState('');
  const [activeStep, setActiveStep] = useState<Step>('identity');
  const [phoneNumber, setPhoneNumber] = useState('');
  const [phoneCode, setPhoneCode] = useState('');
  const [idFile, setIdFile] = useState<File | null>(null);
  const [addressFile, setAddressFile] = useState<File | null>(null);
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
    loadStatus();
  }, []);

  // Realtime verification status updates
  const handleVerificationUpdated = useCallback((data: any) => {
    if (!data?.status) return;
    setVerificationStatus(prev => ({ ...(prev || {}), ...data.status } as VerificationStatus));
    notify.info?.({ title: t('title'), description: t('toasts.submitted') || 'Verification updated' });
  }, [t]);

  useEffect(() => {
    if (!channel) return;
    channel.listen('verification.status.updated', handleVerificationUpdated);
    return () => {
      try { channel.stopListening('verification.status.updated'); } catch {}
    };
  }, [channel, handleVerificationUpdated]);

  const loadStatus = async () => {
    setStatus('loading');
    setError('');
    try {
      const data = await getVerificationStatus();
      setVerificationStatus(data);
      setStatus('idle');
    } catch (e: any) {
      setError(e?.message || 'Failed to load verification status');
      setStatus('error');
    }
  };

  const handleSubmitId = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!idFile) {
      alert('Please select an ID document');
      return;
    }

    const formData = new FormData();
    formData.append('id_document', idFile);
    formData.append('id_type', 'passport');

    try {
      await submitIdVerification(formData);
      loadStatus();
      alert(t('toasts.submitted') || 'Verification submitted');
    } catch (e: any) {
      alert(e?.message || t('toasts.submitFailed') || 'Failed to submit');
    }
  };

  const handleSubmitAddress = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!addressFile) {
      alert('Please select an address proof document');
      return;
    }

    const formData = new FormData();
    formData.append('address_document', addressFile);

    try {
      await submitAddressVerification(formData);
      loadStatus();
      alert(t('toasts.submitted') || 'Verification submitted');
    } catch (e: any) {
      alert(e?.message || t('toasts.submitFailed') || 'Failed to submit');
    }
  };

  const handleSendPhoneCode = async () => {
    if (!phoneNumber) {
      alert('Please enter phone number');
      return;
    }

    try {
      await sendPhoneVerification(phoneNumber);
      alert('Verification code sent to your phone');
    } catch (e: any) {
      alert(e?.message || 'Failed to send code');
    }
  };

  const handleVerifyPhone = async () => {
    if (!phoneCode) {
      alert('Please enter the verification code');
      return;
    }

    try {
      await verifyPhoneCode(phoneCode);
      loadStatus();
      alert('Phone verified successfully');
    } catch (e: any) {
      alert(e?.message || 'Invalid code');
    }
  };

  const handleRequestBackgroundCheck = async () => {
    if (!confirm('Request a background check? You may be redirected to a third-party service.')) return;

    try {
      await requestBackgroundCheck();
      loadStatus();
      alert('Background check requested');
    } catch (e: any) {
      alert(e?.message || 'Failed to request background check');
    }
  };

  const getStatusBadge = (stepStatus?: string) => {
    const st = stepStatus || 'not_started';
    const colors: Record<string, string> = {
      verified: 'bg-green-100 text-green-800',
      pending: 'bg-yellow-100 text-yellow-800',
      rejected: 'bg-red-100 text-red-800',
      not_started: 'bg-gray-100 text-gray-700',
      completed: 'bg-green-100 text-green-800',
    };
    return (
      <span
        className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${colors[st] || colors.not_started}`}
      >
        {t(`status.${st}`) || st}
      </span>
    );
  };

  const trustScore = verificationStatus?.trustScore || 0;

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">{t('title') || 'Profile Verification'}</h1>
        <p className="text-sm text-gray-600">
          {t('subtitle') || 'Verify your identity to build trust'}
        </p>
      </div>

      {/* Trust Score */}
      <div className="border rounded-lg p-6 bg-gradient-to-r from-blue-50 to-indigo-50">
        <h2 className="text-lg font-semibold mb-2">
          {t('trustScore.title') || 'Trust Score'}
        </h2>
        <p className="text-3xl font-bold text-blue-600">
          {t('trustScore.outOf', { score: trustScore }) || `${trustScore} / 100`}
        </p>
        <div className="mt-4 space-y-2 text-sm">
          {verificationStatus?.identity === 'verified' && (
            <p className="flex items-center">
              <span className="text-green-600 mr-2">✓</span>
              {t('trustScore.factors.identity') || 'Identity Verified'}
            </p>
          )}
          {verificationStatus?.address === 'verified' && (
            <p className="flex items-center">
              <span className="text-green-600 mr-2">✓</span>
              {t('trustScore.factors.address') || 'Address Verified'}
            </p>
          )}
          {verificationStatus?.background === 'completed' && (
            <p className="flex items-center">
              <span className="text-green-600 mr-2">✓</span>
              {t('trustScore.factors.background') || 'Background Check'}
            </p>
          )}
        </div>
      </div>

      {/* Steps Navigation */}
      <div className="border-b border-gray-200">
        <nav className="-mb-px flex space-x-4 overflow-x-auto">
          {(['identity', 'address', 'phone', 'background'] as Step[]).map((step) => (
            <button
              key={step}
              onClick={() => setActiveStep(step)}
              className={`py-2 px-3 border-b-2 font-medium text-sm whitespace-nowrap ${
                activeStep === step
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700'
              }`}
            >
              {t(`steps.${step}.title`) || step}
              <span className="ml-2">
                {getStatusBadge((verificationStatus as any)?.[step === 'background' ? 'background' : step])}
              </span>
            </button>
          ))}
        </nav>
      </div>

      {status === 'loading' && <p>{t('loading') || 'Loading...'}</p>}
      {status === 'error' && <p className="text-red-600">{error}</p>}

      {/* Identity Verification */}
      {activeStep === 'identity' && (
        <div className="border rounded-lg p-6 space-y-4">
          <h3 className="text-lg font-semibold">
            {t('steps.identity.title') || 'Identity Verification'}
          </h3>
          <p className="text-sm text-gray-600">
            {t('steps.identity.description') || 'Upload a government-issued ID'}
          </p>
          <form onSubmit={handleSubmitId} className="space-y-4">
            <div>
              <label className="block text-sm font-medium mb-1">
                {t('steps.identity.upload') || 'Upload ID Document'}
              </label>
              <input
                type="file"
                accept="image/*,application/pdf"
                onChange={(e) => setIdFile(e.target.files?.[0] || null)}
                className="w-full px-3 py-2 border rounded"
              />
            </div>
            <button
              type="submit"
              className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              {t('steps.identity.submit') || 'Submit for Verification'}
            </button>
          </form>
        </div>
      )}

      {/* Address Verification */}
      {activeStep === 'address' && (
        <div className="border rounded-lg p-6 space-y-4">
          <h3 className="text-lg font-semibold">
            {t('steps.address.title') || 'Address Verification'}
          </h3>
          <p className="text-sm text-gray-600">
            {t('steps.address.description') || 'Upload proof of address'}
          </p>
          <p className="text-xs text-gray-500">
            {t('steps.address.acceptedDocs') ||
              'Accepted: Utility bill, bank statement, lease agreement'}
          </p>
          <form onSubmit={handleSubmitAddress} className="space-y-4">
            <div>
              <label className="block text-sm font-medium mb-1">
                {t('steps.address.upload') || 'Upload Document'}
              </label>
              <input
                type="file"
                accept="image/*,application/pdf"
                onChange={(e) => setAddressFile(e.target.files?.[0] || null)}
                className="w-full px-3 py-2 border rounded"
              />
            </div>
            <button
              type="submit"
              className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              {t('steps.address.submit') || 'Submit'}
            </button>
          </form>
        </div>
      )}

      {/* Phone Verification */}
      {activeStep === 'phone' && (
        <div className="border rounded-lg p-6 space-y-4">
          <h3 className="text-lg font-semibold">Phone Verification</h3>
          <div className="space-y-3">
            <div>
              <label className="block text-sm font-medium mb-1">Phone Number</label>
              <div className="flex space-x-2">
                <input
                  type="tel"
                  value={phoneNumber}
                  onChange={(e) => setPhoneNumber(e.target.value)}
                  className="flex-1 px-3 py-2 border rounded"
                  placeholder="+1234567890"
                />
                <button
                  onClick={handleSendPhoneCode}
                  className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                  Send Code
                </button>
              </div>
            </div>
            <div>
              <label className="block text-sm font-medium mb-1">Verification Code</label>
              <div className="flex space-x-2">
                <input
                  type="text"
                  value={phoneCode}
                  onChange={(e) => setPhoneCode(e.target.value)}
                  className="flex-1 px-3 py-2 border rounded"
                  placeholder="123456"
                />
                <button
                  onClick={handleVerifyPhone}
                  className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                >
                  Verify
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Background Check */}
      {activeStep === 'background' && (
        <div className="border rounded-lg p-6 space-y-4">
          <h3 className="text-lg font-semibold">
            {t('steps.background.title') || 'Background Check'}
          </h3>
          <p className="text-sm text-gray-600">
            {t('steps.background.description') || 'Authorize a background check'}
          </p>
          <div className="flex items-center space-x-2">
            <input type="checkbox" id="consent" />
            <label htmlFor="consent" className="text-sm">
              {t('steps.background.consent') || 'I consent to a background check'}
            </label>
          </div>
          <button
            onClick={handleRequestBackgroundCheck}
            className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            {t('steps.background.request') || 'Request Background Check'}
          </button>
        </div>
      )}
    </div>
  );
}
