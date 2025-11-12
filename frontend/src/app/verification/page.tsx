'use client';

import { useEffect, useState, useCallback } from 'react';
import { useRouter } from 'next/navigation';
import { useTranslations } from '@/lib/i18n-temp';
import { useAuth } from '@/contexts/auth-context';
import { MainLayout } from '@/components/layouts/main-layout';
import { TooltipProvider } from '@/components/ui/tooltip';
import { 
  Card, 
  CardContent, 
  CardDescription, 
  CardHeader, 
  CardTitle 
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Progress } from '@/components/ui/progress';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Alert, AlertDescription } from '@/components/ui/alert';
import {
  Shield,
  Upload,
  Camera,
  CheckCircle,
  XCircle,
  Clock,
  AlertCircle,
  FileText,
  CreditCard,
  Phone,
  Home,
} from 'lucide-react';
import { notify } from '@/lib/notify';
import { verificationService } from '@/lib/api-service';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { breadcrumbSets } from '@/lib/breadcrumbs';

interface VerificationStatus {
  id_verification_status: 'pending' | 'approved' | 'rejected' | 'not_started';
  phone_verification_status: 'pending' | 'verified' | 'not_started';
  email_verification_status: 'pending' | 'verified' | 'not_started';
  address_verification_status: 'pending' | 'approved' | 'rejected' | 'not_started';
  background_check_status: 'pending' | 'completed' | 'not_started';
  overall_status: 'verified' | 'partially_verified' | 'not_verified';
  verification_score: number;
}

