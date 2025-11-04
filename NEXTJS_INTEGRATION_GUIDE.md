# Next.js Integration Guide - Verification System

## Overview
This guide helps you integrate the verification system into your Next.js frontend.

## File Structure

```
frontend/
├── app/
│   ├── (auth)/
│   │   └── profile/
│   │       └── verification/
│   │           └── page.tsx
│   ├── (owner)/
│   │   └── properties/
│   │       └── [id]/
│   │           └── verification/
│   │               └── page.tsx
│   └── (admin)/
│       └── verifications/
│           ├── users/
│           │   └── page.tsx
│           └── properties/
│               └── page.tsx
├── components/
│   ├── verification/
│   │   ├── VerificationBadge.tsx
│   │   ├── VerificationProgress.tsx
│   │   ├── DocumentUploader.tsx
│   │   ├── IDVerificationForm.tsx
│   │   ├── PhoneVerificationForm.tsx
│   │   ├── AddressVerificationForm.tsx
│   │   ├── PropertyOwnershipForm.tsx
│   │   ├── LegalDocumentsForm.tsx
│   │   └── InspectionScheduler.tsx
│   └── ui/
│       ├── progress.tsx
│       ├── badge.tsx
│       └── file-upload.tsx
├── lib/
│   ├── api/
│   │   └── verification.ts
│   └── types/
│       └── verification.ts
└── hooks/
    ├── useUserVerification.ts
    └── usePropertyVerification.ts
```

## TypeScript Types

### Create `lib/types/verification.ts`:

```typescript
export type VerificationStatus = 'pending' | 'under_review' | 'approved' | 'rejected';
export type OverallStatus = 'unverified' | 'partially_verified' | 'fully_verified';
export type InspectionStatus = 'not_required' | 'pending' | 'scheduled' | 'completed' | 'failed';

export interface UserVerification {
  id: number;
  user_id: number;
  
  // ID Verification
  id_verification_status: VerificationStatus;
  id_document_type?: 'passport' | 'driving_license' | 'national_id';
  id_document_number?: string;
  id_front_image?: string;
  id_back_image?: string;
  selfie_image?: string;
  id_verified_at?: string;
  id_rejection_reason?: string;
  
  // Phone Verification
  phone_verification_status: 'pending' | 'verified';
  phone_number?: string;
  phone_verified_at?: string;
  
  // Email Verification
  email_verification_status: 'pending' | 'verified';
  email_verified_at?: string;
  
  // Address Verification
  address_verification_status: VerificationStatus;
  address?: string;
  address_proof_document?: string;
  address_proof_image?: string;
  address_verified_at?: string;
  address_rejection_reason?: string;
  
  // Background Check
  background_check_status: 'not_requested' | 'pending' | 'in_progress' | 'completed' | 'failed';
  background_check_completed_at?: string;
  
  // Overall
  overall_status: OverallStatus;
  verification_score: number;
  
  created_at: string;
  updated_at: string;
}

export interface PropertyVerification {
  id: number;
  property_id: number;
  user_id: number;
  
  // Ownership
  ownership_status: VerificationStatus;
  ownership_document_type?: string;
  ownership_documents?: string[];
  ownership_verified_at?: string;
  ownership_rejection_reason?: string;
  
  // Inspection
  inspection_status: InspectionStatus;
  inspection_scheduled_at?: string;
  inspection_completed_at?: string;
  inspector_id?: number;
  inspection_score?: number;
  inspection_notes?: string;
  
  // Photos
  photos_status: VerificationStatus;
  photos_verified_at?: string;
  photos_rejection_reason?: string;
  
  // Details
  details_status: VerificationStatus;
  details_verified_at?: string;
  details_to_correct?: any;
  
  // Legal
  has_business_license: boolean;
  has_safety_certificate: boolean;
  has_insurance: boolean;
  insurance_expiry_date?: string;
  
  // Overall
  overall_status: 'unverified' | 'under_review' | 'verified' | 'rejected';
  has_verified_badge: boolean;
  verification_score: number;
  
  next_verification_due?: string;
  last_verified_at?: string;
  
  created_at: string;
  updated_at: string;
}
```

## API Client

### Create `lib/api/verification.ts`:

