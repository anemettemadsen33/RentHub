"use client";
import { ReactNode } from 'react';
import { Card, CardContent } from '@/components/ui/card';

export function EmptyState({ 
  icon: Icon, 
  title, 
  description, 
  action 
}: { 
  icon: React.ComponentType<any>; 
  title: string; 
  description?: string; 
  action?: ReactNode;
}) {
  return (
    <Card className="border-dashed">
      <CardContent className="flex flex-col items-center justify-center text-center py-16 px-4">
        {Icon && (
          <div className="h-16 w-16 rounded-full bg-muted flex items-center justify-center mb-4">
            <Icon className="h-8 w-8 text-muted-foreground" />
          </div>
        )}
        <h3 className="text-lg font-semibold mb-2">{title}</h3>
        {description && (
          <p className="text-sm text-muted-foreground max-w-sm mb-6">{description}</p>
        )}
        {action && <div className="flex flex-col sm:flex-row gap-3">{action}</div>}
      </CardContent>
    </Card>
  );
}
