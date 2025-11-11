'use client';

import Link from 'next/link';
import { useAuth } from '@/contexts/auth-context';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { createLogger } from '@/lib/logger';
import { useForm, FormProvider } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { loginSchema, type LoginFormData } from '@/lib/validation-schemas';
import { FormInput, FormErrorSummary } from '@/components/form/form-components';

const loginLogger = createLogger('LoginPage');

export default function LoginPage() {
  const { login } = useAuth();
  
  const methods = useForm<LoginFormData>({
    resolver: zodResolver(loginSchema),
    defaultValues: {
      email: '',
      password: '',
    },
  });

  const { handleSubmit, formState: { isSubmitting } } = methods;

  const onSubmit = async (data: LoginFormData) => {
    try {
      await login(data.email, data.password);
      loginLogger.info('User logged in successfully', { email: data.email });
    } catch (error) {
      loginLogger.error('Login failed', error, { email: data.email });
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
          <CardTitle className="text-2xl font-bold text-center tracking-tight">Welcome back</CardTitle>
          <CardDescription className="text-center text-sm">
            Enter your credentials to access your account
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
              />

              <FormInput
                name="password"
                label="Password"
                type="password"
                placeholder="••••••••"
                required
                autoComplete="current-password"
              />

              <div className="flex items-center justify-between">
                <Link href="/auth/forgot-password" className="text-sm text-primary hover:underline">
                  Forgot password?
                </Link>
              </div>
            </CardContent>
            <CardFooter className="flex flex-col space-y-4">
              <Button type="submit" className="w-full" disabled={isSubmitting}>
                {isSubmitting ? 'Signing in...' : 'Sign in'}
              </Button>
              <p className="text-sm text-center text-muted-foreground">
                Don&apos;t have an account?{' '}
                <Link href="/auth/register" className="text-primary hover:underline">
                  Sign up
                </Link>
              </p>
            </CardFooter>
          </form>
        </FormProvider>
      </Card>
    </div>
  );
}
