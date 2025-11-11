'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { Badge } from '@/components/ui/badge';
import { memo } from 'react';

interface BottomNavItemProps {
  href: string;
  icon: React.ReactNode;
  label: string;
  badge?: number;
}

function BottomNavItemComponent({ href, icon, label, badge }: BottomNavItemProps) {
  const pathname = usePathname();
  const isActive = pathname === href || (href !== '/dashboard' && pathname.startsWith(href));
  
  return (
    <Link 
      href={href}
      className={`flex flex-col items-center justify-center gap-1 relative transition-all ${
        isActive 
          ? 'text-primary' 
          : 'text-muted-foreground hover:text-foreground'
      }`}
    >
      <div className="relative">
        {isActive && (
          <div className="absolute -top-1 left-1/2 -translate-x-1/2 w-12 h-1 bg-primary rounded-full" />
        )}
        <div className={`transition-transform ${isActive ? 'scale-110' : 'scale-100'}`}>
          {icon}
        </div>
        {badge !== undefined && badge > 0 && (
          <Badge className="absolute -top-2 -right-2 h-4 w-4 flex items-center justify-center p-0 text-[10px] bg-red-500 animate-pulse">
            {badge > 9 ? '9+' : badge}
          </Badge>
        )}
      </div>
      <span className={`text-[10px] font-medium ${isActive ? 'font-semibold' : ''}`}>{label}</span>
    </Link>
  );
}

export const BottomNavItem = memo(BottomNavItemComponent);
