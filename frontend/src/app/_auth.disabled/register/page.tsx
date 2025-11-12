'use client';

import Link from 'next/link';
import { useAuth } from '@/contexts/auth-context';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { createLogger } from '@/lib/logger';
import { useForm, FormProvider } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { registerSchema, type RegisterFormData } from '@/lib/validation-schemas';
import { FormInput, FormErrorSummary } from '@/components/form/form-components';

const registerLogger = createLogger('RegisterPage');

export default function RegisterPage() {
  const { register: registerUser } = useAuth();
  
  const methods = useForm<RegisterFormData>({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      name: '',
      email: '',
      password: '',
      passwordConfirmation: '',
    },
  });

  const { handleSubmit, formState: { isSubmitting } } = methods;

  const onSubmit = async (data: RegisterFormData) => {
    try {
      await registerUser(data.name, data.email, data.password, data.passwordConfirmation);
      registerLogger.info('User registered successfully', { email: data.email });
    } catch (error: any) {
      registerLogger.error('Registration failed', { 
        email: data.email,
        error: error?.message || 'Unknown error',
        response: error?.response?.data,
        status: error?.response?.status,
      });
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-background py-12 px-4 sm:px-6 lg:px-8">
      <Card className="w-full max-w-md border-border">
        <CardHeader className="space-y-1">
          <div className="flex justify-center mb-4">
            <div className="h-12 w-12 rounded-lg bg-primary flex items-center justify-center">
              <span className="text-primary-foreground text-xl font-bold">R</span>
            </div>
          </div>
          <CardTitle className="text-2xl font-bold text-center tracking-tight">Create an account</CardTitle>
          <CardDescription className="text-center text-sm">
            Enter your information to get started
          </CardDescription>
        </CardHeader>
        <FormProvider {...methods}>
          <form onSubmit={handleSubmit(onSubmit)}>
            <CardContent className="space-y-4">
              <FormErrorSummary />
              
              <FormInput
                name="name"
                label="Full Name"
                type="text"
                placeholder="John Doe"
                required
                autoComplete="name"
              />

              <FormInput
                name="email"
                label="Email"
                type="email"
                placeholder="m@example.com"
                required
                autoComplete="email"
              />

              <FormInput
                name="password"
                label="Password"
                type="password"
                placeholder="••••••••"
                required
                autoComplete="new-password"
                description="Must be at least 8 characters with uppercase, lowercase, and number"
              />

              <FormInput
                name="passwordConfirmation"
                label="Confirm Password"
                type="password"
                placeholder="••••••••"
                required
                autoComplete="new-password"
              />
            </CardContent>
            <CardFooter className="flex flex-col space-y-4">
              <Button type="submit" className="w-full" disabled={isSubmitting}>
                {isSubmitting ? 'Creating account...' : 'Create account'}
              </Button>
              <p className="text-sm text-center text-muted-foreground">
                Already have an account?{' '}
                <Link href="/auth/login" className="text-primary hover:underline">
                  Sign in
                </Link>
              </p>
            </CardFooter>
          </form>
        </FormProvider>
      </Card>
    </div>
  );
}
