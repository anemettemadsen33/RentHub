"use client";
import { useCallback } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import {
  getComparisonProperties,
  addPropertyToComparison,
  removePropertyFromComparison,
  clearComparison,
} from '@/lib/api-client';
import { mockProperties } from '@/lib/mock-data';
import type { ComparisonProperty } from '@/lib/schemas';

const isStub = process.env.NEXT_PUBLIC_E2E === 'true';

function readLocalIds(): number[] {
  try {
    const raw = localStorage.getItem('comparison');
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}

function writeLocalIds(ids: number[]) {
  localStorage.setItem('comparison', JSON.stringify(ids));
  window.dispatchEvent(new StorageEvent('storage', { key: 'comparison', newValue: JSON.stringify(ids) }));
}

async function fetchStubList(): Promise<ComparisonProperty[]> {
  const ids = readLocalIds();
  return mockProperties
    .filter((p) => ids.includes(p.id))
    .map((p) => ({ id: p.id, title: p.title, pricePerNight: p.price_per_night ?? p.price, location: p.city } as ComparisonProperty));
}

export function useComparison() {
  const qc = useQueryClient();

  const listQuery = useQuery({
    queryKey: ['comparison', 'list'],
    queryFn: () => (isStub ? fetchStubList() : getComparisonProperties()),
    staleTime: 30_000,
  });

  const add = useMutation({
    mutationFn: async (propertyId: number) => {
      if (isStub) {
        const ids = Array.from(new Set([...readLocalIds(), propertyId])).slice(0, 4);
        writeLocalIds(ids);
        return;
      }
      await addPropertyToComparison(propertyId);
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['comparison', 'list'] }),
  });

  const remove = useMutation({
    mutationFn: async (propertyId: number) => {
      if (isStub) {
        const ids = readLocalIds().filter((id) => id !== propertyId);
        writeLocalIds(ids);
        return;
      }
      await removePropertyFromComparison(propertyId);
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['comparison', 'list'] }),
  });

  const clear = useMutation({
    mutationFn: async () => {
      if (isStub) {
        writeLocalIds([]);
        return;
      }
      await clearComparison();
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['comparison', 'list'] }),
  });

  const addToComparison = useCallback((id: number) => add.mutate(id), [add]);
  const removeFromComparison = useCallback((id: number) => remove.mutate(id), [remove]);
  const clearAll = useCallback(() => clear.mutate(), [clear]);

  return {
    ...listQuery,
    addToComparison,
    removeFromComparison,
    clearAll,
  };
}
