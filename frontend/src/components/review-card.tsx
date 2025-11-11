'use client';

import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Review } from '@/types/extended';
import { Star, ThumbsUp, User as UserIcon } from 'lucide-react';
import { formatDate } from '@/lib/utils';
import Image from 'next/image';

interface ReviewCardProps {
  review: Review;
  onHelpful?: (id: number) => void;
}

export function ReviewCard({ review, onHelpful }: ReviewCardProps) {
  const renderStars = (rating: number) => {
    return (
      <div className="flex items-center" role="img" aria-label={`${rating} out of 5 stars`}>
        {Array.from({ length: 5 }).map((_, i) => (
          <Star
            key={i}
            className={`h-4 w-4 ${
              i < rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'
            }`}
            aria-hidden="true"
          />
        ))}
      </div>
    );
  };

  return (
    <Card>
      <CardContent className="p-6">
        {/* Header */}
        <div className="flex items-start justify-between mb-4">
          <div className="flex items-center gap-3">
            <div className="relative w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center overflow-hidden">
              {review.user?.avatar_url ? (
                <Image
                  src={review.user.avatar_url}
                  alt={`${review.user.name}'s profile picture`}
                  fill
                  className="object-cover"
                  sizes="48px"
                  loading="lazy"
                />
              ) : (
                <UserIcon className="h-6 w-6 text-primary" aria-hidden="true" />
              )}
            </div>
            <div>
              <p className="font-semibold">{review.user?.name || 'Anonymous'}</p>
              <p className="text-sm text-gray-500">{formatDate(review.created_at)}</p>
            </div>
          </div>
          <div className="flex items-center gap-1">
            {renderStars(review.rating)}
            <span className="ml-2 font-semibold">{review.rating.toFixed(1)}</span>
          </div>
        </div>

        {/* Detailed Ratings */}
        <div className="grid grid-cols-2 md:grid-cols-3 gap-3 mb-4">
          {[
            { label: 'Cleanliness', value: review.cleanliness_rating },
            { label: 'Communication', value: review.communication_rating },
            { label: 'Accuracy', value: review.accuracy_rating },
            { label: 'Location', value: review.location_rating },
            { label: 'Value', value: review.value_rating },
          ].map((item) => (
            <div key={item.label} className="text-sm">
              <p className="text-gray-600">{item.label}</p>
              <div className="flex items-center gap-1">
                {renderStars(item.value)}
              </div>
            </div>
          ))}
        </div>

        {/* Comment */}
        <p className="text-gray-700 mb-4">{review.comment}</p>

        {/* Review Images */}
        {review.images && review.images.length > 0 && (
          <div className="grid grid-cols-4 gap-2 mb-4">
            {review.images.map((image, idx) => (
              <div key={idx} className="relative w-full h-24">
                <Image
                  src={image}
                  alt={`Review image ${idx + 1}`}
                  fill
                  className="object-cover rounded-lg cursor-pointer hover:opacity-80"
                  sizes="(max-width: 768px) 25vw, 12vw"
                  loading="lazy"
                />
              </div>
            ))}
          </div>
        )}

        {/* Host Response */}
        {review.host_response && (
          <div className="bg-gray-50 rounded-lg p-4 mb-4">
            <p className="font-semibold text-sm mb-1">Response from host</p>
            <p className="text-sm text-gray-700 mb-2">{review.host_response}</p>
            <p className="text-xs text-gray-500">
              {formatDate(review.host_response_date || review.created_at)}
            </p>
          </div>
        )}

        {/* Helpful Button */}
        <div className="flex items-center gap-2">
          <Button
            variant="outline"
            size="sm"
            onClick={() => onHelpful?.(review.id)}
            className="gap-2"
          >
            <ThumbsUp className="h-4 w-4" />
            Helpful ({review.helpful_count})
          </Button>
        </div>
      </CardContent>
    </Card>
  );
}
