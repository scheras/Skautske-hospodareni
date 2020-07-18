<?php

declare(strict_types=1);

namespace Model\Cashbook\Handlers\Unit;

use CommandHandlerTest;
use Model\Cashbook\Cashbook;
use Model\Cashbook\Cashbook\CashbookType;
use Model\Cashbook\Commands\Unit\CreateUnit;
use Model\Cashbook\Unit;
use Model\Common\UnitId;
use Model\Payment\UnitResolverStub;
use function assert;

final class CreateUnitHandlerTest extends CommandHandlerTest
{
    private const YEAR           = 2019;
    private const OFFICIAL_UNITS = [
        1 => 1,
        2 => 1,
    ];

    protected function _before() : void
    {
        $this->tester->useConfigFiles([__DIR__ . '/CreateUnitHandlerTest.neon']);
        parent::_before();
        $this->tester->grabService(UnitResolverStub::class)->setOfficialUnits(self::OFFICIAL_UNITS);
    }

    /**
     * @return string[]
     */
    protected function getTestedAggregateRoots() : array
    {
        return [
            Unit::class,
            Cashbook::class,
        ];
    }

    /**
     * @dataProvider dataCashbookTypes
     */
    public function testUnitCashbookAndCashbookAggregateAreCreated(CashbookType $cashbookType, UnitId $unitId) : void
    {
        $this->commandBus->handle(new CreateUnit($unitId, self::YEAR));

        $unit = $this->entityManager->find(Unit::class, $unitId);
        assert($unit instanceof Unit);
        $cashbookId = $unit->getCashbooks()[0]->getCashbookId();

        $cashbook = $this->entityManager->find(Cashbook::class, $cashbookId);
        assert($cashbook instanceof Cashbook);
        $this->assertTrue($cashbook->getType()->equals($cashbookType), 'Correct cashbook type is assigned');
    }

    /**
     * @return mixed[][]
     */
    public function dataCashbookTypes() : array
    {
        return [
            [CashbookType::get(CashbookType::OFFICIAL_UNIT), new UnitId(1)],
            [CashbookType::get(CashbookType::TROOP), new UnitId(2)],
        ];
    }
}
