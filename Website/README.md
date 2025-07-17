# Website Module for Laravel School Management System

A comprehensive, modern website module for Laravel-based school management systems, providing a complete public-facing website with blog, events, gallery, staff profiles, and advanced features like SEO optimization, analytics, and Google OAuth integration.

## 🚀 Features

### Core Features
- **Dynamic Page Management** - Create and manage website pages with SEO optimization
- **Blog System** - Full-featured blog with categories, comments, and social sharing
- **Event Management** - Calendar-based events with registration capabilities
- **Gallery System** - Photo albums with image optimization and lightbox viewing
- **Staff Profiles** - Complete staff directory with detailed profiles
- **Contact Forms** - Multiple contact forms with spam protection
- **Newsletter Integration** - Subscription management with email campaigns

### Advanced Features
- **SEO Optimization** - Comprehensive SEO with meta tags, Open Graph, Twitter Cards, and structured data
- **Analytics Integration** - Built-in analytics with Google Analytics support
- **Social Media Integration** - Social login, sharing, and feed integration
- **Performance Optimization** - Advanced caching, image optimization, and CDN support
- **Security Features** - CSRF protection, rate limiting, input sanitization, and XSS protection
- **Multi-device Support** - Responsive design with mobile optimization

### Technical Features
- **Modular Architecture** - Clean, maintainable code structure
- **Cache Management** - Multi-layer caching with automatic invalidation
- **Image Processing** - Automatic image optimization and WebP generation
- **Search Functionality** - Full-text search across all content
- **API Endpoints** - RESTful APIs for frontend integration
- **Command Line Tools** - Artisan commands for maintenance and automation

## 📋 Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x
- MySQL 8.0+ or PostgreSQL 13+
- Redis (recommended for caching)
- Node.js 16+ (for asset compilation)
- Composer
- GD or ImageMagick extension

### Optional Requirements
- Google OAuth credentials (for social login)
- Google Analytics account (for analytics)
- Mailchimp account (for newsletter)
- CDN service (for asset delivery)

## 🛠 Installation

### 1. Install the Module

```bash
# Install via Composer (if published to Packagist)
composer require your-org/website-module

# Or manually copy the module to your Laravel project
cp -r Website/ Modules/
```

### 2. Environment Configuration

Copy the environment variables from `.env.example` to your main `.env` file:

```bash
# Website Module Configuration
WEBSITE_ENABLED=true
WEBSITE_THEME=default
WEBSITE_CACHE_ENABLED=true

# Google OAuth
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret

# Google Analytics
GOOGLE_ANALYTICS_ID=GA-XXXXXXXXX

# SEO Configuration
SEO_DEFAULT_TITLE="Your School Name"
SEO_DEFAULT_DESCRIPTION="Welcome to our educational institution"

# Contact Information
WEBSITE_CONTACT_EMAIL=contact@yourschool.com
WEBSITE_CONTACT_PHONE="+1-555-123-4567"
WEBSITE_ADDRESS="123 School Street, City, State 12345"
```

### 3. Database Migration

```bash
# Run migrations
php artisan migrate

# Seed sample data (optional)
php artisan module:seed Website
```

### 4. Configuration

Publish and configure the module:

```bash
# Publish configuration files
php artisan vendor:publish --tag=website-config

# Clear cache
php artisan config:clear
php artisan cache:clear
```

### 5. Asset Compilation

```bash
# Install Node dependencies
npm install

# Compile assets
npm run build

# Or for development
npm run dev
```

## 🎯 Configuration

### Basic Configuration

The main configuration file is located at `Modules/Website/Config/website.php`:

```php
return [
    'enabled' => true,
    'theme' => 'default',
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
    ],
    'features' => [
        'blog' => true,
        'events' => true,
        'gallery' => true,
        'newsletter' => true,
    ],
    // ... more configuration options
];
```

### SEO Configuration

Configure SEO settings in `Modules/Website/Config/seo.php`:

```php
return [
    'defaults' => [
        'title' => 'Your School Name',
        'description' => 'Quality education for future leaders',
        'keywords' => 'school,education,learning',
    ],
    'open_graph' => [
        'enabled' => true,
        'image' => '/images/og-default.jpg',
    ],
    // ... more SEO options
];
```

### Analytics Configuration

