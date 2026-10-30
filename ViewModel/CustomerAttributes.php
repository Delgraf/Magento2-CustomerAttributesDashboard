<?php
declare(strict_types=1);

namespace Delgraf\CustomerAttributesDashboard\ViewModel;

use Delgraf\CustomerAttributesDashboard\Model\Data\AttributeRow;
use Delgraf\CustomerAttributesDashboard\Model\Formatter\AttributeValueFormatter;
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
        private readonly AttributeValueFormatter $formatter
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
        $sorted = [];
        foreach ($attributes as $attribute) {
            if (!$this->isDisplayable($attribute, $storeId)) {
                continue;
            }
            $attribute->setStoreId($storeId);
            $row = $this->formatter->format(
                $attribute,
                $customer->getData($attribute->getAttributeCode()),
                $customerId
            );
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
