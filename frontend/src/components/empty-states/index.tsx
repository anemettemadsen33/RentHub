import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { 
  Home, 
  MessageSquare, 
  Heart, 
  Calendar, 
  Bell,
  Search,
  Inbox,
  FolderOpen,
  Plus,
  LucideIcon
} from 'lucide-react';

interface EmptyStateProps {
  icon?: LucideIcon;
  title: string;
  description: string;
  action?: {
    label: string;
    onClick: () => void;
  };
  className?: string;
}

export function EmptyState({ 
  icon: Icon = Inbox, 
  title, 
  description, 
  action,
  className = ''
}: EmptyStateProps) {
  return (
    <Card className={`border-dashed ${className}`}>
      <CardContent className="flex flex-col items-center justify-center py-12 px-6 text-center">
        <div className="rounded-full bg-gray-100 p-6 mb-4">
          <Icon className="h-12 w-12 text-gray-400" />
        </div>
        <h3 className="text-xl font-semibold text-gray-900 mb-2">{title}</h3>
        <p className="text-gray-600 mb-6 max-w-md">{description}</p>
        {action && (
          <Button onClick={action.onClick}>
            {action.label}
          </Button>
        )}
      </CardContent>
    </Card>
  );
}

// Specialized empty states
export function NoPropertiesFound({ onReset }: { onReset?: () => void }) {
  return (
    <EmptyState
      icon={Home}
      title="No properties found"
      description="We couldn't find any properties matching your search criteria. Try adjusting your filters."
      action={onReset ? { label: 'Clear Filters', onClick: onReset } : undefined}
    />
  );
}

export function NoBookings({ onCreate }: { onCreate?: () => void }) {
  return (
    <EmptyState
      icon={Calendar}
      title="No bookings yet"
      description="You haven't made any bookings yet. Browse our properties to find your perfect stay."
      action={onCreate ? { label: 'Browse Properties', onClick: onCreate } : undefined}
    />
  );
}

export function NoMessages() {
  return (
    <EmptyState
      icon={MessageSquare}
      title="No messages"
      description="Your inbox is empty. Start a conversation with a host or guest to get started."
    />
  );
}

export function NoFavorites({ onBrowse }: { onBrowse?: () => void }) {
  return (
    <EmptyState
      icon={Heart}
      title="No favorites yet"
      description="You haven't saved any properties to your favorites. Click the heart icon on properties you like."
      action={onBrowse ? { label: 'Browse Properties', onClick: onBrowse } : undefined}
    />
  );
}

export function NoNotifications() {
  return (
    <EmptyState
      icon={Bell}
      title="All caught up!"
      description="You don't have any notifications at the moment. We'll notify you when something important happens."
      className="border-green-200 bg-green-50"
    />
  );
}

export function NoSearchResults({ query, onClear }: { query?: string; onClear?: () => void }) {
  return (
    <EmptyState
      icon={Search}
      title={query ? `No results for "${query}"` : "No results found"}
      description="Try searching for something else or check your spelling."
      action={onClear ? { label: 'Clear Search', onClick: onClear } : undefined}
    />
  );
}

export function EmptyList({ 
  icon = FolderOpen,
  title = "Nothing here yet",
  description = "This list is empty. Come back later or add items to get started.",
  onAdd
}: { 
  icon?: LucideIcon;
  title?: string;
  description?: string;
  onAdd?: () => void;
}) {
  return (
    <EmptyState
      icon={icon}
      title={title}
      description={description}
      action={onAdd ? { label: 'Add Item', onClick: onAdd } : undefined}
    />
  );
}

// Inline empty state for smaller sections
export function InlineEmptyState({ 
  message,
  icon: Icon = Inbox 
}: { 
  message: string;
  icon?: LucideIcon;
}) {
  return (
    <div className="flex flex-col items-center justify-center py-8 text-center">
      <Icon className="h-8 w-8 text-gray-300 mb-2" />
      <p className="text-sm text-gray-500">{message}</p>
    </div>
  );
}
