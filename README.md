=== IGW Admin Cleanup ===
Contributors: crishnakh
Tags: admin, dashboard, cleanup, css, hide
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.3.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Clean up your WordPress admin area by hiding unwanted plugin notices, upgrade links, banners and other unnecessary elements.

== Description ==

IGW Admin Cleanup helps you keep the WordPress administration area clean by hiding unnecessary elements added by WordPress plugins.

Many plugins permanently display upgrade links, premium version promotions, banners, commercial notices, calls to action and other interface elements that you may not need during everyday use.

IGW Admin Cleanup allows you to hide these elements without modifying WordPress core files or third-party plugins.

Each element is stored as an individual cleanup rule that can be enabled, disabled, edited or deleted from the plugin administration panel.

= Cleanup rules =

Each rule contains a CSS selector identifying the element you want to hide.

For example:

`#wfMenuCallout`

`.plugin-upgrade-notice`

`.premium-banner`

IGW Admin Cleanup detects matching elements in the WordPress administration area and automatically hides them.

Rules can perform different cleanup actions depending on the structure of the element:

* Hide the selected element.
* Hide its direct parent.
* Hide the closest `<li>` element.
* Remove the element from the DOM.

= Dynamic elements =

IGW Admin Cleanup can also detect elements dynamically added to the WordPress administration interface after the page has loaded.

This is useful for plugins that generate notices, banners or promotional elements using JavaScript.

The plugin monitors relevant changes in the administration interface and applies your cleanup rules when matching elements appear.

= Rule detection =

IGW Admin Cleanup keeps basic information about rule activity.

The administration panel can show:

* Whether a rule is active or disabled.
* Whether the selector has been detected.
* When the selector was last checked.
* When the element was last detected.
* How many recorded detections have occurred.

This information can help identify selectors that may have changed after a third-party plugin update.

= Search and filters =

Cleanup rules can be searched and filtered directly from the administration panel.

Available filters include:

* Active or disabled rules.
* Recently detected rules.
* Rules that have never been detected.
* Rules not detected recently.
* Plugin or source.

Filters can be combined to quickly locate specific cleanup rules.

= Automatic migration =

Older versions of IGW Admin Cleanup used a textarea containing one CSS selector per line.

When upgrading, existing selectors are automatically migrated to the new rule system so previous cleanup configurations can continue to be used.

= What can IGW Admin Cleanup hide? =

You can use IGW Admin Cleanup to hide elements such as:

* Premium or Pro upgrade links.
* Promotional banners.
* Commercial notices.
* Calls to action.
* Plugin menu entries.
* Dashboard elements.
* Other unnecessary elements in the WordPress administration area.

= Important =

IGW Admin Cleanup only changes the administration interface.

It does not disable plugin functionality, license checks, restrictions or premium features.

It does not modify the files of other plugins.

CSS selectors used by third-party plugins may change after an update. If this happens, the corresponding cleanup rule may need to be updated.

== Installation ==

1. Upload the `igw-admin-cleanup` folder to the `/wp-content/plugins/` directory, or install the plugin from the WordPress Plugins screen.
2. Activate IGW Admin Cleanup from the Plugins screen.
3. Go to Settings → IGW Admin Cleanup.
4. Click "Add rule".
5. Enter the CSS selector of the element you want to hide.
6. Select the cleanup action.
7. Save the rule.

== Frequently Asked Questions ==

= Does IGW Admin Cleanup modify other plugins? =

No. IGW Admin Cleanup does not modify the files or configuration of other plugins. It only changes how selected elements are displayed in the WordPress administration interface.

= Will my rules disappear when another plugin is updated? =

No. Your IGW Admin Cleanup rules remain stored independently.

However, if another plugin changes its HTML structure or CSS selectors, the corresponding cleanup rule may need to be updated.

= Can I create multiple cleanup rules? =

Yes. You can create and manage multiple independent cleanup rules from the IGW Admin Cleanup administration panel.

