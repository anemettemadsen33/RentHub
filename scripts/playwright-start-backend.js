#!/usr/bin/env node
/**
 * Playwright webServer wrapper: Starts backend in background, waits for ready, then exits.
 * The backend process continues running independently.
 */
const { spawn } = require('child_process');
const fs = require('fs');
const path = require('path');
const http = require('http');

const BACKEND_DIR = path.join(__dirname, '..', 'backend');
const HOST = '127.0.0.1';
const PORT = 8000;
const E2E_SQLITE = path.join(BACKEND_DIR, 'database', 'e2e-test.sqlite');
const READY_FILE = path.join(BACKEND_DIR, 'storage', 'framework', 'backend-ready.lock');
const MAX_WAIT = 180_000;

const E2E_ENV = {
  APP_ENV: 'testing',
  APP_DEBUG: 'false',
  DB_CONNECTION: 'sqlite',
  DB_DATABASE: E2E_SQLITE,
  CACHE_DRIVER: 'array',
  QUEUE_CONNECTION: 'sync',
  SESSION_DRIVER: 'array',
  LOG_CHANNEL: 'stack',
};

// Start backend in detached mode so it survives this script's exit
console.log('[Playwright] Starting backend...');
const child = spawn('node', [path.join(__dirname, 'start-backend-for-e2e.js')], {
  cwd: __dirname,
  stdio: 'ignore',
  detached: true,
  env: { ...process.env, ...E2E_ENV },
});
child.unref(); // Allow parent to exit independently

// Wait for ready file + health check
const start = Date.now();
function check() {
  if (fs.existsSync(READY_FILE)) {
    const req = http.request({ host: HOST, port: PORT, path: '/api/health', timeout: 2000 }, res => {
      if ([200, 503].includes(res.statusCode)) {
        console.log('[Playwright] Backend ready!');
        process.exit(0);
      } else {
        retry();
      }
    });
    req.on('error', retry);
    req.end();
  } else {
    retry();
  }
}

function retry() {
  if (Date.now() - start > MAX_WAIT) {
    console.error('[Playwright] Timeout waiting for backend readiness');
    process.exit(1);
  }
  setTimeout(check, 1000);
}

check();