```typescript
import axios from 'axios';
import { UserVerification, PropertyVerification } from '@/lib/types/verification';

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add auth token interceptor
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// User Verification API
export const userVerificationAPI = {
  getMyVerification: () => 
    api.get<UserVerification>('/my-verification'),
  
  getAll: (params?: { status?: string; per_page?: number }) =>
    api.get<{ data: UserVerification[] }>('/user-verifications', { params }),
  
  getById: (id: number) =>
    api.get<UserVerification>(`/user-verifications/${id}`),
  
  submitIdVerification: (data: FormData) =>
    api.post('/user-verifications/id', data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    }),
  
  sendPhoneVerification: (phone_number: string) =>
    api.post('/user-verifications/phone/send', { phone_number }),
  
  verifyPhone: (code: string) =>
    api.post('/user-verifications/phone/verify', { code }),
  
  submitAddressVerification: (data: FormData) =>
    api.post('/user-verifications/address', data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    }),
  
  requestBackgroundCheck: () =>
    api.post('/user-verifications/background-check'),
  
  getStatistics: () =>
    api.get('/user-verifications/statistics'),
};

// Property Verification API
export const propertyVerificationAPI = {
  getAll: (params?: { status?: string; per_page?: number }) =>
    api.get<{ data: PropertyVerification[] }>('/property-verifications', { params }),
  
  getById: (id: number) =>
    api.get<PropertyVerification>(`/property-verifications/${id}`),
  
  getByPropertyId: (propertyId: number) =>
    api.get<PropertyVerification>(`/properties/${propertyId}/verification`),
  
  submitOwnership: (propertyId: number, data: FormData) =>
    api.post(`/properties/${propertyId}/verification/ownership`, data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    }),
  
  submitLegalDocuments: (propertyId: number, data: FormData) =>
    api.post(`/properties/${propertyId}/verification/legal-documents`, data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    }),
  
  requestInspection: (propertyId: number) =>
    api.post(`/properties/${propertyId}/verification/request-inspection`),
  
  getStatistics: () =>
    api.get('/property-verifications/statistics'),
};

// Admin API
export const adminVerificationAPI = {
  // User Verification
  approveId: (id: number) =>
    api.post(`/admin/user-verifications/${id}/approve-id`),
  
  rejectId: (id: number, reason: string) =>
    api.post(`/admin/user-verifications/${id}/reject-id`, { reason }),
  
  approveAddress: (id: number) =>
    api.post(`/admin/user-verifications/${id}/approve-address`),
  
  rejectAddress: (id: number, reason: string) =>
    api.post(`/admin/user-verifications/${id}/reject-address`, { reason }),
  
  // Property Verification
  approveOwnership: (id: number) =>
    api.post(`/admin/property-verifications/${id}/approve-ownership`),
  
  rejectOwnership: (id: number, reason: string) =>
    api.post(`/admin/property-verifications/${id}/reject-ownership`, { reason }),
  
  approvePhotos: (id: number) =>
    api.post(`/admin/property-verifications/${id}/approve-photos`),
  
  rejectPhotos: (id: number, reason: string) =>
    api.post(`/admin/property-verifications/${id}/reject-photos`, { reason }),
  
  approveDetails: (id: number) =>
    api.post(`/admin/property-verifications/${id}/approve-details`),
  
  rejectDetails: (id: number, details_to_correct: any) =>
    api.post(`/admin/property-verifications/${id}/reject-details`, { details_to_correct }),
  
  scheduleInspection: (id: number, data: { inspection_scheduled_at: string; inspector_id: number }) =>
    api.post(`/admin/property-verifications/${id}/schedule-inspection`, data),
  
  completeInspection: (id: number, data: { status: string; inspection_score: number; inspection_notes?: string; inspection_report?: any }) =>
    api.post(`/admin/property-verifications/${id}/complete-inspection`, data),
  
  grantBadge: (id: number) =>
    api.post(`/admin/property-verifications/${id}/grant-badge`),
  
  revokeBadge: (id: number, reason: string) =>
    api.post(`/admin/property-verifications/${id}/revoke-badge`, { reason }),
};
```

## React Hooks

### Create `hooks/useUserVerification.ts`:

