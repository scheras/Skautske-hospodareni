<?php

declare(strict_types=1);

namespace Model\Infrastructure\Repositories\Cashbook;

use Doctrine\ORM\EntityManager;
use eGen\MessageBus\Bus\EventBus;
use IntegrationTest;
use Model\Cashbook\Category;
use Model\Cashbook\ObjectType;
use function array_map;

class StaticCategoryRepositoryTest extends IntegrationTest
{
    private const TABLE_NAME   = 'ac_chitsCategory';
    private const OBJECT_TABLE = 'ac_chitsCategory_object';

    private StaticCategoryRepository $repository;

    protected function _before() : void
    {
        $this->tester->useConfigFiles(['config/doctrine.neon']);
        parent::_before();
        $this->repository = new StaticCategoryRepository($this->tester->grabService(EntityManager::class), new EventBus());
    }

    /**
     * @return string[]
     */
    protected function getTestedAggregateRoots() : array
    {
        return [Category::class];
    }

    public function testFindByObjectType() : void
    {
        $this->tester->haveInDatabase(self::TABLE_NAME, [
            'label' => 'Category 1',
            'short' => 'c1',
            'type' => 'in',
            'virtual' => false,
            'orderby' => 300,
            'deleted' => 0,
        ]);
        $this->tester->haveInDatabase(self::TABLE_NAME, [
            'label' => 'Category 2',
            'short' => 'c2',
            'type' => 'out',
            'virtual' => false,
            'orderby' => 400,
            'deleted' => 0,
        ]);
        $this->tester->haveInDatabase(self::TABLE_NAME, [
            'label' => 'Category 3',
            'short' => 'c3',
            'type' => 'out',
            'virtual' => false,
            'orderby' => 400,
            'deleted' => 0,
        ]);

        $this->tester->haveInDatabase(self::OBJECT_TABLE, [
            'categoryId' => 1,
            'objectTypeId' => ObjectType::EVENT,
        ]);
        $this->tester->haveInDatabase(self::OBJECT_TABLE, [
            'categoryId' => 3,
            'objectTypeId' => ObjectType::EVENT,
        ]);

        $categories = $this->repository->findByObjectType(ObjectType::get(ObjectType::EVENT));

        $expectedNames = [
            'Category 3',
            'Category 1',
        ];

        $actualNames = array_map(static function (Category $category) {
            return $category->getName();
        }, $categories);

        $this->assertSame($expectedNames, $actualNames);
    }
}
