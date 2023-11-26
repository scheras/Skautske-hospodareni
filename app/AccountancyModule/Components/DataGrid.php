<?php

declare(strict_types=1);

namespace App\AccountancyModule\Components;

use Ublaboo\DataGrid\Column\Action;
use Ublaboo\DataGrid\Filter\FilterSelect;

use function array_map;
use function array_reverse;
use function date;
use function range;
use function Safe\array_combine;

final class DataGrid extends \Ublaboo\DataGrid\DataGrid
{
    public const OPTION_ALL = 'all';

    public const SORT_ASC  = 'ASC';
    public const SORT_DESC = 'DESC';

    public function __construct()
    {
        parent::__construct();

        Action::$dataConfirmAttributeName = 'data-confirm';
        $this->onRender[]                 = function (): void {
 //disable autocomplete - issue #1443
            $this['filter']->getElementPrototype()->setAttribute('autocomplete', 'off');
        };
    }

    /**
     * Forces datagrid to filter and sort data source and returns inner data
     *
     * @return mixed[]
     */
    public function getFilteredAndSortedData(): array
    {
        return $this->dataModel->filterData(
            $this->getPaginator(),
            $this->createSorting($this->sort, $this->sortCallback),
            $this->assembleFilters(),
        );
    }

    public function addYearFilter(string $name, string $label): FilterSelect
    {
        return $this->addFilterSelect($name, $label, $this->getYearOptions(), 'year');
    }

    /** @return array<string, string> */
    private function getYearOptions(): array
    {
        $years = array_map(
            function (int $year): string {
                return (string) $year;
            },
            array_reverse(range(2012, (int) date('Y') + 1)),
        );

        return [self::OPTION_ALL => 'Všechny'] + array_combine($years, $years);
    }
}
