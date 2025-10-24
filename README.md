[![CS](https://github.com/ProgressPlanner/brand-assets/actions/workflows/cs.yml/badge.svg)](https://github.com/ProgressPlanner/brand-assets/actions/workflows/cs.yml)

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/brand-assets.svg)](https://wordpress.org/plugins/brand-assets/)
![WordPress Plugin: Tested WP Version](https://img.shields.io/wordpress/plugin/tested/brand-assets.svg)
[![WordPress Plugin Active Installs](https://img.shields.io/wordpress/plugin/installs/brand-assets.svg)](https://wordpress.org/plugins/brand-assets/advanced/)
[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/brand-assets.svg)](https://wordpress.org/plugins/brand-assets/advanced/)
[![WordPress Plugin Rating](https://img.shields.io/wordpress/plugin/stars/brand-assets.svg)](https://wordpress.org/support/plugin/brand-assets/reviews/)
[![GitHub](https://img.shields.io/github/license/ProgressPlanner/brand-assets.svg)](https://github.com/ProgressPlanner/brand-assets/blob/main/LICENSE)

[![Try Brand Assets on the WordPress playground](https://img.shields.io/badge/Try%20Brand%20Assets%20on%20the%20WordPress%20Playground-%23117AC9.svg?style=for-the-badge&logo=WordPress&logoColor=ddd)](https://playground.wordpress.net/#ewoJImxhbmRpbmdQYWdlIjogIi93cC1hZG1pbi9vcHRpb25zLWdlbmVyYWwucGhwP3BhZ2U9YnJhbmQtYXNzZXRzLXNldHRpbmdzIiwKCSJsb2dpbiI6IHRydWUsCgkic3RlcHMiOiBbCgkJewoJCQkic3RlcCI6ICJpbnN0YWxsUGx1Z2luIiwKCQkJInBsdWdpbkRhdGEiOiB7CgkJCQkicmVzb3VyY2UiOiAidXJsIiwKCQkJCSJ1cmwiOiAiaHR0cHM6Ly9naXRodWItcHJveHkuY29tL3Byb3h5Lz9yZXBvPS9Qcm9ncmVzc1BsYW5uZXIvYnJhbmQtYXNzZXRzJmJyYW5jaD1tYWluIgoJCQl9CgkJfQoJXQp9)

# Brand Assets

A WordPress block plugin that allows you to easily display your company's brand assets on your website. Perfect for showcasing your brand identity, color schemes, and design guidelines.

## Features

- **Color Scheme Display**: Create beautiful color palette displays with customizable swatches
- **Flexible Layout**: Adjust swatch sizes, gaps, borders, and styling to match your brand
- **CMYK Support**: Optional CMYK color values for print-ready color information
- **Responsive Design**: Color schemes look great on all devices
- **Easy to Use**: Simple block editor interface with intuitive controls
- **Logo Popover**: Right-click on your site logo to show a customizable popover linking to your brand assets page
- **Settings Page**: Configure popover text, target page, and logo selector through WordPress admin
- **Plugin Settings Link**: Quick access to settings directly from the WordPress plugins page
- **Modern Architecture**: Built with object-oriented PHP 7.4+ and follows WordPress coding standards

## Perfect For

- Brand guidelines pages
- Design system documentation
- Marketing materials
- Portfolio websites
- Agency websites
- Corporate websites

## Installation

### From WordPress Admin

1. Go to **Plugins** → **Add New**
2. Search for "Brand Assets"
3. Click **Install Now** and then **Activate**

### Manual Installation

1. Download the plugin files
2. Upload them to `/wp-content/plugins/brand-assets/`
3. Activate the plugin through the **Plugins** screen in WordPress

## Usage

### Adding the Color Palette Block

1. In the WordPress editor, click the **"+"** button to add a new block
2. Search for **"Color Palette - Brand Assets"** or find it in the **Design** category
3. Click to add the block to your page or post

### Configuring Your Color Palette

1. **Add Colors**: Enter hex color codes (e.g., `#FF5733`) in the block settings
2. **Customize Appearance**: Use the block settings panel to adjust:
   - Swatch width and height
   - Gap between swatches
   - Border width, radius, and color
   - Enable/disable CMYK values

### Configuring the Logo Popover

The Brand Assets plugin includes a logo popover feature that appears when users right-click on your site logo. To configure this feature:

1. Go to **Settings** → **Brand Assets** in your WordPress admin (or click the "Settings" link on the plugins page)
2. **Select Brand Assets Page**: Choose the page where users will be taken when they click the popover link
3. **Customize Text**: Set the popover heading, subheading, and link text
4. **Logo Selector**: Specify the CSS selector for your logo element (default: `.wp-block-site-logo`)
5. Click **Save Changes**

The popover will only appear if you have selected a brand assets page in the settings.

### Download Links Feature

The Brand Assets plugin includes a convenient feature for creating download links. When you add the `ba-download` class to a list item in Gutenberg, all links within that list item will automatically get the `download` attribute added to them.

#### How to Use Download Links

1. **Create a List**: In the WordPress editor, create a list (ordered or unordered)
2. **Add the Class**: Select a list item and add the CSS class `ba-download` in the Advanced settings
3. **Add Links**: Add your download links within that list item
4. **Automatic Processing**: The plugin will automatically add the `download` attribute to all links in that list item

#### Example

```html
<ul>
  <li class="ba-download">
    <a href="https://example.com/files/brand-guidelines.pdf">Brand Guidelines (PDF)</a>
  </li>
  <li class="ba-download">
    <a href="https://example.com/files/logo-pack.zip">Logo Pack (ZIP)</a>
  </li>
</ul>
```

The plugin will automatically transform these links to include the `download` attribute:

```html
<ul>
  <li class="ba-download">
    <a href="https://example.com/files/brand-guidelines.pdf" download>Brand Guidelines (PDF)</a>
  </li>
  <li class="ba-download">
    <a href="https://example.com/files/logo-pack.zip" download>Logo Pack (ZIP)</a>
  </li>
</ul>
```

This feature is particularly useful for brand asset pages where you want to provide downloadable files like logos, brand guidelines, or other resources.

## Brand Page Pattern

The Brand Assets plugin comes with a pre-built block pattern that provides a complete template for creating brand asset pages. This pattern includes:

### Pattern Sections

1. **Logo Section**:
   - Two-column layout for logo and icon display
   - Download links for SVG, EPS, and PNG formats
   - Uses the `ba-download` class for automatic download attributes

2. **Brand Colors Section**:
   - Pre-configured Brand Assets block with sample colors
   - Includes CMYK values for print-ready color information
   - Responsive color swatches with customizable styling

3. **Typography Section**:
   - Complete heading hierarchy (H1-H6)
   - Text formatting examples (normal, bold, italic)
   - Table example for comprehensive typography showcase

### How to Use the Pattern

1. **Create a New Page**: In WordPress admin, create a new page for your brand assets
2. **Add the Pattern**: In the block editor, click the "+" button and go to the **Patterns** tab
3. **Find the Pattern**: Look for "Brand Assets" in the available patterns
4. **Insert and Customize**: Click to insert the pattern, then customize it with your actual:
   - Logo and icon images
   - Brand colors (replace the sample colors in the Brand Assets block)
   - Download links for your actual files
   - Typography information

### Pattern Benefits

- **Quick Setup**: Get a professional brand assets page in minutes
- **Best Practices**: Follows design patterns proven to work well for brand guidelines
- **Download Integration**: Automatically includes the download links feature
- **Responsive Design**: Works perfectly on all devices
- **Customizable**: Easy to modify and adapt to your specific brand needs

## Block Settings

| Setting | Description | Default |
|---------|-------------|---------|
| Swatch Width | Width of each color swatch in pixels | 150px |
| Swatch Height | Height of each color swatch in pixels | 150px |
| Swatch Gap | Space between swatches in pixels | 20px |
| Border Width | Thickness of swatch borders in pixels | 1px |
| Border Radius | Roundness of swatch corners in pixels | 0px |
| Border Color | Color of swatch borders | #888888 |
| Show CMYK | Display CMYK values alongside hex codes | Disabled |

## Settings Page

The Brand Assets plugin includes a comprehensive settings page accessible via:

- **WordPress Admin**: Settings → Brand Assets
- **Plugins Page**: Click the "Settings" link next to the Brand Assets plugin

### Settings Options

| Setting | Description | Default |
|---------|-------------|---------|
| Brand Assets Page | Page where users are taken when clicking the popover link | None (disabled) |
| Popover Heading | Main heading text in the popover | "Looking for our logo?" |
| Popover Text Line 1 | First line of text that appears below the heading | "You're in the right spot!" |
| Popover Text Line 2 | Second line of text that appears before the link | "Check out our" |
| Link Text | Text for the link to your brand assets page | "brand assets" |
| Logo Selector | CSS selector for the logo element | ".wp-block-site-logo" |
| CSS Loading Mode | Choose how CSS should be loaded for the popover | "Load the default CSS" |
| Custom Popover CSS | Custom CSS for styling the popover (only shown when CSS Loading Mode is set to "Load custom CSS") | Empty |

## Developer Hooks

The Brand Assets plugin provides several filters for developers to customize functionality:

### Filters

#### `brand_assets_frontend_css_path`

Filter the path to the frontend CSS file used for styling the logo popover.

**Parameters:**
- `string $css_file_path` - The path to the CSS file

**Default:** `BRAND_ASSETS_PLUGIN_DIR . 'assets/frontend.css'`

**Example:**
```php
add_filter( 'brand_assets_frontend_css_path', function( $path ) {
    return get_template_directory() . '/custom-brand-assets.css';
});
```

#### `brand_assets_pattern_file_path`

Filter the path to the brand page pattern file used for block pattern registration.

**Parameters:**
- `string $pattern_file_path` - The path to the pattern file

**Default:** `BRAND_ASSETS_PLUGIN_DIR . 'includes/brand-page-pattern.inc'`

**Example:**
```php
add_filter( 'brand_assets_pattern_file_path', function( $path ) {
    return get_template_directory() . '/custom-brand-pattern.inc';
});
```

## Architecture

The Brand Assets plugin is built using modern object-oriented PHP with the following structure:

### Main Classes

- **`Brand_Assets`**: Main plugin class implementing singleton pattern
- **`Brand_Assets_Settings`**: Handles admin settings and configuration
- **`Brand_Assets_Options`**: Manages plugin options and default values
- **`Brand_Assets_Frontend`**: Manages frontend functionality and popover display

### Key Features

- **Singleton Pattern**: Ensures only one instance of the main plugin class
- **Separation of Concerns**: Clear separation between admin, frontend, and core functionality
- **Modern PHP**: Uses PHP 7.4+ features with object-oriented design patterns
- **WordPress Standards**: Follows WordPress coding standards and best practices
- **Internationalization**: Full support for translations with proper text domain usage

## Development

### Prerequisites

- PHP 7.4 or higher
- Node.js (version 14 or higher)
- npm or yarn
- WordPress development environment

### Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/your-username/brand-assets.git
   cd brand-assets
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

3. Start development:
   ```bash
   npm start
   ```

4. Build for production:
   ```bash
   npm run build
   ```

### Available Scripts

- `npm start` - Start the development server with hot reloading
- `npm run build` - Build the plugin for production
- `npm run lint:js` - Lint JavaScript files
- `npm run lint:css` - Lint CSS files
- `npm run lint:php` - Lint PHP files using WordPress Coding Standards
- `npm run lint:php:fix` - Auto-fix PHP coding standards issues
- `npm run lint:php:compatibility` - Check PHP compatibility
- `npm run format` - Format code using WordPress standards

## Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit your changes: `git commit -m 'Add amazing feature'`
4. Push to the branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

### Development Guidelines

- Follow WordPress coding standards (WPCS)
- Test your changes thoroughly
- Update documentation as needed
- Ensure compatibility with WordPress 6.6+ and PHP 7.4+

### Code Quality

This plugin follows WordPress Coding Standards (WPCS). To check and fix code quality:

```bash
# Install dependencies
composer install

# Check PHP coding standards
npm run lint:php

# Auto-fix PHP coding standards issues
npm run lint:php:fix

# Check PHP compatibility
npm run lint:php:compatibility
```

## Support

- **Documentation**: [Plugin Documentation](https://progressplanner.com/plugins/brand-assets/)
- **Issues**: [GitHub Issues](https://github.com/progressplanner/brand-assets/issues)
- **Contact**: [Team Progress Planner](https://progressplanner.com/)

## Changelog

### 0.1.0
- Initial release
- Brand Assets block with color scheme display
- Customizable swatch appearance
- CMYK color support
- Responsive design

## License

This plugin is licensed under the GPL-2.0-or-later License. See the [LICENSE](LICENSE) file for details.

## Credits

Created by [Team Progress Planner](https://progressplanner.com) - WordPress development team.

---

**Brand Assets** - Making brand consistency easy in WordPress.
