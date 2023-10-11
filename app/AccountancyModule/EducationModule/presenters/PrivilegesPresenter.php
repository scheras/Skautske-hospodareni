<?php

declare(strict_types=1);

namespace App\AccountancyModule\EducationModule;

use Model\Auth\Resources\Education;

class PrivilegesPresenter extends BasePresenter
{
    public function renderDefault(int $aid): void
    {
        $this->setLayout('layout.new');
        $isDraft = $this->event->getState() === 'draft';

        $privileges = [
            'event' => [
                'label' => 'Základní informace o akci',
                'items' => [
                    [
                        'label' => 'Zobrazovat detaily o akci',
                        'value' => $this->authorizator->isAllowed(Education::ACCESS_DETAIL, $aid),
                        'desc' => 'Lze zobrazovat další údaje o této akci.',
                    ],
                    [
                        'label' => 'Upravovat tuto akci',
                        'value' => $this->authorizator->isAllowed(Education::UPDATE, $aid),
                        'desc' => 'Lze upravovat základní údaje o této akci.',
                    ],
                    [
                        'label' => 'Zobrazovat vedení akce',
                        'value' => $this->authorizator->isAllowed(Education::ACCESS_FUNCTIONS, $aid),
                        'desc' => 'Lze zobrazovat vedení této akce.',
                    ],
                ],
            ],
            'participant' => [
                'label' => 'Účastníci',
                'items' => [
                    [
                        'label' => 'Zobrazovat účastníky',
                        'value' => $this->authorizator->isAllowed(Education::ACCESS_PARTICIPANTS, $aid),
                        'desc' => 'Lze zobrazovat účastníky této akce.',
                    ],
                    [
                        'label' => 'Upravovat účastníky',
                        'value' => $this->authorizator->isAllowed(Education::UPDATE_PARTICIPANT, $aid),
                        'desc' => 'Lze upravovat účastníky této akce.',
                    ],
                ],
            ],
            'budget' => [
                'label' => 'Rozpočet',
                'items' => [
                    [
                        'label' => 'Upravovat závěrečný rozpočet',
                        'value' => $isDraft && $this->authorizator->isAllowed(Education::UPDATE_REAL_BUDGET_SPENDING, $aid),
                        'desc' => 'Lze upravovat závěrečný rozpočet této akce ve SkautISu.',
                    ],
                ],
            ],
        ];
        $this->template->setParameters(['privileges' => $privileges]);
    }
}