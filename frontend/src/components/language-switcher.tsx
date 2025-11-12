'use client';

import { useLocale, useTranslations } from '@/lib/i18n-temp';
import { usePathname, useRouter } from 'next/navigation';
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Globe } from 'lucide-react';
import { locales } from '@/i18n/config';

const languageNames: Record<string, string> = {
  en: 'English',
  ro: 'Română',
};

const languageFlags: Record<string, string> = {
  en: '🇬🇧',
  ro: '🇷🇴',
};

export function LanguageSwitcher() {
  const locale = useLocale();
  const router = useRouter();
  const pathname = usePathname();

  const handleLanguageChange = (newLocale: string) => {
    // Set cookie for persistence (next-intl will read this)
    document.cookie = `NEXT_LOCALE=${newLocale}; path=/; max-age=${60 * 60 * 24 * 365}`;
    
    // Refresh to apply new locale (stays on same URL since localePrefix='never')
    router.refresh();
  };

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button variant="ghost" size="sm" className="gap-2">
          <Globe className="h-4 w-4" />
          <span className="hidden sm:inline">{languageNames[locale]}</span>
          <span className="sm:hidden">{languageFlags[locale]}</span>
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end">
        {locales.map((lang) => (
          <DropdownMenuItem
            key={lang}
            onClick={() => handleLanguageChange(lang)}
            className={locale === lang ? 'bg-accent' : ''}
          >
            <span className="mr-2">{languageFlags[lang]}</span>
            {languageNames[lang]}
            {locale === lang && (
              <span className="ml-auto text-xs text-muted-foreground">✓</span>
            )}
          </DropdownMenuItem>
        ))}
      </DropdownMenuContent>
    </DropdownMenu>
  );
}
