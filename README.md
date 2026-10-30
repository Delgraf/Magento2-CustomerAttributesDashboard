# Delgraf Customer Attributes Dashboard for Magento 2

Displays the custom customer attributes created with
[Amasty Customer Attributes](https://amasty.com/customer-attributes-for-magento-2.html)
on the **My Account** dashboard (`/customer/account/`), inside the
*Contact Information* box, right below the customer e-mail address.

![Magento 2.4](https://img.shields.io/badge/Magento-2.4.x-orange)
![PHP](https://img.shields.io/badge/PHP-8.1%20%7C%208.2%20%7C%208.3%20%7C%208.4-blue)
![License](https://img.shields.io/badge/license-OSL--3.0%20%7C%20AFL--3.0-green)

## Features

- Shows every user-defined Amasty attribute that is **Visible on Frontend** and enabled for the current store view.
- Follows the Amasty **Sorting Order**.
- Renders readable values:
  - select / radio / select with images → option label
  - multiselect / checkboxes / multiselect with images → comma separated labels
  - Yes/No → localized *Yes* / *No*
  - date → localized medium format
  - textarea / multiline → line breaks preserved
  - file / image → *Download* link served by Amasty's protected controller (customers can only access their own files)
- Empty values and *Static Text* attributes are skipped.
- Honours Amasty **Attribute Relations**: a dependent attribute is shown only when its parent
  attribute is visible and has the related option selected (same rules as the Amasty frontend form),
  so stale values left over after changing the parent option are not displayed.
- Section title *Additional Settings* (translatable) above the attributes; change or hide it via layout (see *Customization*).
- No core or Amasty files are overridden; no database changes.

## Requirements

| Package | Version |
|---|---|
| Magento Open Source / Adobe Commerce | 2.4.x |
| PHP | 8.1 – 8.4 |
| amasty/module-customer-attributes | ^2.6 |

> Amasty packages are distributed through Amasty's private Composer repository
> (`composer.amasty.com`). It must already be configured in your project.

## Installation

### Composer (recommended)

```bash
composer require delgraf/module-customer-attributes-dashboard
bin/magento module:enable Delgraf_CustomerAttributesDashboard
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

### Manual

Copy the repository content into `app/code/Delgraf/CustomerAttributesDashboard`,
then run the same `bin/magento` commands listed above.

## How it works

Magento's core template `Magento_Customer::account/dashboard/info.phtml` calls

```php
<?= $block->getChildHtml('customer.account.dashboard.info.extra') ?>
```

right after the e-mail address. The module adds its block with that alias in
`customer_account_index.xml`, so nothing else needs to be changed.

### Custom themes

If your theme overrides `Magento_Customer/templates/account/dashboard/info.phtml`
and nothing is displayed, add the line above to the theme template, right after
the paragraph containing the e-mail.

## Styling

The markup uses BEM classes that can be targeted from your theme:

```text
.delgraf-customer-attributes
.delgraf-customer-attributes__title
.delgraf-customer-attributes__list
.delgraf-customer-attributes__item
.delgraf-customer-attributes__item--{attribute_code}
.delgraf-customer-attributes__label
.delgraf-customer-attributes__value
```

## Customization

The section title is a layout argument. To change it (or hide it with an empty value)
add `customer_account_index.xml` to your theme:

```xml
<referenceBlock name="delgraf.customer.dashboard.attributes">
    <arguments>
        <argument name="title" xsi:type="string" translate="true">My Preferences</argument>
    </arguments>
</referenceBlock>
```

## Architecture

```text
Model/Data/AttributeRow.php                 Immutable value object for one displayed line
Model/Formatter/AttributeValueFormatter.php Raw EAV value → readable AttributeRow
Model/Relation/RelationProvider.php         Reads Amasty relations (parent option → dependent attribute)
Model/Relation/DependencyResolver.php       Applies the relations to decide which attributes are visible
ViewModel/CustomerAttributes.php            Loads, filters and sorts attributes for the template
view/frontend/layout/customer_account_index.xml
view/frontend/templates/account/dashboard/attributes.phtml
view/frontend/web/css/source/_module.less
```

## Uninstall

```bash
bin/magento module:disable Delgraf_CustomerAttributesDashboard
composer remove delgraf/module-customer-attributes-dashboard
bin/magento setup:upgrade
bin/magento cache:flush
```

## License

Dual licensed, like Magento Open Source, under the
[Open Software License 3.0 (OSL-3.0)](LICENSE.txt) and the
[Academic Free License 3.0 (AFL-3.0)](LICENSE_AFL.txt).

Copyright © Delgraf

*Amasty is a trademark of its respective owner. This module is an independent
extension and is not affiliated with or endorsed by Amasty.*
