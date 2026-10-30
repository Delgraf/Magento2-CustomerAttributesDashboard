# Changelog

All notable changes to this project are documented in this file.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and the project adheres to [Semantic Versioning](https://semver.org/).

## [1.2.1] - 2026-10-30

### Changed
- Section title uses the theme `.box-title` class, so spacing and font match "Contact Information".
- Attribute labels are no longer bold.

## [1.2.0] - 2026-10-30

### Added
- "Additional Settings" section title above the attributes (translatable, configurable via the `title` layout argument).

### Changed
- Markup: root element is now a `div.delgraf-customer-attributes` containing `__title` and `dl.delgraf-customer-attributes__list`.

## [1.1.0] - 2026-10-30

### Fixed
- Amasty attribute relations (dependencies) are now respected: dependent attributes are hidden when the parent option is not selected or the parent itself is hidden, matching the Amasty account form.

## [1.0.0] - 2026-10-30

### Added
- Display of Amasty custom customer attributes in the "Contact Information" box of the customer dashboard, below the e-mail address.
- Human readable values for select, multiselect (including image variants), boolean, date, textarea and multiline attributes.
- Secure download link for file/image attributes through Amasty's `amcustomerattr/index/viewfile` controller.
- Respect of Amasty "Visible on Frontend", store visibility and sorting order settings.
- Italian translation.

[1.2.1]: https://github.com/Delgraf/Magento2-CustomerAttributesDashboard/releases/tag/v1.2.1
[1.2.0]: https://github.com/Delgraf/Magento2-CustomerAttributesDashboard/releases/tag/v1.2.0
[1.1.0]: https://github.com/Delgraf/Magento2-CustomerAttributesDashboard/releases/tag/v1.1.0
[1.0.0]: https://github.com/Delgraf/Magento2-CustomerAttributesDashboard/releases/tag/v1.0.0
