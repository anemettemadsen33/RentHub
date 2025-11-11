'use client';

import { useState } from 'react';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { 
  Heart, 
  Mail, 
  MailOpen, 
  Trash2, 
  Plus,
  Loader2,
  Check,
  X,
  Sparkles
} from 'lucide-react';
import { useOptimisticAction, useOptimisticToggle, useOptimisticListUpdate } from '@/hooks/use-optimistic-actions';
import { useFavorites } from '@/hooks/use-favorites';

interface Todo {
  id: number;
  title: string;
  completed: boolean;
}

export default function OptimisticUIDemoPage() {
  // Demo 1: Favorites (existing hook)
  const { favorites, toggleFavorite, isFavorite, isOptimistic: isFavoriteOptimistic } = useFavorites([1, 3, 5]);

  // Demo 2: Read/Unread Toggle
  const { state: isRead, toggle: toggleRead } = useOptimisticToggle(
    false,
    async (newState) => {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 1000));
      if (Math.random() > 0.8) throw new Error('Server error');
    },
    {
      successMessage: (state) => state ? 'Marked as read' : 'Marked as unread',
      errorMessage: 'Failed to update status'
    }
  );

  // Demo 3: List Operations
  const [todos] = useState<Todo[]>([
    { id: 1, title: 'Learn Optimistic UI', completed: false },
    { id: 2, title: 'Build amazing UX', completed: false },
    { id: 3, title: 'Ship to production', completed: false },
  ]);

  const {
    list: todoList,
    updateItem: updateTodo,
    removeItem: removeTodo,
    addItem: addTodo,
    isOptimistic: isTodoOptimistic
  } = useOptimisticListUpdate<Todo>(todos);

  // Demo 4: Generic Action
  const { execute, isLoading } = useOptimisticAction();
  const [counter, setCounter] = useState(0);

  const handleIncrement = async () => {
    const previousCount = counter;
    
    await execute(
      () => setCounter(prev => prev + 1),
      async () => {
        await new Promise(resolve => setTimeout(resolve, 800));
        if (Math.random() > 0.7) throw new Error('Increment failed');
      },
      () => setCounter(previousCount),
      {
        successMessage: 'Counter incremented!',
        errorMessage: 'Failed to increment counter',
        showToast: true
      }
    );
  };

  const handleToggleTodo = async (todo: Todo) => {
    await updateTodo(
      todo.id,
      { completed: !todo.completed },
      async () => {
        await new Promise(resolve => setTimeout(resolve, 1000));
        if (Math.random() > 0.8) throw new Error('Update failed');
      },
      {
        successMessage: todo.completed ? 'Todo marked as incomplete' : 'Todo completed!',
        errorMessage: 'Failed to update todo'
      }
    );
  };

  const handleDeleteTodo = async (id: number) => {
    await removeTodo(
      id,
      async () => {
        await new Promise(resolve => setTimeout(resolve, 1000));
        if (Math.random() > 0.9) throw new Error('Delete failed');
      },
      {
        successMessage: 'Todo deleted',
        errorMessage: 'Failed to delete todo'
      }
    );
  };

  const handleAddTodo = async () => {
    const newTodo: Todo = {
      id: Date.now(),
      title: 'New awesome task',
      completed: false
    };

    await addTodo(
      newTodo,
      async () => {
        await new Promise(resolve => setTimeout(resolve, 1000));
        if (Math.random() > 0.8) throw new Error('Create failed');
        return newTodo;
      },
      {
        successMessage: 'Todo added!',
        errorMessage: 'Failed to add todo'
      }
    );
  };

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8">
        <div className="max-w-6xl mx-auto space-y-8">
          {/* Header */}
          <div className="text-center space-y-4">
            <div className="flex items-center justify-center gap-2">
              <Sparkles className="h-8 w-8 text-primary" />
              <h1 className="text-4xl font-bold">Optimistic UI Demo</h1>
            </div>
            <p className="text-lg text-gray-600">
              Instant feedback before server confirmation - Professional UX patterns
            </p>
          </div>

          {/* Demo 1: Favorites */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Heart className="h-5 w-5" />
                1. Favorites (Like/Unlike)
              </CardTitle>
              <CardDescription>
                Click hearts to toggle favorites. Updates happen instantly!
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                {[1, 2, 3, 4, 5, 6, 7, 8].map((id) => (
                  <Card key={id} className="relative">
                    <CardContent className="pt-6">
                      <div className="aspect-square bg-gradient-to-br from-primary/20 to-primary/5 rounded-lg flex items-center justify-center mb-3">
                        <span className="text-2xl font-bold text-primary">#{id}</span>
                      </div>
                      <Button
                        variant={isFavorite(id) ? 'default' : 'outline'}
                        size="sm"
                        className="w-full"
                        onClick={() => toggleFavorite(id)}
                      >
                        <Heart
                          className={`h-4 w-4 mr-2 ${isFavorite(id) ? 'fill-current' : ''}`}
                        />
                        {isFavorite(id) ? 'Favorited' : 'Favorite'}
                      </Button>
                      {isFavoriteOptimistic(id) && (
                        <div className="absolute top-2 right-2">
                          <Loader2 className="h-4 w-4 animate-spin text-primary" />
                        </div>
                      )}
                    </CardContent>
                  </Card>
                ))}
              </div>
              <div className="mt-4 p-4 bg-gray-50 rounded-lg">
                <p className="text-sm font-medium">Current Favorites: {favorites.length}</p>
                <p className="text-xs text-gray-600 mt-1">
                  {favorites.join(', ') || 'None'}
                </p>
              </div>
            </CardContent>
          </Card>

          {/* Demo 2: Read/Unread Toggle */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                {isRead ? <MailOpen className="h-5 w-5" /> : <Mail className="h-5 w-5" />}
                2. Read/Unread Toggle
              </CardTitle>
              <CardDescription>
                Toggle read state instantly. Try it - notice there&apos;s no loading delay!
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center justify-between p-4 border rounded-lg">
                <div className="flex items-center gap-3">
                  {isRead ? (
                    <MailOpen className="h-6 w-6 text-green-600" />
                  ) : (
                    <Mail className="h-6 w-6 text-blue-600" />
                  )}
                  <div>
                    <p className="font-medium">Notification Status</p>
                    <p className="text-sm text-gray-600">
                      {isRead ? 'You have read this message' : 'This message is unread'}
                    </p>
                  </div>
                </div>
                <Badge variant={isRead ? 'secondary' : 'default'}>
                  {isRead ? 'Read' : 'Unread'}
                </Badge>
              </div>

              <Button onClick={toggleRead} className="w-full">
                {isRead ? (
                  <>
                    <Mail className="h-4 w-4 mr-2" />
                    Mark as Unread
                  </>
                ) : (
                  <>
                    <MailOpen className="h-4 w-4 mr-2" />
                    Mark as Read
                  </>
                )}
              </Button>

              <div className="p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                <p className="text-blue-900">
                  💡 <strong>Tip:</strong> The UI updates instantly when you click. 
                  Server sync happens in the background. If it fails, it automatically rolls back.
                </p>
              </div>
            </CardContent>
          </Card>

          {/* Demo 3: Todo List Operations */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Check className="h-5 w-5" />
                3. Todo List (Add, Update, Delete)
              </CardTitle>
              <CardDescription>
                Manage todos with instant updates. Complete, delete - all instant!
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                {todoList.map((todo) => (
                  <div
                    key={todo.id}
                    className={`flex items-center gap-3 p-3 border rounded-lg transition-all ${
                      todo.completed ? 'bg-green-50 border-green-200' : 'bg-white'
                    }`}
                  >
                    <Button
                      variant={todo.completed ? 'default' : 'outline'}
                      size="sm"
                      onClick={() => handleToggleTodo(todo)}
                      className="shrink-0"
                    >
                      {todo.completed ? (
                        <Check className="h-4 w-4" />
                      ) : (
                        <div className="h-4 w-4" />
                      )}
                    </Button>

                    <span
                      className={`flex-1 ${
                        todo.completed ? 'line-through text-gray-500' : ''
                      }`}
                    >
                      {todo.title}
                    </span>

                    {isTodoOptimistic(todo.id) && (
                      <Loader2 className="h-4 w-4 animate-spin text-primary" />
                    )}

                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => handleDeleteTodo(todo.id)}
                    >
                      <Trash2 className="h-4 w-4 text-red-600" />
                    </Button>
                  </div>
                ))}
              </div>

              <Button onClick={handleAddTodo} variant="outline" className="w-full">
                <Plus className="h-4 w-4 mr-2" />
                Add New Todo
              </Button>

              <div className="p-3 bg-green-50 border border-green-200 rounded-lg text-sm">
                <p className="text-green-900">
                  ✨ <strong>Notice:</strong> When you complete a todo, it updates instantly.
                  The checkmark appears immediately, no waiting for the server!
                </p>
              </div>
            </CardContent>
          </Card>

          {/* Demo 4: Generic Counter Action */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Sparkles className="h-5 w-5" />
                4. Generic Optimistic Action
              </CardTitle>
              <CardDescription>
                Any custom action with optimistic updates and automatic rollback
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="text-center space-y-4">
                <div className="inline-flex items-center justify-center w-32 h-32 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full">
                  <span className="text-5xl font-bold text-white">{counter}</span>
                </div>

                <Button
                  onClick={handleIncrement}
                  disabled={isLoading}
                  size="lg"
                  className="w-full max-w-md"
                >
                  {isLoading ? (
                    <>
                      <Loader2 className="h-5 w-5 mr-2 animate-spin" />
                      Syncing...
                    </>
                  ) : (
                    <>
                      <Plus className="h-5 w-5 mr-2" />
                      Increment Counter
                    </>
                  )}
                </Button>
              </div>

              <div className="p-3 bg-purple-50 border border-purple-200 rounded-lg text-sm">
                <p className="text-purple-900">
                  🎯 <strong>How it works:</strong> Counter increments instantly when you click.
                  It syncs with the server in the background. If server fails (30% chance),
                  it automatically rolls back with an error message.
                </p>
              </div>
            </CardContent>
          </Card>

          {/* How It Works */}
          <Card className="border-2 border-primary/20">
            <CardHeader>
              <CardTitle>How Optimistic UI Works</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <h3 className="font-semibold flex items-center gap-2">
                    <X className="h-4 w-4 text-red-600" />
                    Without Optimistic UI
                  </h3>
                  <ol className="space-y-1 text-sm text-gray-600">
                    <li>1. User clicks button</li>
                    <li>2. Show loading spinner</li>
                    <li>3. Wait for server (300-500ms)</li>
                    <li>4. Update UI</li>
                    <li className="text-red-600 font-medium">⏱️ Feels slow and laggy</li>
                  </ol>
                </div>

                <div className="space-y-2">
                  <h3 className="font-semibold flex items-center gap-2">
                    <Check className="h-4 w-4 text-green-600" />
                    With Optimistic UI
                  </h3>
                  <ol className="space-y-1 text-sm text-gray-600">
                    <li>1. User clicks button</li>
                    <li>2. Update UI instantly ⚡</li>
                    <li>3. Send server request (background)</li>
                    <li>4. On error: Auto-rollback</li>
                    <li className="text-green-600 font-medium">✨ Feels instant and responsive</li>
                  </ol>
                </div>
              </div>

              <div className="p-4 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg border border-blue-200">
                <h4 className="font-semibold mb-2">Performance Benefits</h4>
                <ul className="text-sm space-y-1 text-gray-700">
                  <li>⚡ <strong>0ms perceived latency</strong> (feels instant)</li>
                  <li>😊 <strong>Better UX</strong> than native apps</li>
                  <li>🔄 <strong>Automatic rollback</strong> on errors</li>
                  <li>📱 <strong>Mobile-app-like</strong> experience</li>
                </ul>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </MainLayout>
  );
}
