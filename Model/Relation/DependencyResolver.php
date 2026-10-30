<?php
declare(strict_types=1);

namespace Delgraf\CustomerAttributesDashboard\Model\Relation;

/**
 * Decides whether an attribute is visible according to the Amasty relations,
 * replicating the frontend logic of Amasty_CustomerAttributes/js/view/relation.js:
 *
 * - an attribute without relations is always visible;
 * - relations whose parent is not rendered in the form are ignored
 *   (Amasty never hides the dependent in that case);
 * - otherwise the attribute is visible only if at least one rendered parent
 *   is itself visible and has the related option selected.
 *
 * Stateless and reusable: it receives every input it needs.
 */
class DependencyResolver
{
    /** Guard against misconfigured circular relations. */
    private const MAX_DEPTH = 50;

    public function __construct(
        private readonly RelationProvider $relationProvider
    ) {
    }

    /**
     * @param array<int, mixed> $valuesById Raw customer values indexed by attribute ID,
     *                                      for every attribute rendered in the form.
     * @return int[] IDs of the attributes that are visible.
     */
    public function filterVisible(array $valuesById): array
    {
        $relations = $this->relationProvider->getRelationsByDependent();
        $cache = [];
        $visible = [];

        foreach (array_keys($valuesById) as $attributeId) {
            if ($this->isVisible((int)$attributeId, $valuesById, $relations, $cache, 0)) {
                $visible[] = (int)$attributeId;
            }
        }

        return $visible;
    }

    /**
     * @param array<int, mixed> $valuesById
     * @param array<int, array<int, array{parent_id: int, option_id: string}>> $relations
     * @param array<int, bool> $cache
     */
    private function isVisible(
        int $attributeId,
        array $valuesById,
        array $relations,
        array &$cache,
        int $depth
    ): bool {
        if (isset($cache[$attributeId])) {
            return $cache[$attributeId];
        }
        if ($depth > self::MAX_DEPTH) {
            return false;
        }

        // Only relations whose parent is actually rendered can hide the attribute.
        $activeRelations = array_filter(
            $relations[$attributeId] ?? [],
            static fn (array $relation) => array_key_exists($relation['parent_id'], $valuesById)
        );

        $result = $activeRelations === [];
        foreach ($activeRelations as $relation) {
            $parentId = $relation['parent_id'];
            if (in_array($relation['option_id'], $this->toOptionIds($valuesById[$parentId]), true)
                && $this->isVisible($parentId, $valuesById, $relations, $cache, $depth + 1)
            ) {
                $result = true;
                break;
            }
        }

        return $cache[$attributeId] = $result;
    }

    /**
     * Normalizes select / multiselect values ("12", "12,15", [12, 15]) to string option IDs.
     *
     * @return string[]
     */
    private function toOptionIds(mixed $value): array
    {
        if ($value === null || $value === '' || $value === false) {
            return [];
        }
        $ids = is_array($value) ? $value : explode(',', (string)$value);

        return array_values(array_filter(array_map(static fn ($id) => trim((string)$id), $ids), 'strlen'));
    }
}
