# Website Module Installation Guide

This guide provides step-by-step instructions for installing and configuring the Website module for your Laravel School Management System.

## 📋 Prerequisites

Before installing the Website module, ensure your system meets these requirements:

### System Requirements
- **PHP**: 8.1 or higher
- **Laravel**: 10.x or 11.x
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 512MB minimum (1GB recommended)
- **Storage**: 2GB available disk space

### PHP Extensions
Ensure these PHP extensions are installed and enabled:

```bash
# Required extensions
php -m | grep -E "(gd|imagick|curl|json|mbstring|openssl|pdo|tokenizer|xml|zip)"

# Install missing extensions (Ubuntu/Debian)
sudo apt-get install php8.1-gd php8.1-curl php8.1-mbstring php8.1-xml php8.1-zip php8.1-mysql

# Install missing extensions (CentOS/RHEL)
sudo yum install php-gd php-curl php-mbstring php-xml php-zip php-mysql
```

### Additional Software
- **Composer**: 2.0 or higher
- **Node.js**: 16.x or higher
- **Redis**: 6.0+ (recommended for caching)
- **Git**: For version control

## 🚀 Installation Methods

### Method 1: Manual Installation (Recommended)

#### Step 1: Download the Module

```bash
# Clone the repository
git clone https://github.com/your-org/website-module.git

# Or download and extract the ZIP file
wget https://github.com/your-org/website-module/archive/main.zip
unzip main.zip
```

#### Step 2: Copy to Laravel Project

```bash
# Navigate to your Laravel project root
cd /path/to/your/laravel/project

# Create Modules directory if it doesn't exist
mkdir -p Modules

# Copy the Website module
cp -r /path/to/website-module/Website Modules/

# Set proper permissions
chmod -R 755 Modules/Website
chown -R www-data:www-data Modules/Website  # Linux
chown -R _www:_www Modules/Website          # macOS
```

### Method 2: Composer Installation (If Published)

```bash
# Install via Composer
composer require your-org/laravel-website-module

# Publish the module
php artisan module:publish Website
```

## ⚙️ Configuration

### Step 1: Environment Configuration

Copy the environment variables from `.env.example` to your main `.env` file:

```bash
# Navigate to the Website module
cd Modules/Website

# Copy environment configuration
cat .env.example >> ../../.env
```

Edit your `.env` file and configure the following sections:

#### Basic Configuration
```env
# Website Module
WEBSITE_ENABLED=true
WEBSITE_THEME=default
WEBSITE_CACHE_ENABLED=true
WEBSITE_CACHE_TTL=3600

# Application URL (must be set correctly)
APP_URL=https://yourschool.com
```

#### Database Configuration
```env
# Ensure your database is properly configured
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_school_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

#### Cache Configuration
```env
# Redis configuration (recommended)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache settings
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

#### Google Services
```env
# Google OAuth (for social login)
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

# Google Analytics
GOOGLE_ANALYTICS_ID=GA-XXXXXXXXX
GOOGLE_TAG_MANAGER_ID=GTM-XXXXXXX
GOOGLE_SITE_VERIFICATION=your-verification-code
```

#### School Information
```env
# Contact Information
WEBSITE_CONTACT_EMAIL=contact@yourschool.com
WEBSITE_CONTACT_PHONE="+1-555-123-4567"
WEBSITE_ADDRESS="123 School Street, City, State 12345"
WEBSITE_OFFICE_HOURS="Monday-Friday: 8:00 AM - 5:00 PM"

# SEO Configuration
SEO_DEFAULT_TITLE="Your School Name - Quality Education"
SEO_DEFAULT_DESCRIPTION="Welcome to our educational institution providing quality education and holistic development."
SEO_DEFAULT_KEYWORDS="school,education,learning,students,academics"
```

### Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader

# Install Node.js dependencies
npm install

# Update Composer autoloader
composer dump-autoload
```

### Step 3: Configure Laravel

#### Register the Module

Add the module to your `config/modules.php` (if using Laravel Modules package):

```php
<?php

