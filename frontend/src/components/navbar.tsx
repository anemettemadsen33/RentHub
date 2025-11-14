'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { useAuth } from '@/contexts/auth-context';
import { useNotifications } from '@/contexts/notification-context';
import { Button } from '@/components/ui/button';
import { Home, Menu, User, Bell, Building, MessageSquare, Info } from 'lucide-react';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Separator } from '@/components/ui/separator';
import { BottomNavItem } from '@/components/bottom-nav-item';

export function Navbar() {
  const { user, logout, isAuthenticated } = useAuth();
  const { unreadCount } = useNotifications();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const closeMobileMenu = () => setMobileMenuOpen(false);

  return (
    <React.Fragment>
      <nav className="border-b bg-background/95 backdrop-blur sticky top-0 z-50">
        <div className="container mx-auto px-4">
          <div className="flex h-16 items-center justify-between">
            <Link href="/" className="flex items-center gap-2 font-bold text-xl">
              <div className="h-8 w-8 rounded-lg bg-primary flex items-center justify-center">
                <Home className="h-5 w-5 text-primary-foreground" />
              </div>
              <span className="hidden sm:inline">RentHub</span>
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

            <div className="flex items-center gap-2">
              {isAuthenticated ? (
                <>
                  <div className="hidden sm:flex items-center gap-2">
                    <Button variant="ghost" size="icon" asChild>
                      <Link href="/messages">
                        <MessageSquare className="h-5 w-5" />
                      </Link>
                    </Button>
                    <Button variant="ghost" size="icon" asChild>
                      <Link href="/notifications">
                        <Bell className="h-5 w-5" />
                      </Link>
                    </Button>
                  </div>

                  <div className="hidden sm:block">
                    <Button variant="ghost" size="icon" onClick={logout}>
                      <User className="h-5 w-5" />
                    </Button>
                  </div>

                  <Sheet open={mobileMenuOpen} onOpenChange={setMobileMenuOpen}>
                    <SheetTrigger asChild className="md:hidden">
                      <Button variant="ghost" size="icon">
                        <Menu className="h-5 w-5" />
                      </Button>
                    </SheetTrigger>
                    <SheetContent side="right">
                      <SheetHeader>
                        <SheetTitle>Menu</SheetTitle>
                      </SheetHeader>
                      <div className="mt-6 space-y-1">
                        <MobileNavLink href="/dashboard" icon={<Home />} onClick={closeMobileMenu}>Dashboard</MobileNavLink>
                        <MobileNavLink href="/properties" icon={<Building />} onClick={closeMobileMenu}>Properties</MobileNavLink>
                        <MobileNavLink href="/messages" icon={<MessageSquare />} onClick={closeMobileMenu}>Messages</MobileNavLink>
                        <Separator className="my-4" />
                        <Button onClick={() => { logout(); closeMobileMenu(); }} className="w-full">
                          Logout
                        </Button>
                      </div>
                    </SheetContent>
                  </Sheet>
                </>
              ) : (
                <>
                  <Button variant="ghost" size="sm" asChild className="hidden sm:inline-flex">
                    <Link href="/auth/login">Login</Link>
                  </Button>
                  <Button size="sm" asChild className="hidden sm:inline-flex">
                    <Link href="/auth/register">Sign Up</Link>
                  </Button>
                  <Sheet open={mobileMenuOpen} onOpenChange={setMobileMenuOpen}>
                    <SheetTrigger asChild className="sm:hidden">
                      <Button variant="ghost" size="icon">
                        <Menu className="h-5 w-5" />
                      </Button>
                    </SheetTrigger>
                    <SheetContent side="right">
                      <SheetHeader>
                        <SheetTitle>Menu</SheetTitle>
                      </SheetHeader>
                      <div className="mt-6 space-y-1">
                        <MobileNavLink href="/" icon={<Home />} onClick={closeMobileMenu}>Home</MobileNavLink>
                        <MobileNavLink href="/properties" icon={<Building />} onClick={closeMobileMenu}>Properties</MobileNavLink>
                        <MobileNavLink href="/about" icon={<Info />} onClick={closeMobileMenu}>About</MobileNavLink>
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
<<<<<<< Updated upstream
      <div className="md:hidden fixed bottom-0 left-0 right-0 z-50 border-t bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 safe-bottom shadow-lg">
        <div className="grid grid-cols-5 h-16">
          {isAuthenticated ? (
            <>
              <BottomNavItem href="/dashboard" icon={<Home className="h-5 w-5" />} label="Home" />
              <BottomNavItem href="/properties" icon={<Building className="h-5 w-5" />} label="Browse" />
              <BottomNavItem href="/bookings" icon={<Calendar className="h-5 w-5" />} label="Bookings" />
              <BottomNavItem href="/messages" icon={<MessageSquare className="h-5 w-5" />} label="Messages" badge={0} />
              <BottomNavItem href="/notifications" icon={<Bell className="h-5 w-5" />} label="Alerts" badge={unreadCount} />
            </>
          ) : (
            <>
              <BottomNavItem href="/" icon={<Home className="h-5 w-5" />} label="Home" />
              <BottomNavItem href="/properties" icon={<Building className="h-5 w-5" />} label="Browse" />
              <BottomNavItem href="/about" icon={<Heart className="h-5 w-5" />} label="About" />
              <BottomNavItem href="/contact" icon={<MessageSquare className="h-5 w-5" />} label="Contact" />
              <BottomNavItem href="/auth/login" icon={<User className="h-5 w-5" />} label="Login" />
            </>
          )}
        </div>
      </div>
    </>
=======
      <div className="md:hidden fixed bottom-0 left-0 right-0 z-50 border-t bg-background/95 backdrop-blur">
        <div className="grid grid-cols-5 h-16">
          {isAuthenticated ? (
            <React.Fragment>
              <BottomNavItem href="/dashboard" icon={<Home className="h-5 w-5" />} label="Home" />
              <BottomNavItem href="/properties" icon={<Building className="h-5 w-5" />} label="Browse" />
              <BottomNavItem href="/messages" icon={<MessageSquare className="h-5 w-5" />} label="Messages" />
              <BottomNavItem href="/notifications" icon={<Bell className="h-5 w-5" />} label="Alerts" badge={unreadCount} />
              <BottomNavItem href="/profile" icon={<User className="h-5 w-5" />} label="Profile" />
            </React.Fragment>
          ) : (
            <React.Fragment>
              <BottomNavItem href="/" icon={<Home className="h-5 w-5" />} label="Home" />
              <BottomNavItem href="/properties" icon={<Building className="h-5 w-5" />} label="Browse" />
              <BottomNavItem href="/about" icon={<Info className="h-5 w-5" />} label="About" />
              <BottomNavItem href="/contact" icon={<MessageSquare className="h-5 w-5" />} label="Contact" />
              <BottomNavItem href="/auth/login" icon={<User className="h-5 w-5" />} label="Login" />
            </React.Fragment>
          )}
        </div>
      </div>
    </React.Fragment>
>>>>>>> Stashed changes
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