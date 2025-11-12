'use client';

import { useEffect, useState, useCallback } from 'react';
import { CardSkeleton } from '@/components/skeletons';
import { useRouter } from 'next/navigation';
import apiClient from '@/lib/api-client';
import { useAuth } from '@/contexts/auth-context';
import { useToast } from '@/hooks/use-toast';
import { MainLayout } from '@/components/layouts/main-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from '@/components/ui/tabs';
import { 
  Building2, 
  Globe, 
  Mail, 
  Phone, 
  MapPin, 
  Send,
  Save,
  Loader2,
} from 'lucide-react';

interface SettingsData {
  frontend_url: string;
  company_name: string;
  company_email: string;
  company_phone: string;
  company_address: string;
  company_google_maps: string;
  mail_mailer: string;
  mail_host: string;
  mail_port: string;
  mail_username: string;
  mail_password?: string;
  mail_encryption: string;
  mail_from_address: string;
  mail_from_name: string;
}

export default function AdminSettingsPage() {
  const router = useRouter();
  const { user } = useAuth();
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);
  const [initialLoading, setInitialLoading] = useState(true);
  const [testingEmail, setTestingEmail] = useState(false);
  const [testEmail, setTestEmail] = useState('');
  const [formData, setFormData] = useState<SettingsData>({
    frontend_url: '',
    company_name: '',
    company_email: '',
    company_phone: '',
    company_address: '',
    company_google_maps: '',
    mail_mailer: 'smtp',
    mail_host: '',
    mail_port: '587',
    mail_username: '',
    mail_password: '',
    mail_encryption: 'tls',
    mail_from_address: '',
    mail_from_name: '',
  });

  const loadSettings = useCallback(async () => {
    try {
      const response = await apiClient.get('/settings');
      if (response.data.success) {
        setFormData({
          ...response.data.data,
          mail_password: '', // Don't populate password for security
        });
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to load settings',
        variant: 'destructive',
      });
    } finally {
      setInitialLoading(false);
    }
  }, [toast]);

  useEffect(() => {
    if (!user) {
      router.push('/auth/login');
      return;
    }

    // Check if user is admin
    if (user.role !== 'admin') {
      toast({
        title: 'Access Denied',
        description: 'You must be an administrator to access this page.',
        variant: 'destructive',
      });
      router.push('/dashboard');
      return;
    }

    loadSettings();
  }, [user, router, toast, loadSettings]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      // Remove empty password if not changed
      const dataToSend = { ...formData };
      if (!dataToSend.mail_password) {
        delete dataToSend.mail_password;
      }

      const response = await apiClient.put('/settings', dataToSend);
      
      toast({
        title: 'Success',
        description: 'Settings saved successfully',
      });

      // Reload settings to get updated values
      await loadSettings();
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to save settings',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const handleTestEmail = async () => {
    if (!testEmail) {
      toast({
        title: 'Error',
        description: 'Please enter an email address',
        variant: 'destructive',
      });
      return;
    }

    setTestingEmail(true);
    try {
      const response = await apiClient.post('/settings/test-email', {
        email: testEmail,
      });

      toast({
        title: 'Success',
        description: response.data.message || 'Test email sent successfully',
      });
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to send test email',
        variant: 'destructive',
      });
    } finally {
      setTestingEmail(false);
    }
  };

  if (!user) {
    return null;
  }

  if (initialLoading) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-8 space-y-6 max-w-6xl">
          <div className="space-y-2">
            <div className="h-8 w-64 bg-primary/10 rounded" />
            <div className="h-4 w-80 bg-primary/10 rounded" />
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <CardSkeleton />
            <CardSkeleton />
          </div>
          <CardSkeleton />
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8 max-w-6xl">
        <div className="mb-6">
          <h1 className="text-3xl font-bold mb-2">System Settings</h1>
          <p className="text-gray-600">
            Configure frontend URL, company information, and email settings
          </p>
        </div>

        <form onSubmit={handleSubmit}>
          <Tabs defaultValue="frontend" className="space-y-6">
            <TabsList className="grid w-full grid-cols-3">
              <TabsTrigger value="frontend">Frontend</TabsTrigger>
              <TabsTrigger value="company">Company Info</TabsTrigger>
              <TabsTrigger value="mail">Email (SMTP)</TabsTrigger>
            </TabsList>

            {/* Frontend Settings */}
            <TabsContent value="frontend">
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <Globe className="h-5 w-5" />
                    Frontend Configuration
                  </CardTitle>
                  <CardDescription>
                    Configure the URL where your frontend application is hosted
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div>
                    <Label htmlFor="frontend_url">Frontend URL</Label>
                    <Input
                      id="frontend_url"
                      type="url"
                      value={formData.frontend_url}
                      onChange={(e) =>
                        setFormData({ ...formData, frontend_url: e.target.value })
                      }
                      placeholder="https://renthub.vercel.app"
                      required
                    />
                    <p className="text-sm text-gray-500 mt-1">
                      Used for CORS, email links, and redirects
                    </p>
                  </div>

                  <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 className="font-semibold text-blue-900 mb-2">Examples:</h4>
                    <ul className="text-sm text-blue-800 space-y-1">
                      <li>• Development: http://localhost:3000</li>
                      <li>• Vercel: https://renthub.vercel.app</li>
                      <li>• Custom domain: https://www.renthub.com</li>
                    </ul>
                  </div>
                </CardContent>
              </Card>
            </TabsContent>

            {/* Company Information */}
            <TabsContent value="company">
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <Building2 className="h-5 w-5" />
                    Company Information
                  </CardTitle>
                  <CardDescription>
                    Company details displayed on the website and in emails
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <Label htmlFor="company_name">Company Name</Label>
                      <Input
                        id="company_name"
                        value={formData.company_name}
                        onChange={(e) =>
                          setFormData({ ...formData, company_name: e.target.value })
                        }
                        placeholder="RentHub"
                        required
                      />
                    </div>

                    <div>
                      <Label htmlFor="company_email">
                        <Mail className="inline h-4 w-4 mr-1" />
                        Email
                      </Label>
                      <Input
                        id="company_email"
                        type="email"
                        value={formData.company_email}
                        onChange={(e) =>
                          setFormData({ ...formData, company_email: e.target.value })
                        }
                        placeholder="info@renthub.com"
                        required
                      />
                    </div>

                    <div>
                      <Label htmlFor="company_phone">
                        <Phone className="inline h-4 w-4 mr-1" />
                        Phone
                      </Label>
                      <Input
                        id="company_phone"
                        type="tel"
                        value={formData.company_phone}
                        onChange={(e) =>
                          setFormData({ ...formData, company_phone: e.target.value })
                        }
                        placeholder="+1 (555) 000-0000"
                      />
                    </div>
                  </div>

                  <div>
                    <Label htmlFor="company_address">
                      <MapPin className="inline h-4 w-4 mr-1" />
                      Address
                    </Label>
                    <Textarea
                      id="company_address"
                      value={formData.company_address}
                      onChange={(e) =>
                        setFormData({ ...formData, company_address: e.target.value })
                      }
                      placeholder="123 Main Street, City, Country"
                      rows={3}
                    />
                  </div>

                  <div>
                    <Label htmlFor="company_google_maps">Google Maps Embed URL</Label>
                    <Textarea
                      id="company_google_maps"
                      value={formData.company_google_maps}
                      onChange={(e) =>
                        setFormData({
                          ...formData,
                          company_google_maps: e.target.value,
                        })
                      }
                      placeholder="https://www.google.com/maps/embed?pb=..."
                      rows={2}
                    />
                    <p className="text-sm text-gray-500 mt-1">
                      Get from Google Maps → Share → Embed a map
                    </p>
                  </div>
                </CardContent>
              </Card>
            </TabsContent>

            {/* Mail Settings */}
            <TabsContent value="mail">
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <Mail className="h-5 w-5" />
                    Email Configuration (SMTP)
                  </CardTitle>
                  <CardDescription>
                    Configure SMTP settings for sending emails
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <Label htmlFor="mail_mailer">Mail Driver</Label>
                      <Select
                        value={formData.mail_mailer}
                        onValueChange={(value) =>
                          setFormData({ ...formData, mail_mailer: value })
                        }
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Select driver" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="smtp">SMTP</SelectItem>
                          <SelectItem value="sendmail">Sendmail</SelectItem>
                          <SelectItem value="mailgun">Mailgun</SelectItem>
                          <SelectItem value="ses">Amazon SES</SelectItem>
                          <SelectItem value="postmark">Postmark</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>

                    <div>
                      <Label htmlFor="mail_encryption">Encryption</Label>
                      <Select
                        value={formData.mail_encryption}
                        onValueChange={(value) =>
                          setFormData({ ...formData, mail_encryption: value })
                        }
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Select encryption" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="tls">TLS</SelectItem>
                          <SelectItem value="ssl">SSL</SelectItem>
                          <SelectItem value="none">None</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>

                    <div>
                      <Label htmlFor="mail_host">SMTP Host</Label>
                      <Input
                        id="mail_host"
                        value={formData.mail_host}
                        onChange={(e) =>
                          setFormData({ ...formData, mail_host: e.target.value })
                        }
                        placeholder="smtp.gmail.com"
                        required
                      />
                    </div>

                    <div>
                      <Label htmlFor="mail_port">SMTP Port</Label>
                      <Input
                        id="mail_port"
                        type="number"
                        value={formData.mail_port}
                        onChange={(e) =>
                          setFormData({ ...formData, mail_port: e.target.value })
                        }
                        placeholder="587"
                        required
                      />
                    </div>

                    <div>
                      <Label htmlFor="mail_username">Username</Label>
                      <Input
                        id="mail_username"
                        value={formData.mail_username}
                        onChange={(e) =>
                          setFormData({ ...formData, mail_username: e.target.value })
                        }
                        placeholder="your-email@gmail.com"
                      />
                    </div>

                    <div>
                      <Label htmlFor="mail_password">Password</Label>
                      <Input
                        id="mail_password"
                        type="password"
                        value={formData.mail_password}
                        onChange={(e) =>
                          setFormData({ ...formData, mail_password: e.target.value })
                        }
                        placeholder="Leave empty to keep current"
                      />
                    </div>

                    <div>
                      <Label htmlFor="mail_from_address">From Email</Label>
                      <Input
                        id="mail_from_address"
                        type="email"
                        value={formData.mail_from_address}
                        onChange={(e) =>
                          setFormData({
                            ...formData,
                            mail_from_address: e.target.value,
                          })
                        }
                        placeholder="noreply@renthub.com"
                        required
                      />
                    </div>

                    <div>
                      <Label htmlFor="mail_from_name">From Name</Label>
                      <Input
                        id="mail_from_name"
                        value={formData.mail_from_name}
                        onChange={(e) =>
                          setFormData({ ...formData, mail_from_name: e.target.value })
                        }
                        placeholder="RentHub"
                        required
                      />
                    </div>
                  </div>

                  {/* Test Email Section */}
                  <div className="border-t pt-4 mt-4">
                    <Label htmlFor="test_email">Test Email Configuration</Label>
                    <div className="flex gap-2 mt-2">
                      <Input
                        id="test_email"
                        type="email"
                        value={testEmail}
                        onChange={(e) => setTestEmail(e.target.value)}
                        placeholder="test@example.com"
                      />
                      <Button
                        type="button"
                        variant="outline"
                        onClick={handleTestEmail}
                        disabled={testingEmail || !testEmail}
                      >
                        {testingEmail ? (
                          <>
                            <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                            Sending...
                          </>
                        ) : (
                          <>
                            <Send className="h-4 w-4 mr-2" />
                            Send Test
                          </>
                        )}
                      </Button>
                    </div>
                    <p className="text-sm text-gray-500 mt-1">
                      Send a test email to verify your SMTP configuration
                    </p>
                  </div>

                  {/* Common SMTP Settings */}
                  <div className="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 className="font-semibold mb-2">Common SMTP Settings:</h4>
                    <div className="text-sm space-y-2">
                      <div>
                        <strong>Gmail:</strong> smtp.gmail.com:587 (TLS) - Use App Password
                      </div>
                      <div>
                        <strong>Outlook:</strong> smtp-mail.outlook.com:587 (TLS)
                      </div>
                      <div>
                        <strong>Mailtrap (Testing):</strong> smtp.mailtrap.io:2525
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </TabsContent>
          </Tabs>

          {/* Save Button */}
          <div className="flex justify-end gap-4 mt-6">
            <Button
              type="button"
              variant="outline"
              onClick={() => router.back()}
              disabled={loading}
            >
              Cancel
            </Button>
            <Button type="submit" disabled={loading}>
              {loading ? (
                <>
                  <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                  Saving...
                </>
              ) : (
                <>
                  <Save className="h-4 w-4 mr-2" />
                  Save Settings
                </>
              )}
            </Button>
          </div>
        </form>
      </div>
    </MainLayout>
  );
}
