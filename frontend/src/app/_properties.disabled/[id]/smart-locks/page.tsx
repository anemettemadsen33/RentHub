'use client';

import { useCallback, useEffect, useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { MainLayout } from '@/components/layouts/main-layout';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { breadcrumbSets } from '@/lib/breadcrumbs';
import { 
  Card, 
  CardContent, 
  CardDescription, 
  CardHeader, 
  CardTitle 
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Switch } from '@/components/ui/switch';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  Lock,
  Plus,
  Trash2,
  RefreshCw,
  Key,
  Battery,
  CheckCircle,
  XCircle,
  Copy,
  Eye,
  EyeOff,
} from 'lucide-react';
import { notify } from '@/lib/notify';
import { smartLocksService } from '@/lib/api-service';
import apiClient from '@/lib/api-client';
import { API_ENDPOINTS } from '@/lib/api-endpoints';
import { useTranslations } from 'next-intl';

interface SmartLock {
  id: number;
  name: string;
  provider: string;
  location: string;
  status: 'active' | 'inactive' | 'error';
  auto_generate_codes: boolean;
  battery_level: number | null;
  last_synced_at: string | null;
  error_message: string | null;
}

interface AccessCode {
  id: number;
  code: string;
  type: 'permanent' | 'temporary' | 'one-time';
  valid_from: string;
  valid_until: string;
  status: 'active' | 'expired' | 'revoked';
  uses_count: number;
  max_uses: number | null;
  booking_id: number | null;
  user_id: number | null;
}

