'use client';

import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { User, Shield, AlertTriangle } from 'lucide-react';
import apiClient from '@/lib/api-client';
import { toast } from 'sonner';

interface UserProfile {
  id: number;
  name: string;
  email: string;
  role: 'guest' | 'tenant' | 'owner' | 'admin';
}

const ROLES = [
  { value: 'guest', label: 'Guest', description: 'Can browse properties' },
  { value: 'tenant', label: 'Tenant', description: 'Can book properties' },
  { value: 'owner', label: 'Owner/Host', description: 'Can list and manage properties' },
  { value: 'admin', label: 'Admin', description: 'Full system access' },
];

export default function SettingsPage() {
  const [user, setUser] = useState<UserProfile | null>(null);
  const [loading, setLoading] = useState(true);
  const [selectedRole, setSelectedRole] = useState<string>('');

  useEffect(() => {
    fetchUserProfile();
  }, []);

  const fetchUserProfile = async () => {
    try {
      setLoading(true);
      const response = await apiClient.get('/user');
      const userData = response.data.data || response.data;
      setUser(userData);
      setSelectedRole(userData.role);
    } catch (error) {
      console.error('Failed to fetch user profile:', error);
      toast.error('Failed to load user profile');
    } finally {
      setLoading(false);
    }
  };

  const handleRoleChange = async () => {
    if (!user || selectedRole === user.role) {
      toast.info('No changes to save');
      return;
    }

    try {
      // Note: This endpoint might not exist in production
      // This is for development/testing purposes only
      await apiClient.put('/user/role', { role: selectedRole });
      toast.success('Role updated successfully! Please refresh the page.');
      
      // Refresh user data
      setTimeout(() => {
        fetchUserProfile();
        window.location.reload();
      }, 1500);
    } catch (error: any) {
      console.error('Failed to update role:', error);
      
      // If endpoint doesn't exist, show manual instructions
      if (error.response?.status === 404 || error.response?.status === 405) {
        toast.error('Role change via UI not available. Please contact support or use backend command.');
        console.log('To change role manually, run in backend:');
        console.log(`php artisan tinker --execute="DB::table('users')->where('email', '${user?.email}')->update(['role' => '${selectedRole}']); echo 'Role updated';"`);
      } else {
        toast.error('Failed to update role');
      }
    }
  };

  const getRoleBadgeVariant = (role: string) => {
    const variants: Record<string, any> = {
      admin: 'destructive',
      owner: 'default',
      tenant: 'secondary',
      guest: 'outline',
    };
    return variants[role] || 'outline';
  };

  if (loading) {
    return (
      <div className="container mx-auto p-6">
        <div className="flex items-center justify-center min-h-[400px]">
          <div className="text-center">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
            <p className="text-muted-foreground">Loading settings...</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto p-6 max-w-4xl space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-3xl font-bold flex items-center gap-2">
          <Shield className="h-8 w-8" />
          Account Settings
        </h1>
        <p className="text-muted-foreground mt-1">
          Manage your account role and permissions
        </p>
      </div>

      {/* Current User Info */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <User className="h-5 w-5" />
            Current User
          </CardTitle>
          <CardDescription>Your account information</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label className="text-sm text-muted-foreground">Name</Label>
              <p className="font-medium">{user?.name}</p>
            </div>
            <div>
              <Label className="text-sm text-muted-foreground">Email</Label>
              <p className="font-medium">{user?.email}</p>
            </div>
            <div>
              <Label className="text-sm text-muted-foreground">Current Role</Label>
              <div className="mt-1">
                <Badge variant={getRoleBadgeVariant(user?.role || 'guest')}>
                  {user?.role?.toUpperCase()}
                </Badge>
              </div>
            </div>
            <div>
              <Label className="text-sm text-muted-foreground">User ID</Label>
              <p className="font-medium">#{user?.id}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Role Management */}
      <Card className="border-orange-200 bg-orange-50/50">
        <CardHeader>
          <CardTitle className="flex items-center gap-2 text-orange-900">
            <AlertTriangle className="h-5 w-5" />
            Change User Role (Development Only)
          </CardTitle>
          <CardDescription className="text-orange-700">
            ⚠️ Warning: This is for testing purposes only. In production, roles should be managed by administrators.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="role">Select Role</Label>
            <Select value={selectedRole} onValueChange={setSelectedRole}>
              <SelectTrigger id="role">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                {ROLES.map((role) => (
                  <SelectItem key={role.value} value={role.value}>
                    <div className="flex flex-col">
                      <span className="font-medium">{role.label}</span>
                      <span className="text-xs text-muted-foreground">{role.description}</span>
                    </div>
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          {selectedRole !== user?.role && (
            <div className="bg-white border border-orange-200 rounded-lg p-4">
              <h4 className="font-semibold mb-2">Role Permissions:</h4>
              <ul className="space-y-1 text-sm">
                {selectedRole === 'owner' && (
                  <>
                    <li>✓ Create and manage properties</li>
                    <li>✓ View property analytics</li>
                    <li>✓ Manage bookings as host</li>
                    <li>✓ All tenant permissions</li>
                  </>
                )}
                {selectedRole === 'tenant' && (
                  <>
                    <li>✓ Book properties</li>
                    <li>✓ Manage bookings</li>
                    <li>✓ View invoices</li>
                    <li>✓ Write reviews</li>
                  </>
                )}
                {selectedRole === 'admin' && (
                  <>
                    <li>✓ Full system access</li>
                    <li>✓ Manage all users</li>
                    <li>✓ Manage all properties</li>
                    <li>✓ Access admin panel</li>
                  </>
                )}
                {selectedRole === 'guest' && (
                  <>
                    <li>✓ Browse properties</li>
                    <li>✓ View public listings</li>
                  </>
                )}
              </ul>
            </div>
          )}

          <Button
            onClick={handleRoleChange}
            disabled={selectedRole === user?.role}
            className="w-full"
          >
            Update Role to {selectedRole?.toUpperCase()}
          </Button>

          <div className="text-xs text-muted-foreground bg-white border rounded p-3">
            <strong>Manual Method (if UI update fails):</strong>
            <br />
            Run in backend terminal:
            <pre className="mt-2 p-2 bg-gray-100 rounded text-xs overflow-x-auto">
              php artisan tinker --execute=&quot;DB::table(&apos;users&apos;)-&gt;where(&apos;email&apos;, &apos;{user?.email}&apos;)-&gt;update([&apos;role&apos; =&gt; &apos;{selectedRole}&apos;]); echo &apos;Role updated&apos;;&quot;
            </pre>
          </div>
        </CardContent>
      </Card>

      {/* Info Card */}
      <Card>
        <CardHeader>
          <CardTitle>Need to Create Properties?</CardTitle>
          <CardDescription>You must have Owner or Host role</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-2 text-sm">
            <p>
              To list and manage properties on RentHub, you need the <Badge variant="default">OWNER</Badge> or <Badge variant="default">HOST</Badge> role.
            </p>
            <p>
              If you're getting a 403 error when trying to create properties, change your role to "Owner" above.
            </p>
            <p className="text-muted-foreground">
              Current role: <Badge variant={getRoleBadgeVariant(user?.role || 'guest')}>{user?.role?.toUpperCase()}</Badge>
              {(user?.role === 'owner' || user?.role === 'admin') && ' ✓ You can create properties!'}
            </p>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