Set up analytics in `Modules/Website/Config/analytics.php`:

```php
return [
    'enabled' => true,
    'providers' => [
        'google_analytics' => [
            'enabled' => true,
            'tracking_id' => env('GOOGLE_ANALYTICS_ID'),
        ],
    ],
    // ... more analytics options
];
```

## 🎨 Usage

### Creating Pages

```php
use Modules\Website\Entities\WebsitePage;

$page = WebsitePage::create([
    'title' => 'About Us',
    'slug' => 'about',
    'content' => '<p>Welcome to our school...</p>',
    'status' => 'published',
    'meta_title' => 'About Us - School Name',
    'meta_description' => 'Learn about our school...',
]);
```

### Managing Blog Posts

```php
use Modules\Website\Entities\BlogPost;
use Modules\Website\Entities\BlogCategory;

$category = BlogCategory::create([
    'name' => 'School News',
    'slug' => 'school-news',
    'description' => 'Latest news and updates',
]);

$post = BlogPost::create([
    'title' => 'Welcome Back to School',
    'slug' => 'welcome-back-to-school',
    'content' => '<p>We are excited to welcome...</p>',
    'excerpt' => 'Welcome back message...',
    'category_id' => $category->id,
    'status' => 'published',
    'published_at' => now(),
]);
```

### Creating Events

```php
use Modules\Website\Entities\Event;

$event = Event::create([
    'title' => 'Open House',
    'slug' => 'open-house-2024',
    'description' => 'Join us for our annual open house...',
    'start_date' => '2024-03-15 09:00:00',
    'end_date' => '2024-03-15 15:00:00',
    'location' => 'Main Campus',
    'status' => 'published',
]);
```

## 🛡 Security Features

### CSRF Protection
All forms include CSRF protection automatically.

### Rate Limiting
API endpoints and forms are rate-limited to prevent abuse:

```php
// Contact form: 5 requests per minute
// Newsletter signup: 3 requests per minute
// Search: 30 requests per minute
```

### Input Validation
All user inputs are validated and sanitized:

```php
// XSS protection
// SQL injection prevention
// File upload validation
// Image type verification
```

### Content Security Policy
CSP headers are automatically added to prevent XSS attacks.

## ⚡ Performance Optimization

### Caching Strategy

The module implements multi-layer caching:

1. **Page Caching** - Full page cache with automatic invalidation
2. **Data Caching** - Database query results cached
3. **Asset Caching** - Static assets with long-term caching
4. **CDN Integration** - Optional CDN support for global delivery

### Image Optimization

Automatic image optimization includes:

- **Resizing** - Automatic resizing to optimal dimensions
- **Compression** - Quality optimization while maintaining visual fidelity
- **WebP Generation** - Modern format generation with fallbacks
- **Lazy Loading** - Progressive image loading for better performance

### Database Optimization

- **Eager Loading** - Prevents N+1 query problems
- **Indexing** - Proper database indexes for fast queries
- **Query Optimization** - Optimized database queries

## 🎛 Management Commands

### Generate Sitemap
```bash
# Generate XML sitemap
php artisan website:generate-sitemap

# Force regeneration
php artisan website:generate-sitemap --force

# Ping search engines
php artisan website:generate-sitemap --ping
```

### Optimize Images
```bash
# Optimize all images
php artisan website:optimize-images

# Optimize specific directory
php artisan website:optimize-images --path=uploads/gallery

# Generate WebP versions
php artisan website:optimize-images --webp

# Set custom quality
php artisan website:optimize-images --quality=90
```

### Clear Cache
```bash
# Clear all website cache
php artisan website:clear-cache --all

# Clear specific cache tags
php artisan website:clear-cache --tags=blog_posts,events

# Clear by pattern
php artisan website:clear-cache --pattern=website:blog:*

# Show cache statistics
php artisan website:clear-cache --stats
```

### Export Analytics
```bash
# Export monthly analytics
php artisan website:export-analytics --period=month

# Export custom date range
php artisan website:export-analytics --period=custom --start=2024-01-01 --end=2024-01-31

# Export as JSON
php artisan website:export-analytics --format=json

# Email report
php artisan website:export-analytics --email=admin@school.com
```

## 🔧 API Endpoints

### Public APIs

