# Contributing to RentHub

Thank you for your interest in contributing to RentHub! This document provides guidelines and instructions for contributing to the project.

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/YOUR_USERNAME/RentHub.git`
3. Set up the development environment by following the [README.md](README.md)
4. Create a new branch for your feature: `git checkout -b feature/your-feature-name`

## Development Workflow

### Before Making Changes

1. Pull the latest changes from the main branch
2. Run tests to ensure everything is working: `make test`
3. Create a feature branch with a descriptive name

### Making Changes

1. Follow the existing code style and conventions
2. Write clear, concise commit messages
3. Add tests for new features or bug fixes
4. Update documentation as needed

### Backend Development (Laravel)

- Follow [PSR-12 coding standards](https://www.php-fig.org/psr/psr-12/)
- Use Laravel Pint for code formatting: `make lint-backend`
- Write tests in the `backend/tests` directory
- Run backend tests: `cd backend && php artisan test`

### Frontend Development (Next.js)

- Follow the existing React/TypeScript patterns
- Use ESLint for linting: `make lint-frontend`
- Write tests for new components
- Ensure the build passes: `make build-frontend`

## Code Review Process

1. Push your changes to your fork
2. Create a Pull Request against the main branch
3. Fill out the Pull Request template with all relevant information
4. Wait for code review and address any feedback
5. Once approved, your changes will be merged

## Commit Message Guidelines

Follow these conventions for commit messages:

```
type(scope): subject

body (optional)

footer (optional)
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

**Examples:**
```
feat(auth): add social login with Google
fix(booking): resolve date validation issue
docs(readme): update installation instructions
```

## Testing

- All new features must include tests
- Maintain or improve the current test coverage
- Run tests before submitting a PR: `make test`

## Documentation

- Update the README.md if you change setup or usage instructions
- Document new APIs and endpoints
- Add comments for complex logic

## Code Style

### Backend (PHP/Laravel)
- Use PHP 8.2+ features appropriately
- Follow Laravel best practices
- Use type hints for parameters and return types
- Keep controllers thin, use service classes for business logic

### Frontend (TypeScript/React)
- Use TypeScript for type safety
- Follow React best practices and hooks patterns
- Use functional components
- Keep components small and focused

## Questions or Issues?

If you have questions or run into issues:
1. Check existing issues on GitHub
2. Create a new issue with a clear description
3. Join discussions in pull requests

Thank you for contributing to RentHub! 🎉
