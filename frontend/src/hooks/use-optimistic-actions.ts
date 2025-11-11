import { useState, useCallback } from 'react';
import { useToast } from '@/hooks/use-toast';

/**
 * Generic optimistic update hook for instant UI feedback
 * Updates UI immediately, then syncs with server, with automatic rollback on error
 */
export function useOptimisticAction<T = void>(
  onSuccess?: (data?: T) => void,
  onError?: (error: Error) => void
) {
  const [isLoading, setIsLoading] = useState(false);
  const { toast } = useToast();

  const execute = useCallback(
    async <R = T>(
      optimisticUpdate: () => void,
      serverAction: () => Promise<R>,
      rollback: () => void,
      options?: {
        successMessage?: string;
        errorMessage?: string;
        showToast?: boolean;
      }
    ): Promise<R | undefined> => {
      const { successMessage, errorMessage, showToast = false } = options || {};

      // 1. Immediate UI update (optimistic)
      optimisticUpdate();
      setIsLoading(true);

      try {
        // 2. Sync with server
        const result = await serverAction();

        // 3. Success callback
        onSuccess?.(result as T);

        if (showToast && successMessage) {
          toast({
            title: 'Success',
            description: successMessage,
          });
        }

        return result;
      } catch (error) {
        // 4. Rollback on error
        rollback();

        // 5. Error callback
        const err = error as Error;
        onError?.(err);

        if (showToast || errorMessage) {
          toast({
            title: 'Error',
            description: errorMessage || err.message || 'Something went wrong',
            variant: 'destructive',
          });
        }

        throw error;
      } finally {
        setIsLoading(false);
      }
    },
    [onSuccess, onError, toast]
  );

  return {
    execute,
    isLoading,
  };
}

/**
 * Optimistic toggle hook (for boolean states like read/unread, like/unlike)
 */
export function useOptimisticToggle(
  initialState: boolean,
  serverAction: (newState: boolean) => Promise<void>,
  options?: {
    successMessage?: (state: boolean) => string;
    errorMessage?: string;
  }
) {
  const [state, setState] = useState(initialState);
  const [previousState, setPreviousState] = useState(initialState);
  const { toast } = useToast();

  const toggle = useCallback(async () => {
    const newState = !state;
    
    // Store for rollback
    setPreviousState(state);
    
    // Optimistic update
    setState(newState);

    try {
      await serverAction(newState);
      
      if (options?.successMessage) {
        toast({
          description: options.successMessage(newState),
        });
      }
    } catch (error) {
      // Rollback
      setState(previousState);
      
      toast({
        title: 'Error',
        description: options?.errorMessage || 'Action failed',
        variant: 'destructive',
      });
      
      throw error;
    }
  }, [state, previousState, serverAction, options, toast]);

  return {
    state,
    toggle,
    setState,
  };
}

/**
 * Optimistic list item update hook
 */
export function useOptimisticListUpdate<T extends { id: string | number }>(
  initialList: T[]
) {
  const [list, setList] = useState<T[]>(initialList);
  const [optimisticIds, setOptimisticIds] = useState<Set<string | number>>(new Set());
  const { toast } = useToast();

  const updateItem = useCallback(
    async (
      id: string | number,
      updates: Partial<T>,
      serverAction: () => Promise<T | void>,
      options?: {
        successMessage?: string;
        errorMessage?: string;
      }
    ) => {
      // Store original item for rollback
      const originalItem = list.find(item => item.id === id);
      if (!originalItem) return;

      // Optimistic update
      setList(prev =>
        prev.map(item => (item.id === id ? { ...item, ...updates } : item))
      );
      setOptimisticIds(prev => new Set(prev).add(id));

      try {
        const result = await serverAction();
        
        // Update with server data if returned
        if (result) {
          setList(prev =>
            prev.map(item => (item.id === id ? result : item))
          );
        }

        if (options?.successMessage) {
          toast({
            description: options.successMessage,
          });
        }
      } catch (error) {
        // Rollback to original
        setList(prev =>
          prev.map(item => (item.id === id ? originalItem : item))
        );

        toast({
          title: 'Error',
          description: options?.errorMessage || 'Update failed',
          variant: 'destructive',
        });

        throw error;
      } finally {
        setOptimisticIds(prev => {
          const newSet = new Set(prev);
          newSet.delete(id);
          return newSet;
        });
      }
    },
    [list, toast]
  );

  const removeItem = useCallback(
    async (
      id: string | number,
      serverAction: () => Promise<void>,
      options?: {
        successMessage?: string;
        errorMessage?: string;
      }
    ) => {
      // Store for rollback
      const removedItem = list.find(item => item.id === id);
      if (!removedItem) return;

      // Optimistic removal
      setList(prev => prev.filter(item => item.id !== id));
      setOptimisticIds(prev => new Set(prev).add(id));

      try {
        await serverAction();

        if (options?.successMessage) {
          toast({
            description: options.successMessage,
          });
        }
      } catch (error) {
        // Rollback - restore item
        setList(prev => [...prev, removedItem]);

        toast({
          title: 'Error',
          description: options?.errorMessage || 'Deletion failed',
          variant: 'destructive',
        });

        throw error;
      } finally {
        setOptimisticIds(prev => {
          const newSet = new Set(prev);
          newSet.delete(id);
          return newSet;
        });
      }
    },
    [list, toast]
  );

  const addItem = useCallback(
    async (
      item: T,
      serverAction: () => Promise<T>,
      options?: {
        successMessage?: string;
        errorMessage?: string;
      }
    ) => {
      // Optimistic add
      setList(prev => [...prev, item]);
      setOptimisticIds(prev => new Set(prev).add(item.id));

      try {
        const serverItem = await serverAction();
        
        // Replace with server version
        setList(prev =>
          prev.map(i => (i.id === item.id ? serverItem : i))
        );

        if (options?.successMessage) {
          toast({
            description: options.successMessage,
          });
        }

        return serverItem;
      } catch (error) {
        // Rollback - remove item
        setList(prev => prev.filter(i => i.id !== item.id));

        toast({
          title: 'Error',
          description: options?.errorMessage || 'Failed to add item',
          variant: 'destructive',
        });

        throw error;
      } finally {
        setOptimisticIds(prev => {
          const newSet = new Set(prev);
          newSet.delete(item.id);
          return newSet;
        });
      }
    },
    [toast]
  );

  const isOptimistic = useCallback(
    (id: string | number) => optimisticIds.has(id),
    [optimisticIds]
  );

  return {
    list,
    setList,
    updateItem,
    removeItem,
    addItem,
    isOptimistic,
  };
}
