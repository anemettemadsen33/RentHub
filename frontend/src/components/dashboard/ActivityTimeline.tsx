import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { ReactNode } from 'react';

export interface ActivityItem {
  id: number;
  type: 'booking' | 'payment' | 'message';
  title: string;
  date: string; // ISO
  icon: ReactNode;
}

export function ActivityTimeline({ items, loading }: { items: ActivityItem[]; loading?: boolean }) {
  return (
    <Card className="lg:col-span-2">
      <CardHeader className="flex flex-row items-center justify-between">
        <div>
          <CardTitle>Recent Activity</CardTitle>
          <CardDescription>Key events in the last 24h</CardDescription>
        </div>
      </CardHeader>
      <CardContent>
        {loading ? (
          <div className="space-y-3">
            {Array.from({ length: 4 }).map((_, i) => (
              <div key={i} className="h-10 w-full rounded bg-muted animate-pulse" />
            ))}
          </div>
        ) : items.length === 0 ? (
          <p className="text-sm text-muted-foreground">No recent activity</p>
        ) : (
          <ol className="space-y-4 max-h-72 overflow-y-auto pr-2 scrollbar-thin">
            {items.map(a => (
              <li key={a.id} className="flex items-start gap-3">
                <div className="h-7 w-7 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                  {a.icon}
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium truncate">{a.title}</p>
                  <p className="text-[11px] text-muted-foreground">{new Date(a.date).toLocaleTimeString('ro-RO',{ hour: '2-digit', minute:'2-digit'})} • {new Date(a.date).toLocaleDateString('ro-RO')}</p>
                </div>
              </li>
            ))}
          </ol>
        )}
      </CardContent>
    </Card>
  );
}
