<?php
declare(strict_types=1);

namespace Delgraf\CustomerAttributesDashboard\Model\Data;

/**
 * Immutable value object describing one attribute line rendered on the dashboard.
 */
class AttributeRow
{
    public const TYPE_TEXT = 'text';
    public const TYPE_MULTILINE = 'multiline';
    public const TYPE_LINK = 'link';

    public function __construct(
        private readonly string $code,
        private readonly string $label,
        private readonly string $value,
        private readonly string $type = self::TYPE_TEXT,
        private readonly ?string $url = null
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function isLink(): bool
    {
        return $this->type === self::TYPE_LINK && $this->url !== null;
    }

    public function isMultiline(): bool
    {
        return $this->type === self::TYPE_MULTILINE;
    }
}
