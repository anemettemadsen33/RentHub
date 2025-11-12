'use client';

import { useEffect, useRef, useState, useCallback } from 'react';
import { useParams, useRouter } from 'next/navigation';
import NextImage from 'next/image';
import { MainLayout } from '@/components/layouts/main-layout';
import { useAuth } from '@/contexts/auth-context';
import { notify } from '@/lib/notify';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { breadcrumbSets } from '@/lib/breadcrumbs';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Progress } from '@/components/ui/progress';
import { Separator } from '@/components/ui/separator';
import { ScrollArea } from '@/components/ui/scroll-area';
import { API_ENDPOINTS } from '@/lib/api-endpoints';
import apiClient from '@/lib/api-client';
import { Review, ReviewImage } from '@/types/extended';
import { Star, Camera, Loader2, ThumbsUp, MessageSquare, Image as ImageIcon, ChevronLeft, ChevronRight, X, Trash2 } from 'lucide-react';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import { useTranslations } from 'next-intl';

interface RatingBreakdown { label: string; value: number }

export default function PropertyReviewsPage() {
  const { id } = useParams<{ id: string }>();
  const router = useRouter();
  const { user } = useAuth();
  

  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [reviews, setReviews] = useState<Review[]>([]);
  const [overall, setOverall] = useState(0);
  const [averages, setAverages] = useState({ cleanliness: 0, communication: 0, accuracy: 0, location: 0, value: 0 });
  const [ratingInput, setRatingInput] = useState(0);
  const [comment, setComment] = useState('');
  const [images, setImages] = useState<File[]>([]);
  const [filterTab, setFilterTab] = useState<'all' | '5' | '4' | '3' | '2' | '1'>('all');
  const [propertyTitle, setPropertyTitle] = useState<string>('');

  // Pagination
  const [page, setPage] = useState(1);
  const [perPage] = useState(10);
  const [hasMore, setHasMore] = useState(false);
  const [loadingMore, setLoadingMore] = useState(false);

  // Lightbox
  const [lightboxOpen, setLightboxOpen] = useState(false);
  const [lightboxImages, setLightboxImages] = useState<string[]>([]);
  const [lightboxIndex, setLightboxIndex] = useState(0);
  // Touch gesture state for lightbox
  const [touchStart, setTouchStart] = useState<number | null>(null);
  const [touchEnd, setTouchEnd] = useState<number | null>(null);

  // Helpful voting state
  const [votingIds, setVotingIds] = useState<Set<number>>(new Set());

  const fileInputRef = useRef<HTMLInputElement>(null);
  const t = useTranslations('reviews');
  const tCommon = useTranslations('common');

  const loadPropertyTitle = useCallback(async () => {
    try {
      const resp = await apiClient.get(API_ENDPOINTS.properties.show(id));
      const data = (resp as any).data?.data || (resp as any).data;
      if (data && typeof data.title === 'string') {
        setPropertyTitle(data.title);
      }
    } catch {
      // no-op, fallback title will be used
    }
  }, [id]);

  const fetchReviews = useCallback(async (pageToLoad: number, append: boolean) => {
    const resp = await apiClient
      .get(API_ENDPOINTS.reviews.list, { params: { property_id: id, page: pageToLoad, per_page: perPage } })
      .catch(() => ({ data: { data: demoReviews(pageToLoad, perPage), meta: { current_page: pageToLoad, last_page: 3 } } }));

    const { data, meta } = resp.data || {};
    const items: Review[] = data || [];
    if (append) setReviews(prev => [...prev, ...items]); else setReviews(items);

    if (meta && typeof meta.current_page !== 'undefined' && typeof meta.last_page !== 'undefined') {
      setHasMore(meta.current_page < meta.last_page);
      setPage(meta.current_page);
    } else {
      // If no meta, assume no further pages
      setHasMore(false);
      setPage(pageToLoad);
    }
  }, [id, perPage]);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const [ratingResp] = await Promise.all([
        apiClient.get(API_ENDPOINTS.reviews.propertyRating(id)).catch(() => ({ data: { data: demoRating() } })),
        fetchReviews(1, false),
      ]);
      const rating = ratingResp.data;
      setOverall(rating.data?.overall || 0);
      setAverages({
        cleanliness: rating.data?.cleanliness || 0,
        communication: rating.data?.communication || 0,
        accuracy: rating.data?.accuracy || 0,
        location: rating.data?.location || 0,
        value: rating.data?.value || 0,
      });
    } finally { setLoading(false); }
  }, [id, fetchReviews]);

  useEffect(() => {
    if (!id) return;
    load();
    loadPropertyTitle();
  }, [id, load, loadPropertyTitle]);

  const filteredReviews = reviews.filter(r => filterTab === 'all' ? true : Math.round(r.rating) === parseInt(filterTab));

  const tNotify = useTranslations('notify');
  const submitReview = async () => {
    if (!ratingInput || !comment.trim()) {
      notify.error({ title: tNotify('warning'), description: tNotify('missingReviewData') });
      return;
    }
    setSubmitting(true);
    try {
      const formData = new FormData();
      formData.append('property_id', id!);
      formData.append('rating', String(ratingInput));
      formData.append('comment', comment.trim());
      images.forEach(f => formData.append('images[]', f));
      const { data } = await apiClient.post(API_ENDPOINTS.reviews.create, formData, { headers: { 'Content-Type': 'multipart/form-data' } }).catch(() => ({ data: { data: demoNewReview(ratingInput, comment, images) } }));
      setReviews(prev => [data.data, ...prev]);
      setRatingInput(0); setComment(''); setImages([]);
      notify.success({ title: tNotify('reviewSubmitted') });
    } catch {
      notify.error({ title: tNotify('error'), description: tNotify('failedSubmitReview') });
    } finally { setSubmitting(false); }
  };

  const addImages = (e: React.ChangeEvent<HTMLInputElement>) => {
    const fl = e.target.files;
    if (!fl) return;
    const list = Array.from(fl);
    setImages(prev => [...prev, ...list]);
  };

  // Rating histogram from loaded reviews
  const histogram = [5,4,3,2,1].map(star => {
    const count = reviews.filter(r => Math.round(r.rating) === star).length;
    const percent = reviews.length ? Math.round((count / reviews.length) * 100) : 0;
    return { star, count, percent };
  });

  // Lightbox handlers (moved below before return previously, ensure logic stays before JSX return)
  const openLightbox = (imgs: string[], startIndex: number) => {
    if (!imgs || imgs.length === 0) return;
    setLightboxImages(imgs);
    setLightboxIndex(startIndex);
    setLightboxOpen(true);
  };

  const closeLightbox = useCallback(() => setLightboxOpen(false), []);
  const prevImage = useCallback(
    () => setLightboxIndex(i => (i - 1 + lightboxImages.length) % lightboxImages.length),
    [lightboxImages.length]
  );
  const nextImage = useCallback(
    () => setLightboxIndex(i => (i + 1) % lightboxImages.length),
    [lightboxImages.length]
  );

  // Touch gesture handlers for lightbox
  const minSwipeDistance = 50;

  const onTouchStart = (e: React.TouchEvent) => {
    setTouchEnd(null);
    setTouchStart(e.targetTouches[0].clientX);
  };

  const onTouchMove = (e: React.TouchEvent) => {
    setTouchEnd(e.targetTouches[0].clientX);
  };

  const onTouchEnd = () => {
    if (!touchStart || !touchEnd) return;
    const distance = touchStart - touchEnd;
    const isLeftSwipe = distance > minSwipeDistance;
    const isRightSwipe = distance < -minSwipeDistance;
    if (isLeftSwipe) nextImage();
    else if (isRightSwipe) prevImage();
  };

  useEffect(() => {
    if (!lightboxOpen) return;
    const onKey = (e: KeyboardEvent) => {
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowLeft') prevImage();
      if (e.key === 'ArrowRight') nextImage();
    };
    window.addEventListener('keydown', onKey);
    return () => window.removeEventListener('keydown', onKey);
  }, [lightboxOpen, closeLightbox, prevImage, nextImage]);

  // Voting
  const voteHelpful = async (review: Review) => {
    if (votingIds.has(review.id)) return;
    setVotingIds(prev => new Set(prev).add(review.id));
    try {
      await apiClient.post(API_ENDPOINTS.reviews.vote(review.id)).catch(() => ({}));
      setReviews(prev => prev.map(r => r.id === review.id ? { ...r, helpful_count: (r.helpful_count || 0) + 1 } : r));
    } finally {
      setVotingIds(prev => { const n = new Set(prev); n.delete(review.id); return n; });
    }
  };

  // Deletion
  const deleteReview = async (reviewId: number) => {
    try {
      await apiClient.delete(API_ENDPOINTS.reviews.delete(reviewId)).catch(() => ({}));
  setReviews(prev => prev.filter(r => r.id !== reviewId));
  notify.success({ title: tNotify('reviewDeleted') });
    } catch {
  notify.error({ title: tNotify('error'), description: tNotify('failedDeleteReview') });
    }
  };

  const ratingBreakdown: RatingBreakdown[] = [
    { label: t('cleanliness'), value: averages.cleanliness },
    { label: t('communication'), value: averages.communication },
    { label: t('accuracy'), value: averages.accuracy },
    { label: t('location'), value: averages.location },
    { label: t('value'), value: averages.value },
  ];

  if (!user) return null;

  return (
    <MainLayout>
      <div className="container mx-auto px-4 pt-6">
        <Breadcrumbs items={breadcrumbSets.propertyReviews(String(id), propertyTitle || 'Property')} />
      </div>
      <div className="container mx-auto px-4 py-8 max-w-6xl">
        <div className="flex items-start justify-between mb-6 flex-col md:flex-row gap-4">
          <div>
            <h1 className="text-3xl font-bold mb-2">{t('title')}</h1>
            <p className="text-gray-600">{t('subtitle')}</p>
          </div>
          <Card className="w-full md:w-72">
            <CardContent className="pt-6 pb-4 flex flex-col items-center text-center">
              <div className="text-5xl font-bold flex items-center gap-2">
                {overall.toFixed(1)} <Star className="h-8 w-8 fill-primary text-primary" />
              </div>
              <p className="text-sm text-gray-500">{t('overall')}</p>
              <Separator className="my-4" />
              <div className="w-full space-y-2">
                {ratingBreakdown.map(r => (
                  <div key={r.label} className="flex items-center gap-2">
                    <div className="w-28 text-xs font-medium">{r.label}</div>
                    <Progress value={r.value * 20} className="h-2 flex-1" />
                    <span className="text-xs w-6 text-right">{r.value.toFixed(1)}</span>
                  </div>
                ))}
              </div>
              <Separator className="my-4" />
              <div className="w-full space-y-2">
                {histogram.map(h => (
                  <div key={h.star} className="flex items-center gap-2">
                    <div className="w-16 text-xs font-medium flex items-center gap-1">
                      <Star className="h-3 w-3 fill-yellow-400 text-yellow-400" /> {h.star}
                    </div>
                    <Progress value={h.percent} className="h-2 flex-1" />
                    <span className="text-[11px] w-10 text-right">{h.count}</span>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* New Review */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle className="text-base">{t('writeReview')}</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex items-center gap-2">
              {[1,2,3,4,5].map(star => (
                <button
                  key={star}
                  onClick={() => setRatingInput(star)}
                  className={`transition ${star <= ratingInput ? 'text-yellow-400' : 'text-gray-300'} hover:text-yellow-400`}
                >
                  <Star className={`h-8 w-8 ${star <= ratingInput ? 'fill-yellow-400' : ''}`} />
                </button>
              ))}
            </div>
            <Textarea
              placeholder={t('shareExperiencePlaceholder')}
              value={comment}
              onChange={(e) => setComment(e.target.value)}
              rows={4}
            />
            {/* Image upload */}
            <div>
              <input ref={fileInputRef} type="file" multiple accept="image/*" className="hidden" onChange={addImages} />
              <Button type="button" variant="outline" size="sm" onClick={() => fileInputRef.current?.click()}>
                <Camera className="h-4 w-4 mr-2" /> {t('addPhotos')}
              </Button>
              {images.length > 0 && (
                <div className="mt-3 flex flex-wrap gap-2">
                  {images.map((file, idx) => (
                    <div key={idx} className="w-20 h-20 relative rounded overflow-hidden bg-muted flex items-center justify-center text-xs">
                      <ImageIcon className="h-6 w-6 text-gray-500" />
                      <span className="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-[10px] truncate px-1">{file.name}</span>
                    </div>
                  ))}
                </div>
              )}
            </div>
            <Button disabled={submitting} onClick={submitReview}>
              {submitting && <Loader2 className="h-4 w-4 mr-2 animate-spin" />}{t('submitReview')}
            </Button>
          </CardContent>
        </Card>

        <Tabs value={filterTab} onValueChange={(v: any) => setFilterTab(v)} className="mb-4">
            <TabsList className="flex flex-wrap gap-2">
            <TabsTrigger value="all">{t('all')}</TabsTrigger>
            {[5,4,3,2,1].map(r => (
              <TabsTrigger key={r} value={String(r)}>{t('stars', { count: r })}</TabsTrigger>
            ))}
          </TabsList>
        </Tabs>

        {/* Reviews List */}
        <div className="space-y-4">
          {loading ? (
            <div className="p-10 text-center text-gray-500">{t('loading')}</div>
          ) : filteredReviews.length === 0 ? (
            <div className="p-10 text-center text-gray-500">{t('none')}</div>
          ) : (
            <>
            {filteredReviews.map(review => (
              <Card key={review.id}>
                <CardContent className="pt-6 space-y-4">
                  <div className="flex items-start gap-4">
                    <Avatar>
                      <AvatarImage src={review.user?.avatar_url} />
                      <AvatarFallback>{review.user?.name?.split(' ').map(n=>n[0]).join('')}</AvatarFallback>
                    </Avatar>
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center gap-2 flex-wrap">
                        <div className="flex items-center gap-1">
                          <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                          <span className="font-medium">{review.rating.toFixed(1)}</span>
                        </div>
                        <span className="text-xs text-gray-500">{new Date(review.created_at).toLocaleDateString()}</span>
                        <Badge variant="outline">{t('verifiedStay')}</Badge>
                        {(user?.id === review.user_id || user?.role === 'owner' || user?.role === 'admin') && (
                          <AlertDialog>
                            <AlertDialogTrigger asChild>
                              <Button variant="ghost" size="sm" className="h-7 px-2 text-red-600 hover:text-red-700 hover:bg-red-50">
                                <Trash2 className="h-4 w-4 mr-1" /> Delete
                              </Button>
                            </AlertDialogTrigger>
                            <AlertDialogContent>
                              <AlertDialogHeader>
                                <AlertDialogTitle>{t('deleteDialog.title')}</AlertDialogTitle>
                                <AlertDialogDescription>
                                  {t('deleteDialog.description')}
                                </AlertDialogDescription>
                              </AlertDialogHeader>
                              <AlertDialogFooter>
                                <AlertDialogCancel>{tCommon('cancel')}</AlertDialogCancel>
                                <AlertDialogAction onClick={() => deleteReview(review.id)}>{tCommon('delete')}</AlertDialogAction>
                              </AlertDialogFooter>
                            </AlertDialogContent>
                          </AlertDialog>
                        )}
                      </div>
                      <p className="mt-2 text-sm leading-relaxed whitespace-pre-line">{review.comment}</p>
                      {review.images && review.images.length > 0 && (
                        <div className="mt-3 grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2">
                          {review.images.map((img, idx) => (
                            <button key={idx} className="relative group h-24" onClick={() => openLightbox(review.images as string[], idx)}>
                              <NextImage
                                src={img}
                                alt={`Review image ${idx + 1}`}
                                fill
                                className="object-cover rounded"
                                sizes="(max-width: 640px) 50vw, (max-width: 1024px) 25vw, 12vw"
                                loading="lazy"
                              />
                            </button>
                          ))}
                        </div>
                      )}
                      {review.host_response && (
                        <div className="mt-4 border rounded p-3 bg-muted/40">
                          <div className="text-xs font-semibold mb-1">{t('hostResponse')}</div>
                          <p className="text-xs text-gray-700 whitespace-pre-line">{review.host_response}</p>
                          <div className="text-[10px] text-gray-500 mt-1">{review.host_response_date && new Date(review.host_response_date).toLocaleString()}</div>
                        </div>
                      )}
                      <div className="flex items-center gap-4 mt-4">
                        <button
                          onClick={() => voteHelpful(review)}
                          disabled={votingIds.has(review.id)}
                          className={`flex items-center gap-1 text-xs ${votingIds.has(review.id) ? 'text-gray-400' : 'text-gray-500 hover:text-gray-700'}`}
                        >
                          <ThumbsUp className="h-4 w-4" /> {t('helpful')} ({review.helpful_count})
                        </button>
                        <button className="flex items-center gap-1 text-xs text-gray-500 hover:text-gray-700">
                          <MessageSquare className="h-4 w-4" /> {t('respond')}
                        </button>
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
            {hasMore && (
              <div className="flex justify-center py-4">
                <Button variant="outline" onClick={async () => { setLoadingMore(true); await fetchReviews(page + 1, true); setLoadingMore(false); }} disabled={loadingMore}>
                  {loadingMore && <Loader2 className="h-4 w-4 mr-2 animate-spin" />} {t('loadMore')}
                </Button>
              </div>
            )}
            </>
          )}
        </div>

        {/* Lightbox Overlay */}
        {lightboxOpen && (
          <div 
            className="fixed inset-0 z-50 bg-black/90 flex items-center justify-center"
            onTouchStart={onTouchStart}
            onTouchMove={onTouchMove}
            onTouchEnd={onTouchEnd}
          >
            <button className="absolute top-4 right-4 text-white/80 hover:text-white z-10" onClick={closeLightbox} aria-label="Close">
              <X className="h-7 w-7" />
            </button>
            {lightboxImages.length > 1 && (
              <>
                <button className="absolute left-4 text-white/80 hover:text-white z-10" onClick={prevImage} aria-label="Previous">
                  <ChevronLeft className="h-10 w-10" />
                </button>
                <button className="absolute right-4 text-white/80 hover:text-white z-10" onClick={nextImage} aria-label="Next">
                  <ChevronRight className="h-10 w-10" />
                </button>
              </>
            )}
            <div className="relative w-[90vw] h-[85vh]">
              <NextImage
                src={lightboxImages[lightboxIndex]}
                alt={`Lightbox image ${lightboxIndex + 1}`}
                fill
                className="object-contain rounded touch-none"
                sizes="90vw"
                priority
              />
            </div>
            <div className="absolute bottom-4 left-1/2 -translate-x-1/2 text-white/80 text-sm">
              {lightboxIndex + 1} / {lightboxImages.length}
            </div>
          </div>
        )}
      </div>
    </MainLayout>
  );
}

function demoReviews(page = 1, perPage = 10): Review[] {
  // generate some fake reviews across pages
  const total = perPage;
  const startId = (page - 1) * perPage + 1;
  return Array.from({ length: total }).map((_, i) => {
    const id = startId + i;
    const rating = 3 + ((id % 5) * 0.5);
    const userId = 10 + (id % 7);
    const hasImage = id % 2 === 0;
    return {
      id,
      property_id: 1,
      user_id: userId,
      booking_id: 100 + id,
      rating,
      cleanliness_rating: Math.min(5, Math.max(1, rating)),
      communication_rating: Math.min(5, Math.max(1, rating)),
      accuracy_rating: Math.min(5, Math.max(1, rating)),
      location_rating: Math.min(5, Math.max(1, rating)),
      value_rating: Math.min(5, Math.max(1, rating)),
      comment: `Sample review #${id}: Great location, very clean and easy check-in. Host was responsive!`,
      images: hasImage ? ['https://images.unsplash.com/photo-1522708323590-d24dbb6b0267','https://images.unsplash.com/photo-1505693416388-ac5ce068fe85'] : [],
      user: { id: userId, name: `User ${userId}` },
      host_response: id % 4 === 0 ? 'Thank you! Glad you enjoyed your stay.' : undefined,
      host_response_date: id % 4 === 0 ? new Date().toISOString() : undefined,
      helpful_count: id % 5,
      created_at: new Date(Date.now() - id * 3600_000).toISOString(),
      updated_at: new Date().toISOString(),
    };
  });
}

function demoRating() {
  return {
    overall: 4.7,
    cleanliness: 4.8,
    communication: 4.9,
    accuracy: 4.6,
    location: 4.5,
    value: 4.4,
  };
}

function demoNewReview(rating: number, comment: string, files: File[]) {
  return {
    data: {
      id: Math.floor(Math.random()*100000),
      property_id: 1,
      user_id: 999,
      booking_id: 0,
      rating,
      cleanliness_rating: rating,
      communication_rating: rating,
      accuracy_rating: rating,
      location_rating: rating,
      value_rating: rating,
      comment,
      images: files.map(f => URL.createObjectURL(f)),
      user: { id: 999, name: 'You' },
      helpful_count: 0,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString(),
    }
  };
}
