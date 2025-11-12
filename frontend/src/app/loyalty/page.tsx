'use client';

import { useEffect, useState } from 'react';
import { MainLayout } from '@/components/layouts/main-layout';
import { loyaltyService } from '@/lib/api-service';
import { LoyaltySummary, LoyaltyTier, LoyaltyTransaction, LoyaltyBenefit } from '@/types/extended';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { useToast } from '@/hooks/use-toast';
import { Gift, Trophy, Crown, RefreshCcw, Loader2, PartyPopper, ShoppingBag } from 'lucide-react';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Skeleton } from '@/components/ui/skeleton';

interface DiscountResult { discount_amount: number; final_total: number }

export default function LoyaltyPage() {
  const { toast } = useToast();
  const [summary, setSummary] = useState<LoyaltySummary | null>(null);
  const [tiers, setTiers] = useState<LoyaltyTier[]>([]);
  const [transactions, setTransactions] = useState<LoyaltyTransaction[]>([]);
  const [benefits, setBenefits] = useState<LoyaltyBenefit[]>([]);
  const [leaderboard, setLeaderboard] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [redeemOpen, setRedeemOpen] = useState(false);
  const [rewardCode, setRewardCode] = useState('');
  const [redeeming, setRedeeming] = useState(false);
  const [discountResult, setDiscountResult] = useState<DiscountResult | null>(null);
  // Pagination for transactions
  const [page, setPage] = useState(1);
  const perPage = 10;

  useEffect(() => { load(); }, []);

  async function load(p: number = 1) {
    setLoading(true);
    try {
      const [sum, tierList, tx, lb] = await Promise.all([
        loyaltyService.summary().catch(() => ({ data: demoSummary() })),
        loyaltyService.tiers().catch(() => ({ data: demoTiers() })),
        loyaltyService.transactions({ page: p, per_page: perPage }).catch(() => ({ data: demoTransactions().slice((p-1)*perPage, p*perPage), meta: { current_page: p, last_page: 3 } })),
        loyaltyService.leaderboard().catch(() => ({ data: demoLeaderboard() })),
      ]);
      const s = sum.data || sum; // handle direct demo object
      setSummary(s.data ? s.data : s);
      const t = tierList.data || tierList; setTiers(t.data ? t.data : t);
      const trx = tx.data || tx; setTransactions(trx.data ? trx.data : trx);
      // If server sent meta, update page controls
      if ((tx.data && tx.data.meta) || (tx.meta)) {
        const meta = tx.data?.meta || tx.meta;
        setPage(meta.current_page || p);
      } else {
        setPage(p);
      }
      const lbData = lb.data || lb; setLeaderboard(lbData.data ? lbData.data : lbData);
      // benefits for current tier
      const currentTierId = (s.data ? s.data.tier_id : s.tier_id);
      if (currentTierId) {
        const b = await loyaltyService.tierBenefits(currentTierId).catch(() => ({ data: demoBenefits(currentTierId) }));
        const bd = b.data || b; setBenefits(bd.data ? bd.data : bd);
      }
    } finally { setLoading(false); }
  }

  const progress = summary?.progress_to_next_tier_percent ?? 0;

  async function redeemReward() {
    if (!rewardCode.trim()) {
      toast({ title: 'Enter a reward code', variant: 'destructive' });
      return;
    }
    setRedeeming(true);
    try {
      const resp = await loyaltyService.redeem({ reward_code: rewardCode }).catch(() => ({ data: { points_spent: 0, description: 'Reward redeemed' } }));
      const payload = (resp?.data || resp) as any;
      // Optimistic update if API returns point deltas
      setSummary((prev) => {
        if (!prev) return prev;
        const spent = Number(payload.points_spent || payload.delta_points || 0);
        if (spent && !isNaN(spent)) {
          return { ...prev, points_balance: Math.max(0, prev.points_balance - Math.abs(spent)) };
        }
        return prev;
      });
      setTransactions((prev) => [
        {
          id: Date.now(),
          user_id: summary?.user_id || 0,
          type: 'redeem',
          points: Math.abs(Number((resp?.data?.points_spent ?? 0))) || 0,
          description: payload?.description || 'Reward redeemed',
          created_at: new Date().toISOString(),
        },
        ...prev,
      ]);
      toast({ title: 'Reward redeemed!' });
      setRewardCode('');
      setRedeemOpen(false);
      // Background refresh to reconcile accurate balances
      load(page);
    } catch {
      toast({ title: 'Failed', description: 'Could not redeem reward', variant: 'destructive' });
    } finally { setRedeeming(false); }
  }

  if (loading) return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8 max-w-6xl space-y-6" aria-busy="true" aria-live="polite">
        <Skeleton className="h-8 w-1/3" />
        <div className="grid md:grid-cols-2 gap-6">
          <Skeleton className="h-64 w-full" />
          <Skeleton className="h-64 w-full" />
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
          <h1 className="text-3xl font-bold tracking-tight mb-2 flex items-center gap-2">
            <Trophy className="h-8 w-8 text-yellow-500" />
            Loyalty Program
          </h1>
          <p className="text-muted-foreground">Track your points and unlock exclusive rewards</p>
        </div>
        
        <div className="flex flex-col md:flex-row gap-6">
          <Card className="flex-1">
            <CardHeader><CardTitle className="flex items-center gap-2"><Crown className="h-5 w-5 text-yellow-500" /> Loyalty Status</CardTitle></CardHeader>
            <CardContent className="space-y-4">
              <div className="text-4xl font-bold" aria-live="polite">{summary?.points_balance ?? 0} pts</div>
              <div className="text-sm text-muted-foreground">Tier: <span className="font-semibold">{summary?.tier_name}</span></div>
              <Progress value={progress} className="h-2" />
              <div className="text-xs text-muted-foreground" aria-live="polite">Progress to next tier: {progress.toFixed(1)}%</div>
              {summary?.expiring_points && summary.expiring_points.length > 0 && (
                <div className="text-xs mt-2">
                  <span className="font-semibold">Expiring Soon:</span> {summary.expiring_points.map(ep => `${ep.amount}pts (${new Date(ep.expires_at).toLocaleDateString()})`).join(', ')}
                </div>
              )}
              <TooltipProvider>
                <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                  <Tooltip>
                    <TooltipTrigger asChild>
                      <Button size="sm" variant="secondary" onClick={async () => { const resp = await loyaltyService.claimBirthday().catch(() => ({ data: { bonus_points: 200 } })); const bonus = (resp?.data?.bonus_points) || resp?.bonus_points || 200; setSummary(prev => prev ? { ...prev, points_balance: prev.points_balance + bonus } : prev); setTransactions(prev => [{ id: Date.now(), user_id: summary?.user_id || 0, type: 'bonus', points: bonus, description: 'Birthday bonus', created_at: new Date().toISOString() }, ...prev]); toast({ title: 'Birthday bonus claimed 🎉', description: `+${bonus} pts` }); load(page); }} className="h-11 md:h-9">
                        <PartyPopper className="h-4 w-4 mr-1" /> Claim Birthday Bonus
                      </Button>
                    </TooltipTrigger>
                    <TooltipContent>Add bonus points to your balance</TooltipContent>
                  </Tooltip>
                  <Tooltip>
                    <TooltipTrigger asChild>
                      <Button size="sm" variant="outline" onClick={() => load(page)} className="h-11 md:h-9">
                        <RefreshCcw className="h-4 w-4 mr-1" /> Refresh
                      </Button>
                    </TooltipTrigger>
                    <TooltipContent>Reload loyalty data</TooltipContent>
                  </Tooltip>
                </div>
              </TooltipProvider>
              <Dialog open={redeemOpen} onOpenChange={setRedeemOpen}>
                <DialogTrigger asChild>
                  <Button size="sm" variant="outline" className="h-11 md:h-9"><Gift className="h-4 w-4 mr-1" /> Redeem Reward</Button>
                </DialogTrigger>
                <DialogContent>
                  <DialogHeader>
                    <DialogTitle>Redeem Reward</DialogTitle>
                    <DialogDescription>Enter a reward code to convert points into benefits.</DialogDescription>
                  </DialogHeader>
                  <div className="space-y-4">
                    <Input value={rewardCode} onChange={(e) => setRewardCode(e.target.value)} placeholder="e.g. FREE_NIGHT" className="h-11 md:h-10" />
                    <Button disabled={redeeming} onClick={redeemReward} className="h-11 md:h-10 w-full">{redeeming && <Loader2 className="h-4 w-4 mr-2 animate-spin" />}Redeem</Button>
                  </div>
                </DialogContent>
              </Dialog>
            </CardContent>
          </Card>
          <Card className="flex-1 animate-fade-in-up" style={{ animationDelay: '40ms' }}>
            <CardHeader><CardTitle className="flex items-center gap-2"><Trophy className="h-5 w-5" /> Tier Benefits</CardTitle></CardHeader>
            <CardContent className="space-y-2">
              {benefits.length === 0 ? <div className="text-sm text-gray-500">No benefits loaded.</div> : benefits.map(b => (
                <div key={b.id} className="flex items-start gap-2 text-sm animate-fade-in-up">
                  <Badge variant="secondary" className="mt-0.5">{summary?.tier_name}</Badge>
                  <div className="flex-1">
                    <div className="font-medium">{b.label}</div>
                    {b.description && <div className="text-xs text-gray-500">{b.description}</div>}
                  </div>
                </div>
              ))}
            </CardContent>
          </Card>
        </div>

        <div className="grid md:grid-cols-2 gap-6">
          <Card className="animate-fade-in-up">
            <CardHeader><CardTitle>Recent Transactions</CardTitle></CardHeader>
            <CardContent>
              <div className="overflow-x-auto -mx-6 px-6 md:mx-0 md:px-0">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead className="whitespace-nowrap">Date</TableHead>
                      <TableHead className="whitespace-nowrap">Type</TableHead>
                      <TableHead className="whitespace-nowrap">Points</TableHead>
                      <TableHead className="min-w-[150px]">Description</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {transactions.map(tx => (
                      <TableRow key={tx.id}>
                        <TableCell className="whitespace-nowrap">{new Date(tx.created_at).toLocaleDateString()}</TableCell>
                        <TableCell><Badge variant={tx.type === 'earn' ? 'default' : 'outline'}>{tx.type}</Badge></TableCell>
                        <TableCell className={`whitespace-nowrap ${tx.type === 'redeem' ? 'text-red-600' : 'text-green-600'}`}>{tx.type === 'redeem' ? '-' : '+'}{tx.points}</TableCell>
                        <TableCell className="truncate max-w-[180px]" title={tx.description}>{tx.description || '—'}</TableCell>
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
            <CardHeader><CardTitle>Leaderboard (Top 5)</CardTitle></CardHeader>
            <CardContent>
              <div className="space-y-2">
                {leaderboard.slice(0,5).map((entry, idx) => (
                  <div key={entry.rank} className="flex items-center gap-4 text-sm animate-fade-in-up" style={{ animationDelay: `${Math.min(idx, 8) * 40}ms` }}>
                    <div className="w-6 text-center font-semibold">#{entry.rank}</div>
                    <div className="flex-1">{entry.user_name || `User ${entry.user_id}`}</div>
                    <div className="text-gray-600">{entry.points_earned} pts</div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </div>

        <Card className="animate-fade-in-up">
          <CardHeader><CardTitle>All Tiers</CardTitle></CardHeader>
          <CardContent className="grid md:grid-cols-3 gap-4">
            {tiers.map(t => (
              <div key={t.id} className={`rounded border p-4 space-y-2 ${summary?.tier_id === t.id ? 'bg-primary/5 border-primary' : 'bg-muted/40'} animate-fade-in-up`}>
                <div className="flex items-center gap-2"><Crown className="h-4 w-4 text-yellow-500" /><span className="font-semibold">{t.name}</span></div>
                <div className="text-xs text-gray-500">Requires {t.points_required} pts</div>
                {t.multiplier && <div className="text-xs">Multiplier: x{t.multiplier}</div>}
              </div>
            ))}
          </CardContent>
        </Card>

        {/* Redeem Catalog */}
        <Card className="animate-fade-in-up">
          <CardHeader><CardTitle className="flex items-center gap-2"><ShoppingBag className="h-5 w-5" /> Redeem Catalog</CardTitle></CardHeader>
          <CardContent className="grid md:grid-cols-3 gap-3">
            {demoCatalog().map((item, idx) => (
              <div key={item.code} className="rounded border p-3 space-y-1 bg-muted/40 animate-fade-in-up" style={{ animationDelay: `${Math.min(idx, 8) * 40}ms` }}>
                <div className="font-medium">{item.title}</div>
                <div className="text-xs text-gray-600">Cost: {item.points} pts</div>
                <div className="text-xs text-gray-500">{item.description}</div>
                <Button size="sm" className="mt-2" onClick={async () => { await loyaltyService.redeem({ reward_code: item.code }).catch(() => ({ data: { points_spent: item.points } })); // Optimistic update
                  setSummary(prev => prev ? { ...prev, points_balance: Math.max(0, prev.points_balance - item.points) } : prev);
                  setTransactions(prev => [{ id: Date.now(), user_id: summary?.user_id || 0, type: 'redeem', points: item.points, description: item.title, created_at: new Date().toISOString() }, ...prev]);
                  toast({ title: 'Redeemed', description: item.title });
                  load(page);
                }}>Redeem</Button>
              </div>
            ))}
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}

// Demo fallback data
function demoSummary(): LoyaltySummary {
  return {
    user_id: 1,
    points_balance: 1240,
    lifetime_points: 5320,
    tier_id: 2,
    tier_name: 'Gold',
    tier_points_required: 1000,
    next_tier_points_required: 2500,
    progress_to_next_tier_percent: (1240 - 1000) / (2500 - 1000) * 100,
    birthday_claimed: true,
    expiring_points: [{ amount: 200, expires_at: new Date(Date.now() + 86400000 * 30).toISOString() }],
  };
}
function demoTiers(): LoyaltyTier[] {
  return [
    { id: 1, name: 'Silver', points_required: 0, multiplier: 1 },
    { id: 2, name: 'Gold', points_required: 1000, multiplier: 1.1 },
    { id: 3, name: 'Platinum', points_required: 2500, multiplier: 1.25 },
  ];
}
function demoTransactions(): LoyaltyTransaction[] {
  return Array.from({ length: 15 }).map((_, i) => ({
    id: i + 1,
    user_id: 1,
    type: i % 5 === 0 ? 'redeem' : 'earn',
    points: i % 5 === 0 ? 100 : 250,
    description: i % 5 === 0 ? 'Redeemed gift voucher' : 'Booking stay earnings',
    created_at: new Date(Date.now() - i * 86400000).toISOString(),
  }));
}
function demoLeaderboard() {
  return [
    { rank: 1, user_id: 10, user_name: 'Alice', points_earned: 9800 },
    { rank: 2, user_id: 11, user_name: 'Bob', points_earned: 8450 },
    { rank: 3, user_id: 12, user_name: 'Carlos', points_earned: 8120 },
    { rank: 4, user_id: 13, user_name: 'Diana', points_earned: 7900 },
    { rank: 5, user_id: 14, user_name: 'Eva', points_earned: 7600 },
  ];
}
function demoBenefits(tierId: number): LoyaltyBenefit[] {
  return [
    { id: 1, tier_id: tierId, label: 'Late checkout', description: 'Enjoy 2 extra hours', created_at: new Date().toISOString() },
    { id: 2, tier_id: tierId, label: 'Priority support', description: 'Faster response times', created_at: new Date().toISOString() },
    { id: 3, tier_id: tierId, label: 'Exclusive deals', description: 'Access private discounts', created_at: new Date().toISOString() },
  ];
}
function demoCatalog() {
  return [
    { code: 'LATE_CHECKOUT', title: 'Late Checkout', points: 300, description: 'Extend your checkout by 2 hours' },
    { code: 'CLEANING_FEE_WAIVE', title: 'Cleaning Fee Waiver', points: 600, description: 'Remove cleaning fee for this stay' },
    { code: 'FREE_NIGHT_10', title: '10% Off One Night', points: 800, description: 'Get 10% off one night stay' },
  ];
}