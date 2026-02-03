# Fix Installation Error - Create Version Tag

## The Problem
You're getting this error because:
1. The package isn't on Packagist yet
2. GitHub doesn't have a version tag (v1.0.0)

## Solution 1: Create Version Tag on GitHub

Run these commands in your package directory:

```bash
cd /home/parvez-rahman/Downloads/laravel-gallery-manager-complete/gallery-package

# Make sure you've pushed everything
git add .
git commit -m "Ready for v1.0.0 release"
git push origin main

# Create and push version tag
git tag v1.0.0
git push origin v1.0.0
```

## Solution 2: Install from GitHub in Your Laravel Project

Until you submit to Packagist, your Laravel project needs to know where to find the package.

### In your Laravel project's composer.json:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/parvez/laravel-gallery-manager"
        }
    ],
    "require": {
        "parvez/laravel-gallery-manager": "^1.0"
    }
}
```

**OR if no version tag exists yet:**

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/parvez/laravel-gallery-manager"
        }
    ],
    "require": {
        "parvez/laravel-gallery-manager": "dev-main"
    }
}
```

Then run in your Laravel project:
```bash
composer update parvez/laravel-gallery-manager
```

## Solution 3: Submit to Packagist (Best Long-term)

After creating the version tag:

1. Go to https://packagist.org/
2. Click "Submit" 
3. Sign in with GitHub
4. Enter: `https://github.com/parvez/laravel-gallery-manager`
5. Click Submit

Once on Packagist, users can install normally:
```bash
composer require parvez/laravel-gallery-manager
```

## Quick Fix Commands

```bash
# In your package directory:
cd /home/parvez-rahman/Downloads/laravel-gallery-manager-complete/gallery-package
git tag v1.0.0
git push origin v1.0.0

# In your Laravel project directory:
# Add the repository to composer.json (edit manually or use this):
composer config repositories.parvez-gallery vcs https://github.com/parvez/laravel-gallery-manager
composer require parvez/laravel-gallery-manager:^1.0
```

## Verify on GitHub

After tagging, check:
https://github.com/parvez/laravel-gallery-manager/releases

You should see v1.0.0 listed.
