<?php
declare(strict_types=1);

namespace Delgraf\CustomerAttributesDashboard\Model\Formatter;

use Delgraf\CustomerAttributesDashboard\Model\Data\AttributeRow;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;

/**
 * Converts a raw EAV value into a human readable AttributeRow.
 * Reusable: it knows nothing about the page it is rendered on.
 */
class AttributeValueFormatter
{
    /** Amasty "type_internal" values that carry no customer value. */
    private const NON_VALUE_TYPES = ['statictext'];

    private const OPTION_TYPES = ['select', 'selectimg', 'selectgroup', 'radios'];
    private const MULTI_OPTION_TYPES = ['multiselect', 'multiselectimg', 'checkboxes'];
    private const FILE_TYPES = ['file', 'image'];

    public function __construct(
        private readonly TimezoneInterface $timezone,
        private readonly UrlInterface $urlBuilder,
        private readonly EncoderInterface $urlEncoder
    ) {
    }

    /**
     * Returns null when the attribute has nothing meaningful to display.
     */
    public function format(AbstractAttribute $attribute, mixed $rawValue, int $customerId): ?AttributeRow
    {
        $inputType = $this->resolveInputType($attribute);
        if (in_array($inputType, self::NON_VALUE_TYPES, true) || $this->isEmpty($rawValue)) {
            return null;
        }

        $code = (string)$attribute->getAttributeCode();
        $label = (string)($attribute->getStoreLabel() ?: $attribute->getFrontendLabel() ?: $code);

        if (in_array($inputType, self::FILE_TYPES, true)) {
            return $this->formatFile($code, $label, (string)$rawValue, $customerId);
        }

        $value = match (true) {
            in_array($inputType, self::OPTION_TYPES, true) => $this->getOptionText($attribute, $rawValue),
            in_array($inputType, self::MULTI_OPTION_TYPES, true) => $this->getMultiOptionText($attribute, $rawValue),
            $inputType === 'boolean' => (string)((bool)$rawValue ? __('Yes') : __('No')),
            $inputType === 'date' => $this->formatDate((string)$rawValue),
            default => is_array($rawValue) ? implode(', ', $rawValue) : (string)$rawValue,
        };

        if ($value === '') {
            return null;
        }

        $type = in_array($inputType, ['textarea', 'multiline'], true)
            ? AttributeRow::TYPE_MULTILINE
            : AttributeRow::TYPE_TEXT;

        return new AttributeRow($code, $label, $value, $type);
    }

    /**
     * Amasty stores its own widget type in "type_internal" (e.g. selectimg, statictext).
     */
    private function resolveInputType(AbstractAttribute $attribute): string
    {
        $typeInternal = (string)$attribute->getData('type_internal');

        return $typeInternal !== '' ? $typeInternal : (string)$attribute->getFrontendInput();
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [] || $value === false;
    }

    private function getOptionText(AbstractAttribute $attribute, mixed $rawValue): string
    {
        if (!$attribute->usesSource()) {
            return (string)$rawValue;
        }
        $text = $attribute->getSource()->getOptionText($rawValue);

        return is_array($text) ? implode(', ', $text) : (string)$text;
    }

    private function getMultiOptionText(AbstractAttribute $attribute, mixed $rawValue): string
    {
        $ids = is_array($rawValue) ? $rawValue : explode(',', (string)$rawValue);
        $labels = [];
        foreach (array_filter($ids, static fn ($id) => $id !== '') as $id) {
            $label = $this->getOptionText($attribute, trim((string)$id));
            if ($label !== '') {
                $labels[] = $label;
            }
        }

        return implode(', ', $labels);
    }

    private function formatDate(string $rawValue): string
    {
        try {
            // Dates are stored without timezone: format in UTC to avoid a one-day shift.
            return $this->timezone->formatDateTime(
                $rawValue,
                \IntlDateFormatter::MEDIUM,
                \IntlDateFormatter::NONE,
                null,
                'UTC'
            );
        } catch (\Exception) {
            return $rawValue;
        }
    }

    /**
     * Builds a download link through Amasty's own protected controller.
     */
    private function formatFile(string $code, string $label, string $rawValue, int $customerId): ?AttributeRow
    {
        $fileName = basename($rawValue);
        if ($fileName === '' || !str_contains($fileName, '.')) {
            return null;
        }

        $url = $this->urlBuilder->getUrl(
            'amcustomerattr/index/viewfile',
            [
                'file' => $this->urlEncoder->encode($fileName),
                'customer_id' => $customerId,
            ]
        );

        return new AttributeRow($code, $label, $fileName, AttributeRow::TYPE_LINK, $url);
    }
}
