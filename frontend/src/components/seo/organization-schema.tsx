"use client";
import React from 'react';
import { JsonLd } from './json-ld';

export function OrganizationSchema() {
  const data = {
    '@context': 'https://schema.org',
    '@type': 'Organization',
    name: 'RentHub',
    url: process.env.NEXT_PUBLIC_SITE_URL || 'http://localhost:3000',
    logo: (process.env.NEXT_PUBLIC_SITE_URL || 'http://localhost:3000') + '/images/og-default.png',
    sameAs: [
      'https://twitter.com/renthub',
      'https://www.facebook.com/renthub'
    ],
  };
  return <JsonLd data={data} />;
}
