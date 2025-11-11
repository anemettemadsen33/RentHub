'use client';

import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useToast } from '@/hooks/use-toast';
import apiClient from '@/lib/api-client';
import { Star } from 'lucide-react';

interface ReviewFormProps {
  bookingId: number;
  propertyId: number;
  onSuccess?: () => void;
}

export function ReviewForm({ bookingId, propertyId, onSuccess }: ReviewFormProps) {
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);
  const [ratings, setRatings] = useState({
    overall: 0,
    cleanliness: 0,
    communication: 0,
    accuracy: 0,
    location: 0,
    value: 0,
  });
  const [comment, setComment] = useState('');
  const [hoveredRating, setHoveredRating] = useState<string | null>(null);
  const [hoveredValue, setHoveredValue] = useState(0);

  const ratingCategories = [
    { key: 'overall', label: 'Overall Rating' },
    { key: 'cleanliness', label: 'Cleanliness' },
    { key: 'communication', label: 'Communication' },
    { key: 'accuracy', label: 'Accuracy' },
    { key: 'location', label: 'Location' },
    { key: 'value', label: 'Value for Money' },
  ];

  const handleStarClick = (category: string, value: number) => {
    setRatings({ ...ratings, [category]: value });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (ratings.overall === 0) {
      toast({
        title: 'Error',
        description: 'Please provide an overall rating',
        variant: 'destructive',
      });
      return;
    }

    setLoading(true);
    try {
      await apiClient.post('/reviews', {
        booking_id: bookingId,
        property_id: propertyId,
        rating: ratings.overall,
        cleanliness_rating: ratings.cleanliness || ratings.overall,
        communication_rating: ratings.communication || ratings.overall,
        accuracy_rating: ratings.accuracy || ratings.overall,
        location_rating: ratings.location || ratings.overall,
        value_rating: ratings.value || ratings.overall,
        comment,
      });

      toast({
        title: 'Success',
        description: 'Review submitted successfully!',
      });

      onSuccess?.();
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to submit review',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle>Write a Review</CardTitle>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-6">
          {ratingCategories.map((category) => (
            <div key={category.key}>
              <Label className="text-base font-semibold mb-2 block">
                {category.label}
              </Label>
              <div className="flex items-center gap-2">
                {Array.from({ length: 5 }).map((_, i) => {
                  const value = i + 1;
                  const isHovered = hoveredRating === category.key && value <= hoveredValue;
                  const isSelected = value <= ratings[category.key as keyof typeof ratings];
                  
                  return (
                    <button
                      key={i}
                      type="button"
                      onClick={() => handleStarClick(category.key, value)}
                      onMouseEnter={() => {
                        setHoveredRating(category.key);
                        setHoveredValue(value);
                      }}
                      onMouseLeave={() => {
                        setHoveredRating(null);
                        setHoveredValue(0);
                      }}
                      className="focus:outline-none transition-transform hover:scale-110"
                    >
                      <Star
                        className={`h-8 w-8 ${
                          isHovered || isSelected
                            ? 'fill-yellow-400 text-yellow-400'
                            : 'text-gray-300'
                        }`}
                      />
                    </button>
                  );
                })}
                <span className="ml-2 text-sm text-gray-600">
                  {ratings[category.key as keyof typeof ratings] || 0}/5
                </span>
              </div>
            </div>
          ))}

          <div>
            <Label htmlFor="comment">Your Review</Label>
            <textarea
              id="comment"
              value={comment}
              onChange={(e) => setComment(e.target.value)}
              placeholder="Share your experience with this property..."
              rows={5}
              required
              className="w-full px-3 py-2 border border-input rounded-md focus:outline-none focus:ring-2 focus:ring-ring resize-none"
            />
          </div>

          <Button type="submit" disabled={loading} className="w-full">
            {loading ? 'Submitting...' : 'Submit Review'}
          </Button>
        </form>
      </CardContent>
    </Card>
  );
}
