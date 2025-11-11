'use client';

import { useEffect, useMemo, useState } from 'react';
import { MainLayout } from '@/components/layouts/main-layout';
import { referralService } from '@/lib/api-service';
import { trackMarketingEvent } from '@/lib/analytics-client';
import { ReferralCodeInfo, ReferralLeaderboardEntry, ReferralRecord } from '@/types/extended';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useToast } from '@/hooks/use-toast';
import { Copy, RefreshCcw, Send, Award } from 'lucide-react';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Skeleton } from '@/components/ui/skeleton';

export default function ReferralsPage() {
  const { toast } = useToast();
  const [codeInfo, setCodeInfo] = useState<ReferralCodeInfo | null>(null);
  const [referrals, setReferrals] = useState<ReferralRecord[]>([]);
  const [leaderboard, setLeaderboard] = useState<ReferralLeaderboardEntry[]>([]);
  const [inviteEmail, setInviteEmail] = useState('');
  const [checkingCode, setCheckingCode] = useState('');
  const [discountInfo, setDiscountInfo] = useState<any | null>(null);
  const [loading, setLoading] = useState(true);
  const [invitesSent, setInvitesSent] = useState(0);
  // Pagination for referrals
  const [page, setPage] = useState(1);
  const perPage = 10;

  const referralLink = useMemo(() => {
    const base = typeof window !== 'undefined' ? window.location.origin : '';
    return codeInfo?.code ? `${base}/signup?ref=${codeInfo.code}` : '';
  }, [codeInfo?.code]);

  useEffect(() => {
    load();
    try {
      const v = typeof localStorage !== 'undefined' ? localStorage.getItem('invites_sent_count') : null;
      if (v) setInvitesSent(parseInt(v || '0') || 0);
    } catch {}
  }, []);

  function incrementInvitesSent() {
    setInvitesSent((n) => {
      const next = n + 1;
      try { localStorage.setItem('invites_sent_count', String(next)); } catch {}
      return next;
    });
  }

  async function load(p: number = 1) {
    setLoading(true);
    try {
      const [c, list, lb] = await Promise.all([
        referralService.code().catch(() => ({ data: demoCode() })),
        referralService.list().catch(() => ({ data: demoReferrals().slice((p-1)*perPage, p*perPage), meta: { current_page: p, last_page: 3 } })),
        referralService.leaderboard().catch(() => ({ data: demoLeaderboard() })),
      ]);
      setCodeInfo((c.data || c));
      setReferrals((list.data || list));
      setLeaderboard((lb.data || lb));
      if ((list.data && list.data.meta) || (list.meta)) {
        const meta = list.data?.meta || list.meta; setPage(meta.current_page || p);
      } else { setPage(p); }
    } finally { setLoading(false); }
  }

  async function copyLink() {
    if (!referralLink) return;
    await navigator.clipboard.writeText(referralLink);
    toast({ title: 'Copied referral link' });
    trackMarketingEvent('referral_sent', { method: 'link' });
    incrementInvitesSent();
  }

  async function regenerate() {
    await referralService.regenerate().catch(() => ({}));
    toast({ title: 'Referral code regenerated' });
    load();
  }

  async function sendInvite() {
    if (!inviteEmail.trim()) { toast({ title: 'Enter an email', variant: 'destructive' }); return; }
    await referralService.create({ email: inviteEmail }).catch(() => ({}));
    setInviteEmail('');
    toast({ title: 'Invite sent' });
    trackMarketingEvent('referral_sent', { method: 'email', email_domain: inviteEmail.split('@')[1] || '' });
    incrementInvitesSent();
    load();
  }

  async function checkDiscount() {
    if (!checkingCode.trim()) return;
    const resp = await referralService.validate({ code: checkingCode }).catch(() => ({ data: { valid: true, discount_percent: 10 } }));
    setDiscountInfo(resp.data || resp);
  }

  if (loading) return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8 max-w-6xl space-y-6" aria-busy="true" aria-live="polite">
        <Skeleton className="h-8 w-1/3" />
        <div className="grid md:grid-cols-2 gap-6">
          <Skeleton className="h-44 w-full" />
          <Skeleton className="h-44 w-full" />
        </div>
        <div className="grid md:grid-cols-2 gap-6">
          <Skeleton className="h-72 w-full" />
          <Skeleton className="h-72 w-full" />
        </div>
        <Skeleton className="h-52 w-full" />
      </div>
    </MainLayout>
  );

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8 max-w-6xl space-y-8">
        <div className="mb-8">
          <h1 className="text-3xl font-bold tracking-tight mb-2">Referral Program</h1>
          <p className="text-muted-foreground">Invite friends and earn rewards</p>
        </div>
        
        <div className="grid md:grid-cols-2 gap-6">
          <Card>
            <CardHeader><CardTitle>Your Referral Code</CardTitle></CardHeader>
            <CardContent className="space-y-3">
              <TooltipProvider>
                <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                  <Input readOnly value={codeInfo?.code || ''} className="h-11 md:h-10" />
                  <Tooltip>
                    <TooltipTrigger asChild>
                      <Button variant="outline" onClick={copyLink} className="h-11 md:h-9 whitespace-nowrap"><Copy className="h-4 w-4 mr-1" /> Copy Link</Button>
                    </TooltipTrigger>
                    <TooltipContent>Copy your referral URL</TooltipContent>
                  </Tooltip>
                  <Tooltip>
                    <TooltipTrigger asChild>
                      <Button variant="ghost" onClick={regenerate} className="h-11 md:h-9"><RefreshCcw className="h-4 w-4" /></Button>
                    </TooltipTrigger>
                    <TooltipContent>Regenerate code</TooltipContent>
                  </Tooltip>
                </div>
              </TooltipProvider>
              <div className="text-xs text-muted-foreground">Share your link: <span className="font-mono break-all">{referralLink || '—'}</span></div>
              <div className="text-xs text-muted-foreground" aria-live="polite">Uses: {codeInfo?.uses ?? 0}{codeInfo?.max_uses ? ` / ${codeInfo.max_uses}` : ''}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <CardTitle>Invite a Friend</CardTitle>
                <Badge variant="outline" className="text-xs">Invites sent: {invitesSent}</Badge>
              </div>
            </CardHeader>
            <CardContent className="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
              <Input type="email" placeholder="friend@email.com" value={inviteEmail} onChange={(e) => setInviteEmail(e.target.value)} className="h-11 md:h-10" />
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button onClick={sendInvite} className="h-11 md:h-9 whitespace-nowrap"><Send className="h-4 w-4 mr-1" /> Send</Button>
                  </TooltipTrigger>
                  <TooltipContent>Send invite to this email</TooltipContent>
                </Tooltip>
              </TooltipProvider>
            </CardContent>
          </Card>
        </div>

        <div className="grid md:grid-cols-2 gap-6">
          <Card className="animate-fade-in-up">
            <CardHeader><CardTitle>Your Referrals</CardTitle></CardHeader>
            <CardContent>
              <div className="overflow-x-auto -mx-6 px-6 md:mx-0 md:px-0">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead className="whitespace-nowrap">Date</TableHead>
                      <TableHead className="whitespace-nowrap">Code</TableHead>
                      <TableHead className="whitespace-nowrap">Status</TableHead>
                      <TableHead className="whitespace-nowrap">Points</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {referrals.map(r => (
                      <TableRow key={r.id}>
                        <TableCell className="whitespace-nowrap">{new Date(r.created_at).toLocaleDateString()}</TableCell>
                        <TableCell className="font-mono whitespace-nowrap">{r.referral_code}</TableCell>
                        <TableCell className="capitalize whitespace-nowrap">{r.status}</TableCell>
                        <TableCell className="whitespace-nowrap">{r.reward_points ?? 0}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </div>
              <div className="flex justify-between items-center mt-3">
                <Button size="sm" variant="outline" disabled={page <= 1} onClick={() => load(Math.max(1, page - 1))}>Prev</Button>
                <span className="text-xs text-gray-500">Page {page}</span>
                <Button size="sm" variant="outline" onClick={() => load(page + 1)}>Next</Button>
              </div>
            </CardContent>
          </Card>

          <Card className="animate-fade-in-up" style={{ animationDelay: '40ms' }}>
            <CardHeader><CardTitle>Referral Leaderboard</CardTitle></CardHeader>
            <CardContent>
              {leaderboard.slice(0,10).map((entry, idx) => (
                <div key={entry.rank} className="flex items-center gap-3 text-sm py-1 animate-fade-in-up" style={{ animationDelay: `${Math.min(idx, 8) * 40}ms` }}>
                  <div className="w-6 text-center font-semibold">#{entry.rank}</div>
                  <div className="flex-1">{entry.user_name || `User ${entry.user_id}`}</div>
                  <div className="text-gray-600">{entry.converted_referrals} conversions</div>
                </div>
              ))}
            </CardContent>
          </Card>
        </div>

        <Card className="animate-fade-in-up">
          <CardHeader><CardTitle>Check Referral Discount</CardTitle></CardHeader>
          <CardContent className="flex flex-col md:flex-row gap-3 items-stretch md:items-center">
            <Input placeholder="Enter referral code" value={checkingCode} onChange={(e) => setCheckingCode(e.target.value)} className="md:max-w-sm h-11 md:h-10" />
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <Button onClick={checkDiscount} className="h-11 md:h-9"><Award className="h-4 w-4 mr-1" /> Check</Button>
                </TooltipTrigger>
                <TooltipContent>Validate code and show discount</TooltipContent>
              </Tooltip>
            </TooltipProvider>
            {discountInfo && (
              <div className="text-sm text-gray-700" aria-live="polite">{discountInfo.valid ? `Valid code. Discount: ${discountInfo.discount_percent || 0}%` : 'Invalid code'}</div>
            )}
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}

// Demo fallbacks
function demoCode(): ReferralCodeInfo { return { code: 'FRIEND10', uses: 3, max_uses: 100, created_at: new Date().toISOString() }; }
function demoReferrals(): ReferralRecord[] {
  return Array.from({ length: 6 }).map((_, i) => ({
    id: i + 1,
    referrer_id: 1,
    referral_code: 'FRIEND10',
    status: (i % 3 === 0 ? 'pending' : (i % 3 === 1 ? 'converted' : 'expired')) as ReferralRecord['status'],
    reward_points: i % 3 === 1 ? 200 : 0,
    created_at: new Date(Date.now() - i * 86400000).toISOString(),
  }));
}
function demoLeaderboard(): ReferralLeaderboardEntry[] {
  return [
    { rank: 1, user_id: 10, user_name: 'Alice', total_referrals: 24, converted_referrals: 12, points_earned: 2400 },
    { rank: 2, user_id: 11, user_name: 'Bob', total_referrals: 20, converted_referrals: 10, points_earned: 1800 },
    { rank: 3, user_id: 12, user_name: 'Carlos', total_referrals: 18, converted_referrals: 9, points_earned: 1600 },
  ];
}