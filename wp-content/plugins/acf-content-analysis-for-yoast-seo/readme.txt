=== ACF Content Analysis for Yoast SEO ===
Contributors: yoast, angrycreative, kraftner, marcusforsberg, viktorfroberg, joostdevalk, atimmer, jipmoors, theorboman
Tags: Yoast, SEO, ACF, Advanced Custom Fields, analysis, Search Engine Optimization
Requires at least: 4.9
Tested up to: 5.2
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Stable tag: 2.3.0
Requires PHP: 5.2.4

WordPress plugin that adds the content of all ACF fields to the Yoast SEO score analysis.

== Description ==

This plugin ensures that Yoast SEO analyzes all ACF content including Flexible Content and Repeaters.

[Yoast SEO for WordPress](https://yoast.com/wordpress/plugins/) content and SEO analysis does not take in to account the content of a post's [Advanced Custom Fields](http://www.advancedcustomfields.com/). This plugin uses the plugin system of Yoast SEO for WordPress 3.1+ to hook into the analyser in order to add ACF content to the SEO analysis.

This had previously been done by the [WordPress SEO ACF Content Analysis](https://wordpress.org/plugins/wp-seo-acf-content-analysis/) plugin but that no longer works with Yoast 3.0. Kudos to [ryuheixys](https://profiles.wordpress.org/ryuheixys/), the author of that plugin, for the original idea.

This Plugin is compatible with the free ACF 4 Version as well as with the PRO Version 5. Please be aware that it ignores Pro Add-Ons for Version 4. In that case please upgrade to ACF PRO Version 5.

> If you have issues, please [submit them on GitHub](https://github.com/Yoast/yoast-acf-analysis/issues)

Previously called Yoast ACF Analysis.

== Filters ==

= Remove specific field from scoring =
`add_filter( 'yoast-acf-analysis/blacklist_name', function ( $blacklist_name ) {
    $blacklist_name->add( 'my-field-name' );
    return $blacklist_name;
});`

= Remove field type from scoring =
`add_filter( 'yoast-acf-analysis/blacklist_type', function ( $blacklist_type ) {
    // text, image etc
    $blacklist_type->add( 'text' );
    $blacklist_type->add( 'image' );
    return $blacklist_type;
});`

= Define custom field a specific heading value =
`add_filter( 'yoast-acf-analysis/headlines', function ( $headlines ) {
    // value from 1-6, 1=h1, 6=h6
    $headlines['field_591eb45f2be86'] = 3;
    return $headlines;
});`

= Change refresh rate =
`add_filter( 'yoast-acf-analysis/refresh_rate', function () {
    // Refresh rates in milliseconds
    return 1000;
});`

== Changelog ==

= 2.3.0 =

Released May 15th, 2019

Enhancements:

* ACF URL fields are now analyzed as links. Props to [t49tran](https://github.com/t49tran).

= 2.2.0 =

Released January 22nd, 2019

Bugfixes:

* Fixes a bug where textarea and non-headline text content would not be wrapped in paragraphs for the analysis. Props [skaeser](https://github.com/skaeser).

Enhancements:

 * Introduces the 'yoast-acf-analysis/field_order' filter which allows for adjusting the ACF field order. This also adds the possibility to prepend field content to WordPress' post_content using a negative field order. Props [skaeser](https://github.com/skaeser).

= 2.1.0 =

Released July 10th, 2018

Bugfixes:
 * Fixes a bug where attempting to get the ACF version, wouldn't always be reliable. This would lead the plugin to think that a newer version was installed than what was actually present.
 * Fixes potential conflicts with other plugins due to generic variable naming.
 * Fixes a bug where the YoastSEO ACF Content analysis would attempted to be loaded, although it wasn't available.

Other:
 * Adds filter examples to the readme.

= 2.0.1 =

Released October 19th, 2017

Bugfixes:
	* Fixes the mismatch in textdomain according to the plugin slug.
	* Fixes using an incorrect path when loading plugin data.
	* Fixes a bug with flexible content and repeaters, in combination with ACF 5, causing JavaScript errors.
	* Fixes a bug with short array notation which is a problem on sites running on PHP 5.3 or lower.
	* Fixes a bug where assets are loaded without checking for required dependencies.

= 2.0.0 =

Released August 22th, 2017

Changes:
	* Complete rewrite, including full support for ACF 4 and 5.

= 1.2.1 =

Released July 24th, 2017

Bugfixes:
	* Fixes Yoast SEO Premium social sharing tabs not showing any content when this plugin is active, props [Matt McAchran](https://github.com/mmcachran).

= 1.2.0 =

Released June 30th, 2016

* Bugfixes:
	* Fixes an incompatibility issue with Yoast SEO version 3.2+ where the assets are registered with a new prefix.

* Internationalization:
	* Improved text in notifications when dependencies are missing.
