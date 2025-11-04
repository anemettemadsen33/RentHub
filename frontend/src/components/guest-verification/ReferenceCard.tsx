'use client';

import React, { useState } from 'react';
import type { GuestVerification, ReferenceForm } from '@/types/guest-verification';
import { guestVerificationApi } from '@/lib/api/guest-verification';
import { Users, Plus } from 'lucide-react';

interface Props {
  verification: GuestVerification | null;
  onUpdate: () => void;
}

export function ReferenceCard({ verification, onUpdate }: Props) {
  const [loading, setLoading] = useState(false);
  const [showForm, setShowForm] = useState(false);
  const [formData, setFormData] = useState<ReferenceForm>({
    reference_name: '',
    reference_email: '',
    reference_phone: '',
    reference_type: 'personal',
    relationship: '',
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      setLoading(true);
      await guestVerificationApi.addReference(formData);
      alert('Reference added successfully');
      setShowForm(false);
      setFormData({
        reference_name: '',
        reference_email: '',
        reference_phone: '',
        reference_type: 'personal',
        relationship: '',
      });
      onUpdate();
    } catch (error: any) {
      alert(error.response?.data?.message || 'Failed to add reference');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="bg-white rounded-lg shadow p-6">
      <div className="flex items-center justify-between mb-4">
        <div className="flex items-center space-x-2">
          <Users className="w-5 h-5 text-blue-600" />
          <h3 className="text-lg font-semibold text-gray-900">References</h3>
        </div>
        <span className="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded">
          {verification?.references_verified || 0} Verified
        </span>
      </div>

      <p className="text-sm text-gray-600 mb-4">
        Add references from previous landlords, employers, or personal contacts to boost your trust score.
      </p>

      {!showForm ? (
        <button
          onClick={() => setShowForm(true)}
          className="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center justify-center"
        >
          <Plus className="w-4 h-4 mr-2" />
          Add Reference
        </button>
      ) : (
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700">Name</label>
            <input
              type="text"
              value={formData.reference_name}
              onChange={(e) => setFormData({ ...formData, reference_name: e.target.value })}
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              required
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700">Email</label>
            <input
              type="email"
              value={formData.reference_email}
              onChange={(e) => setFormData({ ...formData, reference_email: e.target.value })}
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              required
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700">Phone (Optional)</label>
            <input
              type="tel"
              value={formData.reference_phone}
              onChange={(e) => setFormData({ ...formData, reference_phone: e.target.value })}
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700">Type</label>
            <select
              value={formData.reference_type}
              onChange={(e) => setFormData({ ...formData, reference_type: e.target.value as any })}
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              required
            >
              <option value="previous_landlord">Previous Landlord</option>
              <option value="employer">Employer</option>
              <option value="personal">Personal</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700">Relationship</label>
            <textarea
              value={formData.relationship}
              onChange={(e) => setFormData({ ...formData, relationship: e.target.value })}
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              rows={2}
            />
          </div>

          <div className="flex space-x-2">
            <button
              type="submit"
              disabled={loading}
              className="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
            >
              {loading ? 'Adding...' : 'Add Reference'}
            </button>
            <button
              type="button"
              onClick={() => setShowForm(false)}
              className="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300"
            >
              Cancel
            </button>
          </div>
        </form>
      )}
    </div>
  );
}
