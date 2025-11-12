'use client';

import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { logger, createLogger } from '@/lib/logger';
import { useState } from 'react';
import { 
  Bug, 
  Info, 
  AlertTriangle, 
  XCircle, 
  Code2, 
  Timer, 
  FolderTree,
  CheckCircle2,
  Terminal,
} from 'lucide-react';

const demoLogger = createLogger('LoggerDemo');

export default function LoggerDemoPage() {
  const [logs, setLogs] = useState<string[]>([]);

  const addLog = (message: string) => {
    setLogs(prev => [`[${new Date().toLocaleTimeString()}] ${message}`, ...prev].slice(0, 10));
  };

  const handleDebugLog = () => {
    logger.debug('This is a debug message', { component: 'Demo', userId: 123 });
    addLog('DEBUG: Debug message logged (check console in dev mode)');
  };

  const handleInfoLog = () => {
    logger.info('User completed action', { action: 'profile_update', success: true });
    addLog('INFO: Info message logged');
  };

  const handleWarnLog = () => {
    logger.warn('Potential issue detected', { reason: 'slow_response', responseTime: 3500 });
    addLog('WARN: Warning logged');
  };

  const handleErrorLog = () => {
    const mockError = new Error('Something went wrong!');
    logger.error('API request failed', mockError, { endpoint: '/api/users', statusCode: 500 });
    addLog('ERROR: Error logged with stack trace');
  };

  const handleNamespacedLog = () => {
    demoLogger.info('Namespaced logger message', { feature: 'demo' });
    addLog('INFO: Namespaced [LoggerDemo] message');
  };

  const handleTimerDemo = () => {
    const timer = logger.time('expensive-operation');
    
    // Simulate work
    setTimeout(() => {
      timer.end();
      addLog('TIMER: Performance measurement completed');
    }, 1000);
  };

  const handleGroupDemo = () => {
    const group = logger.group('User Data');
    logger.info('Name: John Doe');
    logger.info('Email: john@example.com');
    logger.info('Role: Admin');
    group.end();
    addLog('GROUP: Grouped logs in console');
  };

  const handleTableDemo = () => {
    const users = [
      { id: 1, name: 'Alice', role: 'Admin' },
      { id: 2, name: 'Bob', role: 'User' },
      { id: 3, name: 'Charlie', role: 'Moderator' },
    ];
    logger.table(users);
    addLog('TABLE: Table data logged (check console)');
  };

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-12 max-w-6xl">
        {/* Header */}
        <div className="text-center mb-12">
          <Badge className="mb-4">Logger Service</Badge>
          <h1 className="text-4xl font-bold mb-4">Professional Logging System</h1>
          <p className="text-xl text-muted-foreground max-w-2xl mx-auto">
            Environment-aware logging with multiple levels, structured data, and production safety
          </p>
        </div>

        {/* Features Overview */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <CheckCircle2 className="h-5 w-5 text-green-500" />
              Key Features
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="flex items-start gap-3">
                <Bug className="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" />
                <div>
                  <p className="font-semibold">Environment-Aware</p>
                  <p className="text-sm text-muted-foreground">Debug logs only in development, errors always logged</p>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <Code2 className="h-5 w-5 text-purple-500 mt-0.5 flex-shrink-0" />
                <div>
                  <p className="font-semibold">Structured Logging</p>
                  <p className="text-sm text-muted-foreground">Context objects for better debugging</p>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <FolderTree className="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
                <div>
                  <p className="font-semibold">Namespaced Loggers</p>
                  <p className="text-sm text-muted-foreground">Organize logs by component/feature</p>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <Timer className="h-5 w-5 text-orange-500 mt-0.5 flex-shrink-0" />
                <div>
                  <p className="font-semibold">Performance Timing</p>
                  <p className="text-sm text-muted-foreground">Built-in timer for profiling</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Log Levels Demo */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle>1. Log Levels</CardTitle>
            <CardDescription>
              Different severity levels for different use cases
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <Button 
                variant="outline" 
                className="h-auto flex-col items-start p-4"
                onClick={handleDebugLog}
              >
                <div className="flex items-center gap-2 mb-2">
                  <Bug className="h-4 w-4 text-gray-500" />
                  <span className="font-semibold">DEBUG</span>
                </div>
                <p className="text-sm text-muted-foreground text-left">
                  Detailed info for debugging. Only in development.
                </p>
                <pre className="mt-2 text-xs bg-muted p-2 rounded w-full">
                  logger.debug(&apos;Cache hit&apos;, {'{'} key {'}'})
                </pre>
              </Button>

              <Button 
                variant="outline" 
                className="h-auto flex-col items-start p-4"
                onClick={handleInfoLog}
              >
                <div className="flex items-center gap-2 mb-2">
                  <Info className="h-4 w-4 text-blue-500" />
                  <span className="font-semibold">INFO</span>
                </div>
                <p className="text-sm text-muted-foreground text-left">
                  General informational messages
                </p>
                <pre className="mt-2 text-xs bg-muted p-2 rounded w-full">
                  logger.info(&apos;User logged in&apos;, {'{'} id {'}'})
                </pre>
              </Button>

              <Button 
                variant="outline" 
                className="h-auto flex-col items-start p-4"
                onClick={handleWarnLog}
              >
                <div className="flex items-center gap-2 mb-2">
                  <AlertTriangle className="h-4 w-4 text-yellow-500" />
                  <span className="font-semibold">WARN</span>
                </div>
                <p className="text-sm text-muted-foreground text-left">
                  Warning messages for potential issues
                </p>
                <pre className="mt-2 text-xs bg-muted p-2 rounded w-full">
                  logger.warn(&apos;Slow API&apos;, {'{'} ms: 3500 {'}'})
                </pre>
              </Button>

              <Button 
                variant="outline" 
                className="h-auto flex-col items-start p-4"
                onClick={handleErrorLog}
              >
                <div className="flex items-center gap-2 mb-2">
                  <XCircle className="h-4 w-4 text-red-500" />
                  <span className="font-semibold">ERROR</span>
                </div>
                <p className="text-sm text-muted-foreground text-left">
                  Error messages. Always logged, even in prod.
                </p>
                <pre className="mt-2 text-xs bg-muted p-2 rounded w-full">
                  logger.error(&apos;API failed&apos;, error, {'{'} {'}'})
                </pre>
              </Button>
            </div>
          </CardContent>
        </Card>

        {/* Advanced Features */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle>2. Advanced Features</CardTitle>
            <CardDescription>
              Namespacing, timing, grouping, and more
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-3">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
              <Button onClick={handleNamespacedLog} variant="secondary">
                <FolderTree className="mr-2 h-4 w-4" />
                Namespaced Logger
              </Button>
              <Button onClick={handleTimerDemo} variant="secondary">
                <Timer className="mr-2 h-4 w-4" />
                Performance Timer
              </Button>
              <Button onClick={handleGroupDemo} variant="secondary">
                <Terminal className="mr-2 h-4 w-4" />
                Grouped Logs
              </Button>
              <Button onClick={handleTableDemo} variant="secondary">
                <Code2 className="mr-2 h-4 w-4" />
                Table Data
              </Button>
            </div>
          </CardContent>
        </Card>

        {/* Code Examples */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle>3. Usage Examples</CardTitle>
            <CardDescription>How to use the logger in your code</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div>
              <h4 className="font-semibold mb-2">Basic Usage:</h4>
              <pre className="bg-muted p-4 rounded-lg overflow-x-auto text-sm">
{`import { logger } from '@/lib/logger';

// Simple logging
logger.info('User updated profile');

// With context
logger.error('Payment failed', error, {
  userId: 123,
  amount: 99.99,
  method: 'stripe'
});`}
              </pre>
            </div>

            <div>
              <h4 className="font-semibold mb-2">Namespaced Logger:</h4>
              <pre className="bg-muted p-4 rounded-lg overflow-x-auto text-sm">
{`import { createLogger } from '@/lib/logger';

const authLogger = createLogger('AuthContext');

authLogger.info('User logged in', { email });
// Output: [AuthContext] User logged in`}
              </pre>
            </div>

            <div>
              <h4 className="font-semibold mb-2">Performance Timing:</h4>
              <pre className="bg-muted p-4 rounded-lg overflow-x-auto text-sm">
{`const timer = logger.time('api-request');
await fetchData();
timer.end(); // Logs: ⏱️ api-request took 234ms`}
              </pre>
            </div>
          </CardContent>
        </Card>

        {/* Recent Logs */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Terminal className="h-5 w-5" />
              Recent Activity
            </CardTitle>
            <CardDescription>
              Logs from demo interactions (open DevTools console for full output)
            </CardDescription>
          </CardHeader>
          <CardContent>
            {logs.length === 0 ? (
              <p className="text-sm text-muted-foreground text-center py-8">
                Click buttons above to generate logs
              </p>
            ) : (
              <div className="space-y-1 font-mono text-xs bg-black text-green-400 p-4 rounded-lg max-h-64 overflow-y-auto">
                {logs.map((log, index) => (
                  <div key={index}>{log}</div>
                ))}
              </div>
            )}
          </CardContent>
        </Card>

        {/* Migration Guide */}
        <Card className="mt-8">
          <CardHeader>
            <CardTitle>Migration from console.log</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <p className="text-sm font-semibold text-red-600 mb-2">❌ Before (Bad):</p>
                  <pre className="bg-red-50 dark:bg-red-950 p-3 rounded text-xs">
{`console.log('User logged in');
console.error('Error:', error);
console.warn('Slow response');`}
                  </pre>
                </div>
                <div>
                  <p className="text-sm font-semibold text-green-600 mb-2">✅ After (Good):</p>
                  <pre className="bg-green-50 dark:bg-green-950 p-3 rounded text-xs">
{`logger.info('User logged in', { id });
logger.error('API failed', error);
logger.warn('Slow response', { ms });`}
                  </pre>
                </div>
              </div>

              <div className="bg-blue-50 dark:bg-blue-950 p-4 rounded-lg">
                <p className="text-sm font-semibold mb-2">💡 Benefits:</p>
                <ul className="text-sm space-y-1 list-disc list-inside text-muted-foreground">
                  <li>No logs in production by default (only errors)</li>
                  <li>Structured data for better debugging</li>
                  <li>Easy to integrate with error tracking services</li>
                  <li>Better performance (no console spam)</li>
                </ul>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}
