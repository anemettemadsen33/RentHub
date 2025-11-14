'use client';

import { useState } from 'react';
import Link from 'next/link';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { createLogger } from '@/lib/logger';
import { useForm, FormProvider } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { forgotPasswordSchema, type ForgotPasswordFormData } from '@/lib/validation-schemas';
import { FormInput, FormErrorSummary } from '@/components/form/form-components';
import { ArrowLeft, Mail, CheckCircle2 } from 'lucide-react';
import apiClient from '@/lib/api-client';

const forgotPasswordLogger = createLogger('ForgotPasswordPage');

export default function ForgotPasswordPage() {
  const [isSuccess, setIsSuccess] = useState(false);
  const [submittedEmail, setSubmittedEmail] = useState('');
  
  const methods = useForm<ForgotPasswordFormData>({
    resolver: zodResolver(forgotPasswordSchema),
    defaultValues: {
      email: '',
    },
  });

  const { handleSubmit, formState: { isSubmitting } } = methods;

  const onSubmit = async (data: ForgotPasswordFormData) => {
    try {
      // Call the forgot password API
      await apiClient.post('/auth/forgot-password', { email: data.email });
      
      forgotPasswordLogger.info('Password reset email sent', { email: data.email });
      setSubmittedEmail(data.email);
      setIsSuccess(true);
    } catch (error: any) {
      forgotPasswordLogger.error('Failed to send password reset email', error, { email: data.email });
      // Don't reveal if email exists or not for security
      setSubmittedEmail(data.email);
      setIsSuccess(true);
    }
  };

  if (isSuccess) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-background py-12 px-4 sm:px-6 lg:px-8">
        <Card className="w-full max-w-md border-border">
          <CardHeader className="space-y-1 text-center">
            <div className="flex justify-center mb-4">
              <div className="h-16 w-16 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center">
                <CheckCircle2 className="h-8 w-8 text-green-600 dark:text-green-400" />
              </div>
            </div>
            <CardTitle className="text-2xl font-bold tracking-tight">Check your email</CardTitle>
            <CardDescription className="text-sm">
              We&apos;ve sent password reset instructions to
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="text-center">
              <p className="font-semibold text-foreground">{submittedEmail}</p>
            </div>
            
            <div className="bg-muted/50 rounded-lg p-4 text-sm text-muted-foreground">
              <p className="mb-2">
                <Mail className="inline h-4 w-4 mr-2" />
                Didn&apos;t receive the email?
              </p>
              <ul className="list-disc list-inside space-y-1 ml-6">
                <li>Check your spam folder</li>
                <li>Verify the email address is correct</li>
                <li>Wait a few minutes and check again</li>
              </ul>
            </div>
          </CardContent>
          <CardFooter className="flex flex-col space-y-3">
            <Button
              variant="outline"
              className="w-full"
              onClick={() => setIsSuccess(false)}
            >
              Try another email address
            </Button>
            <Link href="/auth/login" className="w-full">
              <Button variant="ghost" className="w-full">
                <ArrowLeft className="mr-2 h-4 w-4" />
                Back to login
              </Button>
            </Link>
          </CardFooter>
        </Card>
      </div>
    );
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-background py-12 px-4 sm:px-6 lg:px-8">
      <Card className="w-full max-w-md border-border">
        <CardHeader className="space-y-1">
          <div className="flex justify-center mb-4">
            <div className="h-12 w-12 rounded-lg bg-primary flex items-center justify-center">
              <span className="text-primary-foreground text-xl font-bold">R</span>
            </div>
          </div>
          <CardTitle className="text-2xl font-bold text-center tracking-tight">
            Forgot your password?
          </CardTitle>
          <CardDescription className="text-center text-sm">
            Enter your email address and we&apos;ll send you instructions to reset your password
          </CardDescription>
        </CardHeader>
        <FormProvider {...methods}>
          <form onSubmit={handleSubmit(onSubmit)}>
            <CardContent className="space-y-4">
              <FormErrorSummary />
              
              <FormInput
                name="email"
                label="Email"
                type="email"
                placeholder="m@example.com"
                required
                autoComplete="email"
                autoFocus
              />

              <div className="bg-muted/50 rounded-lg p-3 text-sm text-muted-foreground">
                <p>
                  We&apos;ll send you an email with a link to reset your password. 
                  The link will expire in 1 hour.
                </p>
              </div>
            </CardContent>
            <CardFooter className="flex flex-col space-y-3">
              <Button type="submit" className="w-full" disabled={isSubmitting}>
                {isSubmitting ? 'Sending...' : 'Send reset instructions'}
              </Button>

              <Link href="/auth/login" className="w-full">
                <Button type="button" variant="ghost" className="w-full">
                  <ArrowLeft className="mr-2 h-4 w-4" />
                  Back to login
                </Button>
              </Link>
            </CardFooter>
          </form>
        </FormProvider>
      </Card>
    </div>
  );
}
