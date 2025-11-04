# Contributing to Shopware Account API Client

Thank you for your interest in contributing to the Shopware Account API Client! This document provides guidelines and instructions for contributing to this project.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Making Changes](#making-changes)
- [Testing](#testing)
- [Code Style](#code-style)
- [Submitting Changes](#submitting-changes)
- [Reporting Bugs](#reporting-bugs)
- [Feature Requests](#feature-requests)

## Code of Conduct

By participating in this project, you agree to maintain a respectful and collaborative environment. Please be kind and considerate in all interactions.

## Getting Started

1. Fork the repository on GitHub
2. Clone your fork locally:
   ```bash
   git clone https://github.com/your-username/shopware-account-php-client.git
   cd shopware-account-php-client
   ```
3. Add the upstream repository:
   ```bash
   git remote add upstream https://github.com/shopware/account-api-client.git
   ```

## Development Setup

### Prerequisites

- PHP 8.1 or higher
- Composer 2.x
- GD extension (for image processing)

### Install Dependencies

```bash
composer install
```

### Development Tools

This project uses several tools to maintain code quality:

- **PHPUnit**: Unit and integration testing
- **PHPStan**: Static analysis (level 8)
- **PHP CS Fixer**: Code style enforcement (PSR-12)

## Making Changes

### Branch Naming

Create a descriptive branch name:
- `feature/add-xyz` for new features
- `fix/issue-123` for bug fixes
- `docs/update-readme` for documentation updates
- `refactor/improve-xyz` for refactoring

### Commit Messages

Write clear, concise commit messages:

```
<type>: <subject>

<body>

<footer>
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

Example:
```
feat: Add validation for extension upload parameters

- Added Validator::fileReadable() check for ZIP files
- Added Validator::positive() for extension and binary IDs
- Improved error messages for invalid inputs

Closes #123
```

## Testing

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
composer test:coverage

# Run specific test suite
vendor/bin/phpunit tests/Unit
```

### Writing Tests

- Write unit tests for all new functionality
- Aim for 80%+ code coverage
- Use descriptive test method names: `testMethodName_Scenario_ExpectedResult()`
- Follow the Arrange-Act-Assert pattern

Example:

```php
public function testValidator_EmptyString_ThrowsException(): void
{
    // Arrange
    $emptyString = '';

    // Act & Assert
    $this->expectException(InvalidArgumentException::class);
    Validator::notEmpty($emptyString, 'Field');
}
```

## Code Style

### Running Code Style Checks

```bash
# Check code style
composer cs:check

# Fix code style issues
composer cs:fix
```

### Code Standards

- Follow PSR-12 coding standards
- Use strict types: `declare(strict_types=1);`
- Use type hints for all parameters and return types
- Write PHPDoc blocks for public methods
- Keep methods focused and small (prefer <50 lines)
- Use meaningful variable and method names

### Example

```php
<?php

declare(strict_types=1);

namespace Shopware\AccountApi;

/**
 * Example class demonstrating code style
 */
class Example
{
    /**
     * Calculate the sum of two numbers
     *
     * @throws InvalidArgumentException When values are invalid
     */
    public function add(int $a, int $b): int
    {
        Validator::notNull($a, 'First number');
        Validator::notNull($b, 'Second number');

        return $a + $b;
    }
}
```

## Static Analysis

Run PHPStan to catch potential issues:

```bash
# Run static analysis
composer analyse
```

Fix any issues reported before submitting your changes.

## Submitting Changes

### Before Submitting

Run the complete quality check:

```bash
composer check
```

This runs:
1. Code style checks
2. Static analysis
3. All tests

### Pull Request Process

1. Update your branch with the latest upstream changes:
   ```bash
   git fetch upstream
   git rebase upstream/main
   ```

2. Push your changes to your fork:
   ```bash
   git push origin your-branch-name
   ```

3. Create a Pull Request on GitHub with:
   - Clear title describing the change
   - Description of what changed and why
   - Link to related issues (if applicable)
   - Screenshots (for UI changes)

4. Ensure CI checks pass (GitHub Actions will run automatically)

5. Respond to review feedback promptly

### Pull Request Guidelines

- Keep PRs focused on a single concern
- Update documentation if needed
- Add tests for new functionality
- Ensure backward compatibility unless explicitly breaking
- Update CHANGELOG.md for notable changes

## Reporting Bugs

### Before Reporting

- Check existing issues to avoid duplicates
- Verify the bug exists in the latest version
- Collect relevant information (PHP version, OS, stack trace, etc.)

### Bug Report Template

```markdown
## Description
Brief description of the bug

## Steps to Reproduce
1. Step one
2. Step two
3. Step three

## Expected Behavior
What should happen

## Actual Behavior
What actually happens

## Environment
- PHP Version: 8.2
- OS: Ubuntu 22.04
- Package Version: 1.0.0

## Additional Context
Any other relevant information
```

## Feature Requests

We welcome feature requests! Please provide:

- Clear use case for the feature
- Expected behavior and API design
- Examples of how it would be used
- Whether you're willing to implement it

## Questions?

If you have questions about contributing, please:

1. Check the README and documentation
2. Search existing issues
3. Open a new issue with the "question" label

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

Thank you for contributing to the Shopware Account API Client! 🎉
