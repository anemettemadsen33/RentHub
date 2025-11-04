'use client';

import React, { useState, useEffect } from 'react';
import { guestVerificationApi } from '@/lib/api/guest-verification';
import type { GuestVerification, VerificationStatistics } from '@/types/guest-verification';
import { IdentityVerificationCard } from './IdentityVerificationCard';
import { ReferenceCard } from './ReferenceCard';
import { CreditCheckCard } from './CreditCheckCard';
import { TrustScoreCard } from './TrustScoreCard';
import { Loader2 } from 'lucide-react';

export function VerificationDashboard() {
  const [loading, setLoading] = useState(true);
  const [verification, setVerification] = useState<GuestVerification | null>(null);
  const [statistics, setStatistics] = useState<VerificationStatistics | null>(null);
  const [canBook, setCanBook] = useState(false);
  const [isFullyVerified, setIsFullyVerified] = useState(false);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setLoading(true);
      const [statusData, statsData] = await Promise.all([
        guestVerificationApi.getStatus(),
        guestVerificationApi.getStatistics(),
      ]);

      if (statusData.verification) {
        setVerification(statusData.verification);
      }
      setCanBook(statusData.can_book);
      setIsFullyVerified(statusData.is_fully_verified);
      setStatistics(statsData);
    } catch (error) {
      console.error('Failed to load verification data:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <Loader2 className="w-8 h-8 animate-spin text-blue-600" />
      </div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900">Guest Verification</h1>
        <p className="mt-2 text-gray-600">
          Complete your verification to increase your booking chances and trust score
        </p>
      </div>

      {/* Trust Score Overview */}
      {statistics && (
        <div className="mb-8">
          <TrustScoreCard
            statistics={statistics}
            canBook={canBook}
            isFullyVerified={isFullyVerified}
          />
        </div>
      )}

      {/* Verification Steps */}
      <div className="grid gap-6 md:grid-cols-1 lg:grid-cols-2">
        {/* Identity Verification */}
        <IdentityVerificationCard
          verification={verification}
          onUpdate={loadData}
        />

        {/* Background & References */}
        <ReferenceCard
          verification={verification}
          onUpdate={loadData}
        />

        {/* Credit Check */}
        <CreditCheckCard
          verification={verification}
          onUpdate={loadData}
        />
      </div>

      {/* Booking Status Alert */}
      {!canBook && (
        <div className="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
          <div className="flex">
            <div className="flex-shrink-0">
              <svg
                className="h-5 w-5 text-yellow-400"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fillRule="evenodd"
                  d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                  clipRule="evenodd"
                />
              </svg>
            </div>
            <div className="ml-3">
              <h3 className="text-sm font-medium text-yellow-800">
                Verification Required
              </h3>
              <p className="mt-2 text-sm text-yellow-700">
                Complete at least identity verification to start booking properties.
              </p>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