```http
# Get blog posts
GET /api/website/blog/posts
GET /api/website/blog/posts/{slug}

# Get events
GET /api/website/events
GET /api/website/events/{slug}

# Get gallery
GET /api/website/gallery/albums
GET /api/website/gallery/albums/{slug}

# Search
GET /api/website/search?q=query

# Contact info
GET /api/website/contact/info
```

### Authentication Required

```http
# Submit contact form
POST /api/website/contact

# Subscribe to newsletter
POST /api/website/newsletter/subscribe

# Submit comment
POST /api/website/blog/posts/{id}/comments
```

## 🎨 Customization

### Themes

Create custom themes by extending the base theme:

```php
// In your theme's service provider
$this->app['view']->addNamespace('website', resource_path('views/website'));
```

### Custom Templates

Override default templates:

```
resources/views/website/
├── layouts/
│   ├── app.blade.php
│   └── partials/
├── pages/
│   ├── home.blade.php
│   └── about.blade.php
├── blog/
│   ├── index.blade.php
│   └── show.blade.php
└── events/
    ├── index.blade.php
    └── show.blade.php
```

### Custom Styles

Override CSS variables:

```css
:root {
    --primary-color: #your-color;
    --secondary-color: #your-color;
    --font-family: 'Your Font', sans-serif;
}
```

## 🧪 Testing

### Run Tests

```bash
# Run all website module tests
php artisan test Modules/Website/Tests

# Run specific test suite
php artisan test Modules/Website/Tests/Feature
php artisan test Modules/Website/Tests/Unit
```

### Test Coverage

```bash
# Generate coverage report
php artisan test --coverage-html coverage-report
```

## 📈 Analytics & Monitoring

### Built-in Analytics

The module tracks:

- Page views and unique visitors
- User sessions and duration
- Popular content and pages
- Search queries and results
- Error rates and performance
- Device and browser statistics

### Google Analytics Integration

Automatic tracking of:

- Page views with custom dimensions
- Events and interactions
- E-commerce (if applicable)
- Custom goals and conversions

### Performance Monitoring

Monitor:

- Page load times
- Cache hit ratios
- Database query performance
- Image optimization savings
- Error rates and types

## 🚨 Troubleshooting

### Common Issues

**Cache Not Working**
```bash
# Check cache configuration
php artisan config:show cache

# Clear and rebuild cache
php artisan website:clear-cache --all
php artisan config:cache
```

**Images Not Optimizing**
```bash
# Check GD extension
php -m | grep -i gd

# Verify file permissions
chmod -R 755 storage/app/public/uploads
```

**SEO Tags Not Showing**
```bash
# Check if middleware is registered
php artisan route:list --middleware=seo
```

**Google OAuth Not Working**
```bash
# Verify environment variables
php artisan config:show services.google
```

### Debug Mode

Enable debug mode for detailed error reporting:

```php
// In config/website.php
'debug' => true,

// Or via environment
WEBSITE_DEBUG=true
```

### Logs

Check logs for issues:

```bash
# View website-specific logs
tail -f storage/logs/laravel.log | grep Website

# Check cache logs
tail -f storage/logs/cache.log
```

## 📚 Resources

### Documentation Links

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Modules](https://nwidart.com/laravel-modules)
- [Google Analytics](https://analytics.google.com)
- [Google OAuth](https://developers.google.com/identity/protocols/oauth2)

### Community

- [GitHub Issues](https://github.com/your-org/website-module/issues)
- [Discussions](https://github.com/your-org/website-module/discussions)
- [Discord Community](https://discord.gg/your-server)

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup

```bash
# Clone the repository
git clone https://github.com/your-org/website-module.git

# Install dependencies
composer install
npm install

# Set up environment
cp .env.example .env
php artisan key:generate

# Run tests
php artisan test
```

### Code Standards

- Follow PSR-12 coding standards
- Write comprehensive tests
- Document new features
- Use semantic versioning

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Laravel Framework team
- Laravel Modules by Nicolas Widart
- Intervention Image library
- All contributors and community members

## 📞 Support

- **Documentation**: [docs.yoursite.com](https://docs.yoursite.com)
- **Issues**: [GitHub Issues](https://github.com/your-org/website-module/issues)
- **Email**: support@yoursite.com
- **Discord**: [Join our community](https://discord.gg/your-server)

---

**Made with ❤️ for the education community**