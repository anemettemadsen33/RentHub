/**
 * Navigation Configuration
 * Centralized configuration for all navigation items and sections
 */

import { 
  Home, 
  Building, 
  Calendar, 
  Heart, 
  MessageSquare, 
  Bell, 
  CreditCard, 
  Award, 
  UserPlus, 
  BarChart3, 
  User, 
  Settings, 
  LogOut,
  Phone,
  Info,
  Search,
  MapPin,
  Star,
  Filter,
  Bookmark,
  Users,
  TrendingUp,
  DollarSign,
  FileText,
  Shield,
  HelpCircle
} from 'lucide-react';
import type { NavigationConfig, NavSection, NavItem } from './types';

/**
 * Main navigation sections
 */
const mainSections: NavSection[] = [
  {
    id: 'explore',
    title: 'Explore',
    roles: ['guest', 'user', 'landlord', 'admin'],
    desktop: { showSection: true, orientation: 'horizontal' },
    mobile: { showSection: true, collapsible: false },
    items: [
      {
        id: 'home',
        label: 'Home',
        href: '/',
        icon: Home,
        roles: ['guest', 'user', 'landlord', 'admin'],
        mobile: { priority: 10, showLabel: true },
        desktop: { priority: 10, showLabel: true }
      },
      {
        id: 'properties',
        label: 'Properties',
        href: '/properties',
        icon: Building,
        roles: ['guest', 'user', 'landlord', 'admin'],
        mobile: { priority: 9, showLabel: true },
        desktop: { priority: 9, showLabel: true }
      },
      {
        id: 'search',
        label: 'Search',
        href: '/search',
        icon: Search,
        roles: ['guest', 'user', 'landlord', 'admin'],
        mobile: { priority: 8, showLabel: false },
        desktop: { priority: 8, showLabel: true }
      },
      {
        id: 'map',
        label: 'Map',
        href: '/map',
        icon: MapPin,
        roles: ['guest', 'user', 'landlord', 'admin'],
        mobile: { priority: 7, showLabel: false },
        desktop: { priority: 7, showLabel: true }
      }
    ]
  },
  {
    id: 'information',
    title: 'Information',
    roles: ['guest', 'user', 'landlord', 'admin'],
    desktop: { showSection: true, orientation: 'horizontal' },
    mobile: { showSection: true, collapsible: false },
    items: [
      {
        id: 'about',
        label: 'About',
        href: '/about',
        icon: Info,
        roles: ['guest', 'user', 'landlord', 'admin'],
        mobile: { priority: 6, showLabel: true },
        desktop: { priority: 6, showLabel: true }
      },
      {
        id: 'contact',
        label: 'Contact',
        href: '/contact',
        icon: Phone,
        roles: ['guest', 'user', 'landlord', 'admin'],
        mobile: { priority: 5, showLabel: true },
        desktop: { priority: 5, showLabel: true }
      },
      {
        id: 'help',
        label: 'Help',
        href: '/help',
        icon: HelpCircle,
        roles: ['guest', 'user', 'landlord', 'admin'],
        mobile: { priority: 4, showLabel: false },
        desktop: { priority: 4, showLabel: true }
      }
    ]
  }
];

/**
 * User dashboard sections
 */
