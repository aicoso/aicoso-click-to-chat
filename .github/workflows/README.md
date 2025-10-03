# GitHub Actions Workflows

This directory contains automated workflows for the Click to Chat plugin.

## Workflows

### 1. PHPCS WordPress Coding Standards (`phpcs.yml`)

**Triggers:**
- Push to `main` or `develop` branches
- Pull requests to `main` or `develop` branches

**What it does:**
- Checks PHP code against WordPress Coding Standards
- Validates PHP 7.4+ compatibility
- Reports issues directly in pull requests
- Ensures code quality and consistency

**Manual run:**
```bash
composer install
composer run phpcs
```

**Fix issues automatically:**
```bash
composer run phpcbf
```

### 2. POT Translation File Generation (`pot-generation.yml`)

**Triggers:**
- Push to `main` branch (when PHP files change)
- Manual trigger via GitHub Actions UI

**What it does:**
- Automatically generates `languages/click-to-chat.pot` file
- Extracts all translatable strings from PHP files
- Commits updated POT file back to repository
- Uses WP-CLI i18n command

**Manual generation:**
```bash
wp i18n make-pot . languages/click-to-chat.pot --domain="click-to-chat"
```

## Setup Requirements

### PHPCS Workflow
No additional setup needed. The workflow installs all dependencies automatically.

### POT Generation Workflow
The workflow needs write access to commit the POT file. This is provided by the default `GITHUB_TOKEN`.

**To customize headers in POT file:**
Edit `.github/workflows/pot-generation.yml` and update:
- `Report-Msgid-Bugs-To`
- `Last-Translator`
- `Language-Team`

## Local Development

### Install Dependencies
```bash
composer install
```

### Check Coding Standards
```bash
composer run phpcs
```

### Auto-fix Coding Issues
```bash
composer run phpcbf
```

### Generate POT File Locally
```bash
wp i18n make-pot . languages/click-to-chat.pot --domain="click-to-chat"
```

## Configuration Files

- **phpcs.xml** - PHPCS configuration
- **composer.json** - PHP dependencies and scripts
- **.github/workflows/** - GitHub Actions workflows

## Badges

Add these badges to your README.md:

```markdown
![PHPCS](https://github.com/YOUR_USERNAME/click-to-chat/actions/workflows/phpcs.yml/badge.svg)
![POT Generation](https://github.com/YOUR_USERNAME/click-to-chat/actions/workflows/pot-generation.yml/badge.svg)
```