= Can I temporarily disable a rule? =

Yes. Rules can be enabled or disabled without deleting them.

= Does IGW Admin Cleanup disable Premium features? =

No. IGW Admin Cleanup only hides elements from the administration interface. It does not enable, disable or modify Premium features, licenses or restrictions.

= Does it affect the public website? =

No. IGW Admin Cleanup operates only inside the WordPress administration area.

= Can it hide elements loaded with JavaScript? =

Yes. IGW Admin Cleanup monitors changes in the administration interface and can apply cleanup rules to matching elements that are dynamically added after the initial page load.

= What does "Last seen" mean? =

"Last seen" indicates when IGW Admin Cleanup last detected an element matching that rule.

= What does "Last checked" mean? =

"Last checked" indicates when the CSS selector was last evaluated by IGW Admin Cleanup.

A recently checked rule that has not been detected does not necessarily mean the rule is obsolete. Some elements only appear on specific WordPress administration screens.

== Screenshots ==

1. IGW Admin Cleanup dashboard showing rule statistics, search, filters and detection status.
2. Creating or editing an individual cleanup rule.
3. Cleanup rules managing unwanted plugin elements in the WordPress administration area.

== Changelog ==

= 0.3.3 =
* Added multiple selector suggestions to the assisted element selector.
* Added support for generating selector candidates from IDs, CSS classes, data attributes and other useful HTML attributes.
* Added selector quality evaluation to help identify reliable and fragile selectors.
* Added quality indicators for suggested selectors: Good option, Acceptable and Fragile.
* Added detection of potentially dynamic selector values, including UUID-like strings, long numeric values and generated identifiers.
* Improved selector candidate ordering based on uniqueness and estimated quality.
* Added cleanup action selection directly to the element selector assistant.
* Preview now reflects the selected cleanup action before creating the rule.

= 0.3.2 =
* Added selector match count to the assisted element selector.
* Added visual warnings when a selector matches multiple elements.
* Added live match count updates when editing the suggested CSS selector.
* Added a preview option to temporarily hide matching elements before creating a rule.
* Added a restore option to safely undo the preview.
* Improved the assisted selector workflow to help prevent overly broad cleanup rules.

= 0.3.1 =
* Fixed the plugin text domain to match the WordPress.org plugin slug.
* Improved compatibility with WordPress.org language packs.
* Added Spanish translation.

= 0.3.0 =
* Added assisted element selection for creating cleanup rules.
* Added automatic CSS selector suggestions.
* Improved rule creation workflow.

= 0.2.0 =
* Replaced the legacy textarea with individual cleanup rules.
* Added automatic migration of selectors from previous versions.
* Added rule creation, editing, deletion and enable/disable controls.
* Added multiple cleanup actions.
* Added support for dynamically loaded admin elements.
* Added rule detection tracking.
* Added last checked and last detected information.
* Added rule detection counters.
* Added search and filtering.
* Added plugin/source filtering.
* Added rule status and detection indicators.
* Added administration dashboard with rule statistics.
* Improved the administration interface.

= 0.1.2 =
* Updated the main plugin filename to match the plugin directory name.

= 0.1.1 =
* Updated plugin strings to English.

= 0.1.0 =
* Initial release.
* Added support for hiding WordPress admin elements using CSS selectors.
* Added support for multiple selectors.
* Added automatic hiding of the closest list item when applicable.

== Upgrade Notice ==

= 0.3.3 =
Adds smarter selector suggestions, quality indicators and cleanup action previews to make assisted rule creation more precise and reliable.

= 0.3.2 =
Adds selector match information and live preview, allowing you to verify what will be hidden before creating a cleanup rule.

= 0.3.1 =
Fixes translation loading and adds Spanish language support.

= 0.3.0 =
Adds assisted element selection and CSS selector suggestions to make creating cleanup rules easier.

= 0.2.0 =
Introduces the new cleanup rule system, dynamic element detection, rule activity tracking, search, filters and an improved administration interface.