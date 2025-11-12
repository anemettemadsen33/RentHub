'use client';

import { useEffect, useState, useRef, useCallback } from 'react';
import { useTranslations } from '@/lib/i18n-temp';
import { useRouter } from 'next/navigation';
import apiClient from '@/lib/api-client';
import { useAuth } from '@/contexts/auth-context';
import { useToast } from '@/hooks/use-toast';
import { MainLayout } from '@/components/layouts/main-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import { User, Mail, Phone, MapPin, Edit, Shield, Camera, Upload, Trash2, CheckCircle, XCircle, Linkedin, Twitter, Facebook, Instagram, Globe, Calendar, Save, X as CloseIcon, Loader2 } from 'lucide-react';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { CardSkeleton, ListSkeleton } from '@/components/skeletons';

export default function ProfilePage() {
  const router = useRouter();
  const { user, logout } = useAuth();
  const { toast } = useToast();
  const fileInputRef = useRef<HTMLInputElement>(null);
  const t = useTranslations('profilePage');
  const tNotify = useTranslations('notify');
  
  const [editing, setEditing] = useState(false);
  const [loading, setLoading] = useState(false);
  const [initialLoading, setInitialLoading] = useState(true);
  const [uploadingAvatar, setUploadingAvatar] = useState(false);
  const [avatarUrl, setAvatarUrl] = useState('');
  
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    address: '',
    bio: '',
    date_of_birth: '',
    occupation: '',
    languages: '',
  });
  
  const [socialLinks, setSocialLinks] = useState({
    linkedin: '',
    twitter: '',
    facebook: '',
    instagram: '',
    website: '',
  });
  
  const [passwordData, setPasswordData] = useState({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
  });
  
  const [verificationStatus, setVerificationStatus] = useState({
    email_verified: false,
    phone_verified: false,
    identity_verified: false,
  });

  const loadProfile = useCallback(async () => {
    setFormData({
      name: user?.name || '',
      email: user?.email || '',
      phone: (user as any)?.phone || '',
      address: (user as any)?.address || '',
      bio: (user as any)?.bio || '',
      date_of_birth: (user as any)?.date_of_birth || '',
      occupation: (user as any)?.occupation || '',
      languages: (user as any)?.languages || '',
    });
    
    try {
      const resp = await apiClient.get('/v1/profile').catch(() => null);
      if (resp?.data) {
        setAvatarUrl(resp.data.avatar_url || '');
        setVerificationStatus({
          email_verified: resp.data.email_verified || false,
          phone_verified: resp.data.phone_verified || false,
          identity_verified: resp.data.identity_verified || false,
        });
        setSocialLinks(resp.data.social_links || {});
      }
    } catch (error) {
      console.log('Profile data not available, using defaults');
    } finally {
      setInitialLoading(false);
    }
  }, [user]);

  useEffect(() => {
    if (!user) {
      router.push('/auth/login');
      return;
    }
    loadProfile();
  }, [user, router, loadProfile]);

  const updateProfile = async () => {
    setLoading(true);
    try {
      await apiClient.put('/v1/profile', { ...formData, social_links: socialLinks });
      toast({ title: tNotify('success'), description: t('toasts.profileUpdated') });
      setEditing(false);
    } catch (error: any) {
      toast({
        title: tNotify('error'),
        description: error.response?.data?.message || t('toasts.updateFailed'),
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    await updateProfile();
  };

  const handleAvatarUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    
    if (!file.type.startsWith('image/')) {
      toast({ title: tNotify('error'), description: t('toasts.selectImage'), variant: 'destructive' });
      return;
    }
    
    setUploadingAvatar(true);
    try {
      const formData = new FormData();
      formData.append('avatar', file);
      const { data } = await apiClient.post('/v1/profile/avatar', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      setAvatarUrl(data.avatar_url);
      toast({ title: tNotify('success'), description: t('toasts.avatarUploaded') });
    } catch (error) {
      toast({ title: tNotify('error'), description: t('toasts.avatarUploadFailed'), variant: 'destructive' });
    } finally {
      setUploadingAvatar(false);
    }
  };

  const handlePasswordChange = async (e: React.FormEvent) => {
    e.preventDefault();
    if (passwordData.new_password !== passwordData.new_password_confirmation) {
      toast({ title: tNotify('error'), description: t('toasts.passwordsMismatch'), variant: 'destructive' });
      return;
    }
    setLoading(true);
    try {
      await apiClient.put('/v1/profile/password', passwordData);
      toast({ title: tNotify('success'), description: t('toasts.passwordUpdated') });
      setPasswordData({ current_password: '', new_password: '', new_password_confirmation: '' });
    } catch (error: any) {
      toast({
        title: tNotify('error'),
        description: error.response?.data?.message || t('toasts.passwordUpdateFailed'),
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteAccount = async () => {
    try {
      await apiClient.delete('/v1/profile');
      toast({ title: t('toasts.accountDeletedTitle'), description: t('toasts.accountDeletedDesc') });
      logout();
      router.push('/');
    } catch (error) {
      toast({ title: tNotify('error'), description: t('toasts.accountDeleteFailed'), variant: 'destructive' });
    }
  };

  if (!user) return null;

  if (initialLoading) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-8 max-w-6xl space-y-6">
          <div className="mb-2">
            <div className="h-8 w-48 bg-primary/10 rounded" />
            <div className="h-4 w-64 bg-primary/10 rounded mt-2" />
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <CardSkeleton />
            <div className="md:col-span-2 space-y-6">
              <CardSkeleton />
              <CardSkeleton />
            </div>
          </div>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
  <div className="container mx-auto px-4 py-8 max-w-6xl animate-fade-in">
        <div className="mb-6">
          <h1 className="text-2xl md:text-3xl font-bold mb-2">{t('title')}</h1>
          <p className="text-gray-600 text-sm md:text-base">{t('subtitle')}</p>
        </div>

        <Tabs defaultValue="profile" className="space-y-6">
          <TabsList className="grid w-full grid-cols-3 lg:w-auto lg:inline-grid">
            <TabsTrigger value="profile">{t('tabs.profile')}</TabsTrigger>
            <TabsTrigger value="security">{t('tabs.security')}</TabsTrigger>
            <TabsTrigger value="social">{t('tabs.social')}</TabsTrigger>
          </TabsList>

          {/* Profile Tab */}
          <TabsContent value="profile" className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <Card className="animate-fade-in-up">
                <CardContent className="pt-6">
                  <div className="flex flex-col items-center text-center space-y-4">
                    <div className="relative">
                      <Avatar className="w-32 h-32">
                        <AvatarImage src={avatarUrl} alt={user.name} />
                        <AvatarFallback className="text-3xl">
                          {user.name?.charAt(0).toUpperCase()}
                        </AvatarFallback>
                      </Avatar>
                      <TooltipProvider>
                        <Tooltip>
                          <TooltipTrigger asChild>
                            <button
                              onClick={() => fileInputRef.current?.click()}
                              className="absolute bottom-0 right-0 p-2 bg-primary text-white rounded-full hover:bg-primary/90 disabled:opacity-50"
                              disabled={uploadingAvatar}
                              aria-label={t('aria.changeAvatar') || 'Change avatar'}
                            >
                              {uploadingAvatar ? (
                                <Loader2 className="h-4 w-4 animate-spin" />
                              ) : (
                                <Camera className="h-4 w-4" />
                              )}
                            </button>
                          </TooltipTrigger>
                          <TooltipContent>{t('tooltips.changeAvatar') || 'Change avatar'}</TooltipContent>
                        </Tooltip>
                      </TooltipProvider>
                      <input
                        ref={fileInputRef}
                        type="file"
                        accept="image/*"
                        className="hidden"
                        onChange={handleAvatarUpload}
                      />
                    </div>
                    
                    <div className="space-y-1">
                      <h2 className="text-xl font-bold">{user.name}</h2>
                      <p className="text-sm text-gray-600">{user.email}</p>
                      <Badge variant={user.role === 'landlord' ? 'default' : 'secondary'}>
                        {user.role === 'landlord' ? t('role.host') : t('role.guest')}
                      </Badge>
                    </div>

                    <Separator />

                    <div className="w-full space-y-2">
                      <h3 className="text-sm font-semibold">{t('verification.status')}</h3>
                      <div className="space-y-1 text-sm">
                        <div className="flex items-center justify-between">
                          <span>{t('verification.email')}</span>
                          {verificationStatus.email_verified ? (
                            <CheckCircle className="h-4 w-4 text-green-500" />
                          ) : (
                            <XCircle className="h-4 w-4 text-gray-400" />
                          )}
                        </div>
                        <div className="flex items-center justify-between">
                          <span>{t('verification.phone')}</span>
                          {verificationStatus.phone_verified ? (
                            <CheckCircle className="h-4 w-4 text-green-500" />
                          ) : (
                            <XCircle className="h-4 w-4 text-gray-400" />
                          )}
                        </div>
                        <div className="flex items-center justify-between">
                          <span>{t('verification.identity')}</span>
                          {verificationStatus.identity_verified ? (
                            <CheckCircle className="h-4 w-4 text-green-500" />
                          ) : (
                            <XCircle className="h-4 w-4 text-gray-400" />
                          )}
                        </div>
                      </div>
                      <Button
                        size="sm"
                        variant="outline"
                        className="w-full"
                        onClick={() => router.push('/verification')}
                      >
                        {t('verification.verifyIdentity')}
                      </Button>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card className="md:col-span-2 animate-fade-in-up">
                <CardHeader>
                  <div className="flex items-center justify-between">
                    <CardTitle>{t('personal.title')}</CardTitle>
                    {!editing ? (
                      <Button variant="outline" size="sm" onClick={() => setEditing(true)}>
                        <Edit className="h-4 w-4 mr-2" />
                        {t('personal.edit')}
                      </Button>
                    ) : (
                      <Button variant="ghost" size="sm" onClick={() => setEditing(false)}>
                        <CloseIcon className="h-4 w-4 mr-2" />
                        {t('personal.cancel')}
                      </Button>
                    )}
                  </div>
                </CardHeader>
                <CardContent>
                  <form onSubmit={handleSubmit} className="space-y-4" aria-busy={loading} aria-describedby="profile-save-status">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <Label htmlFor="name">{t('personal.fullName')}</Label>
                        <Input
                          id="name"
                          value={formData.name}
                          onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                          disabled={!editing}
                          className="h-11 md:h-10"
                        />
                      </div>
                      <div>
                        <Label htmlFor="email">{t('personal.email')}</Label>
                        <Input
                          id="email"
                          type="email"
                          value={formData.email}
                          onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                          disabled={!editing}
                          className="h-11 md:h-10"
                        />
                      </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <Label htmlFor="phone">{t('personal.phone')}</Label>
                        <Input
                          id="phone"
                          type="tel"
                          value={formData.phone}
                          onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                          disabled={!editing}
                          placeholder={t('personal.placeholders.phone')}
                          className="h-11 md:h-10"
                        />
                      </div>
                      <div>
                        <Label htmlFor="date_of_birth">{t('personal.dateOfBirth')}</Label>
                        <Input
                          id="date_of_birth"
                          type="date"
                          value={formData.date_of_birth}
                          onChange={(e) => setFormData({ ...formData, date_of_birth: e.target.value })}
                          disabled={!editing}
                          className="h-11 md:h-10"
                        />
                      </div>
                    </div>

                    <div>
                      <Label htmlFor="address">{t('personal.address')}</Label>
                      <Input
                        id="address"
                        value={formData.address}
                        onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                        disabled={!editing}
                        placeholder={t('personal.placeholders.address')}
                        className="h-11 md:h-10"
                      />
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <Label htmlFor="occupation">{t('personal.occupation')}</Label>
                        <Input
                          id="occupation"
                          value={formData.occupation}
                          onChange={(e) => setFormData({ ...formData, occupation: e.target.value })}
                          disabled={!editing}
                          placeholder={t('personal.placeholders.occupation')}
                          className="h-11 md:h-10"
                        />
                      </div>
                      <div>
                        <Label htmlFor="languages">{t('personal.languages')}</Label>
                        <Input
                          id="languages"
                          value={formData.languages}
                          onChange={(e) => setFormData({ ...formData, languages: e.target.value })}
                          disabled={!editing}
                          placeholder={t('personal.placeholders.languages')}
                          className="h-11 md:h-10"
                        />
                      </div>
                    </div>

                    <div>
                      <Label htmlFor="bio">{t('personal.bio')}</Label>
                      <Textarea
                        id="bio"
                        value={formData.bio}
                        onChange={(e) => setFormData({ ...formData, bio: e.target.value })}
                        disabled={!editing}
                        placeholder={t('personal.placeholders.bio')}
                        rows={4}
                        className="resize-none"
                      />
                    </div>

                    {editing && (
                      <div className="flex gap-3">
                        <Button type="submit" disabled={loading} className="h-11 md:h-10">
                          {loading ? (
                            <span className="inline-flex items-center"><Loader2 className="h-4 w-4 mr-2 animate-spin" />{t('personal.saving')}</span>
                          ) : (
                            <span className="inline-flex items-center"><Save className="h-4 w-4 mr-2" />{t('personal.saveChanges')}</span>
                          )}
                        </Button>
                        <Button
                          type="button"
                          variant="outline"
                          onClick={() => setEditing(false)}
                          className="h-11 md:h-10"
                        >
                          {t('buttons.cancel')}
                        </Button>
                      </div>
                    )}
                    <p id="profile-save-status" className="sr-only" aria-live="polite">{loading ? t('personal.saving') : ''}</p>
                  </form>
                </CardContent>
              </Card>
            </div>
          </TabsContent>

          {/* Security Tab */}
          <TabsContent value="security" className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <Shield className="h-5 w-5" />
                    {t('security.changePassword')}
                  </CardTitle>
                  <CardDescription>{t('security.description')}</CardDescription>
                </CardHeader>
                <CardContent>
                  <form onSubmit={handlePasswordChange} className="space-y-4">
                    <div>
                      <Label htmlFor="current_password">{t('security.current')}</Label>
                      <Input
                        id="current_password"
                        type="password"
                        value={passwordData.current_password}
                        onChange={(e) => setPasswordData({ ...passwordData, current_password: e.target.value })}
                        className="h-11 md:h-10"
                      />
                    </div>
                    <div>
                      <Label htmlFor="new_password">{t('security.new')}</Label>
                      <Input
                        id="new_password"
                        type="password"
                        value={passwordData.new_password}
                        onChange={(e) => setPasswordData({ ...passwordData, new_password: e.target.value })}
                        className="h-11 md:h-10"
                      />
                    </div>
                    <div>
                      <Label htmlFor="confirm_password">{t('security.confirm')}</Label>
                      <Input
                        id="confirm_password"
                        type="password"
                        value={passwordData.new_password_confirmation}
                        onChange={(e) => setPasswordData({ ...passwordData, new_password_confirmation: e.target.value })}
                        className="h-11 md:h-10"
                      />
                    </div>
                    <Button type="submit" disabled={loading} className="w-full h-11 md:h-10">
                      {loading ? t('security.updating') : t('security.update')}
                    </Button>
                  </form>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle className="text-red-600">{t('danger.title')}</CardTitle>
                  <CardDescription>{t('danger.description')}</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="p-4 border border-red-200 rounded-lg bg-red-50">
                    <h3 className="font-semibold text-red-800 mb-2">{t('danger.deleteTitle')}</h3>
                    <p className="text-sm text-red-700 mb-4">
                      {t('danger.deleteDescription')}
                    </p>
                    <AlertDialog>
                      <AlertDialogTrigger asChild>
                        <Button variant="destructive" className="w-full">
                          <Trash2 className="h-4 w-4 mr-2" />
                          {t('danger.deleteTitle')}
                        </Button>
                      </AlertDialogTrigger>
                      <AlertDialogContent>
                        <AlertDialogHeader>
                          <AlertDialogTitle>{t('danger.dialogTitle')}</AlertDialogTitle>
                          <AlertDialogDescription>
                            {t('danger.dialogDescription')}
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel>{t('buttons.cancel')}</AlertDialogCancel>
                          <AlertDialogAction onClick={handleDeleteAccount} className="bg-red-600 hover:bg-red-700">
                            {t('danger.confirm')}
                          </AlertDialogAction>
                        </AlertDialogFooter>
                      </AlertDialogContent>
                    </AlertDialog>
                  </div>
                </CardContent>
              </Card>
            </div>
          </TabsContent>

          {/* Social Links Tab */}
          <TabsContent value="social" className="space-y-6">
            <Card className="animate-fade-in-up">
              <CardHeader>
                <CardTitle>{t('social.title')}</CardTitle>
                <CardDescription>{t('social.description')}</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <Label htmlFor="linkedin" className="flex items-center gap-2">
                    <Linkedin className="h-4 w-4" />
                    {t('social.linkedin')}
                  </Label>
                  <Input
                    id="linkedin"
                    value={socialLinks.linkedin}
                    onChange={(e) => setSocialLinks({ ...socialLinks, linkedin: e.target.value })}
                    placeholder="https://linkedin.com/in/username"
                    className="h-11 md:h-10"
                  />
                </div>
                <div>
                  <Label htmlFor="twitter" className="flex items-center gap-2">
                    <Twitter className="h-4 w-4" />
                    {t('social.twitter')}
                  </Label>
                  <Input
                    id="twitter"
                    value={socialLinks.twitter}
                    onChange={(e) => setSocialLinks({ ...socialLinks, twitter: e.target.value })}
                    placeholder="https://twitter.com/username"
                    className="h-11 md:h-10"
                  />
                </div>
                <div>
                  <Label htmlFor="facebook" className="flex items-center gap-2">
                    <Facebook className="h-4 w-4" />
                    {t('social.facebook')}
                  </Label>
                  <Input
                    id="facebook"
                    value={socialLinks.facebook}
                    onChange={(e) => setSocialLinks({ ...socialLinks, facebook: e.target.value })}
                    placeholder="https://facebook.com/username"
                    className="h-11 md:h-10"
                  />
                </div>
                <div>
                  <Label htmlFor="instagram" className="flex items-center gap-2">
                    <Instagram className="h-4 w-4" />
                    {t('social.instagram')}
                  </Label>
                  <Input
                    id="instagram"
                    value={socialLinks.instagram}
                    onChange={(e) => setSocialLinks({ ...socialLinks, instagram: e.target.value })}
                    placeholder="https://instagram.com/username"
                    className="h-11 md:h-10"
                  />
                </div>
                <div>
                  <Label htmlFor="website" className="flex items-center gap-2">
                    <Globe className="h-4 w-4" />
                    {t('social.website')}
                  </Label>
                  <Input
                    id="website"
                    value={socialLinks.website}
                    onChange={(e) => setSocialLinks({ ...socialLinks, website: e.target.value })}
                    placeholder="https://yourwebsite.com"
                    className="h-11 md:h-10"
                  />
                </div>
                <Button onClick={updateProfile} disabled={loading} className="w-full h-11 md:h-10">
                  {loading ? (
                    <span className="inline-flex items-center"><Loader2 className="h-4 w-4 mr-2 animate-spin" />{t('social.saving')}</span>
                  ) : (
                    <span className="inline-flex items-center"><Save className="h-4 w-4 mr-2" />{t('social.save')}</span>
                  )}
                </Button>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    </MainLayout>
  );
}
