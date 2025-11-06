'use client';

import React, { useState } from 'react';
import type { GuestVerification, IdentityVerificationForm } from '@/types/guest-verification';
import { guestVerificationApi } from '@/lib/api/guest-verification';
import { Upload, CheckCircle, XCircle, Clock } from 'lucide-react';

interface Props {
  verification: GuestVerification | null;
  onUpdate: () => void;
}

export function IdentityVerificationCard({ verification, onUpdate }: Props) {
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState<Partial<IdentityVerificationForm>>({});

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.document_type || !formData.document_number || !formData.document_front || !formData.selfie_photo || !formData.document_expiry_date) {
      alert('Please fill all required fields');
      return;
    }

    try {
      setLoading(true);
      await guestVerificationApi.submitIdentity(formData as IdentityVerificationForm);
      alert('Identity documents submitted successfully');
      onUpdate();
    } catch (error: any) {
      alert(error.response?.data?.message || 'Failed to submit documents');
    } finally {
      setLoading(false);
    }
  };

  const getStatusIcon = () => {
    if (!verification) return <Clock className="w-5 h-5 text-gray-400" />;
    
    switch (verification.identity_status) {
      case 'verified':
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
    if (!verification) return <span className="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">Not Started</span>;
    
    const badges = {
      verified: <span className="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Verified</span>,
      rejected: <span className="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">Rejected</span>,
      pending: <span className="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded">Pending</span>,
      expired: <span className="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">Expired</span>,
    };
    return badges[verification.identity_status] || badges.pending;
  };

  return (
    <div className="bg-white rounded-lg shadow p-6">
      <div className="flex items-center justify-between mb-4">
        <div className="flex items-center space-x-2">
          {getStatusIcon()}
          <h3 className="text-lg font-semibold text-gray-900">Identity Verification</h3>
        </div>
        {getStatusBadge()}
      </div>

      {verification?.identity_status === 'verified' ? (
        <div className="text-sm text-gray-600">
          <p>Your identity has been verified!</p>
          <p className="mt-2">Verified on: {new Date(verification.identity_verified_at!).toLocaleDateString()}</p>
        </div>
      ) : verification?.identity_status === 'rejected' ? (
        <div className="text-sm text-red-600">
          <p>Your identity verification was rejected.</p>
          <p className="mt-2">Reason: {verification.identity_rejection_reason}</p>
        </div>
      ) : verification?.identity_status === 'pending' ? (
        <div className="text-sm text-gray-600">
          <p>Your documents are being reviewed. This usually takes 24-48 hours.</p>
        </div>
      ) : (
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label htmlFor="document-type" className="block text-sm font-medium text-gray-700">Document Type</label>
            <select
              id="document-type"
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              onChange={(e) => setFormData({ ...formData, document_type: e.target.value as any })}
              required
            >
              <option value="">Select...</option>
              <option value="passport">Passport</option>
              <option value="drivers_license">Driver's License</option>
              <option value="id_card">ID Card</option>
              <option value="national_id">National ID</option>
            </select>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700">Document Number</label>
            <input
              type="text"
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              onChange={(e) => setFormData({ ...formData, document_number: e.target.value })}
              required
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700">Expiry Date</label>
            <input
              type="date"
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              onChange={(e) => setFormData({ ...formData, document_expiry_date: e.target.value })}
              required
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700">Document Front</label>
            <input
              type="file"
              accept="image/*"
              className="mt-1 block w-full"
              onChange={(e) => setFormData({ ...formData, document_front: e.target.files?.[0] })}
              required
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700">Document Back (Optional)</label>
            <input
              type="file"
              accept="image/*"
              className="mt-1 block w-full"
              onChange={(e) => setFormData({ ...formData, document_back: e.target.files?.[0] })}
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700">Selfie Photo</label>
            <input
              type="file"
              accept="image/*"
              className="mt-1 block w-full"
              onChange={(e) => setFormData({ ...formData, selfie_photo: e.target.files?.[0] })}
              required
            />
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
          >
            {loading ? 'Submitting...' : 'Submit Documents'}
          </button>
        </form>
      )}
    </div>
  );
}
