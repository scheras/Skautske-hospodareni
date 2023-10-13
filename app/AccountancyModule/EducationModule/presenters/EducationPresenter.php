<?php

declare(strict_types=1);

namespace App\AccountancyModule\EducationModule;

use Model\Auth\Resources\Education;
use Model\Cashbook\Cashbook\CashbookId;
use Model\Cashbook\Cashbook\PaymentMethod;
use Model\Cashbook\MissingCategory;
use Model\Cashbook\ReadModel\Queries\CashbookQuery;
use Model\Cashbook\ReadModel\Queries\EducationCashbookIdQuery;
use Model\Cashbook\ReadModel\Queries\FinalRealBalanceQuery;
use Model\DTO\Cashbook\Cashbook;
use Model\Event\EducationLocation;
use Model\Event\EducationTerm;
use Model\Event\ReadModel\Queries\EducationCourseParticipationStatsQuery;
use Model\Event\ReadModel\Queries\EducationCoursesQuery;
use Model\Event\ReadModel\Queries\EducationFunctions;
use Model\Event\ReadModel\Queries\EducationInstructorsQuery;
use Model\Event\ReadModel\Queries\EducationLocationsQuery;
use Model\Event\ReadModel\Queries\EducationParticipantParticipationStatsQuery;
use Model\Event\ReadModel\Queries\EducationTermsQuery;
use Model\Event\SkautisEducationId;
use Model\ExportService;
use Model\Grant\ReadModel\Queries\GrantQuery;
use Model\Services\PdfRenderer;

use function array_filter;
use function array_map;
use function array_sum;
use function array_unique;
use function assert;
use function count;
use function implode;
use function in_array;

class EducationPresenter extends BasePresenter
{
    public function __construct(
        protected ExportService $exportService,
        private PdfRenderer $pdf,
    ) {
        parent::__construct();
    }

    public function renderDefault(int|null $aid): void
    {
        if ($aid === null) {
            $this->redirect('Default:');
        }

        $cashbook = $this->queryBus->handle(new CashbookQuery($this->getCashbookId($aid)));
        assert($cashbook instanceof Cashbook);

        try {
            $finalRealBalance = $this->queryBus->handle(new FinalRealBalanceQuery($this->getCashbookId($aid)));
        } catch (MissingCategory) {
            $finalRealBalance = null;
        }

        $grant                         = $this->event->grantId !== null
            ? $this->queryBus->handle(new GrantQuery($this->event->grantId->toInt()))
            : null;
        $terms                         = $this->queryBus->handle(new EducationTermsQuery($aid));
        $instructors                   = $this->queryBus->handle(new EducationInstructorsQuery($aid));
        $courseParticipationStats      = $this->queryBus->handle(new EducationCourseParticipationStatsQuery($aid));
        $courses                       = $this->queryBus->handle(new EducationCoursesQuery($aid));
        $locations                     = $this->queryBus->handle(new EducationLocationsQuery($aid));
        $participantParticipationStats = $this->event->grantId !== null
            ? $this->queryBus->handle(new EducationParticipantParticipationStatsQuery($this->event->grantId->toInt()))
            : null;

        $termLocationIds     = array_map(
            static function (EducationTerm $term) {
                return $term->locationId;
            },
            $terms,
        );
        $locationsUsedInTerm = array_filter(
            $locations,
            static function (EducationLocation $location) use ($termLocationIds) {
                return in_array($location->id, $termLocationIds);
            },
        );

        $this->template->setParameters([
            'skautISUrl'       => $this->userService->getSkautisUrl(),
            'accessDetail'     => $this->authorizator->isAllowed(Education::ACCESS_DETAIL, $aid),
            'location'         => implode(
                ', ',
                array_map(
                    static function (EducationLocation $location) {
                        return $location->name;
                    },
                    $locationsUsedInTerm,
                ),
            ),
            'functions' => $this->authorizator->isAllowed(Education::ACCESS_FUNCTIONS, $aid)
                ? $this->queryBus->handle(new EducationFunctions(new SkautisEducationId($aid)))
                : null,
            'finalRealBalance' => $finalRealBalance,
            'prefixCash'           => $cashbook->getChitNumberPrefix(PaymentMethod::CASH()),
            'prefixBank'           => $cashbook->getChitNumberPrefix(PaymentMethod::BANK()),
            'totalDays'            => $this->countDays($terms),
            'teamCount'            => count($instructors),
            'participantsCapacity' => self::propertySum($courseParticipationStats, 'capacity'),
            'participantsAccepted' => self::propertySum($courseParticipationStats, 'accepted'),
            'personDaysEstimated'  => self::propertySum($courses, 'estimatedPersonDays'),
            'personDaysReal'       => $participantParticipationStats !== null
                ? self::propertySum($participantParticipationStats, 'totalDays')
                : null,
            'grantAmountMax'       => $grant?->amountMax,
            'grantAmountMaxReal'   => $grant?->amountMaxReal,
            'grantCostRatio'       => $grant?->costRatio,
            'grantRemainingPay'    => $grant?->remainingPay,
        ]);

        if (! $this->isAjax()) {
            return;
        }

        $this->redrawControl('contentSnip');
    }

    public function renderReport(int $aid): void
    {
        if (! $this->authorizator->isAllowed(Education::ACCESS_DETAIL, $aid)) {
            $this->flashMessage('Nemáte právo přistupovat k akci', 'warning');
            $this->redirect('default', ['aid' => $aid]);
        }

        $template = $this->exportService->getEducationReport(new SkautisEducationId($aid));

        $this->pdf->render($template, 'report.pdf');
        $this->terminate();
    }

    private function getCashbookId(int $skautisEducationId): CashbookId
    {
        return $this->queryBus->handle(new EducationCashbookIdQuery(new SkautisEducationId($skautisEducationId)));
    }

    /** @param array<EducationTerm> $terms */
    private function countDays(array $terms): int
    {
        $days = [];

        foreach ($terms as $term) {
            $date = $term->startDate;

            while ($date->lessThanOrEquals($term->endDate)) {
                $days[] = $date;
                $date   = $date->addDay();
            }
        }

        return count(
            array_unique(
                $days,
            ),
        );
    }

    /**
     * @param array<T> $arr
     *
     * @template T
     */
    private static function propertySum(array $arr, string $property): int|null
    {
        $propertyValues = array_filter(
            array_map(
                static function ($item) use ($property) {
                    return $item->$property;
                },
                $arr,
            ),
            static function (int|null $value) {
                return $value !== null;
            },
        );

        return count($propertyValues) > 0 ? array_sum($propertyValues) : null;
    }
}
