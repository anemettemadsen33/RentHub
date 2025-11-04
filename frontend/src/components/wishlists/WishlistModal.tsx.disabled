'use client';

import { useState, useEffect } from 'react';
import { Plus, Check } from 'lucide-react';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useToast } from '@/hooks/use-toast';
import {
  getWishlists,
  createWishlist,
  addPropertyToWishlist,
  removePropertyFromWishlist,
  checkPropertyInWishlist,
  type Wishlist,
} from '@/lib/api/wishlists';

interface WishlistModalProps {
  propertyId: number;
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

export default function WishlistModal({
  propertyId,
  open,
  onOpenChange,
}: WishlistModalProps) {
  const [wishlists, setWishlists] = useState<Wishlist[]>([]);
  const [selectedWishlists, setSelectedWishlists] = useState<number[]>([]);
  const [isCreating, setIsCreating] = useState(false);
  const [newWishlistName, setNewWishlistName] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const { toast } = useToast();

  useEffect(() => {
    if (open) {
      loadWishlists();
    }
  }, [open, propertyId]);

  const loadWishlists = async () => {
    try {
      setIsLoading(true);
      const [wishlistsData, propertyStatus] = await Promise.all([
        getWishlists(),
        checkPropertyInWishlist(propertyId),
      ]);

      setWishlists(wishlistsData);
      setSelectedWishlists(
        propertyStatus.wishlists.map((w: Wishlist) => w.id)
      );
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to load wishlists',
        variant: 'destructive',
      });
    } finally {
      setIsLoading(false);
    }
  };

  const handleCreateWishlist = async () => {
    if (!newWishlistName.trim()) return;

    try {
      const wishlist = await createWishlist({
        name: newWishlistName,
        is_public: false,
      });

      setWishlists([...wishlists, wishlist]);
      setNewWishlistName('');
      setIsCreating(false);

      toast({
        title: 'Wishlist Created',
        description: `"${wishlist.name}" has been created`,
      });

      // Auto-select the new wishlist
      await handleToggleWishlist(wishlist.id);
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to create wishlist',
        variant: 'destructive',
      });
    }
  };

  const handleToggleWishlist = async (wishlistId: number) => {
    const isSelected = selectedWishlists.includes(wishlistId);

    try {
      if (isSelected) {
        const wishlist = wishlists.find((w) => w.id === wishlistId);
        const item = wishlist?.items?.find(
          (item) => item.property_id === propertyId
        );
        if (item) {
          await removePropertyFromWishlist(wishlistId, item.id);
          setSelectedWishlists(selectedWishlists.filter((id) => id !== wishlistId));
        }
      } else {
        await addPropertyToWishlist(wishlistId, { property_id: propertyId });
        setSelectedWishlists([...selectedWishlists, wishlistId]);
      }
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to update wishlist',
        variant: 'destructive',
      });
    }
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Save to Wishlist</DialogTitle>
          <DialogDescription>
            Choose which wishlists to save this property to
          </DialogDescription>
        </DialogHeader>

        <div className="space-y-4">
          {isLoading ? (
            <div className="py-8 text-center text-sm text-muted-foreground">
              Loading wishlists...
            </div>
          ) : (
            <>
              <div className="space-y-2">
                {wishlists.map((wishlist) => (
                  <button
                    key={wishlist.id}
                    onClick={() => handleToggleWishlist(wishlist.id)}
                    className="flex w-full items-center justify-between rounded-lg border p-3 hover:bg-accent"
                  >
                    <div className="text-left">
                      <p className="font-medium">{wishlist.name}</p>
                      {wishlist.items_count !== undefined && (
                        <p className="text-sm text-muted-foreground">
                          {wishlist.items_count} properties
                        </p>
                      )}
                    </div>
                    {selectedWishlists.includes(wishlist.id) && (
                      <Check className="h-5 w-5 text-primary" />
                    )}
                  </button>
                ))}
              </div>

              {isCreating ? (
                <div className="flex gap-2">
                  <Input
                    placeholder="Enter wishlist name"
                    value={newWishlistName}
                    onChange={(e) => setNewWishlistName(e.target.value)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter') handleCreateWishlist();
                      if (e.key === 'Escape') setIsCreating(false);
                    }}
                    autoFocus
                  />
                  <Button onClick={handleCreateWishlist} disabled={!newWishlistName.trim()}>
                    Create
                  </Button>
                  <Button variant="outline" onClick={() => setIsCreating(false)}>
                    Cancel
                  </Button>
                </div>
              ) : (
                <Button
                  variant="outline"
                  className="w-full"
                  onClick={() => setIsCreating(true)}
                >
                  <Plus className="mr-2 h-4 w-4" />
                  Create New Wishlist
                </Button>
              )}
            </>
          )}
        </div>
      </DialogContent>
    </Dialog>
  );
}
