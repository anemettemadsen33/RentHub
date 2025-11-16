'use client';

import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { FaGoogle, FaFacebook } from 'react-icons/fa';
import Link from 'next/link';

export default function SocialAuthPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleGoogleLogin = async () => {
    setLoading(true);
    window.location.href = `${process.env.NEXT_PUBLIC_API_BASE_URL}/auth/google/redirect`;
  };

  const handleFacebookLogin = async () => {
    setLoading(true);
    window.location.href = `${process.env.NEXT_PUBLIC_API_BASE_URL}/auth/facebook/redirect`;
  };

  const handleEmailLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    
    try {
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_BASE_URL}/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ email, password }),
      });

      const data = await response.json();

      if (response.ok) {
        localStorage.setItem('auth_token', data.token);
        window.location.href = '/dashboard';
      } else {
        alert(data.message || 'Login failed');
      }
    } catch (error) {
      console.error('Login error:', error);
      alert('An error occurred during login');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-purple-50 p-4">
      <Card className="w-full max-w-md shadow-xl">
        <CardHeader className="space-y-1">
          <CardTitle className="text-2xl font-bold text-center">Welcome to RentHub</CardTitle>
          <CardDescription className="text-center">Sign in to your account to continue</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Button variant="outline" className="w-full h-11" onClick={handleGoogleLogin} disabled={loading}>
              <FaGoogle className="mr-2 h-4 w-4 text-red-500" />
              Continue with Google
            </Button>
            <Button variant="outline" className="w-full h-11" onClick={handleFacebookLogin} disabled={loading}>
              <FaFacebook className="mr-2 h-4 w-4 text-blue-600" />
              Continue with Facebook
            </Button>
          </div>
          <div className="relative"><div className="absolute inset-0 flex items-center"><Separator /></div><div className="relative flex justify-center text-xs uppercase"><span className="bg-white px-2 text-muted-foreground">Or continue with email</span></div></div>
          <form onSubmit={handleEmailLogin} className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="email">Email</Label>
              <Input id="email" type="email" placeholder="name@example.com" value={email} onChange={(e) => setEmail(e.target.value)} required disabled={loading} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="password">Password</Label>
              <Input id="password" type="password" placeholder="••••••••" value={password} onChange={(e) => setPassword(e.target.value)} required disabled={loading} />
            </div>
            <Button type="submit" className="w-full" disabled={loading}>{loading ? 'Signing in...' : 'Sign In'}</Button>
          </form>
          <div className="text-center text-sm text-muted-foreground space-y-2">
            <Link href="/auth/forgot-password" className="hover:text-primary hover:underline block">Forgot your password?</Link>
            <div>Don&apos;t have an account? <Link href="/auth/register" className="text-primary hover:underline font-medium">Sign up</Link></div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
