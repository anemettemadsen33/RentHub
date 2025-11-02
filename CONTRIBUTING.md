# Contributing to RentHub

Thank you for your interest in contributing to RentHub! This document provides guidelines and instructions for contributing.

## Getting Started

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 20+
- NPM
- Git

### Initial Setup

1. Clone the repository:
```bash
git clone <repository-url>
cd RentHub
```

2. Setup Backend:
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```

3. Setup Frontend (in a new terminal):
```bash
cd frontend
npm install
cp .env.example .env.local
npm run dev
```

## Development Workflow

### Branch Strategy

- `main` - Production branch, always deployable
- `develop` - Development branch for integration
- `feature/*` - New features
- `bugfix/*` - Bug fixes
- `hotfix/*` - Emergency production fixes

### Creating a Feature

1. Create a new branch from `develop`:
```bash
git checkout develop
git pull origin develop
git checkout -b feature/your-feature-name
```

2. Make your changes following the coding standards

3. Commit your changes:
```bash
git add .
git commit -m "feat: add your feature description"
```

4. Push your branch:
```bash
git push origin feature/your-feature-name
```

5. Create a Pull Request to `develop`

### Commit Message Convention

We follow [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation changes
- `style:` - Code style changes (formatting, etc.)
- `refactor:` - Code refactoring
- `test:` - Adding or updating tests
- `chore:` - Maintenance tasks

Examples:
```
feat: add user authentication
fix: resolve login redirect issue
docs: update deployment guide
refactor: optimize database queries
```

## Coding Standards

### Backend (Laravel)

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standard
- Use type hints for function parameters and return types
- Write descriptive variable and function names
- Add PHPDoc comments for complex functions
- Use Laravel best practices and conventions

Run Laravel Pint before committing:
```bash
cd backend
./vendor/bin/pint
```

### Frontend (Next.js/TypeScript)

- Follow TypeScript best practices
- Use functional components with hooks
- Write meaningful component and variable names
- Add JSDoc comments for complex functions
- Use proper TypeScript types (avoid `any`)
- Keep components small and focused

Run ESLint before committing:
```bash
cd frontend
npm run lint
```

## Testing

### Backend Tests

```bash
cd backend
php artisan test
```

### Frontend Tests

```bash
cd frontend
npm run test
```

## Pull Request Process

1. Update documentation if needed
2. Ensure all tests pass
3. Update the CHANGELOG.md if applicable
4. Fill out the PR template completely
5. Request review from maintainers
6. Address review feedback
7. Squash commits if requested
8. Wait for approval and merge

## Pull Request Checklist

- [ ] Code follows project style guidelines
- [ ] Self-review completed
- [ ] Comments added to complex code
- [ ] Documentation updated
- [ ] No new warnings generated
- [ ] Tests added/updated
- [ ] All tests passing
- [ ] Branch is up to date with base branch

## Code Review Guidelines

### For Authors
- Keep PRs focused and reasonably sized
- Provide context in PR description
- Respond to feedback promptly
- Don't take feedback personally

### For Reviewers
- Be constructive and specific
- Explain the "why" behind suggestions
- Approve when ready, don't nitpick
- Be respectful and professional

## Reporting Issues

When reporting issues, please include:

1. Clear description of the problem
2. Steps to reproduce
3. Expected vs actual behavior
4. Environment details (OS, PHP version, Node version, etc.)
5. Screenshots if applicable
6. Error messages or logs

## Questions?

If you have questions, please:
- Check existing documentation
- Search closed issues
- Open a new issue with the "question" label

## License

By contributing, you agree that your contributions will be licensed under the same license as the project.
