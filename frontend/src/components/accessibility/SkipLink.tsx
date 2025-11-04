import React from 'react';

export const SkipLink: React.FC = () => {
  return (
    <a 
      href="#main-content" 
      className="skip-link"
      tabIndex={0}
    >
      Skip to main content
    </a>
  );
};
