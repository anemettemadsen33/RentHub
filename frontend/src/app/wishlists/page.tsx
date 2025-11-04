'use client';

import { useState } from 'react';
import { Plus } from 'lucide-react';
import Layout from '@/components/Layout';
import WishlistList from '@/components/wishlists/WishlistList';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { useToast } from '@/hooks/use-toast';
import { createWishlist } from '@/lib/api/wishlists';
import { Checkbox } from '@/components/ui/checkbox';

export default function WishlistsPage() {
  const [open, setOpen] = useState(false);
  const [name, setName] = useState('');
  const [description, setDescription] = useState('');
  const [isPublic, setIsPublic] = useState(false);
  const [isCreating, setIsCreating] = useState(false);
  const [refreshKey, setRefreshKey] = useState(0);
  const { toast } = useToast();

  const handleCreate = async () => {
    if (!name.trim()) {
      toast({
        title: 'Error',
        description: 'Please enter a wishlist name',
        variant: 'destructive',
      });
      return;
    }

    try {
      setIsCreating(true);
      await createWishlist({
        name,
        description: description || undefined,
        is_public: isPublic,
      });

      toast({
        title: 'Wishlist Created',
        description: `"${name}" has been created successfully`,
      });

      setName('');
      setDescription('');
      setIsPublic(false);
      setOpen(false);
      setRefreshKey((prev) => prev + 1);
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to create wishlist',
        variant: 'destructive',
      });
    } finally {
      setIsCreating(false);
    }
  };

  return (
    <Layout>
      <div className="container mx-auto px-4 py-8">
        <div className="mb-8 flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold">My Wishlists</h1>
            <p className="mt-2 text-muted-foreground">
              Organize and save your favorite properties
            </p>
          </div>
          <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
              <Button>
                <Plus className="mr-2 h-4 w-4" />
                Create Wishlist
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Create New Wishlist</DialogTitle>
                <DialogDescription>
                  Give your wishlist a name and optional description
                </DialogDescription>
              </DialogHeader>
              <div className="space-y-4">
                <div>
                  <label className="mb-2 block text-sm font-medium">Name</label>
                  <Input
                    placeholder="e.g., Summer Vacation, Business Trips"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                  />
                </div>
                <div>
                  <label className="mb-2 block text-sm font-medium">Description (optional)</label>
                  <Textarea
                    placeholder="Describe what this wishlist is for..."
                    value={description}
                    onChange={(e) => setDescription(e.target.value)}
                    rows={3}
                  />
                </div>
                <div className="flex items-center space-x-2">
                  <Checkbox
                    id="public"
                    checked={isPublic}
                    onCheckedChange={(checked) => setIsPublic(checked as boolean)}
                  />
                  <label
                    htmlFor="public"
                    className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                  >
                    Make this wishlist public (can be shared with others)
                  </label>
                </div>
                <div className="flex gap-2">
                  <Button
                    onClick={handleCreate}
                    disabled={isCreating}
                    className="flex-1"
                  >
                    {isCreating ? 'Creating...' : 'Create Wishlist'}
                  </Button>
                  <Button
                    variant="outline"
                    onClick={() => setOpen(false)}
                    disabled={isCreating}
                  >
                    Cancel
                  </Button>
                </div>
              </div>
            </DialogContent>
          </Dialog>
        </div>

        <WishlistList key={refreshKey} />
      </div>
    </Layout>
  );
}
