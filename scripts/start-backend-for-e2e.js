#!/usr/bin/env node
/**
 * Simple backend launcher for Playwright E2E.
 * Spawns `php artisan serve` and waits until port 8000 responds.
 */
const { spawn, spawnSync, execSync } = require('child_process');
const fs = require('fs');
const http = require('http');
const path = require('path');

const BACKEND_DIR = path.join(__dirname, '..', 'backend');
const HOST = '127.0.0.1';
const PORT = 8000;
const MAX_WAIT_MS = 120_000;
const CHECK_INTERVAL = 1_000;
// Use temp sqlite file (not :memory:) so migrations persist across requests in php artisan serve
const E2E_SQLITE = path.join(BACKEND_DIR, 'database', 'e2e-test.sqlite');
const READY_FILE = path.join(BACKEND_DIR, 'storage', 'framework', 'backend-ready.lock');
const E2E_ENV = {
  APP_ENV: 'testing',
  APP_DEBUG: 'false',
  DB_CONNECTION: process.env.DB_CONNECTION || 'sqlite',
  DB_DATABASE: process.env.DB_DATABASE || E2E_SQLITE,
  CACHE_DRIVER: 'array',
  QUEUE_CONNECTION: 'sync',
  SESSION_DRIVER: 'array',
  LOG_CHANNEL: 'stack',
};

function resolvePhp() {
  const laragonPhp = 'C:\\laragon\\bin\\php\\php.exe';
  const candidates = [
    process.env.PHP_PATH,
    fs.existsSync(laragonPhp) ? laragonPhp : undefined,
    'php',
    'php.exe',
  ].filter(Boolean);
  for (const cmd of candidates) {
    try {
      const r = spawnSync(cmd, ['-v'], { stdio: 'ignore' });
      if (r.status === 0) return cmd;
    } catch {}
  }
  // Try Windows where.exe
  try {
    const r = spawnSync('where', ['php'], { stdio: 'pipe' });
    if (r.status === 0) return 'php';
  } catch {}
  return null;
}

function waitForServer() {
  return new Promise((resolve, reject) => {
    const start = Date.now();
    const attempt = () => {
      const req = http.request({ host: HOST, port: PORT, path: '/api/health', timeout: 2000 }, res => {
        if ([200,503].includes(res.statusCode)) {
          resolve();
        } else {
          retry();
        }
      });
      req.on('error', retry);
      req.end();
    };
    const retry = () => {
      if (Date.now() - start > MAX_WAIT_MS) {
        reject(new Error('Backend did not start within timeout'));
      } else {
        setTimeout(attempt, CHECK_INTERVAL);
      }
    };
    attempt();
  });
}

const php = resolvePhp();
if (!php) {
  console.error('[E2E] Could not find PHP in PATH. Set PHP_PATH env to php executable.');
  process.exit(1);
}

console.log(`[E2E] Using PHP binary: ${php}`);

// Pre-commands: config clear and migrate (best-effort)
function runArtisan(args) {
  return new Promise((resolve) => {
    const p = spawn(php, ['artisan', ...args], {
      cwd: BACKEND_DIR,
      stdio: 'inherit',
      env: { ...process.env, ...E2E_ENV },
    });
    p.on('exit', () => resolve(undefined));
    p.on('error', () => resolve(undefined));
  });
}

(async () => {
  // Ensure we have an APP_KEY for encryption (required for cookies/CSRF even if unused)
  try {
    if (!process.env.APP_KEY) {
      const key = spawnSync(resolvePhp() || 'php', ['artisan', 'key:generate', '--show'], { cwd: BACKEND_DIR, encoding: 'utf8' });
      if (key.status === 0) {
        const appKey = String(key.stdout || '').trim();
        if (appKey) {
          E2E_ENV.APP_KEY = appKey.startsWith('base64:') ? appKey : `base64:${appKey}`;
          console.log('[E2E] Generated APP_KEY');
        }
      }
    }
  } catch (e) {
    console.warn('[E2E] Could not auto-generate APP_KEY (continuing):', e?.message || e);
  }
  // Clean up old test DB and ready marker
  if (fs.existsSync(E2E_SQLITE)) {
    console.log('[E2E] Removing old test database...');
    fs.unlinkSync(E2E_SQLITE);
  }
  if (fs.existsSync(READY_FILE)) {
    fs.unlinkSync(READY_FILE);
  }
  // Create empty sqlite file
  console.log('[E2E] Creating empty sqlite database...');
  fs.writeFileSync(E2E_SQLITE, '');
  
  console.log('[E2E] Running: php artisan config:clear');
  await runArtisan(['config:clear']);
  console.log('[E2E] Running: php artisan migrate:fresh --force (fresh sqlite with seed)');
  await runArtisan(['migrate:fresh', '--force', '--seed', '--no-interaction']);
  console.log('[E2E] Database ready at:', E2E_SQLITE);

  console.log('[E2E] Starting backend (php artisan serve)...');
  const child = spawn(php, ['artisan', 'serve', `--host=${HOST}`, `--port=${PORT}`], {
    cwd: BACKEND_DIR,
    stdio: ['ignore', 'pipe', 'pipe'], // Capture stdout/stderr to detect "Server running"
    env: { ...process.env, ...E2E_ENV },
  });

  // Forward output but also check for ready signal
  let serverReady = false;
  child.stdout.on('data', (data) => {
    const line = data.toString();
    process.stdout.write(line);
    if (line.includes('Server running')) {
      serverReady = true;
    }
  });
  child.stderr.on('data', (data) => {
    process.stderr.write(data);
  });

  child.on('exit', code => {
    console.error(`[E2E] Backend process exited early with code ${code}`);
    process.exit(code || 1);
  });

  await waitForServer()
    .then(() => {
      console.log('[E2E] Backend ready on http://'+HOST+':'+PORT);
      // Create ready marker file so Playwright can detect readiness via file check
      fs.writeFileSync(READY_FILE, new Date().toISOString());
    })
    .catch(err => {
      console.error('[E2E] Backend failed to start:', err.message);
      process.exit(1);
    });
})();