export default function SmartLocksPage() {
  const params = useParams();
  const router = useRouter();
  const propertyId = Number(params.id);
  const tNotify = useTranslations('notify');
  
  const [locks, setLocks] = useState<SmartLock[]>([]);
  const [selectedLock, setSelectedLock] = useState<SmartLock | null>(null);
  const [accessCodes, setAccessCodes] = useState<AccessCode[]>([]);
  const [loading, setLoading] = useState(true);
  const [showAddDialog, setShowAddDialog] = useState(false);
  const [showCodeDialog, setShowCodeDialog] = useState(false);
  const [revealedCodes, setRevealedCodes] = useState<Set<number>>(new Set());
  const [propertyTitle, setPropertyTitle] = useState<string>('');
  
  // Add Lock Form
  const [lockForm, setLockForm] = useState({
    name: '',
    provider: 'august',
    location: '',
    auto_generate_codes: true,
  });

  // Add Code Form
  const [codeForm, setCodeForm] = useState({
    type: 'temporary',
    valid_from: '',
    valid_until: '',
    max_uses: '',
  });

  const loadPropertyTitle = useCallback(async () => {
    try {
      const resp = await apiClient.get(API_ENDPOINTS.properties.show(propertyId));
      const data = (resp as any).data?.data || (resp as any).data;
      if (data && typeof data.title === 'string') {
        setPropertyTitle(data.title);
      }
    } catch {}
  }, [propertyId]);

  const loadLocks = useCallback(async () => {
    try {
      setLoading(true);
      const data = await smartLocksService.list(propertyId);
      setLocks(data.data || []);
    } catch (error: any) {
      notify.error({ title: 'Error', description: error.response?.data?.message || 'Failed to load smart locks' });
    } finally {
      setLoading(false);
    }
  }, [propertyId]);

  useEffect(() => {
    loadLocks();
    loadPropertyTitle();
  }, [propertyId, loadLocks, loadPropertyTitle]);

  const loadAccessCodes = async (lockId: number) => {
    try {
      const data = await smartLocksService.accessCodes.list(lockId);
      setAccessCodes(data.data || []);
    } catch (error: any) {
      notify.error({ title: 'Error', description: error.response?.data?.message || 'Failed to load access codes' });
    }
  };

  const handleAddLock = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await smartLocksService.create(propertyId, lockForm);
      notify.success({ title: 'Success', description: 'Smart lock added successfully' });
      setShowAddDialog(false);
      loadLocks();
      setLockForm({
        name: '',
        provider: 'august',
        location: '',
        auto_generate_codes: true,
      });
    } catch (error: any) {
      notify.error({ title: 'Error', description: error.response?.data?.message || 'Failed to add smart lock' });
    }
  };

  const handleDeleteLock = async (lockId: number) => {
    if (!confirm('Are you sure you want to delete this smart lock?')) return;
    
    try {
      await smartLocksService.delete(propertyId, lockId);
      notify.success({ title: 'Success', description: 'Smart lock deleted successfully' });
      loadLocks();
    } catch (error: any) {
      notify.error({ title: 'Error', description: error.response?.data?.message || 'Failed to delete smart lock' });
    }
  };

  const handleSyncLock = async (lockId: number) => {
    try {
      await smartLocksService.sync(propertyId, lockId);
      notify.success({ title: 'Success', description: 'Smart lock synced successfully' });
      loadLocks();
    } catch (error: any) {
      notify.error({ title: 'Error', description: error.response?.data?.message || 'Failed to sync smart lock' });
    }
  };

  const handleAddCode = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedLock) return;

    try {
      await smartLocksService.accessCodes.create(selectedLock.id, codeForm);
      notify.success({ title: 'Success', description: 'Access code created successfully' });
      setShowCodeDialog(false);
      loadAccessCodes(selectedLock.id);
      setCodeForm({
        type: 'temporary',
        valid_from: '',
        valid_until: '',
        max_uses: '',
      });
    } catch (error: any) {
      notify.error({ title: 'Error', description: error.response?.data?.message || 'Failed to create access code' });
    }
  };

  const handleDeleteCode = async (lockId: number, codeId: number) => {
    if (!confirm('Are you sure you want to delete this access code?')) return;
    
    try {
      await smartLocksService.accessCodes.delete(lockId, codeId);
      notify.success({ title: 'Success', description: 'Access code deleted successfully' });
      loadAccessCodes(lockId);
    } catch (error: any) {
      notify.error({ title: 'Error', description: error.response?.data?.message || 'Failed to delete access code' });
    }
  };

  const toggleCodeVisibility = (codeId: number) => {
    setRevealedCodes(prev => {
      const newSet = new Set(prev);
      if (newSet.has(codeId)) {
        newSet.delete(codeId);
      } else {
        newSet.add(codeId);
      }
      return newSet;
    });
  };

  const copyCode = (code: string) => {
    navigator.clipboard.writeText(code);
    notify.success({ title: tNotify('copiedTitle'), description: tNotify('copiedCode') });
  };

  const getBatteryColor = (level: number | null) => {
    if (!level) return 'text-muted-foreground';
    if (level > 50) return 'text-green-600';
    if (level > 20) return 'text-yellow-600';
    return 'text-red-600';
  };

  return (
    <MainLayout>
      <div className="container mx-auto p-6 max-w-7xl space-y-6">
  <Breadcrumbs items={breadcrumbSets.propertySmartLocks(String(propertyId), propertyTitle || 'Property')} />
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
              <Lock className="h-8 w-8" />
              Smart Locks & Access Codes
            </h1>
            <p className="text-muted-foreground mt-2">
              Manage smart locks and generate access codes for self check-in
            </p>
          </div>
          
          <Dialog open={showAddDialog} onOpenChange={setShowAddDialog}>
            <DialogTrigger asChild>
              <Button>
                <Plus className="mr-2 h-4 w-4" />
                Add Smart Lock
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Add Smart Lock</DialogTitle>
                <DialogDescription>
                  Connect a smart lock to enable self check-in for guests
                </DialogDescription>
              </DialogHeader>
              <form onSubmit={handleAddLock} className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="lock-name">Lock Name</Label>
                  <Input
                    id="lock-name"
                    value={lockForm.name}
                    onChange={(e) => setLockForm({ ...lockForm, name: e.target.value })}
                    placeholder="Front Door Lock"
                    required
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="provider">Lock Provider</Label>
                  <Select
                    value={lockForm.provider}
                    onValueChange={(value) => setLockForm({ ...lockForm, provider: value })}
                  >
                    <SelectTrigger id="provider">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="august">August</SelectItem>
                      <SelectItem value="yale">Yale</SelectItem>
                      <SelectItem value="schlage">Schlage</SelectItem>
                      <SelectItem value="kwikset">Kwikset</SelectItem>
                      <SelectItem value="other">Other</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="location">Location</Label>
                  <Input
                    id="location"
                    value={lockForm.location}
                    onChange={(e) => setLockForm({ ...lockForm, location: e.target.value })}
                    placeholder="Main entrance, Garage, etc."
                    required
                  />
                </div>

                <div className="flex items-center justify-between">
                  <div className="space-y-0.5">
                    <Label>Auto-Generate Codes</Label>
                    <p className="text-sm text-muted-foreground">
                      Automatically create codes for new bookings
                    </p>
                  </div>
                  <Switch
                    checked={lockForm.auto_generate_codes}
                    onCheckedChange={(checked) =>
                      setLockForm({ ...lockForm, auto_generate_codes: checked })
                    }
                  />
                </div>

                <div className="flex gap-2 justify-end">
                  <Button type="button" variant="outline" onClick={() => setShowAddDialog(false)}>
                    Cancel
                  </Button>
                  <Button type="submit">Add Lock</Button>
                </div>
              </form>
            </DialogContent>
          </Dialog>
        </div>

        {/* Smart Locks List */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {locks.map((lock) => (
            <Card key={lock.id} className="relative">
              <CardHeader>
                <div className="flex items-start justify-between">
                  <div>
                    <CardTitle className="flex items-center gap-2">
                      <Lock className="h-5 w-5" />
                      {lock.name}
                    </CardTitle>
                    <CardDescription className="capitalize">
                      {lock.provider} • {lock.location}
                    </CardDescription>
                  </div>
                  <Badge variant={lock.status === 'active' ? 'default' : 'destructive'}>
                    {lock.status}
                  </Badge>
                </div>
              </CardHeader>
              <CardContent className="space-y-4">
                {lock.battery_level !== null && (
                  <div className="flex items-center justify-between text-sm">
                    <span className="text-muted-foreground">Battery</span>
                    <span className={`flex items-center gap-1 font-medium ${getBatteryColor(lock.battery_level)}`}>
                      <Battery className="h-4 w-4" />
                      {lock.battery_level}%
                    </span>
                  </div>
                )}

                <div className="flex items-center justify-between text-sm">
                  <span className="text-muted-foreground">Auto-Generate</span>
                  <span>
                    {lock.auto_generate_codes ? (
                      <CheckCircle className="h-4 w-4 text-green-600" />
                    ) : (
                      <XCircle className="h-4 w-4 text-red-600" />
                    )}
                  </span>
                </div>

                {lock.last_synced_at && (
                  <div className="text-xs text-muted-foreground">
                    Last synced: {new Date(lock.last_synced_at).toLocaleString()}
                  </div>
                )}

                {lock.error_message && (
                  <div className="text-xs text-destructive">
                    Error: {lock.error_message}
                  </div>
                )}

                <div className="flex gap-2">
                  <Button
                    variant="outline"
                    size="sm"
                    className="flex-1"
                    onClick={() => {
                      setSelectedLock(lock);
                      loadAccessCodes(lock.id);
                    }}
                  >
                    <Key className="mr-2 h-4 w-4" />
                    Codes
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => handleSyncLock(lock.id)}
                  >
                    <RefreshCw className="h-4 w-4" />
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => handleDeleteLock(lock.id)}
                  >
                    <Trash2 className="h-4 w-4" />
                  </Button>
                </div>
              </CardContent>
            </Card>
          ))}

          {locks.length === 0 && !loading && (
            <Card className="col-span-full">
              <CardContent className="flex flex-col items-center justify-center py-12">
                <Lock className="h-12 w-12 text-muted-foreground mb-4" />
                <h3 className="text-lg font-semibold mb-2">No Smart Locks Yet</h3>
                <p className="text-sm text-muted-foreground mb-4">
                  Add your first smart lock to enable self check-in
                </p>
                <Button onClick={() => setShowAddDialog(true)}>
                  <Plus className="mr-2 h-4 w-4" />
                  Add Smart Lock
                </Button>
              </CardContent>
            </Card>
          )}
        </div>

        {/* Access Codes Panel */}
        {selectedLock && (
          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <div>
                  <CardTitle>Access Codes - {selectedLock.name}</CardTitle>
                  <CardDescription>
                    Manage access codes for this smart lock
                  </CardDescription>
                </div>
                <Dialog open={showCodeDialog} onOpenChange={setShowCodeDialog}>
                  <DialogTrigger asChild>
                    <Button size="sm">
                      <Plus className="mr-2 h-4 w-4" />
                      Add Code
                    </Button>
                  </DialogTrigger>
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>Create Access Code</DialogTitle>
                      <DialogDescription>
                        Generate a new access code for this lock
                      </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleAddCode} className="space-y-4">
                      <div className="space-y-2">
                        <Label htmlFor="code-type">Code Type</Label>
                        <Select
                          value={codeForm.type}
                          onValueChange={(value: any) => setCodeForm({ ...codeForm, type: value })}
                        >
                          <SelectTrigger id="code-type">
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="temporary">Temporary</SelectItem>
                            <SelectItem value="permanent">Permanent</SelectItem>
                            <SelectItem value="one-time">One-Time</SelectItem>
                          </SelectContent>
                        </Select>
                      </div>

                      <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                          <Label htmlFor="valid-from">Valid From</Label>
                          <Input
                            id="valid-from"
                            type="datetime-local"
                            value={codeForm.valid_from}
                            onChange={(e) => setCodeForm({ ...codeForm, valid_from: e.target.value })}
                            required
                          />
                        </div>

                        <div className="space-y-2">
                          <Label htmlFor="valid-until">Valid Until</Label>
                          <Input
                            id="valid-until"
                            type="datetime-local"
                            value={codeForm.valid_until}
                            onChange={(e) => setCodeForm({ ...codeForm, valid_until: e.target.value })}
                            required
                          />
                        </div>
                      </div>

                      {codeForm.type === 'one-time' && (
                        <div className="space-y-2">
                          <Label htmlFor="max-uses">Max Uses</Label>
                          <Input
                            id="max-uses"
                            type="number"
                            value={codeForm.max_uses}
                            onChange={(e) => setCodeForm({ ...codeForm, max_uses: e.target.value })}
                            placeholder="1"
                          />
                        </div>
                      )}

                      <div className="flex gap-2 justify-end">
                        <Button type="button" variant="outline" onClick={() => setShowCodeDialog(false)}>
                          Cancel
                        </Button>
                        <Button type="submit">Create Code</Button>
                      </div>
                    </form>
                  </DialogContent>
                </Dialog>
              </div>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Code</TableHead>
                    <TableHead>Type</TableHead>
                    <TableHead>Valid Period</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Uses</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {accessCodes.map((code) => (
                    <TableRow key={code.id}>
                      <TableCell className="font-mono">
                        <div className="flex items-center gap-2">
                          {revealedCodes.has(code.id) ? code.code : '••••••'}
                          <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => toggleCodeVisibility(code.id)}
                          >
                            {revealedCodes.has(code.id) ? (
                              <EyeOff className="h-4 w-4" />
                            ) : (
                              <Eye className="h-4 w-4" />
                            )}
                          </Button>
                          <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => copyCode(code.code)}
                          >
                            <Copy className="h-4 w-4" />
                          </Button>
                        </div>
                      </TableCell>
                      <TableCell className="capitalize">{code.type}</TableCell>
                      <TableCell className="text-sm">
                        {new Date(code.valid_from).toLocaleDateString()} -{' '}
                        {new Date(code.valid_until).toLocaleDateString()}
                      </TableCell>
                      <TableCell>
                        <Badge
                          variant={
                            code.status === 'active'
                              ? 'default'
                              : code.status === 'expired'
                              ? 'secondary'
                              : 'destructive'
                          }
                        >
                          {code.status}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        {code.uses_count}
                        {code.max_uses ? ` / ${code.max_uses}` : ''}
                      </TableCell>
                      <TableCell>
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => handleDeleteCode(selectedLock.id, code.id)}
                        >
                          <Trash2 className="h-4 w-4" />
                        </Button>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>

              {accessCodes.length === 0 && (
                <div className="text-center py-8 text-muted-foreground">
                  No access codes yet. Create one to get started.
                </div>
              )}
            </CardContent>
          </Card>
        )}
      </div>
    </MainLayout>
  );
}
