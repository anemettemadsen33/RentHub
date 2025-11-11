"use client";
import { toast } from '@/hooks/use-toast';

type Options = { title?: string; description?: string };

export const notify = {
  success(opts: Options) {
    toast({ title: opts.title || 'Success', description: opts.description });
  },
  error(opts: Options) {
    toast({ title: opts.title || 'Error', description: opts.description, variant: 'destructive' });
  },
  info(opts: Options) {
    toast({ title: opts.title || 'Info', description: opts.description });
  },
};
