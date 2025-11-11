import { useState, useCallback } from 'react';

/**
 * Optimistic update hook for instant UI feedback
 * Updates UI immediately, then syncs with server
 */
export function useOptimistic<T>(
  initialData: T,
  updateFn: (data: T) => Promise<T>
) {
  const [data, setData] = useState<T>(initialData);
  const [isOptimistic, setIsOptimistic] = useState(false);
  const [error, setError] = useState<Error | null>(null);

  const update = useCallback(
    async (optimisticData: T) => {
      // Store current state for rollback
      const previousData = data;
      
      // Immediately update UI (optimistic)
      setData(optimisticData);
      setIsOptimistic(true);
      setError(null);

      try {
        // Sync with server
        const serverData = await updateFn(optimisticData);
        setData(serverData);
        setIsOptimistic(false);
        return serverData;
      } catch (err) {
        // Rollback on error
        setData(previousData);
        setError(err as Error);
        setIsOptimistic(false);
        throw err;
      }
    },
    [data, updateFn]
  );

  return {
    data,
    update,
    isOptimistic,
    error,
  };
}

/**
 * Optimistic list operations (add, remove, update)
 */
export function useOptimisticList<T extends { id: number | string }>(
  initialList: T[]
) {
  const [list, setList] = useState<T[]>(initialList);
  const [optimisticIds, setOptimisticIds] = useState<Set<number | string>>(new Set());

  const addOptimistic = useCallback(
    async (item: T, serverFn: () => Promise<T>) => {
      // Add to list immediately
      setList(prev => [...prev, item]);
      setOptimisticIds(prev => new Set(prev).add(item.id));

      try {
        const serverItem = await serverFn();
        // Replace optimistic item with server item
        setList(prev => prev.map(i => i.id === item.id ? serverItem : i));
        setOptimisticIds(prev => {
          const newSet = new Set(prev);
          newSet.delete(item.id);
          return newSet;
        });
        return serverItem;
      } catch (error) {
        // Remove on error
        setList(prev => prev.filter(i => i.id !== item.id));
        setOptimisticIds(prev => {
          const newSet = new Set(prev);
          newSet.delete(item.id);
          return newSet;
        });
        throw error;
      }
    },
    []
  );

  const removeOptimistic = useCallback(
    async (id: number | string, serverFn: () => Promise<void>) => {
      // Store for rollback
      const removedItem = list.find(item => item.id === id);
      if (!removedItem) return;

      // Remove immediately
      setList(prev => prev.filter(item => item.id !== id));
      setOptimisticIds(prev => new Set(prev).add(id));

      try {
        await serverFn();
        setOptimisticIds(prev => {
          const newSet = new Set(prev);
          newSet.delete(id);
          return newSet;
        });
      } catch (error) {
        // Rollback on error
        setList(prev => [...prev, removedItem]);
        setOptimisticIds(prev => {
          const newSet = new Set(prev);
          newSet.delete(id);
          return newSet;
        });
        throw error;
      }
    },
    [list]
  );

  const updateOptimistic = useCallback(
    async (id: number | string, updates: Partial<T>, serverFn: () => Promise<T>) => {
      // Store for rollback
      const originalItem = list.find(item => item.id === id);
      if (!originalItem) return;

      // Update immediately
      setList(prev => prev.map(item => 
        item.id === id ? { ...item, ...updates } : item
      ));
      setOptimisticIds(prev => new Set(prev).add(id));

      try {
        const serverItem = await serverFn();
        setList(prev => prev.map(item => item.id === id ? serverItem : item));
        setOptimisticIds(prev => {
          const newSet = new Set(prev);
          newSet.delete(id);
          return newSet;
        });
        return serverItem;
      } catch (error) {
        // Rollback on error
        setList(prev => prev.map(item => item.id === id ? originalItem : item));
        setOptimisticIds(prev => {
          const newSet = new Set(prev);
          newSet.delete(id);
          return newSet;
        });
        throw error;
      }
    },
    [list]
  );

  return {
    list,
    addOptimistic,
    removeOptimistic,
    updateOptimistic,
    optimisticIds,
    isOptimistic: (id: number | string) => optimisticIds.has(id),
  };
}
