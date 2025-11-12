"use client";
import { useTranslations } from '@/lib/i18n-temp';

interface Partner {
  name: string;
  description: string;
}

export default function PartnerLogos() {
  const t = useTranslations('partnerships');

  const partners: Partner[] = [
    { name: 'Booking.com', description: t('booking_description') },
    { name: 'Airbnb', description: t('airbnb_description') },
    { name: 'VRBO', description: t('vrbo_description') },
  ];

  return (
    <section className="py-12 md:py-16 bg-muted/50 border-y">
      <div className="container mx-auto px-4">
        <div className="text-center mb-8">
          <h2 className="text-2xl md:text-3xl font-bold text-foreground mb-2">{t('title')}</h2>
          <p className="text-base text-muted-foreground max-w-2xl mx-auto">{t('subtitle')}</p>
        </div>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
          {partners.map((partner) => (
            <div
              key={partner.name}
              className="relative bg-card text-card-foreground rounded-xl shadow-sm hover:shadow-md transition-all p-6 text-center group border border-border"
            >
              <h3 className="font-semibold mb-2 text-base tracking-tight">{partner.name}</h3>
              <p className="text-muted-foreground text-xs leading-relaxed mb-6">{partner.description}</p>
              <div className="absolute bottom-2 right-3 inline-flex items-center gap-1.5 bg-primary/10 text-primary px-2 py-1 rounded-md text-[10px] font-medium">
                <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" /></svg>
                {t('verified_partner')}
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
