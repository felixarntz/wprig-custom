<img align="right" width="90" height="90"
		 src="https://avatars1.githubusercontent.com/u/38340689"
		 title="WP Rig logo by Morten Rand-Hendriksen">
# WP Rig: WordPress Theme Boilerplate (Custom Fork)

This is a customized fork of [WP Rig](https://github.com/wprig/wprig), the progressive starter theme for WordPress. Please refer to the readme on the original repository to get started.

The fork is regularly updated to always contain the latest improvements added to WP Rig upstream. If you like the additional features this fork provides, you can follow the [original workflow for setting up WP Rig](https://github.com/wprig/wprig/wiki/Recommended-Git-Workflow), but use this repository's URI instead of the original one. This will get you everything WP Rig offers, plus the additional features outlined in the following section.

## Additional Features

The fork contains several additional features which are more opinionated than what the WP Rig base should contain (hence not part of WP Rig itself), but are still commonly requested for WordPress themes. They particularly address the needs for theme customization. Here is a list of the additional features:

* Sitewide color configuration with the Customizer
* Sitewide font configuration (including Google Fonts) with the Customizer
* Sitewide toggling of whether specific post metadata and taxonomies are shown per post type with the Customizer
* Toggling of whether front page header/footer is displayed with the Customizer
* Sitewide modification of the footer info message with the Customizer
* Full WYSIWYG support for CSS custom properties controlled by the Customizer in the block editor
* Infrastructure for conditionally displaying post-specific content (e.g. post header, post footer, post date, post author, post taxonomies)
* Infrastructure for centrally managing the footer info message
* Accessible and performant SVG icon infrastructure
* Multiple footer widget areas as individual columns
* Social nav menu with automatically added icons
* Inline footer menu for additional links
* CSS colors fully controlled by custom properties
* Coherent button styles and variations
* No unnecessary `.site` wrap element as sole child of the `body`
* [Atomic Blocks](https://wordpress.org/plugins/atomic-blocks/) support
