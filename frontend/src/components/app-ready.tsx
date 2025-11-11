"use client";

import { useEffect, useState } from 'react';

/**
 * AppReady places a hidden marker in the DOM after client hydration is complete,
 * providing a deterministic signal for E2E tests to start interacting with the UI.
 */
export function AppReady() {
  const [ready, setReady] = useState(false);
  useEffect(() => {
    setReady(true);
    // Also add an attribute on <html> for optional CSS/testing hooks
    if (typeof document !== 'undefined') {
      document.documentElement.setAttribute('data-app-ready', 'true');
    }
  }, []);
  return (
    <div
      id="app-ready"
      data-testid={ready ? 'hydration-ready' : undefined}
      aria-hidden="true"
      style={{ display: 'none' }}
    />
  );
}
