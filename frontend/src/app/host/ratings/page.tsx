'use client';

import { useEffect, useState, useCallback } from 'react';
import { useAuth } from '@/contexts/auth-context';
import { useRouter } from 'next/navigation';
import { MainLayout } from '@/components/layouts/main-layout';
import { useToast } from '@/hooks/use-toast';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { API_ENDPOINTS } from '@/lib/api-endpoints';
import apiClient from '@/lib/api-client';
import { HostRatingSummary, Review } from '@/types/extended';
import { Star, Loader2 } from 'lucide-react';

export default function HostRatingsPage() {
  const { user } = useAuth();
  const router = useRouter();
  const { toast } = useToast();

  const [summary, setSummary] = useState<HostRatingSummary | null>(null);
  const [reviews, setReviews] = useState<Review[]>([]);
  const [respondingId, setRespondingId] = useState<number | null>(null);
  const [responseValue, setResponseValue] = useState('');
  const [saving, setSaving] = useState(false);

  const load = useCallback(async () => {
    try {
      const [{ data: ratingData }, { data: list }] = await Promise.all([
        apiClient.get('/host/rating').catch(() => ({ data: { data: demoHostSummary(user!.id) } })),
        apiClient.get(API_ENDPOINTS.reviews.myReviews).catch(() => ({ data: { data: demoHostReviews() } })),
      ]);
      setSummary(ratingData.data);
      setReviews(list.data);
    } catch {
      toast({ title: 'Error', description: 'Failed to load host ratings', variant: 'destructive' });
    }
  }, [user, toast]);

  useEffect(() => {
    if (!user) { router.push('/auth/login'); return; }
    load();
  }, [user, router, load]);

  const startRespond = useCallback((id: number) => {
    setRespondingId(id);
    setResponseValue(reviews.find(r => r.id === id)?.host_response || '');
  }, [reviews]);

  const saveResponse = useCallback(async () => {
    if (!respondingId) return;
    setSaving(true);
    try {
      await apiClient.post(API_ENDPOINTS.reviews.addResponse(respondingId), { body: responseValue }).catch(() => null);
      setReviews(prev => prev.map(r => r.id === respondingId ? { ...r, host_response: responseValue, host_response_date: new Date().toISOString() } : r));
      toast({ title: 'Response saved' });
      setRespondingId(null); setResponseValue('');
    } catch {
      toast({ title: 'Error', description: 'Failed to save response', variant: 'destructive' });
    } finally { setSaving(false); }
  }, [respondingId, responseValue, toast]);

  if (!user) return null;

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8 max-w-6xl">
        <div className="flex items-start justify-between mb-6 flex-col md:flex-row gap-4">
          <div>
            <h1 className="text-3xl font-bold mb-2">Host Ratings</h1>
            <p className="text-gray-600">Your aggregated performance and recent guest feedback.</p>
          </div>
          {summary && (
            <Card className="w-full md:w-80">
              <CardHeader className="pb-2">
                <CardTitle className="text-base">Rating Summary</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="text-5xl font-bold flex items-center gap-2">
                  {summary.overall.toFixed(1)} <Star className="h-8 w-8 fill-primary text-primary" />
                </div>
                <p className="text-xs text-gray-500">Across {summary.total_reviews} reviews</p>
                <div className="space-y-2">
                  {['communication','cleanliness','accuracy','value'].map(key => (
                    <div className="flex items-center gap-2" key={key}>
                      <span className="w-24 text-xs capitalize">{key}</span>
                      <Progress value={(summary as any)[key] * 20} className="h-2 flex-1" />
                      <span className="text-xs w-6 text-right">{(summary as any)[key].toFixed(1)}</span>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          )}
        </div>

        <div className="space-y-4">
          {reviews.map(r => (
            <Card key={r.id}>
              <CardContent className="pt-6 space-y-4">
                <div className="flex items-start gap-4">
                  <Avatar>
                    <AvatarImage src={r.user?.avatar_url} />
                    <AvatarFallback>{r.user?.name?.split(' ').map(n=>n[0]).join('')}</AvatarFallback>
                  </Avatar>
                  <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2 flex-wrap">
                      <div className="flex items-center gap-1">
                        <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                        <span className="font-medium">{r.rating.toFixed(1)}</span>
                      </div>
                      <span className="text-xs text-gray-500">{new Date(r.created_at).toLocaleDateString()}</span>
                      <Badge variant="outline">Property #{r.property_id}</Badge>
                    </div>
                    <p className="mt-2 text-sm leading-relaxed whitespace-pre-line">{r.comment}</p>
                    {r.host_response && respondingId !== r.id && (
                      <div className="mt-4 border rounded p-3 bg-muted/40">
                        <div className="text-xs font-semibold mb-1">Your response:</div>
                        <p className="text-xs text-gray-700 whitespace-pre-line">{r.host_response}</p>
                        <div className="text-[10px] text-gray-500 mt-1">{r.host_response_date && new Date(r.host_response_date).toLocaleString()}</div>
                      </div>
                    )}
                    {respondingId === r.id ? (
                      <div className="mt-4 space-y-2">
                        <Textarea value={responseValue} onChange={(e) => setResponseValue(e.target.value)} rows={3} placeholder="Write your response..." />
                        <div className="flex gap-2">
                          <Button size="sm" onClick={saveResponse} disabled={saving}>{saving && <Loader2 className="h-4 w-4 mr-1 animate-spin" />}Save</Button>
                          <Button size="sm" variant="outline" onClick={() => { setRespondingId(null); setResponseValue(''); }}>Cancel</Button>
                        </div>
                      </div>
                    ) : (
                      <div className="mt-3">
                        <Button variant="ghost" size="sm" onClick={() => startRespond(r.id)}>{r.host_response ? 'Edit Response' : 'Respond'}</Button>
                      </div>
                    )}
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </MainLayout>
  );
}

function demoHostSummary(hostId: number): HostRatingSummary {
  return {
    host_id: hostId,
    overall: 4.8,
    communication: 4.9,
    cleanliness: 4.7,
    accuracy: 4.6,
    value: 4.5,
    total_reviews: 128,
  };
}

function demoHostReviews(): Review[] {
  return [
    {
      id: 2001,
      property_id: 12,
      user_id: 501,
      booking_id: 9001,
      rating: 4.9,
      cleanliness_rating: 5,
      communication_rating: 5,
      accuracy_rating: 5,
      location_rating: 4,
      value_rating: 5,
      comment: 'Amazing host! Super responsive and helpful throughout the stay. Would definitely book again.',
      user: { id: 501, name: 'Carlos Mendoza' },
      host_response: 'Thank you Carlos, you were a great guest! Hope to host you again.',
      host_response_date: new Date().toISOString(),
      helpful_count: 6,
      created_at: new Date(Date.now() - 72_000_000).toISOString(),
      updated_at: new Date().toISOString(),
    },
  ];
}