const userSections: NavSection[] = [
  {
    id: 'dashboard',
    title: 'Dashboard',
    roles: ['user', 'landlord', 'admin'],
    desktop: { showSection: true, orientation: 'vertical' },
    mobile: { showSection: true, collapsible: true },
    items: [
      {
        id: 'dashboard-home',
        label: 'Overview',
        href: '/dashboard',
        icon: Home,
        roles: ['user', 'landlord', 'admin'],
        mobile: { priority: 10, showLabel: true },
        desktop: { priority: 10, showLabel: true }
      },
      {
        id: 'bookings',
        label: 'My Bookings',
        href: '/dashboard/bookings',
        icon: Calendar,
        roles: ['user', 'landlord', 'admin'],
        mobile: { priority: 9, showLabel: true },
        desktop: { priority: 9, showLabel: true }
      },
      {
        id: 'favorites',
        label: 'Favorites',
        href: '/dashboard/favorites',
        icon: Heart,
        roles: ['user', 'landlord', 'admin'],
        mobile: { priority: 8, showLabel: true },
        desktop: { priority: 8, showLabel: true }
      }
    ]
  },
  {
    id: 'communications',
    title: 'Communications',
    roles: ['user', 'landlord', 'admin'],
    desktop: { showSection: true, orientation: 'vertical' },
    mobile: { showSection: true, collapsible: true },
    items: [
      {
        id: 'messages',
        label: 'Messages',
        href: '/messages',
        icon: MessageSquare,
        roles: ['user', 'landlord', 'admin'],
        mobile: { priority: 7, showLabel: true },
        desktop: { priority: 7, showLabel: true },
        badge: { count: 0, variant: 'default' }
      },
      {
        id: 'notifications',
        label: 'Notifications',
        href: '/notifications',
        icon: Bell,
        roles: ['user', 'landlord', 'admin'],
        mobile: { priority: 6, showLabel: true },
        desktop: { priority: 6, showLabel: true },
        badge: { count: 0, variant: 'destructive' }
      }
    ]
  },
  {
    id: 'account',
    title: 'Account',
    roles: ['user', 'landlord', 'admin'],
    desktop: { showSection: true, orientation: 'vertical' },
    mobile: { showSection: true, collapsible: true },
    items: [
      {
        id: 'profile',
        label: 'Profile',
        href: '/profile',
        icon: User,
        roles: ['user', 'landlord', 'admin'],
        mobile: { priority: 5, showLabel: true },
        desktop: { priority: 5, showLabel: true }
      },
      {
        id: 'settings',
        label: 'Settings',
        href: '/settings',
        icon: Settings,
        roles: ['user', 'landlord', 'admin'],
        mobile: { priority: 4, showLabel: true },
        desktop: { priority: 4, showLabel: true }
      }
    ]
  }
];

/**
 * Landlord specific sections
 */
const landlordSections: NavSection[] = [
  {
    id: 'landlord',
    title: 'Property Management',
    roles: ['landlord', 'admin'],
    desktop: { showSection: true, orientation: 'vertical' },
    mobile: { showSection: true, collapsible: true },
    items: [
      {
        id: 'my-properties',
        label: 'My Properties',
        href: '/dashboard/properties',
        icon: Building,
        roles: ['landlord', 'admin'],
        mobile: { priority: 10, showLabel: true },
        desktop: { priority: 10, showLabel: true }
      },
      {
        id: 'analytics',
        label: 'Analytics',
        href: '/analytics',
        icon: BarChart3,
        roles: ['landlord', 'admin'],
        mobile: { priority: 3, showLabel: true },
        desktop: { priority: 3, showLabel: true }
      },
      {
        id: 'bookings-received',
        label: 'Bookings Received',
        href: '/dashboard/bookings-received',
        icon: Calendar,
        roles: ['landlord', 'admin'],
        mobile: { priority: 2, showLabel: true },
        desktop: { priority: 2, showLabel: true }
      }
    ]
  }
];

/**
 * Financial sections
 */
const financialSections: NavSection[] = [
  {
    id: 'financial',
    title: 'Financial',
    roles: ['user', 'landlord', 'admin'],
    desktop: { showSection: true, orientation: 'vertical' },
    mobile: { showSection: true, collapsible: true },
    items: [
      {
        id: 'payment-history',
        label: 'Payment History',
        href: '/payments/history',
        icon: CreditCard,
        roles: ['user', 'landlord', 'admin'],
        mobile: { priority: 3, showLabel: true },
        desktop: { priority: 3, showLabel: true }
      },
      {
        id: 'loyalty',
        label: 'Loyalty Program',
        href: '/loyalty',
        icon: Award,
        roles: ['user', 'landlord', 'admin'],
        mobile: { priority: 2, showLabel: true },
        desktop: { priority: 2, showLabel: true }
      }
    ]
  }
];

