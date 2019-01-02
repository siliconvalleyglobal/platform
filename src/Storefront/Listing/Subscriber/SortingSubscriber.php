<?php declare(strict_types=1);

namespace Shopware\Storefront\Listing\Subscriber;

use Shopware\Core\Framework\DataAbstractionLayer\RepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Listing\ListingSortingEntity;
use Shopware\Storefront\Listing\Event\ListingPageLoadedEvent;
use Shopware\Storefront\Listing\Event\ListingPageRequestEvent;
use Shopware\Storefront\Listing\Event\PageCriteriaCreatedEvent;
use Shopware\Storefront\Listing\ListingEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SortingSubscriber implements EventSubscriberInterface
{
    public const SORTING_PARAMETER = 'o';

    /**
     * @var RepositoryInterface
     */
    private $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public static function getSubscribedEvents()
    {
        return [
            ListingEvents::CRITERIA_CREATED => 'buildCriteria',
            ListingEvents::LOADED => 'buildPage',
            ListingEvents::REQUEST => 'transformRequest',
        ];
    }

    public function transformRequest(ListingPageRequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->query->has(self::SORTING_PARAMETER)) {
            return;
        }

        $event->getListingPageRequest()->setSortingKey(
            $request->query->get(self::SORTING_PARAMETER)
        );
    }

    public function buildCriteria(PageCriteriaCreatedEvent $event): void
    {
        $request = $event->getRequest();

        $search = new Criteria();
        $search->addFilter(new EqualsFilter('listing_sorting.uniqueKey', $request->getSortingKey()));
        $sortings = $this->repository->search($search, $event->getContext());

        if ($sortings->count() <= 0) {
            return;
        }

        /** @var ListingSortingEntity $sorting */
        $sorting = $sortings->first();
        $criteria = $event->getCriteria();
        foreach ($sorting->getPayload() as $fieldSorting) {
            $criteria->addSorting($fieldSorting);
        }
    }

    public function buildPage(ListingPageLoadedEvent $event): void
    {
        $search = new Criteria();
        $sortings = $this->repository->search($search, $event->getContext());

        foreach ($sortings as $sort) {
            $event->getPage()->getSortings()->add($sort);
        }

        $event->getPage()->setCurrentSorting(
            $event->getRequest()->getSortingKey()
        );
    }
}