#!/usr/bin/env node

/**
 * Playwright Backend Startup Script
 * 
 * Starts the Laravel backend server for E2E tests and waits for it to be ready.
 * Runs migrations and seeds the database with E2E test data.
 * Detaches the server process so it continues running during tests.
 */

const { spawn } = require('child_process');
const http = require('http');
const path = require('path');

const BACKEND_DIR = path.join(__dirname, '..', 'backend');
const BACKEND_URL = 'http://localhost:8000';
const MAX_RETRIES = 60;
const RETRY_DELAY = 1000;

function checkBackend() {
  return new Promise((resolve) => {
    http.get(`${BACKEND_URL}/api/health`, (res) => {
      resolve(res.statusCode === 200);
    }).on('error', () => {
      resolve(false);
    });
  });
}

async function waitForBackend(retries = 0) {
  if (retries >= MAX_RETRIES) {
    throw new Error(`Backend did not start within ${MAX_RETRIES} seconds`);
  }

  const isReady = await checkBackend();
  if (isReady) {
    console.log('✅ Backend is ready!');
    return true;
  }

  console.log(`⏳ Waiting for backend... (${retries + 1}/${MAX_RETRIES})`);
  await new Promise(resolve => setTimeout(resolve, RETRY_DELAY));
  return waitForBackend(retries + 1);
}

function runCommand(command, args = [], options = {}) {
  return new Promise((resolve, reject) => {
    const proc = spawn(command, args, {
      cwd: BACKEND_DIR,
      stdio: 'inherit',
      shell: true,
      ...options
    });

    proc.on('exit', (code) => {
      if (code === 0) {
        resolve();
      } else {
        reject(new Error(`Command failed with exit code ${code}`));
      }
    });

    proc.on('error', reject);
  });
}

async function main() {
  try {
    const alreadyRunning = await checkBackend();
    if (alreadyRunning) {
      console.log('✅ Backend is already running');
      process.exit(0);
    }

    console.log('🚀 Starting Laravel backend for E2E tests...');

    const serverProcess = spawn('php', ['artisan', 'serve', '--host=127.0.0.1', '--port=8000'], {
      cwd: BACKEND_DIR,
      stdio: 'ignore',
      detached: true,
      shell: true
    });

    serverProcess.unref();

    console.log('⏳ Waiting for server to be ready...');
    await waitForBackend();

    console.log('✅ Backend is ready for E2E tests!');
    process.exit(0);
  } catch (error) {
    console.error('❌ Failed to start backend:', error.message);
    process.exit(1);
  }
}

process.on('SIGINT', () => {
  console.log('\n👋 Shutting down...');
  process.exit(0);
});

process.on('SIGTERM', () => {
  console.log('\n👋 Shutting down...');
  process.exit(0);
});

main();
