#!/usr/bin/env node
/**
 * Simple helper that exits when backend ready marker exists.
 * Used by Playwright webServer to wait for backend.
 */
const fs = require('fs');
const path = require('path');
const http = require('http');

const READY_FILE = path.join(__dirname, '..', 'backend', 'storage', 'framework', 'backend-ready.lock');
const HOST = '127.0.0.1';
const PORT = 8000;
const MAX_WAIT = 180_000;

const start = Date.now();

function check() {
  if (fs.existsSync(READY_FILE)) {
    // Double-check health endpoint
    const req = http.request({ host: HOST, port: PORT, path: '/api/health', timeout: 2000 }, res => {
      if ([200, 503].includes(res.statusCode)) {
        console.log('[wait-for-backend] Backend ready!');
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
    console.error('[wait-for-backend] Timeout waiting for backend');
    process.exit(1);
  }
  setTimeout(check, 1000);
}

check();
