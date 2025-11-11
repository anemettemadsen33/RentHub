"use client";
import clsx from 'clsx';

export type StatusKind = 'pending' | 'confirmed' | 'cancelled' | 'completed' | 'paid' | 'failed';

const MAP: Record<StatusKind, string> = {
  pending: 'bg-amber-100 text-amber-800 dark:bg-amber-200 dark:text-amber-900',
  confirmed: 'bg-blue-100 text-blue-700 dark:bg-blue-200 dark:text-blue-900',
  cancelled: 'bg-red-100 text-red-700 dark:bg-red-200 dark:text-red-900',
  completed: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-200 dark:text-emerald-900',
  paid: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-200 dark:text-emerald-900',
  failed: 'bg-red-100 text-red-700 dark:bg-red-200 dark:text-red-900',
};

export function StatusPill({ status, className }: { status: StatusKind; className?: string }) {
  return (
    <span
      className={clsx(
        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize',
        MAP[status],
        className
      )}
      data-status={status}
    >
      {status}
    </span>
  );
}
