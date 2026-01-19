# Ncloud Outbound Mailer - Project Instructions

## Project Overview
WordPress plugin for sending emails through Ncloud Cloud Outbound Mailer API.

## Development Commands

```bash
# Run tests
./vendor/bin/phpunit

# Build distribution zip
./scripts/build.sh

# Deploy to WordPress.org SVN
./scripts/deploy-svn.sh
```

## WordPress.org SVN Deployment

### SVN Repository
- URL: `https://plugins.svn.wordpress.org/ncloud-outbound-mailer`
- Username: `dhlee7`
- Password: `svn_rsZPTbwgK61ejwT6HXdKLXDDZjONncq75a3c78d7`

### SVN Structure
```
ncloud-outbound-mailer/
├── trunk/          # Latest development version
├── tags/           # Released versions (1.0.0, 1.0.1, etc.)
│   └── 1.0.0/
└── assets/         # Plugin assets for WordPress.org
    ├── banner-772x250.png
    ├── banner-1544x500.png
    ├── icon-128x128.png
    ├── icon-256x256.png
    └── screenshot-1.png
```

### Deployment Process
1. Run `./scripts/deploy-svn.sh` to deploy
2. Script will:
   - Checkout SVN repository
   - Sync trunk with latest code
   - Create new tag for version
   - Commit changes to WordPress.org

### Manual SVN Commands
```bash
# Checkout repository
svn checkout https://plugins.svn.wordpress.org/ncloud-outbound-mailer svn-repo --username dhlee7 --password svn_rsZPTbwgK61ejwT6HXdKLXDDZjONncq75a3c78d7

# Update trunk
cd svn-repo/trunk
# ... copy files ...
svn add --force .
svn commit -m "Update to version X.X.X" --username dhlee7 --password svn_rsZPTbwgK61ejwT6HXdKLXDDZjONncq75a3c78d7

# Create tag
svn copy trunk tags/X.X.X
svn commit -m "Tag version X.X.X" --username dhlee7 --password svn_rsZPTbwgK61ejwT6HXdKLXDDZjONncq75a3c78d7
```

## File Structure

```
├── ncloud-outbound-mailer.php   # Main plugin file
├── includes/
│   ├── API/
│   │   ├── class-client.php     # Ncloud API client
│   │   └── class-signature.php  # HMAC-SHA256 signature
│   ├── Admin/
│   │   └── class-settings.php   # Admin settings page
│   └── class-mail-handler.php   # wp_mail override
├── admin/
│   ├── css/admin.css
│   └── js/admin.js
├── languages/                    # i18n files
├── tests/                        # PHPUnit tests
└── scripts/
    ├── build.sh                  # Build distribution zip
    └── deploy-svn.sh             # Deploy to WordPress.org
```

## Key Hooks

### Filters
- `ncloud_mailer_before_send` - Modify mail data before sending
- `ncloud_mailer_fallback_on_error` - Enable fallback to default wp_mail
- `ncloud_mailer_enable_logging` - Enable/disable logging

### Actions
- `ncloud_mailer_init` - After plugin initialization
- `ncloud_mailer_after_send` - After successful send
- `ncloud_mailer_error` - On send error

## Testing

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test
./vendor/bin/phpunit --filter SignatureTest

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage/
```

## Release Checklist

1. [ ] Update version in `ncloud-outbound-mailer.php`
2. [ ] Update version in `readme.txt` (Stable tag)
3. [ ] Update changelog in `readme.txt`
4. [ ] Run tests: `./vendor/bin/phpunit`
5. [ ] Build zip: `./scripts/build.sh`
6. [ ] Test installation on fresh WordPress
7. [ ] Deploy to SVN: `./scripts/deploy-svn.sh`
8. [ ] Create GitHub release with zip file
