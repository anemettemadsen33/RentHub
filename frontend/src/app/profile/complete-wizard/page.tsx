'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/AuthContext';
import { authApi } from '@/lib/api/auth';

type Step = 'basic' | 'address' | 'verification' | 'complete';

export default function ProfileWizardPage() {
  const router = useRouter();
  const { user, refreshUser } = useAuth();
  const [currentStep, setCurrentStep] = useState<Step>('basic');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [completionStatus, setCompletionStatus] = useState<any>(null);

  const [basicInfo, setBasicInfo] = useState({
    name: user?.name || '',
    phone: user?.phone || '',
    date_of_birth: user?.date_of_birth || '',
    gender: user?.gender || '',
  });

  const [addressInfo, setAddressInfo] = useState({
    address: user?.address || '',
    city: user?.city || '',
    state: user?.state || '',
    country: user?.country || '',
    zip_code: user?.zip_code || '',
  });

  const [verificationCode, setVerificationCode] = useState('');

  useEffect(() => {
    fetchCompletionStatus();
  }, []);

  const fetchCompletionStatus = async () => {
    try {
      const response = await authApi.getProfileCompletionStatus();
      if (response.data) {
        setCompletionStatus(response.data);
      }
    } catch (err) {
      console.error('Failed to fetch completion status:', err);
    }
  };

  const handleBasicInfoSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      await authApi.updateBasicInfo(basicInfo);
      await refreshUser();
      setCurrentStep('address');
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to update basic info');
    } finally {
      setLoading(false);
    }
  };

  const handleAddressSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      await authApi.updateProfileDetails(addressInfo);
      await refreshUser();
      setCurrentStep('verification');
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to update address');
    } finally {
      setLoading(false);
    }
  };

  const handleSendPhoneVerification = async () => {
    if (!basicInfo.phone) {
      setError('Please enter a phone number');
      return;
    }

    setError('');
    setLoading(true);

    try {
      await authApi.sendPhoneVerification(basicInfo.phone);
      alert('Verification code sent to your phone!');
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to send verification code');
    } finally {
      setLoading(false);
    }
  };

  const handleVerifyPhone = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      await authApi.verifyPhone(verificationCode);
      await refreshUser();
      setCurrentStep('complete');
    } catch (err: any) {
      setError(err.response?.data?.message || 'Invalid verification code');
    } finally {
      setLoading(false);
    }
  };

  const handleComplete = async () => {
    setError('');
    setLoading(true);

    try {
      await authApi.completeWizard();
      await refreshUser();
      router.push('/dashboard');
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to complete profile');
    } finally {
      setLoading(false);
    }
  };

  const steps = [
    { id: 'basic', name: 'Basic Info', completed: false },
    { id: 'address', name: 'Address', completed: false },
    { id: 'verification', name: 'Verification', completed: false },
    { id: 'complete', name: 'Complete', completed: false },
  ];

  const currentStepIndex = steps.findIndex(s => s.id === currentStep);

  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-3xl mx-auto">
        <div className="bg-white shadow rounded-lg">
          {/* Header */}
          <div className="px-6 py-8 border-b border-gray-200">
            <h2 className="text-2xl font-bold text-gray-900">Complete Your Profile</h2>
            <p className="mt-2 text-sm text-gray-600">
              Help us get to know you better. This will only take a few minutes.
            </p>

            {/* Progress Bar */}
            <div className="mt-6">
              <div className="flex items-center justify-between">
                {steps.map((step, index) => (
                  <div key={step.id} className="flex items-center">
                    <div
                      className={`flex items-center justify-center w-10 h-10 rounded-full border-2 ${
                        index <= currentStepIndex
                          ? 'border-blue-600 bg-blue-600 text-white'
                          : 'border-gray-300 bg-white text-gray-500'
                      }`}
                    >
                      {index < currentStepIndex ? '✓' : index + 1}
                    </div>
                    {index < steps.length - 1 && (
                      <div
                        className={`w-16 h-1 mx-2 ${
                          index < currentStepIndex ? 'bg-blue-600' : 'bg-gray-300'
                        }`}
                      />
                    )}
                  </div>
                ))}
              </div>
              <div className="flex justify-between mt-2">
                {steps.map(step => (
                  <span key={step.id} className="text-xs text-gray-500">
                    {step.name}
                  </span>
                ))}
              </div>
            </div>
          </div>

          {/* Content */}
          <div className="px-6 py-8">
            {error && (
              <div className="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded">
                {error}
              </div>
            )}

            {currentStep === 'basic' && (
              <form onSubmit={handleBasicInfoSubmit} className="space-y-6">
                <h3 className="text-lg font-medium text-gray-900">Basic Information</h3>

                <div>
                  <label className="block text-sm font-medium text-gray-700">Full Name</label>
                  <input
                    type="text"
                    required
                    value={basicInfo.name}
                    onChange={e => setBasicInfo({ ...basicInfo, name: e.target.value })}
                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700">Phone Number</label>
                  <input
                    type="tel"
                    value={basicInfo.phone}
                    onChange={e => setBasicInfo({ ...basicInfo, phone: e.target.value })}
                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
                    placeholder="+1 (555) 123-4567"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700">Date of Birth</label>
                  <input
                    type="date"
                    value={basicInfo.date_of_birth}
                    onChange={e => setBasicInfo({ ...basicInfo, date_of_birth: e.target.value })}
                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700">Gender</label>
                  <select
                    value={basicInfo.gender}
                    onChange={e => setBasicInfo({ ...basicInfo, gender: e.target.value })}
                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
                  >
                    <option value="">Select...</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                  </select>
                </div>

                <button
                  type="submit"
                  disabled={loading}
                  className="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
                >
                  {loading ? 'Saving...' : 'Continue'}
                </button>
              </form>
            )}

            {currentStep === 'address' && (
              <form onSubmit={handleAddressSubmit} className="space-y-6">
                <h3 className="text-lg font-medium text-gray-900">Address Information</h3>

                <div>
                  <label className="block text-sm font-medium text-gray-700">Street Address</label>
                  <input
                    type="text"
                    required
                    value={addressInfo.address}
                    onChange={e => setAddressInfo({ ...addressInfo, address: e.target.value })}
                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
                  />
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700">City</label>
                    <input
                      type="text"
                      required
                      value={addressInfo.city}
                      onChange={e => setAddressInfo({ ...addressInfo, city: e.target.value })}
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700">State/Province</label>
                    <input
                      type="text"
                      value={addressInfo.state}
                      onChange={e => setAddressInfo({ ...addressInfo, state: e.target.value })}
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
                    />
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700">Country</label>
                    <input
                      type="text"
                      required
                      value={addressInfo.country}
                      onChange={e => setAddressInfo({ ...addressInfo, country: e.target.value })}
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700">ZIP/Postal Code</label>
                    <input
                      type="text"
                      required
                      value={addressInfo.zip_code}
                      onChange={e => setAddressInfo({ ...addressInfo, zip_code: e.target.value })}
                      className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
                    />
                  </div>
                </div>

                <div className="flex gap-4">
                  <button
                    type="button"
                    onClick={() => setCurrentStep('basic')}
                    className="flex-1 py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                  >
                    Back
                  </button>
                  <button
                    type="submit"
                    disabled={loading}
                    className="flex-1 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
                  >
                    {loading ? 'Saving...' : 'Continue'}
                  </button>
                </div>
              </form>
            )}

            {currentStep === 'verification' && (
              <div className="space-y-6">
                <h3 className="text-lg font-medium text-gray-900">Verify Your Phone</h3>
                <p className="text-sm text-gray-600">
                  We'll send a verification code to your phone number.
                </p>

                {!user?.phone_verified_at && (
                  <>
                    <button
                      type="button"
                      onClick={handleSendPhoneVerification}
                      disabled={loading}
                      className="w-full py-2 px-4 border border-blue-600 rounded-md text-blue-600 hover:bg-blue-50 disabled:opacity-50"
                    >
                      {loading ? 'Sending...' : 'Send Verification Code'}
                    </button>

                    <form onSubmit={handleVerifyPhone}>
                      <label className="block text-sm font-medium text-gray-700">
                        Verification Code
                      </label>
                      <input
                        type="text"
                        required
                        value={verificationCode}
                        onChange={e => setVerificationCode(e.target.value)}
                        className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
                        placeholder="Enter 6-digit code"
                        maxLength={6}
                      />

                      <div className="mt-4 flex gap-4">
                        <button
                          type="button"
                          onClick={() => setCurrentStep('address')}
                          className="flex-1 py-2 px-4 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50"
                        >
                          Back
                        </button>
                        <button
                          type="submit"
                          disabled={loading}
                          className="flex-1 py-2 px-4 border border-transparent rounded-md text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
                        >
                          {loading ? 'Verifying...' : 'Verify'}
                        </button>
                      </div>
                    </form>
                  </>
                )}

                {user?.phone_verified_at && (
                  <div className="text-center">
                    <div className="text-green-600 text-lg mb-4">✓ Phone Verified</div>
                    <button
                      onClick={() => setCurrentStep('complete')}
                      className="w-full py-2 px-4 border border-transparent rounded-md text-white bg-blue-600 hover:bg-blue-700"
                    >
                      Continue
                    </button>
                  </div>
                )}
              </div>
            )}

            {currentStep === 'complete' && (
              <div className="text-center space-y-6">
                <div className="text-6xl">🎉</div>
                <h3 className="text-2xl font-bold text-gray-900">You're All Set!</h3>
                <p className="text-gray-600">
                  Your profile is now complete. You can start exploring properties.
                </p>

                {completionStatus && (
                  <div className="bg-blue-50 p-4 rounded-lg">
                    <div className="text-sm text-blue-900">
                      Profile Completion: {completionStatus.percentage}%
                    </div>
                  </div>
                )}

                <button
                  onClick={handleComplete}
                  disabled={loading}
                  className="w-full py-3 px-4 border border-transparent rounded-md text-white bg-blue-600 hover:bg-blue-700 text-lg font-medium disabled:opacity-50"
                >
                  {loading ? 'Completing...' : 'Go to Dashboard'}
                </button>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
