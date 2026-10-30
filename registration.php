<?php
/**
 * Delgraf_CustomerAttributesDashboard
 *
 * Shows Amasty custom customer attributes on the "My Account" dashboard,
 * right below the customer e-mail in the "Contact Information" box.
 */
declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Delgraf_CustomerAttributesDashboard',
    __DIR__
);
