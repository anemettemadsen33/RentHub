'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Upload, CreditCard, CheckCircle, Copy, Info } from 'lucide-react';
import { useToast } from '@/hooks/use-toast';

export default function BankTransferPaymentPage() {
  const router = useRouter();
  const { toast } = useToast();
  const [proofFile, setProofFile] = useState<File | null>(null);
  const [transactionId, setTransactionId] = useState('');
  const [notes, setNotes] = useState('');
  const [uploading, setUploading] = useState(false);

  const bankDetails = {
    bankName: 'RentHub International Bank',
    accountName: 'RentHub Platform SRL',
    accountNumber: '1234567890',
    iban: 'RO49AAAA1B31007593840000',
    swift: 'AAABROBU',
    reference: `BOOKING-${Date.now()}`,
  };

  const copyToClipboard = (text: string, label: string) => {
    navigator.clipboard.writeText(text);
    toast({ title: 'Copied!', description: `${label} copied to clipboard` });
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files?.[0]) setProofFile(e.target.files[0]);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!proofFile) {
      toast({ title: 'Error', description: 'Please upload proof of payment', variant: 'destructive' });
      return;
    }
    setUploading(true);
    try {
      const formData = new FormData();
      formData.append('proof', proofFile);
      formData.append('transaction_id', transactionId);
      formData.append('notes', notes);
      formData.append('reference', bankDetails.reference);

      const response = await fetch(`${process.env.NEXT_PUBLIC_API_BASE_URL}/payments/bank-transfer/proof`, {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` },
        body: formData,
      });

      if (response.ok) {
        toast({ title: 'Success!', description: 'Payment proof uploaded successfully. We will verify it within 24 hours.' });
        router.push('/bookings');
      } else throw new Error('Upload failed');
    } catch (error) {
      toast({ title: 'Error', description: 'Failed to upload payment proof. Please try again.', variant: 'destructive' });
    } finally {
      setUploading(false);
    }
  };

  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4">
      <div className="max-w-4xl mx-auto space-y-6">
        <div className="text-center">
          <h1 className="text-3xl font-bold">Bank Transfer Payment</h1>
          <p className="text-muted-foreground mt-2">Complete your booking by transferring funds to our bank account</p>
        </div>
        <Alert><Info className="h-4 w-4" /><AlertDescription>Please transfer the exact amount to the account below and upload your payment proof. Your booking will be confirmed within 24 hours after verification.</AlertDescription></Alert>
        <div className="grid md:grid-cols-2 gap-6">
          <Card>
            <CardHeader><CardTitle className="flex items-center gap-2"><CreditCard className="h-5 w-5" />Bank Account Details</CardTitle><CardDescription>Transfer funds to this account</CardDescription></CardHeader>
            <CardContent className="space-y-4">
              {Object.entries({ 'Bank Name': bankDetails.bankName, 'Account Name': bankDetails.accountName, 'Account Number': bankDetails.accountNumber, 'IBAN': bankDetails.iban, 'SWIFT/BIC': bankDetails.swift }).map(([label, value]) => (
                <div key={label} className="space-y-2"><Label className="text-xs text-muted-foreground">{label}</Label><div className="flex items-center justify-between p-3 bg-gray-50 rounded-lg"><span className="font-mono text-sm">{value}</span><Button size="sm" variant="ghost" onClick={() => copyToClipboard(value, label)}><Copy className="h-4 w-4" /></Button></div></div>
              ))}
              <div className="space-y-2"><Label className="text-xs text-muted-foreground">Payment Reference (Important!)</Label><div className="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-lg"><span className="font-mono font-bold text-blue-700">{bankDetails.reference}</span><Button size="sm" variant="ghost" onClick={() => copyToClipboard(bankDetails.reference, 'Reference')}><Copy className="h-4 w-4" /></Button></div><p className="text-xs text-muted-foreground">⚠️ Include this reference in your transfer to ensure fast processing</p></div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader><CardTitle className="flex items-center gap-2"><Upload className="h-5 w-5" />Upload Payment Proof</CardTitle><CardDescription>Upload your bank transfer receipt or screenshot</CardDescription></CardHeader>
            <CardContent>
              <form onSubmit={handleSubmit} className="space-y-4">
                <div className="space-y-2"><Label htmlFor="proof">Payment Receipt/Screenshot *</Label><Input id="proof" type="file" accept="image/*,.pdf" onChange={handleFileChange} required />{proofFile && <p className="text-sm text-green-600 flex items-center gap-2"><CheckCircle className="h-4 w-4" />{proofFile.name}</p>}</div>
                <div className="space-y-2"><Label htmlFor="transaction">Transaction ID (Optional)</Label><Input id="transaction" placeholder="e.g., TXN123456789" value={transactionId} onChange={(e) => setTransactionId(e.target.value)} /></div>
                <div className="space-y-2"><Label htmlFor="notes">Additional Notes (Optional)</Label><Textarea id="notes" placeholder="Any additional information..." value={notes} onChange={(e) => setNotes(e.target.value)} rows={3} /></div>
                <Button type="submit" className="w-full" disabled={uploading}>{uploading ? 'Uploading...' : 'Submit Payment Proof'}</Button>
              </form>
            </CardContent>
          </Card>
        </div>
        <Card>
          <CardHeader><CardTitle>Payment Steps</CardTitle></CardHeader>
          <CardContent>
            <ol className="space-y-3">
              {[
                { title: 'Transfer the amount', desc: 'Use the bank details provided above' },
                { title: 'Include payment reference', desc: 'This helps us identify your payment quickly' },
                { title: 'Upload proof of payment', desc: 'Screenshot or receipt from your bank' },
                { title: 'Wait for confirmation', desc: "We'll verify and confirm within 24 hours" }
              ].map((step, i) => (
                <li key={i} className="flex gap-3"><span className="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-medium">{i+1}</span><div><p className="font-medium">{step.title}</p><p className="text-sm text-muted-foreground">{step.desc}</p></div></li>
              ))}
            </ol>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
