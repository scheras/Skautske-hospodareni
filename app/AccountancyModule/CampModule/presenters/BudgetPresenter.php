<?php

namespace App\AccountancyModule\CampModule;

class BudgetPresenter extends BasePresenter
{

    protected function startup() : void
    {
        parent::startup();
        if (!$this->aid) {
            $this->flashMessage("Musíš vybrat akci", "danger");
            $this->redirect("Default:");
        }
    }

    public function renderDefault(int $aid) : void
    {
        $toRepair = [];
        $this->template->isConsistent = $this->eventService->chits->isConsistent($aid, FALSE, $toRepair);
        $this->template->toRepair = $toRepair;
        $this->template->dataEstimate = $this->eventService->chits->getCategories($aid, TRUE);
        $this->template->dataReal = $this->eventService->chits->getCategories($aid, FALSE);
        $this->template->isUpdateStatementAllowed = in_array("EV_EventCampStatement_UPDATE_EventCamp", $this->availableActions);
        if ($this->isAjax()) {
            $this->redrawControl("contentSnip");
        }
    }

    /**
     * přepočte hodnoty v jednotlivých kategorich
     * @param int $aid
     */
    public function handleConvert(int $aid) : void
    {
        $this->editableOnly();
        $this->eventService->chits->isConsistent($aid, $repair = TRUE);
        $this->flashMessage("Kategorie byly přepočítány.");

        if ($this->isAjax()) {
            $this->redrawControl("flash");
        } else {
            $this->redirect('this', $aid);
        }
    }

}
