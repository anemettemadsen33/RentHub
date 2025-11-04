'use client';

import React from 'react';
import type { VerificationStatistics } from '@/types/guest-verification';
import { Shield, CheckCircle, XCircle, Star } from 'lucide-react';

interface TrustScoreCardProps {
  statistics: VerificationStatistics;
  canBook: boolean;
  isFullyVerified: boolean;
}

export function TrustScoreCard({ statistics, canBook, isFullyVerified }: TrustScoreCardProps) {
  const getTrustScoreColor = (score: number) => {
    if (score >= 4.5) return 'text-green-600 bg-green-100';
    if (score >= 3.5) return 'text-blue-600 bg-blue-100';
    if (score >= 2.5) return 'text-yellow-600 bg-yellow-100';
    return 'text-red-600 bg-red-100';
  };

  const getVerificationLevelBadge = (level: string) => {
    const badges = {
      none: { label: 'Not Started', color: 'bg-gray-100 text-gray-700' },
      basic: { label: 'Basic', color: 'bg-blue-100 text-blue-700' },
      verified: { label: 'Verified', color: 'bg-green-100 text-green-700' },
      full: { label: 'Fully Verified', color: 'bg-purple-100 text-purple-700' },
    };
    return badges[level as keyof typeof badges] || badges.none;
  };

  const badge = getVerificationLevelBadge(statistics.verification_level);

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center space-x-3">
          <Shield className="w-8 h-8 text-blue-600" />
          <div>
            <h2 className="text-2xl font-bold text-gray-900">Trust Score</h2>
            <span className={`inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${badge.color}`}>
              {badge.label}
            </span>
          </div>
        </div>
        <div className={`flex items-center justify-center w-20 h-20 rounded-full ${getTrustScoreColor(statistics.trust_score)}`}>
          <span className="text-3xl font-bold">{statistics.trust_score.toFixed(1)}</span>
        </div>
      </div>

      {/* Verification Status */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div className="flex items-center space-x-2">
          {statistics.identity_verified ? (
            <CheckCircle className="w-5 h-5 text-green-600" />
          ) : (
            <XCircle className="w-5 h-5 text-gray-400" />
          )}
          <span className="text-sm text-gray-700">Identity</span>
        </div>
        <div className="flex items-center space-x-2">
          {statistics.background_clear ? (
            <CheckCircle className="w-5 h-5 text-green-600" />
          ) : (
            <XCircle className="w-5 h-5 text-gray-400" />
          )}
          <span className="text-sm text-gray-700">Background</span>
        </div>
        <div className="flex items-center space-x-2">
          {statistics.credit_approved ? (
            <CheckCircle className="w-5 h-5 text-green-600" />
          ) : (
            <XCircle className="w-5 h-5 text-gray-400" />
          )}
          <span className="text-sm text-gray-700">Credit</span>
        </div>
        <div className="flex items-center space-x-2">
          {statistics.references_count > 0 ? (
            <CheckCircle className="w-5 h-5 text-green-600" />
          ) : (
            <XCircle className="w-5 h-5 text-gray-400" />
          )}
          <span className="text-sm text-gray-700">References ({statistics.references_count})</span>
        </div>
      </div>

      {/* Statistics */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 pt-6 border-t border-gray-200">
        <div className="text-center">
          <div className="text-2xl font-bold text-gray-900">{statistics.completed_bookings}</div>
          <div className="text-sm text-gray-600">Completed</div>
        </div>
        <div className="text-center">
          <div className="text-2xl font-bold text-gray-900">{statistics.cancelled_bookings}</div>
          <div className="text-sm text-gray-600">Cancelled</div>
        </div>
        <div className="text-center">
          <div className="text-2xl font-bold text-green-600">{statistics.positive_reviews}</div>
          <div className="text-sm text-gray-600">Positive Reviews</div>
        </div>
        <div className="text-center">
          <div className="text-2xl font-bold text-red-600">{statistics.negative_reviews}</div>
          <div className="text-sm text-gray-600">Negative Reviews</div>
        </div>
      </div>

      {/* Booking Status */}
      {canBook ? (
        <div className="mt-6 flex items-center justify-center p-3 bg-green-50 border border-green-200 rounded-lg">
          <CheckCircle className="w-5 h-5 text-green-600 mr-2" />
          <span className="text-green-700 font-medium">You can book properties</span>
        </div>
      ) : (
        <div className="mt-6 flex items-center justify-center p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
          <XCircle className="w-5 h-5 text-yellow-600 mr-2" />
          <span className="text-yellow-700 font-medium">Complete verification to book</span>
        </div>
      )}

      {isFullyVerified && (
        <div className="mt-4 flex items-center justify-center p-3 bg-purple-50 border border-purple-200 rounded-lg">
          <Star className="w-5 h-5 text-purple-600 mr-2" />
          <span className="text-purple-700 font-medium">Fully Verified Guest</span>
        </div>
      )}
    </div>
  );
}
