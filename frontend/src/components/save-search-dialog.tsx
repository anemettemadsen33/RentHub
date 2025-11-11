"use client";

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { useToast } from '@/hooks/use-toast';
import apiClient from '@/lib/api-client';
import { FilterOptions } from '@/components/filter-panel';
import { Bell, BellOff } from 'lucide-react';

interface SaveSearchDialogProps {
  filters: FilterOptions;
  trigger?: React.ReactNode;
}

export function SaveSearchDialog({ filters, trigger }: SaveSearchDialogProps) {
  const router = useRouter();
  const { toast } = useToast();
  const [open, setOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const [name, setName] = useState('');
  const [emailNotifications, setEmailNotifications] = useState(true);
  const [frequency, setFrequency] = useState<'instant' | 'daily' | 'weekly'>('instant');

  const handleSave = async () => {
    if (!name.trim()) {
      toast({
        title: 'Error',
        description: 'Please enter a name for your saved search',
        variant: 'destructive',
      });
      return;
    }

    setLoading(true);
    try {
      await apiClient.post('/saved-searches', {
        name: name.trim(),
        criteria: filters,
        email_notifications: emailNotifications,
        frequency,
      });

      toast({
        title: 'Success',
        description: 'Search saved successfully! You will receive notifications for new matches.',
      });

      setOpen(false);
      setName('');
      router.refresh();
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to save search',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        {trigger || (
          <Button variant="outline" size="sm">
            <Bell className="mr-2 h-4 w-4" />
            Save Search
          </Button>
        )}
      </DialogTrigger>
      <DialogContent className="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Save This Search</DialogTitle>
          <DialogDescription>
            Get notified when new properties match your criteria
          </DialogDescription>
        </DialogHeader>
        
        <div className="grid gap-4 py-4">
          <div className="grid gap-2">
            <Label htmlFor="name">Search Name</Label>
            <Input
              id="name"
              placeholder="e.g., 2-bed apartments in London"
              value={name}
              onChange={(e) => setName(e.target.value)}
            />
          </div>

          <div className="flex items-center justify-between">
            <Label htmlFor="notifications" className="cursor-pointer">
              Email Notifications
            </Label>
            <Switch
              id="notifications"
              checked={emailNotifications}
              onCheckedChange={setEmailNotifications}
            />
          </div>

          {emailNotifications && (
            <div className="grid gap-2">
              <Label htmlFor="frequency">Notification Frequency</Label>
              <Select value={frequency} onValueChange={(v: any) => setFrequency(v)}>
                <SelectTrigger id="frequency">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="instant">Instant (as they appear)</SelectItem>
                  <SelectItem value="daily">Daily Digest</SelectItem>
                  <SelectItem value="weekly">Weekly Summary</SelectItem>
                </SelectContent>
              </Select>
            </div>
          )}

          <div className="rounded-lg bg-muted p-3 text-sm">
            <p className="font-medium mb-1">Active Filters:</p>
            <ul className="space-y-1 text-muted-foreground">
              {filters.priceRange && (
                <li>Price: ${filters.priceRange[0]} - ${filters.priceRange[1]}</li>
              )}
              {filters.bedrooms && <li>Bedrooms: {filters.bedrooms}+</li>}
              {filters.bathrooms && <li>Bathrooms: {filters.bathrooms}+</li>}
              {filters.guests && <li>Guests: {filters.guests}+</li>}
              {filters.propertyType && filters.propertyType.length > 0 && (
                <li>Type: {filters.propertyType.join(', ')}</li>
              )}
              {filters.amenities && filters.amenities.length > 0 && (
                <li>Amenities: {filters.amenities.length} selected</li>
              )}
            </ul>
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" onClick={() => setOpen(false)}>
            Cancel
          </Button>
          <Button onClick={handleSave} disabled={loading}>
            {loading ? 'Saving...' : 'Save Search'}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
