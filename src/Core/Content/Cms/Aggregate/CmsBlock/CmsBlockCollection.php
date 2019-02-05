<?php declare(strict_types=1);

namespace Shopware\Core\Content\Cms\Aggregate\CmsBlock;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class CmsBlockCollection extends EntityCollection
{
    public function getSlots(): CmsSlotCollection
    {
        $slots = new CmsSlotCollection();

        /** @var CmsBlockEntity $block */
        foreach ($this->getElements() as $block) {
            if (!$block->getSlots()) {
                continue;
            }

            $slots->merge($block->getSlots());
        }

        return $slots;
    }

    public function setSlots(CmsSlotCollection $slots): void
    {
        /** @var CmsBlockEntity $block */
        foreach ($this->elements as $block) {
            $blockSlots = $block->getSlots();
            if (!$blockSlots) {
                continue;
            }

            foreach ($blockSlots->getIds() as $slotId) {
                $blockSlots->set($slotId, $slots->get($slotId));
            }
        }
    }

    protected function getExpectedClass(): string
    {
        return CmsBlockEntity::class;
    }
}