export default function VerificationPage() {
  const { user, isAuthenticated, isLoading } = useAuth();
  const router = useRouter();
  const tNotify = useTranslations('notify');
  const [status, setStatus] = useState<VerificationStatus | null>(null);
  const [loading, setLoading] = useState(true);
  const [uploading, setUploading] = useState(false);
  
  // ID Verification
  const [idType, setIdType] = useState('passport');
  const [idNumber, setIdNumber] = useState('');
  const [idFrontFile, setIdFrontFile] = useState<File | null>(null);
  const [idBackFile, setIdBackFile] = useState<File | null>(null);
  const [selfieFile, setSelfieFile] = useState<File | null>(null);
  
  // Phone Verification
  const [phoneNumber, setPhoneNumber] = useState('');
  const [phoneCode, setPhoneCode] = useState('');
  const [phoneSent, setPhoneSent] = useState(false);
  
  // Address Verification
  const [address, setAddress] = useState('');
  const [addressProofFile, setAddressProofFile] = useState<File | null>(null);

  useEffect(() => {
    if (!isLoading && !isAuthenticated) {
      router.push('/auth/login');
    }
  }, [isAuthenticated, isLoading, router]);

  // Load verification status once auth state resolves
  useEffect(() => {
    if (isAuthenticated) {
      loadVerificationStatus();
    }
    // loadVerificationStatus defined below; wrap call in inline function to avoid TS use-before-declare warning
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isAuthenticated]);

  const loadVerificationStatus = useCallback(async () => {
    try {
      setLoading(true);
      const data = await verificationService.userVerification.getStatus();
      setStatus(data);
      if (data.phone_number) setPhoneNumber(data.phone_number);
      if (data.address) setAddress(data.address);
    } catch (error: any) {
      console.error('Failed to load verification status:', error);
  notify.error({ title: tNotify('error'), description: tNotify('failedLoadVerificationStatus') });
    } finally {
      setLoading(false);
    }
  }, [tNotify]);

  const handleIdUpload = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!idFrontFile || !selfieFile) {
  notify.error({ title: tNotify('warning'), description: tNotify('missingIdUpload') });
      return;
    }

    const formData = new FormData();
    formData.append('document_type', idType);
    formData.append('document_number', idNumber);
    formData.append('document_front', idFrontFile);
    if (idBackFile) formData.append('document_back', idBackFile);
    formData.append('selfie_photo', selfieFile);

    setUploading(true);
    try {
      await verificationService.userVerification.uploadId(formData);
  notify.success({ title: tNotify('success'), description: tNotify('idVerificationSubmitted') });
      loadVerificationStatus();
    } catch (error: any) {
      notify.error({ title: tNotify('error'), description: error.response?.data?.message || tNotify('failedSubmitIdVerification') });
    } finally {
      setUploading(false);
    }
  };

  const handlePhoneVerification = async () => {
    if (!phoneNumber) {
  notify.error({ title: tNotify('warning'), description: tNotify('missingPhone') });
      return;
    }

    try {
      await verificationService.userVerification.sendPhoneVerification({ phone: phoneNumber });
      setPhoneSent(true);
  notify.success({ title: 'Sent', description: 'Verification code sent to your phone' });
    } catch (error: any) {
  notify.error({ title: tNotify('error'), description: error.response?.data?.message || tNotify('failedSendVerificationCode') });
    }
  };

  const handlePhoneCodeVerify = async () => {
    if (!phoneCode) {
  notify.error({ title: tNotify('warning'), description: tNotify('missingVerificationCode') });
      return;
    }

    try {
      await verificationService.userVerification.verifyPhone({ code: phoneCode });
  notify.success({ title: tNotify('success'), description: tNotify('phoneVerified') });
      loadVerificationStatus();
      setPhoneSent(false);
      setPhoneCode('');
    } catch (error: any) {
  notify.error({ title: tNotify('error'), description: error.response?.data?.message || tNotify('invalidVerificationCode') });
    }
  };

  const handleAddressUpload = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!address || !addressProofFile) {
  notify.error({ title: 'Missing', description: 'Please enter address and upload proof' });
      return;
    }

    const formData = new FormData();
    formData.append('address', address);
    formData.append('address_proof_image', addressProofFile);
    formData.append('address_proof_document', 'utility_bill');

    setUploading(true);
    try {
      await verificationService.userVerification.uploadAddress(formData);
  notify.success({ title: tNotify('success'), description: tNotify('addressVerificationSubmitted') });
      loadVerificationStatus();
    } catch (error: any) {
      notify.error({ title: tNotify('error'), description: error.response?.data?.message || tNotify('failedSubmitAddressVerification') });
    } finally {
      setUploading(false);
    }
  };

  const handleBackgroundCheck = async () => {
    try {
      await verificationService.userVerification.requestBackgroundCheck();
  notify.success({ title: 'Success', description: 'Background check requested' });
      loadVerificationStatus();
    } catch (error: any) {
      notify.error({ title: 'Error', description: error.response?.data?.message || 'Failed to request background check' });
    }
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'approved':
      case 'verified':
      case 'completed':
        return <Badge variant="default"><CheckCircle className="mr-1 h-3 w-3" /> Verified</Badge>;
      case 'pending':
        return <Badge variant="secondary"><Clock className="mr-1 h-3 w-3" /> Pending</Badge>;
      case 'rejected':
        return <Badge variant="destructive"><XCircle className="mr-1 h-3 w-3" /> Rejected</Badge>;
      default:
        return <Badge variant="outline"><AlertCircle className="mr-1 h-3 w-3" /> Not Started</Badge>;
    }
  };

  const calculateProgress = () => {
    if (!status) return 0;
    let completed = 0;
    let total = 5;

    if (status.id_verification_status === 'approved') completed++;
    if (status.phone_verification_status === 'verified') completed++;
    if (status.email_verification_status === 'verified') completed++;
    if (status.address_verification_status === 'approved') completed++;
    if (status.background_check_status === 'completed') completed++;

    return (completed / total) * 100;
  };

  if (loading || !status) {
    return (
  <TooltipProvider>
      <MainLayout>
        <div className="container mx-auto p-6">
          <div className="animate-pulse space-y-4">
            <div className="h-12 bg-muted rounded w-1/3" />
            <div className="h-64 bg-muted rounded" />
          </div>
        </div>
      </MainLayout>
  </TooltipProvider>
    );
  }

  return (
    <MainLayout>
      <div className="container mx-auto p-6 max-w-5xl space-y-6">
        <Breadcrumbs items={breadcrumbSets.verification()} />
        {/* Header */}
        <div className="animate-fade-in" style={{ animationDelay: '0ms' }}>
          <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2 animate-fade-in" style={{ animationDelay: '0ms' }}>
            <Shield className="h-8 w-8" />
            Identity Verification
          </h1>
          <p className="text-muted-foreground mt-2 animate-fade-in" style={{ animationDelay: '100ms' }}>
            Complete your verification to unlock all features and build trust
          </p>
        </div>

        {/* Overall Progress */}
  <Card className="animate-fade-in-up" style={{ animationDelay: '0ms' }}>
          <CardHeader>
            <CardTitle>Verification Progress</CardTitle>
            <CardDescription>
              {status.overall_status === 'verified' ? 'Your account is fully verified!' : 'Complete all steps to become verified'}
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium">Overall Progress</span>
                <span className="text-sm text-muted-foreground">{Math.round(calculateProgress())}%</span>
              </div>
              <Progress value={calculateProgress()} />
            </div>
            
            <div className="grid grid-cols-2 md:grid-cols-5 gap-4 mt-4">
              <div className="text-center space-y-1">
                <div className="mx-auto w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                  <CreditCard className="h-6 w-6 text-primary" />
                </div>
                <p className="text-xs">ID Verification</p>
                {getStatusBadge(status.id_verification_status)}
              </div>
              
              <div className="text-center space-y-1">
                <div className="mx-auto w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                  <Phone className="h-6 w-6 text-primary" />
                </div>
                <p className="text-xs">Phone</p>
                {getStatusBadge(status.phone_verification_status)}
              </div>
              
              <div className="text-center space-y-1">
                <div className="mx-auto w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                  <FileText className="h-6 w-6 text-primary" />
                </div>
                <p className="text-xs">Email</p>
                {getStatusBadge(status.email_verification_status)}
              </div>
              
              <div className="text-center space-y-1">
                <div className="mx-auto w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                  <Home className="h-6 w-6 text-primary" />
                </div>
                <p className="text-xs">Address</p>
                {getStatusBadge(status.address_verification_status)}
              </div>
              
              <div className="text-center space-y-1">
                <div className="mx-auto w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                  <Shield className="h-6 w-6 text-primary" />
                </div>
                <p className="text-xs">Background</p>
                {getStatusBadge(status.background_check_status)}
              </div>
            </div>

            {status.verification_score && (
              <Alert>
                <AlertCircle className="h-4 w-4" />
                <AlertDescription>
                  Your verification score: <strong>{status.verification_score}/100</strong>
                </AlertDescription>
              </Alert>
            )}
          </CardContent>
        </Card>

        {/* ID Verification */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <CreditCard className="h-5 w-5" />
              Government ID Verification
            </CardTitle>
            <CardDescription>
              Upload a government-issued ID and a selfie for identity verification
            </CardDescription>
          </CardHeader>
          <CardContent>
            {status.id_verification_status === 'approved' ? (
              <Alert>
                <CheckCircle className="h-4 w-4 text-green-600" />
                <AlertDescription className="text-green-600">
                  Your ID has been verified successfully
                </AlertDescription>
              </Alert>
            ) : (
              <form onSubmit={handleIdUpload} className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="id-type">Document Type</Label>
                    <Select value={idType} onValueChange={setIdType}>
                      <SelectTrigger id="id-type">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="passport">Passport</SelectItem>
                        <SelectItem value="drivers_license">Driver&apos;s License</SelectItem>
                        <SelectItem value="national_id">National ID</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="id-number">Document Number</Label>
                    <Input
                      id="id-number"
                      value={idNumber}
                      onChange={(e) => setIdNumber(e.target.value)}
                      placeholder="Enter document number"
                      required
                    />
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="id-front">ID Front Image</Label>
                  <Input
                    id="id-front"
                    type="file"
                    accept="image/*"
                    onChange={(e) => setIdFrontFile(e.target.files?.[0] || null)}
                    required
                  />
                </div>

                {idType === 'drivers_license' && (
                  <div className="space-y-2">
                    <Label htmlFor="id-back">ID Back Image (Optional)</Label>
                    <Input
                      id="id-back"
                      type="file"
                      accept="image/*"
                      onChange={(e) => setIdBackFile(e.target.files?.[0] || null)}
                    />
                  </div>
                )}

                <div className="space-y-2">
                  <Label htmlFor="selfie">Selfie Photo</Label>
                  <Input
                    id="selfie"
                    type="file"
                    accept="image/*"
                    onChange={(e) => setSelfieFile(e.target.files?.[0] || null)}
                    required
                  />
                  <p className="text-xs text-muted-foreground">
                    Take a clear selfie holding your ID next to your face
                  </p>
                </div>

                <Button type="submit" disabled={uploading}>
                  <Upload className="mr-2 h-4 w-4" />
                  {uploading ? 'Uploading...' : 'Submit ID Verification'}
                </Button>
              </form>
            )}
          </CardContent>
        </Card>

        {/* Phone Verification */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Phone className="h-5 w-5" />
              Phone Verification
            </CardTitle>
            <CardDescription>
              Verify your phone number to receive booking notifications
            </CardDescription>
          </CardHeader>
          <CardContent>
            {status.phone_verification_status === 'verified' ? (
              <Alert>
                <CheckCircle className="h-4 w-4 text-green-600" />
                <AlertDescription className="text-green-600">
                  Phone number verified: {phoneNumber}
                </AlertDescription>
              </Alert>
            ) : (
              <div className="space-y-4">
                <div className="flex gap-2">
                  <Input
                    type="tel"
                    placeholder="+1 (555) 000-0000"
                    value={phoneNumber}
                    onChange={(e) => setPhoneNumber(e.target.value)}
                    disabled={phoneSent}
                  />
                  <Button onClick={handlePhoneVerification} disabled={phoneSent || !phoneNumber}>
                    Send Code
                  </Button>
                </div>

                {phoneSent && (
                  <div className="flex gap-2">
                    <Input
                      type="text"
                      placeholder="Enter 6-digit code"
                      value={phoneCode}
                      onChange={(e) => setPhoneCode(e.target.value)}
                      maxLength={6}
                    />
                    <Button onClick={handlePhoneCodeVerify}>
                      Verify
                    </Button>
                  </div>
                )}
              </div>
            )}
          </CardContent>
        </Card>

        {/* Address Verification */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Home className="h-5 w-5" />
              Address Verification
            </CardTitle>
            <CardDescription>
              Upload a utility bill or bank statement to verify your address
            </CardDescription>
          </CardHeader>
          <CardContent>
            {status.address_verification_status === 'approved' ? (
              <Alert>
                <CheckCircle className="h-4 w-4 text-green-600" />
                <AlertDescription className="text-green-600">
                  Address verified successfully
                </AlertDescription>
              </Alert>
            ) : (
              <form onSubmit={handleAddressUpload} className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="address">Full Address</Label>
                  <Input
                    id="address"
                    value={address}
                    onChange={(e) => setAddress(e.target.value)}
                    placeholder="123 Main St, City, State, ZIP"
                    required
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="address-proof">Proof of Address</Label>
                  <Input
                    id="address-proof"
                    type="file"
                    accept="image/*,application/pdf"
                    onChange={(e) => setAddressProofFile(e.target.files?.[0] || null)}
                    required
                  />
                  <p className="text-xs text-muted-foreground">
                    Upload utility bill, bank statement, or government letter (max 3 months old)
                  </p>
                </div>

                <Button type="submit" disabled={uploading}>
                  <Upload className="mr-2 h-4 w-4" />
                  {uploading ? 'Uploading...' : 'Submit Address Verification'}
                </Button>
              </form>
            )}
          </CardContent>
        </Card>

        {/* Background Check */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Shield className="h-5 w-5" />
              Background Check
            </CardTitle>
            <CardDescription>
              Optional background check to increase your trust score
            </CardDescription>
          </CardHeader>
          <CardContent>
            {status.background_check_status === 'completed' ? (
              <Alert>
                <CheckCircle className="h-4 w-4 text-green-600" />
                <AlertDescription className="text-green-600">
                  Background check completed successfully
                </AlertDescription>
              </Alert>
            ) : status.background_check_status === 'pending' ? (
              <Alert>
                <Clock className="h-4 w-4" />
                <AlertDescription>
                  Background check is in progress. This may take 1-3 business days.
                </AlertDescription>
              </Alert>
            ) : (
              <div className="space-y-4">
                <p className="text-sm text-muted-foreground">
                  A background check will verify your criminal record and increase your verification score.
                  This is optional but highly recommended for property owners.
                </p>
                <Button onClick={handleBackgroundCheck}>
                  Request Background Check
                </Button>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}
