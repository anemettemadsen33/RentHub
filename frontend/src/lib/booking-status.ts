export type BookingStatus = 'pending' | 'confirmed' | 'cancelled' | 'completed';

interface StatusStep {
  key: BookingStatus;
  label: string; // i18n key (bookingDetail.timeline.*)
  done: boolean;
  current: boolean;
  cancelled: boolean;
}

const ORDER: BookingStatus[] = ['pending', 'confirmed', 'completed'];

export function getBookingStatusSteps(status: BookingStatus): StatusStep[] {
  const cancelled = status === 'cancelled';
  return ORDER.map((step) => {
    const stepIndex = ORDER.indexOf(step);
    const statusIndex = status === 'cancelled' ? ORDER.indexOf('confirmed') : ORDER.indexOf(status as any);
    const isReached = stepIndex <= statusIndex && !cancelled;
    return {
      key: step,
      label: step,
      done: isReached && step !== (status as any),
      current: !cancelled && step === (status as any),
      cancelled,
    };
  });
}

export function bookingStatusColor(status: BookingStatus): string {
  switch (status) {
    case 'confirmed':
      return 'text-green-600 bg-green-100';
    case 'pending':
      return 'text-yellow-600 bg-yellow-100';
    case 'cancelled':
      return 'text-red-600 bg-red-100';
    case 'completed':
      return 'text-blue-600 bg-blue-100';
    default:
      return 'text-gray-600 bg-gray-100';
  }
}
