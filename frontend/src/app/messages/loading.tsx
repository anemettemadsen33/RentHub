import { MainLayout } from '@/components/layouts/main-layout';
import { MessageListSkeleton, ConversationSkeleton } from '@/components/skeletons';
import { Skeleton } from '@/components/ui/skeleton';

export default function MessagesLoading() {
  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-6">
        <div className="mb-6">
          <Skeleton className="h-8 w-48 mb-2" />
          <Skeleton className="h-4 w-64" />
        </div>
        
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100vh-250px)]">
          {/* Conversations List */}
          <div className="lg:col-span-1 border rounded-lg overflow-hidden">
            <div className="p-4 border-b">
              <Skeleton className="h-10 w-full" />
            </div>
            <div className="p-4">
              <MessageListSkeleton items={8} />
            </div>
          </div>

          {/* Chat Area */}
          <div className="lg:col-span-2 border rounded-lg overflow-hidden">
            <ConversationSkeleton />
          </div>
        </div>
      </div>
    </MainLayout>
  );
}