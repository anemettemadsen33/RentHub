"use client";

import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RefreshCw, Activity } from 'lucide-react';
import apiClient from '@/lib/api-client';
import { useToast } from '@/hooks/use-toast';

interface BucketUsage {
  count: number;
  limit: number;
  remaining: number;
  key: string;
}

interface RateLimiterData {
  clientId: string;
  minuteWindow: string;
  buckets: {
    pageview: BucketUsage;
    default: BucketUsage;
  };
}

export function RateLimiterWidget() {
  const { toast } = useToast();
  const [clientId, setClientId] = useState('');
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState<RateLimiterData | null>(null);

  const fetchUsage = async () => {
    if (!clientId.trim()) {
      toast({ title: 'Error', description: 'Please enter a client ID', variant: 'destructive' });
      return;
    }

    setLoading(true);
    try {
      const response = await apiClient.get(`/analytics/rate/usage?clientId=${encodeURIComponent(clientId)}`);
      setData(response.data);
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.error || 'Failed to fetch rate limiter data',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const getUsageColor = (remaining: number, limit: number): string => {
    const percentage = (remaining / limit) * 100;
    if (percentage > 50) return 'text-green-600';
    if (percentage > 20) return 'text-yellow-600';
    return 'text-red-600';
  };

  const getProgressBarColor = (remaining: number, limit: number): string => {
    const percentage = (remaining / limit) * 100;
    if (percentage > 50) return 'bg-green-500';
    if (percentage > 20) return 'bg-yellow-500';
    return 'bg-red-500';
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Activity className="h-5 w-5" />
          Rate Limiter Usage
        </CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        <div className="flex gap-2">
          <div className="flex-1">
            <Label htmlFor="clientId">Client ID</Label>
            <Input
              id="clientId"
              placeholder="Enter client ID..."
              value={clientId}
              onChange={(e) => setClientId(e.target.value)}
              onKeyDown={(e) => e.key === 'Enter' && fetchUsage()}
            />
          </div>
          <Button
            onClick={fetchUsage}
            disabled={loading}
            className="mt-auto"
          >
            {loading ? (
              <RefreshCw className="h-4 w-4 animate-spin" />
            ) : (
              <RefreshCw className="h-4 w-4" />
            )}
            <span className="ml-2">Check</span>
          </Button>
        </div>

        {data && (
          <div className="space-y-4 mt-4">
            <div className="text-sm text-gray-600">
              <p><strong>Client:</strong> {data.clientId}</p>
              <p><strong>Minute Window:</strong> {data.minuteWindow}</p>
            </div>

            {/* Pageview Bucket */}
            <div className="space-y-2">
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium">Pageview Events</span>
                <span className={`text-sm font-semibold ${getUsageColor(data.buckets.pageview.remaining, data.buckets.pageview.limit)}`}>
                  {data.buckets.pageview.remaining} / {data.buckets.pageview.limit} remaining
                </span>
              </div>
              <div className="w-full bg-gray-200 rounded-full h-2">
                <div
                  className={`h-2 rounded-full transition-all ${getProgressBarColor(data.buckets.pageview.remaining, data.buckets.pageview.limit)}`}
                  style={{ width: `${(data.buckets.pageview.count / data.buckets.pageview.limit) * 100}%` }}
                />
              </div>
              <p className="text-xs text-gray-500">
                {data.buckets.pageview.count} requests this minute
              </p>
            </div>

            {/* Default Bucket */}
            <div className="space-y-2">
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium">Default Events</span>
                <span className={`text-sm font-semibold ${getUsageColor(data.buckets.default.remaining, data.buckets.default.limit)}`}>
                  {data.buckets.default.remaining} / {data.buckets.default.limit} remaining
                </span>
              </div>
              <div className="w-full bg-gray-200 rounded-full h-2">
                <div
                  className={`h-2 rounded-full transition-all ${getProgressBarColor(data.buckets.default.remaining, data.buckets.default.limit)}`}
                  style={{ width: `${(data.buckets.default.count / data.buckets.default.limit) * 100}%` }}
                />
              </div>
              <p className="text-xs text-gray-500">
                {data.buckets.default.count} requests this minute
              </p>
            </div>
          </div>
        )}

        {!data && !loading && (
          <div className="text-center py-8 text-gray-500 text-sm">
            Enter a client ID to view rate limiter usage
          </div>
        )}
      </CardContent>
    </Card>
  );
}