return [
    'namespace' => 'Modules',
    'stubs' => [
        'enabled' => false,
        'path' => base_path('vendor/nwidart/laravel-modules/src/Commands/stubs'),
    ],
    'paths' => [
        'modules' => base_path('Modules'),
        'assets' => public_path('modules'),
        'migration' => base_path('database/migrations'),
        'generator' => [
            'config' => ['path' => 'Config', 'generate' => true],
            'command' => ['path' => 'Console', 'generate' => true],
            'migration' => ['path' => 'Database/Migrations', 'generate' => true],
            'seeder' => ['path' => 'Database/Seeders', 'generate' => true],
            'factory' => ['path' => 'Database/Factories', 'generate' => true],
            'model' => ['path' => 'Entities', 'generate' => true],
            'controller' => ['path' => 'Http/Controllers', 'generate' => true],
            'filter' => ['path' => 'Http/Middleware', 'generate' => true],
            'request' => ['path' => 'Http/Requests', 'generate' => true],
            'provider' => ['path' => 'Providers', 'generate' => true],
            'assets' => ['path' => 'Resources/assets', 'generate' => true],
            'lang' => ['path' => 'Resources/lang', 'generate' => true],
            'views' => ['path' => 'Resources/views', 'generate' => true],
            'test' => ['path' => 'Tests', 'generate' => true],
            'repository' => ['path' => 'Repositories', 'generate' => true],
            'event' => ['path' => 'Events', 'generate' => true],
            'listener' => ['path' => 'Listeners', 'generate' => true],
            'policies' => ['path' => 'Policies', 'generate' => true],
            'rules' => ['path' => 'Rules', 'generate' => true],
            'jobs' => ['path' => 'Jobs', 'generate' => true],
            'emails' => ['path' => 'Emails', 'generate' => true],
            'notifications' => ['path' => 'Notifications', 'generate' => true],
        ],
    ],
    'scan' => [
        'enabled' => false,
        'paths' => [
            base_path('vendor/*/*'),
        ],
    ],
    'composer' => [
        'vendor' => 'nwidart',
        'author' => [
            'name' => 'Nicolas Widart',
            'email' => 'n.widart@gmail.com',
        ],
    ],
    'cache' => [
        'enabled' => false,
        'key' => 'laravel-modules',
        'lifetime' => 60,
    ],
    'register' => [
        'translations' => true,
    ],
];
```

#### Update Configuration Cache

```bash
# Clear configuration cache
php artisan config:clear

# Cache configuration
php artisan config:cache
```

### Step 4: Database Setup

#### Run Migrations

```bash
# Run Website module migrations
php artisan migrate

# Or specifically run module migrations
php artisan module:migrate Website
```

#### Seed Sample Data (Optional)

```bash
# Seed all Website module data
php artisan module:seed Website

# Or seed specific seeders
php artisan db:seed --class="Modules\Website\Database\Seeders\WebsitePageSeeder"
php artisan db:seed --class="Modules\Website\Database\Seeders\BlogCategorySeeder"
php artisan db:seed --class="Modules\Website\Database\Seeders\BlogPostSeeder"
```

### Step 5: File Permissions

Set proper file permissions for uploaded content:

```bash
# Create storage directories
mkdir -p storage/app/public/uploads/website/{blog,events,gallery,staff,pages}

# Set permissions
chmod -R 755 storage/app/public/uploads
chown -R www-data:www-data storage/app/public/uploads  # Linux
chown -R _www:_www storage/app/public/uploads          # macOS

# Create symbolic link for public access
php artisan storage:link
```

### Step 6: Compile Assets

```bash
# For production
npm run build

# For development
npm run dev

# For continuous development
npm run watch
```

## 🔧 Advanced Configuration

### Google OAuth Setup

#### Step 1: Create Google OAuth Application

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing project
3. Enable Google+ API and Google OAuth2 API
4. Create OAuth 2.0 credentials
5. Add authorized redirect URIs:
   - `https://yourdomain.com/auth/google/callback`

#### Step 2: Configure OAuth in Laravel

Add to `config/services.php`:

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],
```

### Google Analytics Setup

#### Step 1: Create Google Analytics Property

1. Go to [Google Analytics](https://analytics.google.com/)
2. Create a new property for your website
3. Get your tracking ID (GA-XXXXXXXXX or G-XXXXXXXXXX for GA4)

#### Step 2: Configure Analytics

Update your `.env` file:

```env
GOOGLE_ANALYTICS_ID=GA-XXXXXXXXX
ANALYTICS_ENABLED=true
```

### Mail Configuration

Configure mail settings for contact forms and newsletters:

```env
# SMTP Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourschool.com
MAIL_FROM_NAME="${APP_NAME}"
```

### CDN Configuration (Optional)

For better performance, configure a CDN:

```env
# CDN Configuration
CDN_ENABLED=true
CDN_URL=https://cdn.yourschool.com
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-s3-bucket
```

## 🚦 Testing Installation

### Step 1: Basic Functionality Test

```bash
# Test database connection
php artisan migrate:status

