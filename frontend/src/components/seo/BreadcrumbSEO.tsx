import Link from 'next/link';
import { ChevronRightIcon } from '@heroicons/react/20/solid';
import { getBreadcrumbSchema } from '@/lib/schema';
import JsonLd from './JsonLd';

interface BreadcrumbItem {
  name: string;
  url: string;
}

interface BreadcrumbSEOProps {
  items: BreadcrumbItem[];
}

export default function BreadcrumbSEO({ items }: BreadcrumbSEOProps) {
  const allItems = [{ name: 'Home', url: '/' }, ...items];

  return (
    <>
      <JsonLd data={getBreadcrumbSchema(allItems)} />
      <nav className="flex items-center space-x-2 text-sm text-gray-600 mb-6">
        {allItems.map((item, index) => (
          <div key={item.url} className="flex items-center">
            {index > 0 && <ChevronRightIcon className="h-4 w-4 mx-2 text-gray-400" />}
            {index === allItems.length - 1 ? (
              <span className="font-medium text-gray-900">{item.name}</span>
            ) : (
              <Link
                href={item.url}
                className="hover:text-blue-600 transition-colors"
              >
                {item.name}
              </Link>
            )}
          </div>
        ))}
      </nav>
    </>
  );
}
