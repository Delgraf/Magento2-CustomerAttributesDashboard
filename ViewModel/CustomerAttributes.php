<?php
declare(strict_types=1);

namespace Delgraf\CustomerAttributesDashboard\ViewModel;

use Delgraf\CustomerAttributesDashboard\Model\Data\AttributeRow;
use Delgraf\CustomerAttributesDashboard\Model\Formatter\AttributeValueFormatter;
use Delgraf\CustomerAttributesDashboard\Model\Relation\DependencyResolver;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Prepares the list of custom customer attributes for the dashboard template.
 */
class CustomerAttributes implements ArgumentInterface
{
    /** Form code Amasty uses for attributes editable in "Account Information". */
    private const FORM_CODE = 'customer_account_edit';

    /** @var AttributeRow[]|null */
    private ?array $rows = null;

    public function __construct(
        private readonly CustomerSession $customerSession,
        private readonly AttributeMetadataDataProvider $attributeMetadataDataProvider,
        private readonly StoreManagerInterface $storeManager,
        private readonly AttributeValueFormatter $formatter,
        private readonly DependencyResolver $dependencyResolver
    ) {
    }

    public function hasRows(): bool
    {
        return $this->getRows() !== [];
    }

    /**
     * @return AttributeRow[]
     */
    public function getRows(): array
    {
        if ($this->rows !== null) {
            return $this->rows;
        }

        $this->rows = [];
        if (!$this->customerSession->isLoggedIn()) {
            return $this->rows;
        }

        $customer = $this->customerSession->getCustomer();
        $customerId = (int)$customer->getId();
        $storeId = (int)$this->storeManager->getStore()->getId();

        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection('customer', self::FORM_CODE);

        // Pass 1: attributes rendered in the form, with their raw values (needed to evaluate relations).
        $displayable = [];
        $valuesById = [];
        foreach ($attributes as $attribute) {
            if (!$this->isDisplayable($attribute, $storeId)) {
                continue;
            }
            $attributeId = (int)$attribute->getId();
            $displayable[$attributeId] = $attribute;
            $valuesById[$attributeId] = $customer->getData($attribute->getAttributeCode());
        }

        // Pass 2: drop attributes hidden by Amasty relations, then format.
        $visibleIds = $this->dependencyResolver->filterVisible($valuesById);
        $sorted = [];
        foreach ($visibleIds as $attributeId) {
            $attribute = $displayable[$attributeId];
            $attribute->setStoreId($storeId);
            $row = $this->formatter->format($attribute, $valuesById[$attributeId], $customerId);
            if ($row !== null) {
                $sorted[] = ['order' => (int)$attribute->getData('sorting_order'), 'row' => $row];
            }
        }

        usort($sorted, static fn (array $a, array $b) => $a['order'] <=> $b['order']);
        $this->rows = array_column($sorted, 'row');

        return $this->rows;
    }

    /**
     * Only Amasty user-defined attributes, visible on frontend and enabled for the current store.
     */
    private function isDisplayable($attribute, int $storeId): bool
    {
        if (!$attribute->getIsUserDefined() || (string)$attribute->getData('is_visible_on_front') !== '1') {
            return false;
        }

        $storeIds = array_filter(explode(',', (string)$attribute->getData('store_ids')), 'strlen');

        return $storeIds === [] || in_array((string)$storeId, $storeIds, true) || in_array('0', $storeIds, true);
    }
}
