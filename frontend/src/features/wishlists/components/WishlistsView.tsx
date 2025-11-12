"use client";

import { useEffect, useMemo, useState } from 'react';
import { useTranslations } from '@/lib/i18n-temp';
import { type Wishlist } from '@/lib/schemas';
import { usePrivateChannel } from '@/hooks/use-echo';
import {
  addPropertyToWishlist,
  createWishlist,
  deleteWishlist,
  listWishlists,
  renameWishlist,
  removePropertyFromWishlist,
} from '../api';

type Status = 'idle' | 'loading' | 'error' | 'success';

export default function WishlistsView() {
  const t = useTranslations('wishlists');
  const [status, setStatus] = useState<Status>('idle');
  const [error, setError] = useState<string | null>(null);
  const [items, setItems] = useState<Wishlist[]>([]);
  const [newName, setNewName] = useState('');
  const [selectedId, setSelectedId] = useState<number | null>(null);
  const [renameValue, setRenameValue] = useState('');
  const [propertyInput, setPropertyInput] = useState('');

  // Get user ID from localStorage for private channel
  const userId = typeof window !== 'undefined' 
    ? JSON.parse(localStorage.getItem('user') || '{}')?.id 
    : null;
  const authToken = typeof window !== 'undefined'
    ? localStorage.getItem('auth_token') || ''
    : '';

  // Subscribe to private user channel for wishlist updates
  const channel = usePrivateChannel(
    userId ? `user.${userId}` : '',
    authToken,
    !!userId
  );

  // Listen for realtime wishlist events
  useEffect(() => {
    if (!channel) return;

    const handleWishlistCreated = (data: { wishlist: Wishlist }) => {
      setItems((prev) => {
        // Avoid duplicates
        if (prev.some(w => w.id === data.wishlist.id)) return prev;
        return [data.wishlist, ...prev];
      });
    };

    const handleWishlistUpdated = (data: { wishlist: Wishlist }) => {
      setItems((prev) => prev.map(w => w.id === data.wishlist.id ? data.wishlist : w));
    };

    const handleWishlistDeleted = (data: { wishlistId: number }) => {
      setItems((prev) => prev.filter(w => w.id !== data.wishlistId));
    };

    channel.listen('wishlist.created', handleWishlistCreated);
    channel.listen('wishlist.updated', handleWishlistUpdated);
    channel.listen('wishlist.deleted', handleWishlistDeleted);

    return () => {
      channel.stopListening('wishlist.created');
      channel.stopListening('wishlist.updated');
      channel.stopListening('wishlist.deleted');
    };
  }, [channel]);

  // Load on mount
  useEffect(() => {
    let mounted = true;
    (async () => {
      try {
        setStatus('loading');
        const data = await listWishlists();
        if (mounted) {
          setItems(data);
          setStatus('success');
        }
      } catch (e: any) {
        setError(e?.message ?? 'Failed to load wishlists');
        setStatus('error');
      }
    })();
    return () => {
      mounted = false;
    };
  }, []);

  async function handleCreate(e: React.FormEvent) {
    e.preventDefault();
    if (!newName.trim()) return;
    const optimistic: Wishlist = { id: Math.floor(Math.random() * 1e9), name: newName.trim(), items: [] };
    setItems((prev) => [optimistic, ...prev]);
    setNewName('');
    try {
      const created = await createWishlist(optimistic.name);
      setItems((prev) => [created, ...prev.filter((w) => w.id !== optimistic.id)]);
    } catch (e) {
      setItems((prev) => prev.filter((w) => w.id !== optimistic.id));
    }
  }

  async function handleRename(id: number) {
    const old = items.find((w) => w.id === id);
    if (!old) return;
    const val = renameValue.trim();
    if (!val) return;
    setItems((prev) => prev.map((w) => (w.id === id ? { ...w, name: val } : w)));
    try {
      const updated = await renameWishlist(id, val);
      setItems((prev) => prev.map((w) => (w.id === id ? updated : w)));
      setSelectedId(null);
      setRenameValue('');
    } catch (e) {
      // revert
      setItems((prev) => prev.map((w) => (w.id === id ? (old as Wishlist) : w)));
    }
  }

  async function handleDelete(id: number) {
    const old = items;
    setItems((prev) => prev.filter((w) => w.id !== id));
    try {
      await deleteWishlist(id);
    } catch (e) {
      setItems(old); // revert
    }
  }

  async function handleAddProperty(id: number) {
    const pid = Number(propertyInput);
    if (!pid) return;
    const prev = items;
    // optimistic add with fake item id
    const tempId = Math.floor(Math.random() * 1e9);
    setItems((cur) =>
      cur.map((w) => (w.id === id ? { ...w, items: [...w.items, { id: tempId, propertyId: pid }] } : w))
    );
    setPropertyInput('');
    try {
      const updated = await addPropertyToWishlist(id, pid);
      setItems((cur) => cur.map((w) => (w.id === id ? updated : w)));
    } catch (e) {
      setItems(prev); // revert
    }
  }

  async function handleRemoveItem(wishlistId: number, itemId: number) {
    const prev = items;
    setItems((cur) => cur.map((w) => (w.id === wishlistId ? { ...w, items: w.items.filter((it) => it.id !== itemId) } : w)));
    try {
      const updated = await removePropertyFromWishlist(wishlistId, itemId);
      setItems((cur) => cur.map((w) => (w.id === wishlistId ? updated : w)));
    } catch (e) {
      setItems(prev);
    }
  }

  return (
    <section className="space-y-6">
      <form onSubmit={handleCreate} className="flex gap-2">
        <input
          aria-label={t('create.name_placeholder', { default: 'Wishlist name' })}
          className="input input-bordered flex-1"
          value={newName}
          onChange={(e) => setNewName(e.target.value)}
          placeholder={t('create.placeholder', { default: 'e.g. Summer Trips' })}
        />
        <button className="btn btn-primary" type="submit">{t('create.action', { default: 'Create' })}</button>
      </form>

      {status === 'error' && (
        <div role="alert" className="alert alert-error">
          {error || t('errors.load_failed', { default: 'Failed to load' })}
        </div>
      )}

      <ul className="space-y-4">
        {items.map((w) => (
          <li key={w.id} className="rounded border p-4">
            <div className="flex items-center justify-between gap-3">
              <div className="flex items-center gap-3">
                <h2 className="text-lg font-medium">{w.name}</h2>
                <span className="badge" aria-label={t('items_count', { count: w.items.length })}>{w.items.length}</span>
              </div>
              <div className="flex items-center gap-2">
                {selectedId === w.id ? (
                  <>
                    <input
                      className="input input-bordered"
                      value={renameValue}
                      onChange={(e) => setRenameValue(e.target.value)}
                      placeholder={t('rename.placeholder', { default: 'New name' })}
                    />
                    <button className="btn btn-success" onClick={() => handleRename(w.id)}>{t('rename.save', { default: 'Save' })}</button>
                    <button className="btn" onClick={() => { setSelectedId(null); setRenameValue(''); }}>
                      {t('rename.cancel', { default: 'Cancel' })}
                    </button>
                  </>
                ) : (
                  <>
                    <button className="btn" onClick={() => { setSelectedId(w.id); setRenameValue(w.name); }}>
                      {t('rename.action', { default: 'Rename' })}
                    </button>
                    <button className="btn btn-outline btn-error" onClick={() => handleDelete(w.id)}>
                      {t('delete.action', { default: 'Delete' })}
                    </button>
                  </>
                )}
              </div>
            </div>

            <div className="mt-3 flex items-end gap-2">
              <input
                className="input input-bordered"
                value={propertyInput}
                onChange={(e) => setPropertyInput(e.target.value)}
                placeholder={t('add.property_placeholder', { default: 'Property ID' })}
                inputMode="numeric"
              />
              <button className="btn" onClick={() => handleAddProperty(w.id)}>
                {t('add.action', { default: 'Add property' })}
              </button>
            </div>

            {w.items.length > 0 && (
              <div className="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                {w.items.map((it) => (
                  <div key={it.id} className="rounded border p-3 flex items-center justify-between">
                    <div>
                      <div className="font-medium">#{it.propertyId}</div>
                      {it.title && <div className="text-sm text-muted-foreground">{it.title}</div>}
                    </div>
                    <button className="btn btn-sm btn-outline" onClick={() => handleRemoveItem(w.id, it.id)}>
                      {t('remove.action', { default: 'Remove' })}
                    </button>
                  </div>
                ))}
              </div>
            )}
          </li>
        ))}
      </ul>
    </section>
  );
}
