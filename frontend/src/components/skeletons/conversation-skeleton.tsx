import { Skeleton } from '@/components/ui/skeleton';

interface ConversationSkeletonProps {
  messages?: number;
}

export function ConversationSkeleton({ messages = 6 }: ConversationSkeletonProps) {
  return (
    <div className="flex flex-col h-full">
      {/* Header */}
      <div className="p-4 border-b flex items-center gap-3">
        <Skeleton className="h-10 w-10 rounded-full" />
        <div className="flex-1 space-y-2">
          <Skeleton className="h-4 w-40" />
          <Skeleton className="h-3 w-24" />
        </div>
        <div className="flex gap-2">
          <Skeleton className="h-8 w-8 rounded" />
          <Skeleton className="h-8 w-8 rounded" />
          <Skeleton className="h-8 w-8 rounded" />
        </div>
      </div>
      {/* Messages */}
      <div className="flex-1 p-4 space-y-4 overflow-y-auto">
        {Array.from({ length: messages }).map((_, i) => (
          <div key={i} className={`flex ${i % 2 === 0 ? 'justify-start' : 'justify-end'}`}>            
            <div className={`max-w-[70%] space-y-2 ${i % 2 === 0 ? '' : 'text-right'}`}>
              <Skeleton className="h-4 w-full" />
              <Skeleton className="h-4 w-5/6" />
              <Skeleton className="h-3 w-1/2" />
            </div>
          </div>
        ))}
      </div>
      {/* Input */}
      <div className="p-4 border-t space-y-2">
        <Skeleton className="h-10 w-full" />
        <Skeleton className="h-4 w-32" />
      </div>
    </div>
  );
}