```typescript
'use client';

import { useState, useEffect } from 'react';
import { userVerificationAPI } from '@/lib/api/verification';
import { UserVerification } from '@/lib/types/verification';
import { toast } from 'sonner';

export function useUserVerification() {
  const [verification, setVerification] = useState<UserVerification | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    fetchVerification();
  }, []);

  const fetchVerification = async () => {
    try {
      setLoading(true);
      const response = await userVerificationAPI.getMyVerification();
      setVerification(response.data);
      setError(null);
    } catch (err: any) {
      setError(err.message);
      toast.error('Failed to load verification status');
    } finally {
      setLoading(false);
    }
  };

  const submitIdVerification = async (formData: FormData) => {
    try {
      const response = await userVerificationAPI.submitIdVerification(formData);
      setVerification(response.data.verification);
      toast.success('ID verification submitted successfully');
      return response.data;
    } catch (err: any) {
      toast.error(err.response?.data?.message || 'Failed to submit ID verification');
      throw err;
    }
  };

  const sendPhoneVerification = async (phoneNumber: string) => {
    try {
      const response = await userVerificationAPI.sendPhoneVerification(phoneNumber);
      toast.success('Verification code sent to your phone');
      return response.data;
    } catch (err: any) {
      toast.error(err.response?.data?.message || 'Failed to send verification code');
      throw err;
    }
  };

  const verifyPhone = async (code: string) => {
    try {
      const response = await userVerificationAPI.verifyPhone(code);
      setVerification(response.data.verification);
      toast.success('Phone verified successfully');
      return response.data;
    } catch (err: any) {
      toast.error(err.response?.data?.message || 'Invalid verification code');
      throw err;
    }
  };

  const submitAddressVerification = async (formData: FormData) => {
    try {
      const response = await userVerificationAPI.submitAddressVerification(formData);
      setVerification(response.data.verification);
      toast.success('Address verification submitted successfully');
      return response.data;
    } catch (err: any) {
      toast.error(err.response?.data?.message || 'Failed to submit address verification');
      throw err;
    }
  };

  return {
    verification,
    loading,
    error,
    fetchVerification,
    submitIdVerification,
    sendPhoneVerification,
    verifyPhone,
    submitAddressVerification,
  };
}
```

### Create `hooks/usePropertyVerification.ts`:

```typescript
'use client';

import { useState, useEffect } from 'react';
import { propertyVerificationAPI } from '@/lib/api/verification';
import { PropertyVerification } from '@/lib/types/verification';
import { toast } from 'sonner';

export function usePropertyVerification(propertyId: number) {
  const [verification, setVerification] = useState<PropertyVerification | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (propertyId) {
      fetchVerification();
    }
  }, [propertyId]);

  const fetchVerification = async () => {
    try {
      setLoading(true);
      const response = await propertyVerificationAPI.getByPropertyId(propertyId);
      setVerification(response.data);
      setError(null);
    } catch (err: any) {
      setError(err.message);
      toast.error('Failed to load verification status');
    } finally {
      setLoading(false);
    }
  };

  const submitOwnership = async (formData: FormData) => {
    try {
      const response = await propertyVerificationAPI.submitOwnership(propertyId, formData);
      setVerification(response.data.verification);
      toast.success('Ownership documents submitted successfully');
      return response.data;
    } catch (err: any) {
      toast.error(err.response?.data?.message || 'Failed to submit ownership documents');
      throw err;
    }
  };

  const submitLegalDocuments = async (formData: FormData) => {
    try {
      const response = await propertyVerificationAPI.submitLegalDocuments(propertyId, formData);
      setVerification(response.data.verification);
      toast.success('Legal documents submitted successfully');
      return response.data;
    } catch (err: any) {
      toast.error(err.response?.data?.message || 'Failed to submit legal documents');
      throw err;
    }
  };

  const requestInspection = async () => {
    try {
      const response = await propertyVerificationAPI.requestInspection(propertyId);
      setVerification(response.data.verification);
      toast.success('Inspection requested successfully');
      return response.data;
    } catch (err: any) {
      toast.error(err.response?.data?.message || 'Failed to request inspection');
      throw err;
    }
  };

  return {
    verification,
    loading,
    error,
    fetchVerification,
    submitOwnership,
    submitLegalDocuments,
    requestInspection,
  };
}
```

## Components

### VerificationBadge Component

```typescript
import { Badge } from '@/components/ui/badge';
import { CheckCircle } from 'lucide-react';

interface VerificationBadgeProps {
  isVerified: boolean;
  className?: string;
}

export function VerificationBadge({ isVerified, className }: VerificationBadgeProps) {
  if (!isVerified) return null;

  return (
    <Badge variant="success" className={className}>
      <CheckCircle className="w-3 h-3 mr-1" />
      Verified
    </Badge>
  );
}
```

### VerificationProgress Component

