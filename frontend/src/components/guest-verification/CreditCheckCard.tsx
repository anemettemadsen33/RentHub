'use client';

import React, { useState } from 'react';
import type { GuestVerification } from '@/types/guest-verification';
import { guestVerificationApi } from '@/lib/api/guest-verification';
import { CreditCard, CheckCircle, XCircle, Clock } from 'lucide-react';

interface Props {
  verification: GuestVerification | null;
  onUpdate: () => void;
}

export function CreditCheckCard({ verification, onUpdate }: Props) {
  const [loading, setLoading] = useState(false);

  const handleRequest = async () => {
    try {
      setLoading(true);
      await guestVerificationApi.requestCreditCheck();
      alert('Credit check requested successfully');
      onUpdate();
    } catch (error: any) {
      alert(error.response?.data?.message || 'Failed to request credit check');
    } finally {
      setLoading(false);
    }
  };

  const getStatusIcon = () => {
    if (!verification?.credit_check_enabled) return <Clock className="w-5 h-5 text-gray-400" />;
    
    switch (verification.credit_status) {
      case 'approved':
        return <CheckCircle className="w-5 h-5 text-green-600" />;
      case 'rejected':
        return <XCircle className="w-5 h-5 text-red-600" />;
      case 'pending':
        return <Clock className="w-5 h-5 text-yellow-600" />;
      default:
        return <Clock className="w-5 h-5 text-gray-400" />;
    }
  };

  const getStatusBadge = () => {
    if (!verification?.credit_check_enabled) {
      return <span className="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">Not Requested</span>;
    }
    
    const badges = {
      approved: <span className="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Approved</span>,
      rejected: <span className="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">Rejected</span>,
      pending: <span className="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded">Pending</span>,
      not_requested: <span className="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">Not Requested</span>,
    };
    return badges[verification.credit_status] || badges.not_requested;
  };

  return (
    <div className="bg-white rounded-lg shadow p-6">
      <div className="flex items-center justify-between mb-4">
        <div className="flex items-center space-x-2">
          {getStatusIcon()}
          <h3 className="text-lg font-semibold text-gray-900">Credit Check (Optional)</h3>
        </div>
        {getStatusBadge()}
      </div>

      <p className="text-sm text-gray-600 mb-4">
        A credit check can significantly boost your trust score and increase booking approval rates.
      </p>

      {verification?.credit_status === 'approved' ? (
        <div className="text-sm text-gray-600">
          <p>Credit check approved!</p>
          {verification.credit_score && (
            <p className="mt-2">Score: {verification.credit_score}</p>
          )}
        </div>
      ) : verification?.credit_status === 'rejected' ? (
        <div className="text-sm text-red-600">
          <p>Credit check was not approved.</p>
        </div>
      ) : verification?.credit_status === 'pending' ? (
        <div className="text-sm text-gray-600">
          <p>Your credit check is being processed.</p>
        </div>
      ) : (
        <button
          onClick={handleRequest}
          disabled={loading}
          className="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center"
        >
          <CreditCard className="w-4 h-4 mr-2" />
          {loading ? 'Requesting...' : 'Request Credit Check'}
        </button>
      )}
    </div>
  );
}
