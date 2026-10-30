<?php
declare(strict_types=1);

namespace Delgraf\CustomerAttributesDashboard\Model\Relation;

use Amasty\CustomerAttributes\Model\ResourceModel\RelationDetails\CollectionFactory;

/**
 * Reads the attribute relations (dependencies) configured in
 * Customers > Customer Attributes Relations (Amasty).
 */
class RelationProvider
{
    /** @var array<int, array<int, array{parent_id: int, option_id: string}>>|null */
    private ?array $relations = null;

    public function __construct(
        private readonly CollectionFactory $relationDetailsCollectionFactory
    ) {
    }

    /**
     * Relations indexed by dependent attribute ID.
     *
     * A dependent attribute is shown when at least one of its parents
     * has the related option selected.
     *
     * @return array<int, array<int, array{parent_id: int, option_id: string}>>
     */
    public function getRelationsByDependent(): array
    {
        if ($this->relations !== null) {
            return $this->relations;
        }

        $this->relations = [];
        foreach ($this->relationDetailsCollectionFactory->create() as $detail) {
            $dependentId = (int)$detail->getDependentAttributeId();
            $this->relations[$dependentId][] = [
                'parent_id' => (int)$detail->getAttributeId(),
                'option_id' => (string)$detail->getOptionId(),
            ];
        }

        return $this->relations;
    }
}
