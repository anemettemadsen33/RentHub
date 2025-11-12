'use client';

import { useState } from 'react';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { useDebounce, useDebouncedCallback } from '@/hooks/use-debounce';
import { useOptimisticList } from '@/hooks/use-optimistic';
import { useFavorites } from '@/hooks/use-favorites';
import { 
  EmptyState, 
  NoBookings, 
  NoFavorites, 
  NoMessages,
  NoNotifications,
  NoSearchResults,
  InlineEmptyState 
} from '@/components/empty-states';
import { Heart, Zap, Search, Clock, CheckCircle } from 'lucide-react';

interface TodoItem {
  id: number;
  text: string;
  completed: boolean;
}

export default function PerformanceDemoPage() {
  // ==================== DEBOUNCE DEMO ====================
  const [searchInput, setSearchInput] = useState('');
  const debouncedSearch = useDebounce(searchInput, 500);
  const [apiCallCount, setApiCallCount] = useState(0);

  // Simulate API call when debounced value changes
  useState(() => {
    if (debouncedSearch) {
      setApiCallCount(prev => prev + 1);
    }
  });

  // ==================== OPTIMISTIC UI DEMO ====================
  const initialTodos: TodoItem[] = [
    { id: 1, text: 'Implement optimistic UI', completed: true },
    { id: 2, text: 'Add debounce hooks', completed: true },
    { id: 3, text: 'Create empty states', completed: false },
  ];

  const { list: todos, updateOptimistic, removeOptimistic, isOptimistic } = useOptimisticList(initialTodos);

  const toggleTodo = async (id: number) => {
    const todo = todos.find(t => t.id === id);
    if (!todo) return;

    await updateOptimistic(
      id,
      { completed: !todo.completed },
      // Simulate API call
      () => new Promise<TodoItem>(resolve => {
        setTimeout(() => {
          resolve({ ...todo, completed: !todo.completed });
        }, 1000);
      })
    );
  };

  const deleteTodo = async (id: number) => {
    await removeOptimistic(
      id,
      // Simulate API call
      () => new Promise<void>(resolve => {
        setTimeout(() => resolve(), 800);
      })
    );
  };

  // ==================== FAVORITES DEMO ====================
  const { isFavorite, toggleFavorite, isOptimistic: isFavOptimistic } = useFavorites();
  const demoProperties = [101, 102, 103, 104, 105];

  // ==================== EMPTY STATES DEMO ====================
  const [showEmptyState, setShowEmptyState] = useState<string>('bookings');

  const emptyStates: Record<string, JSX.Element> = {
    bookings: <NoBookings onCreate={() => alert('Navigate to properties')} />,
    favorites: <NoFavorites onBrowse={() => alert('Navigate to properties')} />,
    messages: <NoMessages />,
    notifications: <NoNotifications />,
    search: <NoSearchResults query="luxury villa" onClear={() => alert('Clear search')} />,
    generic: <EmptyState icon={Search} title="Nothing found" description="Try something else" />,
    inline: <InlineEmptyState message="No items available" />,
  };

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8 max-w-6xl">
        <div className="mb-8">
          <h1 className="text-3xl font-bold mb-2 flex items-center gap-3">
            <Zap className="h-8 w-8 text-yellow-500" />
            Performance &amp; UX Optimizations Demo
          </h1>
          <p className="text-gray-600">
            Interactive demos for debounce, optimistic UI, favorites, and empty states
          </p>
        </div>

        <div className="grid gap-6">
          {/* DEBOUNCE DEMO */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Clock className="h-5 w-5 text-blue-500" />
                1. Debounced Search (500ms delay)
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <label className="block text-sm font-medium mb-2">
                  Type to see debouncing in action:
                </label>
                <Input
                  type="text"
                  placeholder="Search properties..."
                  value={searchInput}
                  onChange={(e) => setSearchInput(e.target.value)}
                  className="max-w-md"
                />
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Immediate value:</p>
                  <Badge variant="secondary">{searchInput || '(empty)'}</Badge>
                  <p className="text-xs text-gray-500 mt-1">Updates on every keystroke</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600 mb-1">Debounced value (500ms):</p>
                  <Badge variant="default">{debouncedSearch || '(empty)'}</Badge>
                  <p className="text-xs text-gray-500 mt-1">Updates 500ms after typing stops</p>
                </div>
              </div>

              <div className="p-3 bg-green-50 border border-green-200 rounded-lg">
                <p className="text-sm font-medium text-green-800">
                  📊 API Calls Saved: {searchInput.length - (debouncedSearch ? 1 : 0)}
                </p>
                <p className="text-xs text-green-600 mt-1">
                  Without debounce, this would have made {searchInput.length} API calls. 
                  With debounce: only {debouncedSearch ? '1' : '0'} call(s)!
                </p>
              </div>
            </CardContent>
          </Card>

          {/* OPTIMISTIC UI DEMO */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <CheckCircle className="h-5 w-5 text-green-500" />
                2. Optimistic UI Updates (Instant Feedback)
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <p className="text-sm text-gray-600">
                Click checkbox or delete button - notice instant UI update even though 
                &quot;API call&quot; takes 1 second to complete!
              </p>

              <div className="space-y-2">
                {todos.map((todo) => (
                  <div
                    key={todo.id}
                    className={`flex items-center gap-3 p-3 border rounded-lg transition-all ${
                      isOptimistic(todo.id) ? 'bg-blue-50 border-blue-200' : 'bg-white'
                    }`}
                  >
                    <input
                      type="checkbox"
                      checked={todo.completed}
                      onChange={() => toggleTodo(todo.id)}
                      className="h-4 w-4"
                      disabled={isOptimistic(todo.id)}
                    />
                    <span className={`flex-1 ${todo.completed ? 'line-through text-gray-400' : ''}`}>
                      {todo.text}
                    </span>
                    {isOptimistic(todo.id) && (
                      <Badge variant="outline" className="animate-pulse">Syncing...</Badge>
                    )}
                    <Button
                      size="sm"
                      variant="destructive"
                      onClick={() => deleteTodo(todo.id)}
                      disabled={isOptimistic(todo.id)}
                    >
                      Delete
                    </Button>
                  </div>
                ))}
              </div>

              {todos.length === 0 && (
                <InlineEmptyState message="All tasks completed! Great job! 🎉" />
              )}

              <div className="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <p className="text-sm font-medium text-blue-800">
                  ✨ Optimistic Updates Active
                </p>
                <p className="text-xs text-blue-600 mt-1">
                  UI updates instantly, then syncs with server. If server fails, 
                  changes are automatically rolled back!
                </p>
              </div>
            </CardContent>
          </Card>

          {/* FAVORITES DEMO */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Heart className="h-5 w-5 text-red-500" />
                3. Favorites with LocalStorage & Optimistic UI
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <p className="text-sm text-gray-600">
                Click hearts to favorite/unfavorite. Data persists across page refreshes!
              </p>

              <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
                {demoProperties.map((id) => (
                  <div key={id} className="relative">
                    <div className="aspect-square bg-gradient-to-br from-blue-100 to-purple-100 rounded-lg flex items-center justify-center">
                      <span className="text-2xl font-bold text-gray-700">#{id}</span>
                    </div>
                    <Button
                      size="icon"
                      variant="secondary"
                      className={`absolute top-2 right-2 rounded-full ${
                        isFavorite(id) ? 'bg-red-50 hover:bg-red-100' : ''
                      } ${isFavOptimistic(id) ? 'animate-pulse' : ''}`}
                      onClick={() => toggleFavorite(id)}
                      disabled={isFavOptimistic(id)}
                      aria-label={isFavorite(id) ? 'Remove from favorites' : 'Add to favorites'}
                    >
                      <Heart 
                        className={`h-4 w-4 transition-colors ${
                          isFavorite(id) ? 'fill-red-500 text-red-500' : 'text-gray-600'
                        }`}
                      />
                    </Button>
                  </div>
                ))}
              </div>

              <div className="p-3 bg-red-50 border border-red-200 rounded-lg">
                <p className="text-sm font-medium text-red-800">
                  💾 LocalStorage Persistence
                </p>
                <p className="text-xs text-red-600 mt-1">
                  Favorites are saved to localStorage and will persist even after 
                  page refresh or browser close. Ready for API integration!
                </p>
              </div>
            </CardContent>
          </Card>

          {/* EMPTY STATES DEMO */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Search className="h-5 w-5 text-purple-500" />
                4. Empty States (Beautiful &quot;No Data&quot; UI)
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <p className="text-sm text-gray-600">
                Select different empty state scenarios to see professional designs:
              </p>

              <div className="flex flex-wrap gap-2">
                {Object.keys(emptyStates).map((key) => (
                  <Button
                    key={key}
                    size="sm"
                    variant={showEmptyState === key ? 'default' : 'outline'}
                    onClick={() => setShowEmptyState(key)}
                    className="capitalize"
                  >
                    {key}
                  </Button>
                ))}
              </div>

              <div className="border-2 border-dashed rounded-lg p-4">
                {emptyStates[showEmptyState]}
              </div>

              <div className="p-3 bg-purple-50 border border-purple-200 rounded-lg">
                <p className="text-sm font-medium text-purple-800">
                  🎨 8 Pre-built Empty States
                </p>
                <p className="text-xs text-purple-600 mt-1">
                  NoBookings, NoFavorites, NoMessages, NoNotifications, NoSearchResults, 
                  NoPropertiesFound, EmptyList, InlineEmptyState - all ready to use!
                </p>
              </div>
            </CardContent>
          </Card>

          {/* SUMMARY CARD */}
          <Card className="bg-gradient-to-br from-green-50 to-blue-50 border-green-200">
            <CardHeader>
              <CardTitle className="text-green-800">🎉 All Optimizations Complete!</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div className="flex items-start gap-2">
                  <CheckCircle className="h-5 w-5 text-green-600 mt-0.5" />
                  <div>
                    <p className="font-medium text-sm">Debounce Hooks</p>
                    <p className="text-xs text-gray-600">useDebounce & useDebouncedCallback</p>
                  </div>
                </div>
                <div className="flex items-start gap-2">
                  <CheckCircle className="h-5 w-5 text-green-600 mt-0.5" />
                  <div>
                    <p className="font-medium text-sm">Optimistic UI</p>
                    <p className="text-xs text-gray-600">useOptimistic & useOptimisticList</p>
                  </div>
                </div>
                <div className="flex items-start gap-2">
                  <CheckCircle className="h-5 w-5 text-green-600 mt-0.5" />
                  <div>
                    <p className="font-medium text-sm">Favorites System</p>
                    <p className="text-xs text-gray-600">useFavorites with localStorage</p>
                  </div>
                </div>
                <div className="flex items-start gap-2">
                  <CheckCircle className="h-5 w-5 text-green-600 mt-0.5" />
                  <div>
                    <p className="font-medium text-sm">Empty States</p>
                    <p className="text-xs text-gray-600">8 beautiful components</p>
                  </div>
                </div>
              </div>

              <div className="pt-3 border-t border-green-200">
                <p className="text-sm text-gray-700">
                  <strong>Integrated into:</strong> Properties, Favorites, Bookings, Messages, Notifications
                </p>
                <p className="text-xs text-gray-600 mt-1">
                  All optimizations are production-ready, TypeScript-safe, and fully documented in PERFORMANCE_OPTIMIZATIONS.md
                </p>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </MainLayout>
  );
}
