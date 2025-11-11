import React from 'react';
import Link from 'next/link';
import { useTranslations } from 'next-intl';
import { breadcrumbI18nKey, BreadcrumbKey } from '@/lib/breadcrumbs';
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';

export type Crumb =
  | { label: string; href?: string }
  | { key: BreadcrumbKey; href?: string; titleOverride?: string };

export function Breadcrumbs({ items }: { items: Crumb[] }) {
  const t = useTranslations();
  if (!items || items.length === 0) return null;
  const last = items[items.length - 1];
  return (
    <Breadcrumb>
      <BreadcrumbList>
        {items.map((item, idx) => {
          const isLast = idx === items.length - 1;
          const label = 'key' in item ? (item.titleOverride || t(breadcrumbI18nKey(item.key))) : item.label;
          const key = ('key' in item ? item.key : item.label) + '-' + idx;
          return (
            <React.Fragment key={key}>
              <BreadcrumbItem>
                {isLast || !item.href ? (
                  <BreadcrumbPage>{label}</BreadcrumbPage>
                ) : (
                  <BreadcrumbLink asChild>
                    <Link href={item.href}>{label}</Link>
                  </BreadcrumbLink>
                )}
              </BreadcrumbItem>
              {!isLast ? <BreadcrumbSeparator /> : null}
            </React.Fragment>
          );
        })}
      </BreadcrumbList>
    </Breadcrumb>
  );
}
