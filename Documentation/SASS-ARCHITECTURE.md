# SASS Architecture Documentation

## Overview
The SASS architecture has been optimized for maintainable, logical separation between structure and color schemes, resulting in two main CSS outputs:

- **style.min.css** - Front-end styles for the business location finder interface
- **admin-styles.min.css** - Back-end styles for WordPress admin pages

## File Structure

### Root SASS Files
- `style.sass` - Main front-end stylesheet (existing structure)
- `admin-styles.sass` - **NEW** Root admin stylesheet for all admin interface styling

### Shared Partials
- `_colors.sass` - Centralized color system for entire plugin
- Front-end partials: `_cards.sass`, `_counters.sass`, `_fonts.sass`, etc.

### Admin-Specific Partials
- `_admin-styles-color-scheme.sass` - Color scheme for admin settings page + shared admin components
- `_import-page.sass` - Structure for import page layout
- `_import-page-color-scheme.sass` - Color scheme for import page

## Architecture Principles

### 1. Separation of Concerns
- **Structure** is separated from **appearance**
- Admin settings page styling is separated from import page styling
- Shared components (forms, buttons, tables) have centralized styling

### 2. Logical File Organization
```
admin-styles.sass (root)
├── _colors.sass (shared color system)
├── _admin-styles-color-scheme.sass (admin settings + shared components)
├── _import-page.sass (import page structure)
└── _import-page-color-scheme.sass (import page colors)
```

### 3. Two-File CSS Output
- **Front-end**: `style.sass` → `style.min.css` (unchanged)
- **Back-end**: `admin-styles.sass` → `admin-styles.min.css` (new optimized structure)

## Key Benefits

### Maintainability
- Color changes can be made in centralized locations
- Structure changes don't affect color schemes
- Import page styling is isolated from admin settings

### Performance
- Efficient CSS output with no duplication
- Clean separation allows for targeted optimization
- Compressed output for production use

### Developer Experience
- Clear file organization matches admin page structure
- Easy to locate specific styling rules
- Logical separation reduces conflicts

## Usage

### Development
```bash
# Compile both stylesheets
sass style.sass:../css/style.min.css --style=compressed
sass admin-styles.sass:../css/admin-styles.min.css --style=compressed
```

### Deployment
- Include both CSS files in plugin
- `style.min.css` for front-end business finder
- `admin-styles.min.css` for WordPress admin pages

## Migration Notes
- All inline admin styles have been moved to the SASS architecture
- Previous monolithic admin styling has been refactored into logical components
- Color system is now centralized and reusable across all plugin interfaces