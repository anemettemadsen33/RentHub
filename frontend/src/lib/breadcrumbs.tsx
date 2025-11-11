export type BreadcrumbKey = 'home' | 'properties' | 'property' | 'reviews' | 'smartLocks' | 'verification';

export const breadcrumbSets = {
  propertyRoot: (title: string) => [
    { key: 'home' as BreadcrumbKey, href: '/' },
    { key: 'properties' as BreadcrumbKey, href: '/properties' },
    { key: 'property' as BreadcrumbKey, href: '#', titleOverride: title },
  ],
  propertyReviews: (propertyId: string, title: string) => [
    { key: 'home' as BreadcrumbKey, href: '/' },
    { key: 'properties' as BreadcrumbKey, href: '/properties' },
    { key: 'property' as BreadcrumbKey, href: `/properties/${propertyId}`, titleOverride: title },
    { key: 'reviews' as BreadcrumbKey },
  ],
  propertySmartLocks: (propertyId: string, title: string) => [
    { key: 'home' as BreadcrumbKey, href: '/' },
    { key: 'properties' as BreadcrumbKey, href: '/properties' },
    { key: 'property' as BreadcrumbKey, href: `/properties/${propertyId}`, titleOverride: title },
    { key: 'smartLocks' as BreadcrumbKey },
  ],
  verification: () => [
    { key: 'home' as BreadcrumbKey, href: '/' },
    { key: 'verification' as BreadcrumbKey },
  ],
};

// Map breadcrumb key to i18n translation path
export const breadcrumbI18nKey = (key: BreadcrumbKey) => `breadcrumbs.${key}`;
