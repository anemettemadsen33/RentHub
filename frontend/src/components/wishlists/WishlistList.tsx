'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { Heart, Share2, Trash2, Edit, Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useToast } from '@/hooks/use-toast';
import {
  getWishlists,
  deleteWishlist,
  type Wishlist,
} from '@/lib/api/wishlists';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/dialog';

export default function WishlistList() {
  const [wishlists, setWishlists] = useState<Wishlist[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [deleteId, setDeleteId] = useState<number | null>(null);
  const { toast } = useToast();

  useEffect(() => {
    loadWishlists();
  }, []);

  const loadWishlists = async () => {
    try {
      setIsLoading(true);
      const data = await getWishlists();
      setWishlists(data);
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

  const handleDelete = async (id: number) => {
    try {
      await deleteWishlist(id);
      setWishlists(wishlists.filter((w) => w.id !== id));
      toast({
        title: 'Wishlist Deleted',
        description: 'The wishlist has been removed',
      });
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to delete wishlist',
        variant: 'destructive',
      });
    } finally {
      setDeleteId(null);
    }
  };

  const handleShare = (wishlist: Wishlist) => {
    if (!wishlist.is_public || !wishlist.share_token) {
      toast({
        title: 'Cannot Share',
        description: 'This wishlist is not public',
        variant: 'destructive',
      });
      return;
    }

    const shareUrl = `${window.location.origin}/wishlists/shared/${wishlist.share_token}`;
    navigator.clipboard.writeText(shareUrl);
    
    toast({
      title: 'Link Copied',
      description: 'Wishlist link copied to clipboard',
    });
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-12">
        <div className="text-center">
          <div className="mb-4 inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-r-transparent"></div>
          <p className="text-sm text-muted-foreground">Loading wishlists...</p>
        </div>
      </div>
    );
  }

  if (wishlists.length === 0) {
    return (
      <div className="flex flex-col items-center justify-center py-12">
        <Heart className="mb-4 h-12 w-12 text-muted-foreground" />
        <h3 className="mb-2 text-lg font-semibold">No Wishlists Yet</h3>
        <p className="mb-4 text-sm text-muted-foreground">
          Start saving your favorite properties to wishlists
        </p>
        <Link href="/properties">
          <Button>
            <Plus className="mr-2 h-4 w-4" />
            Browse Properties
          </Button>
        </Link>
      </div>
    );
  }

  return (
    <>
      <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        {wishlists.map((wishlist) => (
          <Card key={wishlist.id} className="overflow-hidden">
            <CardHeader>
              <CardTitle className="flex items-center justify-between">
                <Link
                  href={`/wishlists/${wishlist.id}`}
                  className="hover:underline"
                >
                  {wishlist.name}
                </Link>
                <div className="flex gap-1">
                  {wishlist.is_public && (
                    <Button
                      variant="ghost"
                      size="icon"
                      onClick={() => handleShare(wishlist)}
                    >
                      <Share2 className="h-4 w-4" />
                    </Button>
                  )}
                  <Link href={`/wishlists/${wishlist.id}/edit`}>
                    <Button variant="ghost" size="icon">
                      <Edit className="h-4 w-4" />
                    </Button>
                  </Link>
                  <Button
                    variant="ghost"
                    size="icon"
                    onClick={() => setDeleteId(wishlist.id)}
                  >
                    <Trash2 className="h-4 w-4 text-destructive" />
                  </Button>
                </div>
              </CardTitle>
            </CardHeader>
            <CardContent>
              <p className="mb-2 text-sm text-muted-foreground">
                {wishlist.description || 'No description'}
              </p>
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium">
                  {wishlist.items_count || 0} properties
                </span>
                {wishlist.is_public && (
                  <span className="rounded-full bg-primary/10 px-2 py-1 text-xs text-primary">
                    Public
                  </span>
                )}
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      <AlertDialog open={deleteId !== null} onOpenChange={() => setDeleteId(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Delete Wishlist</AlertDialogTitle>
            <AlertDialogDescription>
              Are you sure you want to delete this wishlist? This action cannot be undone.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Cancel</AlertDialogCancel>
            <AlertDialogAction
              onClick={() => deleteId && handleDelete(deleteId)}
              className="bg-destructive hover:bg-destructive/90"
            >
              Delete
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </>
  );
}
