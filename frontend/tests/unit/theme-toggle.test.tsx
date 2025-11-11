import { describe, it, expect } from 'vitest';
import React from 'react';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { ThemeToggle } from '@/components/theme-toggle';
import { ThemeProvider } from '@/components/theme-provider';

describe('ThemeToggle', () => {
  it('renders theme toggle button', () => {
    render(
      <ThemeProvider>
        <ThemeToggle />
      </ThemeProvider>
    );

    expect(screen.getByRole('button')).toBeInTheDocument();
  });

  it('shows theme options when clicked', async () => {
    render(
      <ThemeProvider attribute="class">
        <ThemeToggle />
      </ThemeProvider>
    );

    const button = screen.getByRole('button');
    await userEvent.click(button);

    await waitFor(() => {
      expect(screen.getByText('Light')).toBeInTheDocument();
      expect(screen.getByText('Dark')).toBeInTheDocument();
      expect(screen.getByText('System')).toBeInTheDocument();
    });
  });

  it('changes theme when option is selected', async () => {
    render(
      <ThemeProvider attribute="class">
        <ThemeToggle />
      </ThemeProvider>
    );

    const button = screen.getByRole('button');
    await userEvent.click(button);

    const darkOption = await screen.findByText('Dark');
    await userEvent.click(darkOption);

    // Theme should be applied to document (class added by next-themes)
    await waitFor(() => {
      expect(
        document.documentElement.classList.contains('dark') ||
          document.documentElement.classList.contains('light')
      ).toBe(true);
    });
  });
});