/**
 * User menu items
 */
const userMenuItems: NavItem[] = [
  {
    id: 'user-dashboard',
    label: 'Dashboard',
    href: '/dashboard',
    icon: Home,
    roles: ['user', 'landlord', 'admin']
  },
  {
    id: 'user-profile',
    label: 'Profile',
    href: '/profile',
    icon: User,
    roles: ['user', 'landlord', 'admin']
  },
  {
    id: 'user-settings',
    label: 'Settings',
    href: '/settings',
    icon: Settings,
    roles: ['user', 'landlord', 'admin']
  },
  {
    id: 'admin-settings',
    label: 'Admin Settings',
    href: '/admin/settings',
    icon: Settings,
    roles: ['admin']
  }
];

/**
 * Authentication menu items
 */
const authMenuItems: NavItem[] = [
  {
    id: 'login',
    label: 'Login',
    href: '/auth/login',
    icon: User,
    roles: ['guest']
  },
  {
    id: 'register',
    label: 'Sign Up',
    href: '/auth/register',
    icon: UserPlus,
    roles: ['guest']
  }
];

/**
 * Bottom navigation configuration
 */
export const getBottomNavItems = (role: string, badgeCounts: Record<string, number> = {}): NavItem[] => {
  const allItems = [
    ...mainSections.flatMap(s => s.items),
    ...userSections.flatMap(s => s.items),
    ...financialSections.flatMap(s => s.items)
  ];

  return allItems
    .filter(item => item.roles.includes(role as any) && item.mobile)
    .sort((a, b) => (b.mobile?.priority || 0) - (a.mobile?.priority || 0))
    .slice(0, 5) // Max 5 items for bottom nav
    .map(item => ({
      ...item,
      badge: badgeCounts[item.id] ? { 
        count: badgeCounts[item.id], 
        variant: item.id === 'notifications' ? 'destructive' : 'default' 
      } : undefined
    }));
};

/**
 * Complete navigation configuration
 */
export const navigationConfig: NavigationConfig = {
  sections: [
    ...mainSections,
    ...userSections,
    ...landlordSections,
    ...financialSections
  ],
  userMenu: userMenuItems,
  authMenu: authMenuItems,
  footer: {
    copyright: `© ${new Date().getFullYear()} RentHub. All rights reserved.`,
    links: [
      {
        id: 'privacy',
        label: 'Privacy Policy',
        href: '/privacy',
        icon: Shield,
        roles: ['guest', 'user', 'landlord', 'admin']
      },
      {
        id: 'terms',
        label: 'Terms of Service',
        href: '/terms',
        icon: FileText,
        roles: ['guest', 'user', 'landlord', 'admin']
      },
      {
        id: 'support',
        label: 'Support',
        href: '/support',
        icon: HelpCircle,
        roles: ['guest', 'user', 'landlord', 'admin']
      }
    ]
  }
};

/**
 * Get navigation sections by role
 */
export const getNavigationSections = (role: string): NavSection[] => {
  return navigationConfig.sections.filter(section => 
    section.roles.includes(role as any)
  );
};

/**
 * Get navigation items by role
 */
export const getNavigationItems = (role: string): NavItem[] => {
  return navigationConfig.sections
    .filter(section => section.roles.includes(role as any))
    .flatMap(section => section.items);
};

/**
 * Get user menu items by role
 */
export const getUserMenuItems = (role: string): NavItem[] => {
  return navigationConfig.userMenu.filter(item => 
    item.roles.includes(role as any)
  );
};

/**
 * Get auth menu items by role
 */
export const getAuthMenuItems = (role: string): NavItem[] => {
  return navigationConfig.authMenu.filter(item => 
    item.roles.includes(role as any)
  );
};

export default navigationConfig;