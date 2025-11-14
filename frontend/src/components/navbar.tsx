'use client';

import { useState } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { useAuth } from '@/contexts/auth-context';
import { useNotifications } from '@/contexts/notification-context';
import { Button } from '@/components/ui/button';
import { ThemeToggle } from '@/components/theme-toggle';
import { Home, Menu, User, LogOut, Settings, MessageSquare, CreditCard, Bell, Building, Heart, Calendar, Award, Gift, UserPlus, BarChart3, X } from 'lucide-react';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Badge } from '@/components/ui/badge';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger, SheetClose } from '@/components/ui/sheet';
import { Separator } from '@/components/ui/separator';
// TEMP DISABLED: next-intl causing build errors
// import { LanguageSwitcher } from '@/components/language-switcher';
import { BottomNavItem } from '@/components/bottom-nav-item';

export function Navbar() {
  const { user, logout, isAuthenticated } = useAuth();
  const { unreadCount } = useNotifications();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const closeMobileMenu = () => setMobileMenuOpen(false);

  return (
    <>
      <nav className="border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 sticky top-0 z-50" role="navigation" aria-label="Main navigation">
        <div className="container mx-auto px-4">
          <div className="flex h-16 items-center justify-between">
            <Link href="/" className="flex items-center gap-2 font-bold text-xl focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-md transition-all" aria-label="RentHub home">
              <div className="h-8 w-8 rounded-lg bg-primary flex items-center justify-center">
                <Home className="h-5 w-5 text-primary-foreground" aria-hidden="true" />
              </div>
              <span className="hidden sm:inline bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text">RentHub</span>
            </Link>

            <div className="hidden md:flex items-center gap-1">
              <Button variant="ghost" size="sm" asChild className="hover:bg-accent/50 transition-colors">
                <Link href="/properties">Properties</Link>
              </Button>
              <Button variant="ghost" size="sm" asChild className="hover:bg-accent/50 transition-colors">
                <Link href="/about">About</Link>
              </Button>
              <Button variant="ghost" size="sm" asChild className="hover:bg-accent/50 transition-colors">
                <Link href="/contact">Contact</Link>
              </Button>
            </div>

            <div className="flex items-center gap-2 md:gap-3">
              <div className="flex items-center gap-2">
                <ThemeToggle />
                {/* TEMP DISABLED: next-intl causing build errors */}
                {/* <div className="h-6 w-px bg-border hidden md:block" /> */}
                {/* <LanguageSwitcher /> */}
              </div>
              {isAuthenticated ? (
                <>
                  <div className="hidden sm:flex items-center gap-2">
                    <Button variant="ghost" size="icon" asChild className="relative h-9 w-9 hover:bg-accent/50 transition-colors">
                      <Link href="/messages" aria-label="Messages">
                        <MessageSquare className="h-5 w-5" aria-hidden="true" />
                      </Link>
                    </Button>
                    <Button variant="ghost" size="icon" asChild className="relative h-9 w-9 hover:bg-accent/50 transition-colors">
                      <Link href="/notifications" aria-label={`Notifications${unreadCount > 0 ? `, ${unreadCount} unread` : ''}`}>
                        <Bell className="h-5 w-5" aria-hidden="true" />
                        {unreadCount > 0 && (
                          <Badge className="absolute -top-1 -right-1 h-5 w-5 flex items-center justify-center p-0 text-xs animate-pulse bg-red-500" aria-label={`${unreadCount} unread notifications`}>
                            {unreadCount > 99 ? '99+' : unreadCount}
                          </Badge>
                        )}
                      </Link>
                    </Button>
                  </div>
                  <div className="h-6 w-px bg-border hidden md:block" />
                  <div className="hidden md:block">
                    <DropdownMenu>
                      <DropdownMenuTrigger asChild>
                        <Button variant="ghost" size="icon" aria-label="User menu" className="hover:bg-accent/50 transition-colors">
                          <User className="h-5 w-5" aria-hidden="true" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end" className="w-56 animate-in fade-in-0 zoom-in-95">
                        <div className="flex items-center justify-start gap-2 p-2">
                          <div className="flex flex-col space-y-1">
                            <p className="text-sm font-medium">{user?.name}</p>
                            <p className="text-xs text-muted-foreground">{user?.email}</p>
                          </div>
                        </div>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem asChild>
                          <Link href="/dashboard">Dashboard</Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                          <Link href="/dashboard/properties">My Properties</Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                          <Link href="/dashboard/bookings">My Bookings</Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                          <Link href="/messages">
                            <MessageSquare className="mr-2 h-4 w-4" />
                            Messages
                          </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                          <Link href="/payments/history">
                            <CreditCard className="mr-2 h-4 w-4" />
                            Payment History
                          </Link>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem asChild>
                          <Link href="/profile">Profile</Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                          <Link href="/settings">Settings</Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                          <Link href="/admin/settings">
                            <Settings className="mr-2 h-4 w-4" />
                            Admin Settings
                          </Link>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem onClick={logout}>
                          <LogOut className="mr-2 h-4 w-4" />
                          Logout
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </div>
                  
                  {/* Mobile Menu Sheet */}
                  <Sheet open={mobileMenuOpen} onOpenChange={setMobileMenuOpen}>
                    <SheetTrigger asChild className="md:hidden">
                      <Button variant="ghost" size="icon" className="h-9 w-9" aria-label="Open menu">
                        <Menu className="h-5 w-5" />
                      </Button>
                    </SheetTrigger>
                    <SheetContent side="right" className="w-[280px] sm:w-[320px] overflow-y-auto">
                      <SheetHeader className="text-left">
                        <SheetTitle>Menu</SheetTitle>
                      </SheetHeader>
                      <div className="mt-6 space-y-4">
                        {/* TEMP DISABLED: next-intl causing build errors */}
                        {/* <div className="px-3 py-2">
                          <LanguageSwitcher />
                        </div> */}
                        
                        <Separator />
                        
                        {/* User Info */}
                        <div className="px-3 py-3 rounded-lg bg-gradient-to-r from-primary/10 to-primary/5 border border-primary/20">
                          <p className="text-sm font-semibold truncate">{user?.name}</p>
                          <p className="text-xs text-muted-foreground truncate">{user?.email}</p>
                        </div>
                        
                        {/* Main Section */}
                        <div className="space-y-1">
                          <p className="px-3 text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2">Main</p>
                          <MobileNavLink href="/dashboard" icon={<Home className="text-blue-500" />} onClick={closeMobileMenu}>Dashboard</MobileNavLink>
                          <MobileNavLink href="/properties" icon={<Building className="text-green-500" />} onClick={closeMobileMenu}>Browse Properties</MobileNavLink>
                          <MobileNavLink href="/bookings" icon={<Calendar className="text-purple-500" />} onClick={closeMobileMenu}>My Bookings</MobileNavLink>
                          <MobileNavLink href="/favorites" icon={<Heart className="text-red-500" />} onClick={closeMobileMenu}>Favorites</MobileNavLink>
                        </div>
                        
                        <Separator />
                        
                        {/* Communications */}
                        <div className="space-y-1">
                          <p className="px-3 text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2">Communications</p>
                          <MobileNavLink href="/messages" icon={<MessageSquare className="text-blue-500" />} onClick={closeMobileMenu}>
                            Messages
                          </MobileNavLink>
                          <MobileNavLink href="/notifications" icon={<Bell className="text-amber-500" />} onClick={closeMobileMenu}>
                            Notifications
                            {unreadCount > 0 && <Badge className="ml-auto bg-red-500 animate-pulse">{unreadCount > 99 ? '99+' : unreadCount}</Badge>}
                          </MobileNavLink>
                        </div>
                        
                        <Separator />
                        
                        {/* Features */}
                        <div className="space-y-1">
                          <p className="px-3 text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2">Features</p>
                          <MobileNavLink href="/payments/history" icon={<CreditCard className="text-emerald-500" />} onClick={closeMobileMenu}>Payments</MobileNavLink>
                          <MobileNavLink href="/loyalty" icon={<Award className="text-yellow-500" />} onClick={closeMobileMenu}>Loyalty Program</MobileNavLink>
                          <MobileNavLink href="/referrals" icon={<UserPlus className="text-indigo-500" />} onClick={closeMobileMenu}>Referrals</MobileNavLink>
                        </div>
                        
                        {user?.role === 'landlord' && (
                          <>
                            <Separator />
                            <div className="space-y-1">
                              <p className="px-3 text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2">Landlord</p>
                              <MobileNavLink href="/analytics" icon={<BarChart3 className="text-cyan-500" />} onClick={closeMobileMenu}>Analytics</MobileNavLink>
                              <MobileNavLink href="/dashboard/properties" icon={<Building className="text-violet-500" />} onClick={closeMobileMenu}>My Properties</MobileNavLink>
                            </div>
                          </>
                        )}
                        
                        <Separator />
                        
                        {/* Account Settings */}
                        <div className="space-y-1">
                          <p className="px-3 text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2">Account</p>
                          <MobileNavLink href="/profile" icon={<User className="text-slate-500" />} onClick={closeMobileMenu}>Profile</MobileNavLink>
                          <MobileNavLink href="/settings" icon={<Settings className="text-gray-500" />} onClick={closeMobileMenu}>Settings</MobileNavLink>
                        </div>
                        
                        <Separator />
                        
                        <button 
                          onClick={() => { logout(); closeMobileMenu(); }} 
                          className="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium hover:bg-destructive/10 text-destructive transition-colors"
                        >
                          <LogOut className="h-4 w-4" />
                          Logout
                        </button>
                      </div>
                    </SheetContent>
                  </Sheet>
                </>
              ) : (
                <>
                  <div className="h-6 w-px bg-border hidden sm:block" />
                  <Button variant="ghost" size="sm" asChild className="hidden sm:inline-flex hover:bg-accent/50 transition-colors">
                    <Link href="/auth/login">Login</Link>
                  </Button>
                  <Button size="sm" asChild className="bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 transition-all shadow-sm">
                    <Link href="/auth/register">Sign Up</Link>
                  </Button>
                  <Sheet open={mobileMenuOpen} onOpenChange={setMobileMenuOpen}>
                    <SheetTrigger asChild className="sm:hidden">
                      <Button variant="ghost" size="icon" className="h-9 w-9" aria-label="Open menu">
                        <Menu className="h-5 w-5" />
                      </Button>
                    </SheetTrigger>
                    <SheetContent side="right" className="w-[280px]">
                      <SheetHeader>
                        <SheetTitle>Menu</SheetTitle>
                      </SheetHeader>
                      <div className="mt-6 space-y-1">
                        {/* TEMP DISABLED: next-intl causing build errors */}
                        {/* <div className="px-3 py-2">
                          <LanguageSwitcher />
                        </div> */}
                        
                        <Separator className="my-4" />
                        
                        <MobileNavLink href="/properties" icon={<Building />} onClick={closeMobileMenu}>Properties</MobileNavLink>
                        <MobileNavLink href="/about" icon={<Home />} onClick={closeMobileMenu}>About</MobileNavLink>
                        <MobileNavLink href="/contact" icon={<MessageSquare />} onClick={closeMobileMenu}>Contact</MobileNavLink>
                        <Separator className="my-4" />
                        <Button onClick={() => { closeMobileMenu(); }} asChild className="w-full">
                          <Link href="/auth/login">Login</Link>
                        </Button>
                        <Button onClick={() => { closeMobileMenu(); }} asChild variant="outline" className="w-full">
                          <Link href="/auth/register">Sign Up</Link>
                        </Button>
                      </div>
                    </SheetContent>
                  </Sheet>
                </>
              )}
            </div>
          </div>
        </div>
      </nav>

      {/* Mobile Bottom Navigation */}
      {isAuthenticated && (
        <div className="md:hidden fixed bottom-0 left-0 right-0 z-50 border-t bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 safe-bottom shadow-lg">
          <div className="grid grid-cols-5 h-16">
            <BottomNavItem href="/dashboard" icon={<Home className="h-5 w-5" />} label="Home" />
            <BottomNavItem href="/properties" icon={<Building className="h-5 w-5" />} label="Browse" />
            <BottomNavItem href="/bookings" icon={<Calendar className="h-5 w-5" />} label="Bookings" />
            <BottomNavItem href="/messages" icon={<MessageSquare className="h-5 w-5" />} label="Messages" badge={0} />
            <BottomNavItem href="/notifications" icon={<Bell className="h-5 w-5" />} label="Alerts" badge={unreadCount} />
          </div>
        </div>
      )}
    </>
  );
}

function MobileNavLink({ href, icon, children, onClick }: { href: string; icon: React.ReactNode; children: React.ReactNode; onClick?: () => void }) {
  return (
    <Link 
      href={href} 
      onClick={onClick}
      className="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium hover:bg-accent transition-colors"
    >
      <span className="h-4 w-4 flex-shrink-0">{icon}</span>
      <span className="flex-1">{children}</span>
    </Link>
  );
}
