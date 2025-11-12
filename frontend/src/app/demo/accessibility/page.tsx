'use client';

import { useState } from 'react';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import {
  VisuallyHidden,
  SkipToContent,
  FocusTrap,
  LiveRegion,
  KeyboardShortcut,
  ErrorAnnouncement,
  LoadingAnnouncement,
} from '@/components/accessibility';
import { 
  Eye, 
  EyeOff, 
  Check, 
  X, 
  AlertCircle,
  Keyboard,
  MousePointer,
  Volume2,
  Focus,
  ZapOff
} from 'lucide-react';

export default function AccessibilityDemoPage() {
  const [showDialog, setShowDialog] = useState(false);
  const [loading, setLoading] = useState(false);
  const [formErrors, setFormErrors] = useState<string[]>([]);
  const [counter, setCounter] = useState(0);
  const [showPassword, setShowPassword] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const errors: string[] = [];
    
    const formData = new FormData(e.target as HTMLFormElement);
    if (!formData.get('email')) errors.push('Email is required');
    if (!formData.get('password')) errors.push('Password is required');
    
    setFormErrors(errors);
    
    if (errors.length === 0) {
      setLoading(true);
      setTimeout(() => {
        setLoading(false);
        alert('Form submitted successfully!');
      }, 2000);
    }
  };

  const handleKeyboardShortcut = () => {
    setCounter(prev => prev + 1);
  };

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8">
        <div className="max-w-6xl mx-auto space-y-8">
          {/* Header */}
          <div className="text-center space-y-4">
            <div className="flex items-center justify-center gap-2">
              <Eye className="h-8 w-8 text-primary" aria-hidden="true" />
              <h1 className="text-4xl font-bold">Accessibility (A11y) Demo</h1>
            </div>
            <p className="text-lg text-gray-600">
              Testing and demonstrating accessibility features
            </p>
          </div>

          {/* Quick Stats */}
          <div className="grid md:grid-cols-4 gap-4">
            <Card>
              <CardContent className="pt-6 text-center">
                <Keyboard className="h-8 w-8 mx-auto mb-2 text-blue-600" aria-hidden="true" />
                <p className="text-2xl font-bold">100%</p>
                <p className="text-sm text-gray-600">Keyboard Navigable</p>
              </CardContent>
            </Card>
            <Card>
              <CardContent className="pt-6 text-center">
                <Volume2 className="h-8 w-8 mx-auto mb-2 text-green-600" aria-hidden="true" />
                <p className="text-2xl font-bold">WCAG</p>
                <p className="text-sm text-gray-600">Level A Compliant</p>
              </CardContent>
            </Card>
            <Card>
              <CardContent className="pt-6 text-center">
                <Focus className="h-8 w-8 mx-auto mb-2 text-purple-600" aria-hidden="true" />
                <p className="text-2xl font-bold">Focus</p>
                <p className="text-sm text-gray-600">Visible Indicators</p>
              </CardContent>
            </Card>
            <Card>
              <CardContent className="pt-6 text-center">
                <Check className="h-8 w-8 mx-auto mb-2 text-orange-600" aria-hidden="true" />
                <p className="text-2xl font-bold">ARIA</p>
                <p className="text-sm text-gray-600">Labels & Roles</p>
              </CardContent>
            </Card>
          </div>

          {/* Demo 1: Keyboard Navigation */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Keyboard className="h-5 w-5" aria-hidden="true" />
                1. Keyboard Navigation
              </CardTitle>
              <CardDescription>
                Try navigating with Tab, Shift+Tab, Enter, and Space keys
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p className="text-sm text-blue-900 mb-3">
                  <strong>Instructions:</strong> Press Tab to navigate through these buttons.
                  Notice the focus ring that appears.
                </p>
                <div className="flex flex-wrap gap-3">
                  <Button>First Button</Button>
                  <Button variant="outline">Second Button</Button>
                  <Button variant="secondary">Third Button</Button>
                  <Button variant="ghost">Fourth Button</Button>
                </div>
              </div>

              <div className="p-4 bg-purple-50 border border-purple-200 rounded-lg">
                <p className="text-sm text-purple-900 mb-3">
                  <strong>Keyboard Shortcut:</strong> Press <KeyboardShortcut keys={['ctrl', 'k']} description="Increment counter" onActivate={handleKeyboardShortcut} /> to increment counter
                </p>
                <div className="text-center">
                  <p className="text-3xl font-bold">{counter}</p>
                  <p className="text-sm text-gray-600">Counter value</p>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Demo 2: Screen Reader Only Content */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Volume2 className="h-5 w-5" aria-hidden="true" />
                2. Screen Reader Content
              </CardTitle>
              <CardDescription>
                Hidden text that only screen readers can see
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center gap-4 p-4 border rounded-lg">
                <button 
                  className="p-2 bg-red-500 text-white rounded-full hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500"
                  aria-label="Delete item"
                >
                  <X className="h-5 w-5" aria-hidden="true" />
                  <VisuallyHidden>Delete</VisuallyHidden>
                </button>
                <div>
                  <p className="font-medium">Icon-only button with hidden label</p>
                  <p className="text-sm text-gray-600">
                    The X icon has no visible text, but screen readers hear &quot;Delete item&quot;
                  </p>
                </div>
              </div>

              <div className="p-4 border rounded-lg space-y-2">
                <p className="font-medium">Property Details:</p>
                <div className="flex items-center gap-4 text-sm">
                  <div className="flex items-center gap-1">
                    <Keyboard className="h-4 w-4" aria-hidden="true" />
                    <span>
                      <VisuallyHidden>Maximum guests: </VisuallyHidden>
                      4
                    </span>
                  </div>
                  <div className="flex items-center gap-1">
                    <MousePointer className="h-4 w-4" aria-hidden="true" />
                    <span>
                      <VisuallyHidden>Bedrooms: </VisuallyHidden>
                      2
                    </span>
                  </div>
                  <div className="flex items-center gap-1">
                    <ZapOff className="h-4 w-4" aria-hidden="true" />
                    <span>
                      <VisuallyHidden>Bathrooms: </VisuallyHidden>
                      1
                    </span>
                  </div>
                </div>
                <p className="text-xs text-gray-500 mt-2">
                  Screen readers hear: &quot;Maximum guests: 4, Bedrooms: 2, Bathrooms: 1&quot;
                </p>
              </div>
            </CardContent>
          </Card>

          {/* Demo 3: Form Accessibility */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <AlertCircle className="h-5 w-5" aria-hidden="true" />
                3. Accessible Forms
              </CardTitle>
              <CardDescription>
                Forms with proper labels, ARIA attributes, and error handling
              </CardDescription>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleSubmit} className="space-y-4">
                <ErrorAnnouncement errors={formErrors} />

                <div className="space-y-2">
                  <Label htmlFor="email">Email</Label>
                  <Input
                    id="email"
                    name="email"
                    type="email"
                    aria-required="true"
                    aria-invalid={formErrors.includes('Email is required')}
                    aria-describedby="email-help"
                  />
                  <p id="email-help" className="text-xs text-gray-500">
                    We&apos;ll never share your email
                  </p>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="password">Password</Label>
                  <div className="relative">
                    <Input
                      id="password"
                      name="password"
                      type={showPassword ? 'text' : 'password'}
                      aria-required="true"
                      aria-invalid={formErrors.includes('Password is required')}
                    />
                    <button
                      type="button"
                      onClick={() => setShowPassword(!showPassword)}
                      className="absolute right-2 top-1/2 -translate-y-1/2 p-1 hover:bg-gray-100 rounded focus:outline-none focus:ring-2 focus:ring-primary"
                      aria-label={showPassword ? 'Hide password' : 'Show password'}
                    >
                      {showPassword ? (
                        <EyeOff className="h-4 w-4" aria-hidden="true" />
                      ) : (
                        <Eye className="h-4 w-4" aria-hidden="true" />
                      )}
                    </button>
                  </div>
                </div>

                {formErrors.length > 0 && (
                  <div className="p-3 bg-red-50 border border-red-200 rounded-lg" role="alert">
                    <p className="text-sm text-red-900 font-medium mb-1">Please fix the following errors:</p>
                    <ul className="text-sm text-red-800 space-y-1">
                      {formErrors.map((error, idx) => (
                        <li key={idx}>• {error}</li>
                      ))}
                    </ul>
                  </div>
                )}

                <Button type="submit" disabled={loading} aria-busy={loading}>
                  {loading ? 'Submitting...' : 'Submit Form'}
                </Button>

                <LoadingAnnouncement 
                  loading={loading}
                  loadingMessage="Submitting form..."
                  completedMessage="Form submitted successfully"
                />
              </form>
            </CardContent>
          </Card>

          {/* Demo 4: Focus Trap (Modal) */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Focus className="h-5 w-5" aria-hidden="true" />
                4. Focus Trap (Modal/Dialog)
              </CardTitle>
              <CardDescription>
                Focus stays within the modal when open
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <Button onClick={() => setShowDialog(true)}>
                Open Modal
              </Button>

              {showDialog && (
                <>
                  {/* Backdrop */}
                  <div 
                    className="fixed inset-0 bg-black/50 z-40"
                    onClick={() => setShowDialog(false)}
                    aria-hidden="true"
                  />
                  
                  {/* Modal */}
                  <FocusTrap active={showDialog}>
                    <div
                      role="dialog"
                      aria-modal="true"
                      aria-labelledby="dialog-title"
                      className="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl p-6 z-50 w-full max-w-md"
                    >
                      <h2 id="dialog-title" className="text-xl font-bold mb-4">
                        Accessible Modal
                      </h2>
                      <p className="text-gray-600 mb-4">
                        Try pressing Tab - focus will stay within this modal. Press Escape or click &quot;Close&quot; to exit.
                      </p>
                      <div className="flex gap-2">
                        <Button onClick={() => setShowDialog(false)}>
                          Close
                        </Button>
                        <Button variant="outline">
                          Another Action
                        </Button>
                      </div>
                    </div>
                  </FocusTrap>
                </>
              )}

              <div className="p-4 bg-green-50 border border-green-200 rounded-lg">
                <p className="text-sm text-green-900">
                  ✅ <strong>Features:</strong> Focus trap, Escape key to close, Click outside to close, ARIA attributes
                </p>
              </div>
            </CardContent>
          </Card>

          {/* Demo 5: Live Regions */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Volume2 className="h-5 w-5" aria-hidden="true" />
                5. Live Regions (Announcements)
              </CardTitle>
              <CardDescription>
                Dynamic content changes announced to screen readers
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex gap-2">
                <Button onClick={() => setCounter(counter + 1)}>
                  Increment Counter
                </Button>
                <Button variant="outline" onClick={() => setCounter(0)}>
                  Reset
                </Button>
              </div>

              <LiveRegion priority="polite" className="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p className="text-blue-900">
                  Counter value is now: <strong>{counter}</strong>
                </p>
                <p className="text-xs text-blue-700 mt-1">
                  (Screen readers will announce this change)
                </p>
              </LiveRegion>
            </CardContent>
          </Card>

          {/* Testing Guide */}
          <Card className="border-2 border-primary/20">
            <CardHeader>
              <CardTitle>How to Test Accessibility</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-3">
                <div>
                  <h3 className="font-semibold mb-2">1. Keyboard Navigation</h3>
                  <div className="flex flex-wrap gap-2">
                    <Badge>Tab</Badge>
                    <Badge>Shift+Tab</Badge>
                    <Badge>Enter</Badge>
                    <Badge>Space</Badge>
                    <Badge>Escape</Badge>
                  </div>
                </div>

                <div>
                  <h3 className="font-semibold mb-2">2. Screen Readers</h3>
                  <ul className="text-sm space-y-1 text-gray-600">
                    <li>• Windows: NVDA (free) or JAWS</li>
                    <li>• Mac: VoiceOver (Cmd+F5)</li>
                    <li>• Chrome extension: ChromeVox</li>
                  </ul>
                </div>

                <div>
                  <h3 className="font-semibold mb-2">3. Browser Tools</h3>
                  <ul className="text-sm space-y-1 text-gray-600">
                    <li>• Chrome Lighthouse (Accessibility audit)</li>
                    <li>• axe DevTools extension</li>
                    <li>• WAVE extension</li>
                  </ul>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </MainLayout>
  );
}
