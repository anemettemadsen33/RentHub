import type { Metadata } from 'next';

export const dynamic = 'force-dynamic';

export async function generateMetadata({ params }: { params: Promise<{ id: string }> }): Promise<Metadata> {
  const { id } = await params;
  return {
    title: `Booking #${id}`,
    robots: { index: false, follow: false },
    openGraph: {
      title: `Booking #${id}`,
      description: 'Private booking details',
      type: 'website',
    },
    alternates: { canonical: `/bookings/${id}` },
  };
}

export default function BookingDetailLayout({ children }: { children: React.ReactNode }) {
  return children;
}
