<?php


namespace AlbumBundle\Service;

use AlbumBundle\Entity\PaginatedCollection;
use Doctrine\ORM\Query;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * Reusable Pagination System
 *
 * Class PaginationFactory
 * @package AlbumBundle\Helper
 */
class PaginationFactory
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * PaginationFactory constructor.
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Create a collection for any given request.
     *
     * @param Query $qb
     * @param Request $request
     * @param $maxPerPage
     * @param $route
     * @param $slug
     * @param array $routeParameters
     *
     * @return PaginatedCollection
     */
    public function createCollection(Query $qb, Request $request, $maxPerPage, $route, $slug,
                                     array $routeParameters = array()) {

        $adapter = new DoctrineORMAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);

        $page = $request->query->get('page', 1);
        $pagerfanta->setMaxPerPage($maxPerPage);
        $pagerfanta->setCurrentPage($page);

        $reviews = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $reviews[] = $result;
        }

        $paginatedCollection = new PaginatedCollection($reviews, $pagerfanta->getNbPages());


        $createLinkUrl = function ($slug, $targetPage) use ($route, $routeParameters) {
            return $this->router->generate($route, array_merge($routeParameters,
                array('slug' => $slug, 'page' => $targetPage)));
        };

        $paginatedCollection->addLink('self', $createLinkUrl($slug, $page));
        $paginatedCollection->addLink('first', $createLinkUrl($slug, 1));
        $paginatedCollection->addLink('last', $createLinkUrl($slug, $pagerfanta->getNbPages()));

        if ($pagerfanta->hasNextPage()) {
            $paginatedCollection->addLink('next', $createLinkUrl($slug, $pagerfanta->getNextPage()));
        }
        if ($pagerfanta->hasPreviousPage()) {
            $paginatedCollection->addLink('prev', $createLinkUrl($slug, $pagerfanta->getPreviousPage()));
        }

        return $paginatedCollection;
    }
}