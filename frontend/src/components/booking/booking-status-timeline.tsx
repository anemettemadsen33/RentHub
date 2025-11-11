"use client";
import { getBookingStatusSteps, bookingStatusColor, BookingStatus } from '@/lib/booking-status';
import { useTranslations } from 'next-intl';

export interface BookingStatusTimelineProps {
  status: BookingStatus;
  className?: string;
}

export function BookingStatusTimeline({ status, className }: BookingStatusTimelineProps) {
  const t = useTranslations('bookingDetail.timeline');
  const steps = getBookingStatusSteps(status);
  return (
    <ol className={"relative border-l pl-4 space-y-6 " + (className || '')}>
      {steps.map((step, i) => {
        const color = step.cancelled && step.key !== 'pending' && step.key !== 'confirmed'
          ? 'bg-red-100 text-red-600'
          : step.current
            ? 'bg-primary/10 text-primary'
            : step.done
              ? 'bg-green-100 text-green-700'
              : 'bg-gray-100 text-gray-600';
        return (
          <li key={i} className="ml-2">
            <div className={"w-fit px-3 py-1 rounded-full text-xs font-medium mb-1 " + color}>
              {t(step.label)}
            </div>
            {i < steps.length - 1 && (
              <span className="absolute left-[-1px] top-5 h-full w-[2px] bg-gradient-to-b from-gray-300 to-transparent" />
            )}
          </li>
        );
      })}
    </ol>
  );
}
