import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { ReactNode } from 'react';

interface StatCardProps {
  label: string;
  value: string | number;
  description?: string;
  icon?: ReactNode;
  loading?: boolean;
}

export function StatCard({ label, value, description, icon, loading }: StatCardProps) {
  return (
    <Card className="relative overflow-hidden">
      <CardHeader className="flex flex-row items-center justify-between pb-2 space-y-0">
        <CardTitle className="text-sm font-medium">
          {loading ? <span className="inline-block h-4 w-24 rounded bg-muted animate-pulse" /> : label}
        </CardTitle>
        {!loading && icon}
      </CardHeader>
      <CardContent>
        <div className="text-2xl font-bold">
          {loading ? <span className="inline-block h-6 w-20 rounded bg-muted animate-pulse" /> : value}
        </div>
        {description && (
          <p className="text-xs text-muted-foreground">
            {loading ? <span className="inline-block h-3 w-28 rounded bg-muted animate-pulse" /> : description}
          </p>
        )}
      </CardContent>
    </Card>
  );
}
