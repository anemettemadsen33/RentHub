/**
 * Professional Logger Service for RentHub
 * 
 * Features:
 * - Environment-aware logging (dev/prod)
 * - Multiple log levels (debug, info, warn, error)
 * - Structured logging with context
 * - Color-coded console output in dev
 * - Optional remote error tracking integration
 * - Performance timing utilities
 * - TypeScript type safety
 * 
 * Usage:
 * ```typescript
 * import { logger } from '@/lib/logger';
 * 
 * logger.info('User logged in', { userId: 123 });
 * logger.error('API request failed', { error, endpoint: '/api/users' });
 * logger.debug('Cache hit', { key: 'user-profile' });
 * 
 * // Performance timing
 * const timer = logger.time('api-request');
 * await fetchData();
 * timer.end(); // Logs elapsed time
 * ```
 */

export enum LogLevel {
  DEBUG = 0,
  INFO = 1,
  WARN = 2,
  ERROR = 3,
  NONE = 4,
}

interface LogContext {
  [key: string]: unknown;
}

interface LoggerConfig {
  level: LogLevel;
  enableInProduction: boolean;
  enableRemoteLogging: boolean;
  remoteEndpoint?: string;
}

class Logger {
  private config: LoggerConfig;
  private isDevelopment: boolean;

  constructor() {
    this.isDevelopment = process.env.NODE_ENV === 'development';
    this.config = {
      level: this.isDevelopment ? LogLevel.DEBUG : LogLevel.WARN,
      enableInProduction: false,
      enableRemoteLogging: false,
      remoteEndpoint: process.env.NEXT_PUBLIC_LOG_ENDPOINT,
    };
  }

  /**
   * Configure logger behavior
   */
  configure(config: Partial<LoggerConfig>): void {
    this.config = { ...this.config, ...config };
  }

  /**
   * Check if logging is enabled for this level
   */
  private shouldLog(level: LogLevel): boolean {
    // In production, only log if explicitly enabled or level is ERROR
    if (!this.isDevelopment) {
      return this.config.enableInProduction || level >= LogLevel.ERROR;
    }
    return level >= this.config.level;
  }

  /**
   * Format log message with timestamp and context
   */
  private formatMessage(level: string, message: string, context?: LogContext): string {
    const timestamp = new Date().toISOString();
    const contextStr = context ? ` | ${JSON.stringify(context)}` : '';
    return `[${timestamp}] [${level}] ${message}${contextStr}`;
  }

  /**
   * Get console color for log level (dev only)
   */
  private getColor(level: LogLevel): string {
    switch (level) {
      case LogLevel.DEBUG:
        return 'color: #9CA3AF'; // Gray
      case LogLevel.INFO:
        return 'color: #3B82F6'; // Blue
      case LogLevel.WARN:
        return 'color: #F59E0B'; // Orange
      case LogLevel.ERROR:
        return 'color: #EF4444'; // Red
      default:
        return '';
    }
  }

  /**
   * Send log to remote endpoint (optional)
   */
  private async sendToRemote(level: string, message: string, context?: LogContext): Promise<void> {
    if (!this.config.enableRemoteLogging || !this.config.remoteEndpoint) {
      return;
    }

    try {
      await fetch(this.config.remoteEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          level,
          message,
          context,
          timestamp: new Date().toISOString(),
          userAgent: typeof window !== 'undefined' ? window.navigator.userAgent : 'server',
          url: typeof window !== 'undefined' ? window.location.href : 'server',
        }),
      });
    } catch (error) {
      // Fail silently - don't log errors about logging
    }
  }

  /**
   * Core logging method
   */
  private log(
    level: LogLevel,
    levelName: string,
    message: string,
    context?: LogContext,
    consoleMethod: 'log' | 'info' | 'warn' | 'error' = 'log'
  ): void {
    if (!this.shouldLog(level)) {
      return;
    }

    const formattedMessage = this.formatMessage(levelName, message, context);

    // Console output in development
    if (this.isDevelopment) {
      const color = this.getColor(level);
      if (context) {
        console[consoleMethod](`%c${message}`, color, context);
      } else {
        console[consoleMethod](`%c${message}`, color);
      }
    } else if (level >= LogLevel.ERROR) {
      // In production, only show errors
      console.error(formattedMessage);
    }

    // Send to remote logging service
    if (level >= LogLevel.ERROR) {
      this.sendToRemote(levelName, message, context);
    }
  }

  /**
   * Debug level - detailed information for debugging
   * Only shown in development
   */
  debug(message: string, context?: LogContext): void {
    this.log(LogLevel.DEBUG, 'DEBUG', message, context, 'log');
  }

  /**
   * Info level - general informational messages
   */
  info(message: string, context?: LogContext): void {
    this.log(LogLevel.INFO, 'INFO', message, context, 'info');
  }

  /**
   * Warn level - warning messages
   */
  warn(message: string, context?: LogContext): void {
    this.log(LogLevel.WARN, 'WARN', message, context, 'warn');
  }

  /**
   * Error level - error messages
   * Always logged, even in production
   */
  error(message: string, error?: Error | unknown, context?: LogContext): void {
    const errorContext: LogContext = {
      ...context,
      error: error instanceof Error ? {
        message: error.message,
        stack: error.stack,
        name: error.name,
      } : error,
    };

    this.log(LogLevel.ERROR, 'ERROR', message, errorContext, 'error');
  }

  /**
   * Performance timing utility
   * 
   * @example
   * const timer = logger.time('fetch-users');
   * await fetchUsers();
   * timer.end(); // Logs: "⏱️ fetch-users took 234ms"
   */
  time(label: string): { end: () => void } {
    const start = performance.now();
    
    return {
      end: () => {
        const duration = performance.now() - start;
        this.debug(`⏱️ ${label} took ${duration.toFixed(2)}ms`);
      },
    };
  }

  /**
   * Group related logs together (dev only)
   */
  group(label: string): { end: () => void } {
    if (this.isDevelopment) {
      console.group(label);
    }

    return {
      end: () => {
        if (this.isDevelopment) {
          console.groupEnd();
        }
      },
    };
  }

  /**
   * Log table data (dev only)
   */
  table(data: unknown): void {
    if (this.isDevelopment) {
      console.table(data);
    }
  }

  /**
   * Clear console (dev only)
   */
  clear(): void {
    if (this.isDevelopment) {
      console.clear();
    }
  }
}

// Export singleton instance
export const logger = new Logger();

// Export factory for creating namespaced loggers
export function createLogger(namespace: string) {
  return {
    debug: (message: string, context?: LogContext) => 
      logger.debug(`[${namespace}] ${message}`, context),
    info: (message: string, context?: LogContext) => 
      logger.info(`[${namespace}] ${message}`, context),
    warn: (message: string, context?: LogContext) => 
      logger.warn(`[${namespace}] ${message}`, context),
    error: (message: string, error?: Error | unknown, context?: LogContext) => 
      logger.error(`[${namespace}] ${message}`, error, context),
    time: (label: string) => logger.time(`[${namespace}] ${label}`),
    group: (label: string) => logger.group(`[${namespace}] ${label}`),
  };
}

// Export for testing/utilities that need verbose logging
export const devLogger = {
  /**
   * Always log in development, never in production
   */
  log: (...args: unknown[]) => {
    if (process.env.NODE_ENV === 'development') {
      console.log(...args);
    }
  },
  /**
   * Always log tables in development
   */
  table: (data: unknown) => {
    if (process.env.NODE_ENV === 'development') {
      console.table(data);
    }
  },
};
