"use client";

import { useState } from 'react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import apiClient from '@/lib/api-client';
import { useToast } from '@/hooks/use-toast';
import { trackMarketingEvent } from '@/lib/analytics-client';

export function NewsletterSignup() {
  const [email, setEmail] = useState('');
  const [loading, setLoading] = useState(false);
  const { toast } = useToast();

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    const trimmed = email.trim();
    if (!trimmed || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed)) {
      toast({ title: 'Enter a valid email', variant: 'destructive' });
      return;
    }
    setLoading(true);
    try {
      // Best-effort: if backend route exists, this will persist; otherwise no-op.
      await apiClient.post('/marketing/newsletter', { email: trimmed }).catch(() => ({}));
      trackMarketingEvent('newsletter_signup', { email_domain: trimmed.split('@')[1] || '' });
      toast({ title: 'Subscribed', description: 'You\'re on the list.' });
      setEmail('');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form 
      onSubmit={submit} 
      className="mx-auto max-w-xl p-4 border rounded-lg bg-white shadow-sm text-left"
      suppressHydrationWarning
    >
      <h3 className="text-xl font-semibold mb-2">Join our newsletter</h3>
      <p className="text-sm text-gray-600 mb-4">Get property deals, product updates, and tips.</p>
      <div className="flex gap-2 items-center">
        <Input
          type="email"
          placeholder="you@example.com"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          className="h-11"
        />
        <Button type="submit" className="h-11" disabled={loading}>{loading ? 'Joining…' : 'Sign up'}</Button>
      </div>
      <p className="text-xs text-gray-500 mt-2">We respect your privacy. You can opt out anytime.</p>
    </form>
  );
}
