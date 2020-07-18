<?php

declare(strict_types=1);

namespace Model\Cashbook\Repositories;

use eGen\MessageBus\Bus\QueryBus;
use Model\Cashbook\Cashbook\CashbookId;
use Model\Cashbook\Cashbook\CashbookType;
use Model\Cashbook\CategoryNotFound;
use Model\Cashbook\ICategory;
use Model\Cashbook\ReadModel\Queries\SkautisIdQuery;
use function array_merge;
use function sprintf;

class CategoryRepository
{
    private ICampCategoryRepository $campCategories;

    private IStaticCategoryRepository $staticCategories;

    private QueryBus $queryBus;

    public function __construct(
        ICampCategoryRepository $campCategories,
        IStaticCategoryRepository $staticCategories,
        QueryBus $queryBus
    ) {
        $this->campCategories   = $campCategories;
        $this->staticCategories = $staticCategories;
        $this->queryBus         = $queryBus;
    }

    /**
     * @return ICategory[]
     */
    public function findForCashbook(CashbookId $cashbookId, CashbookType $type) : array
    {
        $skautisType = $type->getSkautisObjectType();
        $categories  = $this->staticCategories->findByObjectType($skautisType);

        if (! $skautisType->equalsValue(CashbookType::CAMP)) {
            return $categories;
        }

        $campId         = $this->queryBus->handle(new SkautisIdQuery($cashbookId));
        $campCategories = $this->campCategories->findForCamp($campId);

        return array_merge($categories, $campCategories);
    }

    /**
     * @throws CategoryNotFound
     */
    public function find(int $categoryId, CashbookId $cashbookId, CashbookType $type) : ICategory
    {
        if ($type->equalsValue(CashbookType::CAMP)) {
            foreach ($this->findForCashbook($cashbookId, $type) as $category) {
                if ($category->getId() === $categoryId) {
                    return $category;
                }
            }

            throw new CategoryNotFound(
                sprintf('Category #%d for cashbook #%d not found', $categoryId, $cashbookId)
            );
        }

        $category = $this->staticCategories->find($categoryId);

        if (! $category->supportsType($type->getSkautisObjectType())) {
            throw new CategoryNotFound(
                sprintf("Category #%d found, but it doesn't support cashbook type %s", $categoryId, $type->getValue())
            );
        }

        return $category;
    }
}