# Test cache
php artisan cache:clear
php artisan config:cache

# Test queues (if using)
php artisan queue:work --once
```

### Step 2: Website Access Test

1. Visit your website homepage: `https://yourdomain.com`
2. Test blog section: `https://yourdomain.com/blog`
3. Test events: `https://yourdomain.com/events`
4. Test contact form: `https://yourdomain.com/contact`

### Step 3: Admin Panel Test

1. Access admin panel (if integrated)
2. Create a test blog post
3. Create a test event
4. Upload a test image to gallery

### Step 4: SEO Test

1. View page source and check for:
   - Meta tags
   - Open Graph tags
   - Structured data
   - Canonical URLs

2. Test sitemap: `https://yourdomain.com/sitemap.xml`
3. Test robots.txt: `https://yourdomain.com/robots.txt`

## 🔧 Production Deployment

### Step 1: Production Environment

```env
# Set environment to production
APP_ENV=production
APP_DEBUG=false

# Enable caching
WEBSITE_CACHE_ENABLED=true
CACHE_DRIVER=redis
SESSION_DRIVER=redis

# Security settings
FORCE_HTTPS=true
HSTS_ENABLED=true
```

### Step 2: Optimize for Production

```bash
# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Cache configuration and routes
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate sitemap
php artisan website:generate-sitemap

# Optimize images
php artisan website:optimize-images --webp
```

### Step 3: Set up Scheduled Tasks

Add to your crontab:

```bash
# Edit crontab
crontab -e

# Add Laravel scheduler
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Generate sitemap daily
    $schedule->command('website:generate-sitemap')->daily();
    
    // Clear old cache weekly
    $schedule->command('website:clear-cache --all')->weekly();
    
    // Export analytics monthly
    $schedule->command('website:export-analytics --period=month')->monthly();
}
```

### Step 4: Monitor Performance

Set up monitoring for:

- Website uptime
- Page load times
- Error rates
- Cache hit ratios
- Database performance

## 🚨 Troubleshooting

### Common Issues

#### Permission Errors

```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache

# Fix upload permissions
sudo chown -R www-data:www-data storage/app/public/uploads
sudo chmod -R 755 storage/app/public/uploads
```

#### Cache Issues

```bash
# Clear all cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Database Connection Issues

```bash
# Test database connection
php artisan tinker
DB::connection()->getPdo();

# Check database configuration
php artisan config:show database
```

#### Module Not Loading

```bash
# Check if module is enabled
php artisan module:list

# Enable module if disabled
php artisan module:enable Website

# Clear module cache
php artisan module:cache-clear
```

### Debug Mode

Enable debug mode for troubleshooting:

```env
APP_DEBUG=true
WEBSITE_DEBUG=true
```

### Log Files

Check log files for errors:

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Web server logs
tail -f /var/log/apache2/error.log  # Apache
tail -f /var/log/nginx/error.log    # Nginx
```

## 📞 Support

If you encounter issues during installation:

1. Check the [README.md](README.md) for general documentation
2. Review [troubleshooting section](#-troubleshooting)
3. Check existing [GitHub Issues](https://github.com/your-org/website-module/issues)
4. Create a new issue with:
   - Laravel version
   - PHP version
   - Error messages
   - Steps to reproduce

## 🎯 Next Steps

After successful installation:

1. **Customize Content**: Update pages, blog posts, and events
2. **Configure Theme**: Customize colors, fonts, and layout
3. **Set up Analytics**: Configure Google Analytics and tracking
4. **Test Performance**: Use tools like GTmetrix or PageSpeed Insights
5. **Security Review**: Implement security best practices
6. **Backup Strategy**: Set up regular backups
7. **Monitoring**: Configure uptime and performance monitoring

## 📚 Additional Resources

- [Configuration Guide](docs/configuration.md)
- [Theme Development](docs/theming.md)
- [API Documentation](docs/api.md)
- [Performance Optimization](docs/performance.md)
- [Security Best Practices](docs/security.md)

---

**Installation complete! 🎉**

Your Website module is now ready to serve your school community with a modern, feature-rich website experience.