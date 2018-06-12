<?php declare(strict_types=1);

namespace Shopware\Core\Content\Product\Storefront;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shopware\Core\Checkout\CheckoutContext;
use Shopware\Core\Content\Product\Exception\ProductNotFoundException;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Api\Context\RestContext;
use Shopware\Core\Framework\Api\Response\ResponseFactory;
use Shopware\Core\Framework\ORM\Search\Criteria;
use Shopware\Core\Framework\ORM\Search\SearchCriteriaBuilder;
use Shopware\Core\Framework\Routing\Firewall\ContextUser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * @var \Shopware\Core\Content\Product\Storefront\StorefrontProductRepository
     */
    private $repository;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    public function __construct(
        StorefrontProductRepository $repository,
        ResponseFactory $responseFactory,
        SearchCriteriaBuilder $criteriaBuilder
    ) {
        $this->repository = $repository;
        $this->responseFactory = $responseFactory;
        $this->criteriaBuilder = $criteriaBuilder;
    }

    /**
     * @Route("/storefront-api/product", name="storefront.api.product.list")
     */
    public function list(Request $request, CheckoutContext $context): Response
    {
        $criteria = new Criteria();

        $criteria = $this->criteriaBuilder->handleRequest(
            $request,
            $criteria,
            ProductDefinition::class,
            $context->getContext()
        );

        $result = $this->repository->search($criteria, $context);

        return $this->responseFactory->createListingResponse(
            $result,
            ProductDefinition::class,
            new RestContext($request, $context->getContext(), null)
        );
    }

    /**
     * @Route("/storefront-api/product/{productId}", name="storefront.api.product.detail")
     * @Method({"GET"})
     */
    public function detail(string $productId, Request $request): Response
    {
        /** @var ContextUser $user */
        $user = $this->getUser();

        $products = $this->repository->readDetail([$productId], $user->getContext());
        if (!$products->has($productId)) {
            throw new ProductNotFoundException($productId);
        }

        return $this->responseFactory->createDetailResponse(
            $products->get($productId),
            ProductDefinition::class,
            new RestContext($request, $user->getContext()->getContext(), null)
        );
    }
}