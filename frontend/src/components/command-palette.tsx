'use client';

import * as React from 'react';
import { useRouter } from 'next/navigation';
import {
  CommandDialog,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
  CommandSeparator,
} from '@/components/ui/command';
import {
  Home,
  Calendar,
  CreditCard,
  Settings,
  User,
  MessageSquare,
  Heart,
  Building,
  Plus,
  Search,
  LogOut,
  Bell,
  Shield,
  Key,
  Link2,
  UserCheck,
  BarChart3,
  Wrench,
} from 'lucide-react';
import { useAuth } from '@/contexts/auth-context';

interface CommandItem {
  icon: React.ReactNode;
  label: string;
  href?: string;
  action?: () => void;
  keywords?: string[];
}

export function CommandPalette() {
  const router = useRouter();
  const { user, logout } = useAuth();
  const [open, setOpen] = React.useState(false);

  React.useEffect(() => {
    const down = (e: KeyboardEvent) => {
      if (e.key === 'k' && (e.metaKey || e.ctrlKey)) {
        e.preventDefault();
        setOpen((open) => !open);
      }
    };

    document.addEventListener('keydown', down);
    return () => document.removeEventListener('keydown', down);
  }, []);

  const navigate = (href: string) => {
    setOpen(false);
    router.push(href);
  };

  const handleLogout = () => {
    setOpen(false);
    logout();
    router.push('/');
  };

  const navigationItems: CommandItem[] = [
    {
      icon: <Home className="mr-2 h-4 w-4" />,
      label: 'Dashboard',
      href: '/dashboard',
      keywords: ['home', 'overview'],
    },
    {
      icon: <Building className="mr-2 h-4 w-4" />,
      label: 'Browse Properties',
      href: '/properties',
      keywords: ['search', 'find', 'rentals', 'apartments'],
    },
    {
      icon: <Calendar className="mr-2 h-4 w-4" />,
      label: 'My Bookings',
      href: '/bookings',
      keywords: ['reservations', 'trips'],
    },
    {
      icon: <Heart className="mr-2 h-4 w-4" />,
      label: 'Favorites',
      href: '/favorites',
      keywords: ['saved', 'wishlist'],
    },
    {
      icon: <MessageSquare className="mr-2 h-4 w-4" />,
      label: 'Messages',
      href: '/messages',
      keywords: ['inbox', 'chat', 'conversations'],
    },
    {
      icon: <CreditCard className="mr-2 h-4 w-4" />,
      label: 'Payments',
      href: '/payments/history',
      keywords: ['transactions', 'invoices', 'billing'],
    },
    {
      icon: <Bell className="mr-2 h-4 w-4" />,
      label: 'Notifications',
      href: '/notifications',
      keywords: ['alerts', 'updates'],
    },
    {
      icon: <Shield className="mr-2 h-4 w-4" />,
      label: 'Loyalty Program',
      href: '/loyalty',
      keywords: ['rewards', 'points', 'tiers'],
    },
    {
      icon: <Link2 className="mr-2 h-4 w-4" />,
      label: 'Referrals',
      href: '/referrals',
      keywords: ['invite', 'discount', 'share'],
    },
  ];

  const profileItems: CommandItem[] = [
    {
      icon: <User className="mr-2 h-4 w-4" />,
      label: 'My Profile',
      href: '/profile',
      keywords: ['account', 'user', 'details'],
    },
    {
      icon: <Settings className="mr-2 h-4 w-4" />,
      label: 'Settings',
      href: '/settings',
      keywords: ['preferences', 'configuration'],
    },
  ];

  const landlordItems: CommandItem[] = user?.role === 'landlord' ? [
    {
      icon: <Building className="mr-2 h-4 w-4" />,
      label: 'My Properties',
      href: '/dashboard/properties',
      keywords: ['listings', 'manage'],
    },
    {
      icon: <Plus className="mr-2 h-4 w-4" />,
      label: 'Add Property',
      href: '/properties/create',
      keywords: ['new', 'create', 'listing'],
    },
    {
      icon: <Calendar className="mr-2 h-4 w-4" />,
      label: 'Manage Bookings',
      href: '/dashboard/bookings',
      keywords: ['reservations', 'calendar'],
    },
    {
      icon: <Shield className="mr-2 h-4 w-4" />,
      label: 'Guest Screening',
      href: '/screening',
      keywords: ['verification', 'background', 'trust', 'approve'],
    },
    {
      icon: <Link2 className="mr-2 h-4 w-4" />,
      label: 'Calendar Sync',
      href: '/calendar-sync',
      keywords: ['airbnb', 'booking', 'ical', 'synchronize'],
    },
    {
      icon: <BarChart3 className="mr-2 h-4 w-4" />,
      label: 'Global Analytics',
      href: '/analytics',
      keywords: ['performance', 'stats', 'host', 'revenue'],
    },
    {
      icon: <BarChart3 className="mr-2 h-4 w-4" />,
      label: 'Host Ratings',
      href: '/host/ratings',
      keywords: ['reviews', 'reputation', 'feedback'],
    },
  ] : [];

  const securityItems: CommandItem[] = [
    {
      icon: <UserCheck className="mr-2 h-4 w-4" />,
      label: 'Verification',
      href: '/verification',
      keywords: ['kyc', 'identity', 'verify', 'documents'],
    },
  ];

  const actionItems: CommandItem[] = [
    {
      icon: <LogOut className="mr-2 h-4 w-4" />,
      label: 'Logout',
      action: handleLogout,
      keywords: ['sign out', 'exit'],
    },
  ];

  return (
    <CommandDialog open={open} onOpenChange={setOpen}>
      <CommandInput placeholder="Type a command or search..." />
      <CommandList>
        <CommandEmpty>No results found.</CommandEmpty>
        
        <CommandGroup heading="Navigation">
          {navigationItems.map((item, index) => (
            <CommandItem
              key={index}
              onSelect={() => item.href && navigate(item.href)}
            >
              {item.icon}
              <span>{item.label}</span>
            </CommandItem>
          ))}
        </CommandGroup>

        {landlordItems.length > 0 && (
          <>
            <CommandSeparator />
            <CommandGroup heading="Host Tools">
              {landlordItems.map((item, index) => (
                <CommandItem
                  key={index}
                  onSelect={() => item.href && navigate(item.href)}
                >
                  {item.icon}
                  <span>{item.label}</span>
                </CommandItem>
              ))}
            </CommandGroup>
          </>
        )}

        <CommandSeparator />

        <CommandGroup heading="Security & Verification">
          {securityItems.map((item, index) => (
            <CommandItem
              key={index}
              onSelect={() => item.href && navigate(item.href)}
            >
              {item.icon}
              <span>{item.label}</span>
            </CommandItem>
          ))}
        </CommandGroup>

        <CommandSeparator />
        
        <CommandGroup heading="Profile">
          {profileItems.map((item, index) => (
            <CommandItem
              key={index}
              onSelect={() => item.href && navigate(item.href)}
            >
              {item.icon}
              <span>{item.label}</span>
            </CommandItem>
          ))}
        </CommandGroup>

        <CommandSeparator />
        
        <CommandGroup heading="Actions">
          {actionItems.map((item, index) => (
            <CommandItem
              key={index}
              onSelect={() => item.action && item.action()}
            >
              {item.icon}
              <span>{item.label}</span>
            </CommandItem>
          ))}
        </CommandGroup>
      </CommandList>
    </CommandDialog>
  );
}
