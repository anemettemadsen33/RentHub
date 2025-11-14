import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import { EmptyState } from '@/components/empty-state';

describe('EmptyState', () => {
  it('renders with title and description', () => {
    render(
      <EmptyState
        title="No results found"
        description="Try adjusting your search filters"
      />
    );

    expect(screen.getByText('No results found')).toBeInTheDocument();
    expect(screen.getByText('Try adjusting your search filters')).toBeInTheDocument();
  });

  it('renders with custom icon', () => {
    const CustomIcon = () => <div data-testid="custom-icon">Icon</div>;
    
    render(
      <EmptyState
        title="Empty"
        description="No data"
        icon={<CustomIcon />}
      />
    );

    expect(screen.getByTestId('custom-icon')).toBeInTheDocument();
  });

  it('renders action button when provided', () => {
    const mockAction = { label: 'Add Item', onClick: () => {} };
    
    render(
      <EmptyState
        title="Empty"
        description="No items"
        action={mockAction}
      />
    );

    expect(screen.getByRole('button', { name: 'Add Item' })).toBeInTheDocument();
  });

  it('does not render action button when not provided', () => {
    render(
      <EmptyState
        title="Empty"
        description="No items"
      />
    );

    expect(screen.queryByRole('button')).not.toBeInTheDocument();
  });

  it('applies custom className', () => {
    const { container } = render(
      <EmptyState
        title="Empty"
        description="No items"
        className="custom-class"
      />
    );

    expect(container.firstChild).toHaveClass('custom-class');
  });
});
