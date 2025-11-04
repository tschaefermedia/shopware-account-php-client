# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Development tooling: PHPUnit, PHPStan (level 8), PHP CS Fixer
- CI/CD pipeline with GitHub Actions for automated testing
- Input validation layer with `Validator` utility class
- Comprehensive unit tests for core functionality
- HTTP client timeout and retry configuration
- CHANGELOG and CONTRIBUTING documentation

### Changed
- Cache directory permissions changed from 0755 to 0700 for improved security
- Token cache file permissions set to 0600 (owner read/write only)
- HTTP client configured with 30s timeout and 10s connection timeout
- Added validation to critical API methods

### Security
- Fixed security vulnerability: Token cache now uses secure permissions (0700 for directory, 0600 for file)
- Tokens are no longer world-readable on the filesystem

## [1.0.0] - 2024-01-XX

### Added
- Initial release of Shopware Account API PHP Client
- Complete authentication system with token caching
- Producer endpoint for extension management
- Merchant endpoint for shop management
- Comprehensive data models for all API entities
- Automatic token refresh and validation
- File upload support for extensions and images
- Image resizing capability for extension icons
- Detailed documentation and examples
- MIT License

### Features
- **Authentication**: Email/password login with JWT token management
- **Producer API**: Full extension lifecycle management (create, update, upload, publish)
- **Merchant API**: Shop and license management
- **Caching**: Automatic token caching to reduce login frequency
- **Error Handling**: Comprehensive exception hierarchy
- **Documentation**: Extensive API documentation with real-world examples

[Unreleased]: https://github.com/shopware/account-api-client/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/shopware/account-api-client/releases/tag/v1.0.0
