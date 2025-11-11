import { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Properties | RentHub',
  description: 'Browse available rental properties',
};

export default function PropertiesLayout({ children }: { children: React.ReactNode }) {
  return children;
}