```typescript
import { Progress } from '@/components/ui/progress';

interface VerificationProgressProps {
  score: number;
  className?: string;
}

export function VerificationProgress({ score, className }: VerificationProgressProps) {
  const getColor = (score: number) => {
    if (score >= 80) return 'bg-green-500';
    if (score >= 50) return 'bg-yellow-500';
    return 'bg-red-500';
  };

  return (
    <div className={className}>
      <div className="flex justify-between mb-2">
        <span className="text-sm font-medium">Verification Progress</span>
        <span className="text-sm font-medium">{score}%</span>
      </div>
      <Progress value={score} className={getColor(score)} />
    </div>
  );
}
```

### DocumentUploader Component

```typescript
'use client';

import { useState } from 'react';
import { Upload } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface DocumentUploaderProps {
  onFileSelect: (file: File) => void;
  accept?: string;
  maxSize?: number; // in MB
}

export function DocumentUploader({
  onFileSelect,
  accept = 'image/*,.pdf',
  maxSize = 10,
}: DocumentUploaderProps) {
  const [dragActive, setDragActive] = useState(false);

  const handleDrag = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    if (e.type === 'dragenter' || e.type === 'dragover') {
      setDragActive(true);
    } else if (e.type === 'dragleave') {
      setDragActive(false);
    }
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setDragActive(false);

    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      handleFile(e.dataTransfer.files[0]);
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      handleFile(e.target.files[0]);
    }
  };

  const handleFile = (file: File) => {
    if (file.size > maxSize * 1024 * 1024) {
      alert(`File size must be less than ${maxSize}MB`);
      return;
    }
    onFileSelect(file);
  };

  return (
    <div
      className={`border-2 border-dashed rounded-lg p-6 text-center ${
        dragActive ? 'border-primary bg-primary/10' : 'border-gray-300'
      }`}
      onDragEnter={handleDrag}
      onDragLeave={handleDrag}
      onDragOver={handleDrag}
      onDrop={handleDrop}
    >
      <Upload className="w-12 h-12 mx-auto mb-4 text-gray-400" />
      <p className="mb-2">Drag and drop your file here, or</p>
      <Button variant="outline" asChild>
        <label className="cursor-pointer">
          Browse Files
          <input
            type="file"
            className="hidden"
            accept={accept}
            onChange={handleChange}
          />
        </label>
      </Button>
      <p className="text-sm text-gray-500 mt-2">
        Max file size: {maxSize}MB
      </p>
    </div>
  );
}
```

## Example Page

### User Verification Page (`app/(auth)/profile/verification/page.tsx`):

```typescript
'use client';

import { useUserVerification } from '@/hooks/useUserVerification';
import { VerificationProgress } from '@/components/verification/VerificationProgress';
import { DocumentUploader } from '@/components/verification/DocumentUploader';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';

export default function UserVerificationPage() {
  const { verification, loading, submitIdVerification, sendPhoneVerification } = useUserVerification();

  if (loading) return <div>Loading...</div>;

  return (
    <div className="container mx-auto py-8">
      <h1 className="text-3xl font-bold mb-6">Account Verification</h1>

      {verification && (
        <VerificationProgress 
          score={verification.verification_score} 
          className="mb-8"
        />
      )}

      <div className="grid gap-6">
        {/* ID Verification Card */}
        <Card className="p-6">
          <h2 className="text-xl font-semibold mb-4">ID Verification</h2>
          <p className="text-gray-600 mb-4">
            Status: {verification?.id_verification_status || 'Not submitted'}
          </p>
          {/* Add ID verification form here */}
        </Card>

        {/* Phone Verification Card */}
        <Card className="p-6">
          <h2 className="text-xl font-semibold mb-4">Phone Verification</h2>
          <p className="text-gray-600 mb-4">
            Status: {verification?.phone_verification_status || 'Not verified'}
          </p>
          {/* Add phone verification form here */}
        </Card>

        {/* Address Verification Card */}
        <Card className="p-6">
          <h2 className="text-xl font-semibold mb-4">Address Verification</h2>
          <p className="text-gray-600 mb-4">
            Status: {verification?.address_verification_status || 'Not submitted'}
          </p>
          {/* Add address verification form here */}
        </Card>
      </div>
    </div>
  );
}
```

## Environment Variables

Add to your `.env.local`:

```env
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
NEXT_PUBLIC_MAX_FILE_SIZE=10485760
```

## Next Steps

1. Install required packages:
```bash
npm install axios sonner lucide-react
```

2. Set up Tailwind CSS and shadcn/ui components

3. Implement the forms and file uploaders

4. Add validation with zod

5. Implement the admin panel pages

6. Add real-time updates with WebSockets (optional)

7. Test all workflows

---

**Ready to integrate! 🎉**
