<?php

declare(strict_types=1);

namespace App\AccountancyModule\PaymentModule\Components;

use App\AccountancyModule\Components\Dialog;
use App\Forms\BaseForm;
use eGen\MessageBus\Bus\CommandBus;
use Model\Payment\Commands\Group\RemoveGroup;
use Model\PaymentService;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;

final class RemoveGroupDialog extends Dialog
{
    private int $groupId;

    private bool $isAllowed;

    private CommandBus $commandBus;

    private PaymentService $paymentService;

    public function __construct(
        int $groupId,
        bool $isAllowed,
        CommandBus $commandBus,
        PaymentService $paymentService
    ) {
        parent::__construct();

        $this->groupId        = $groupId;
        $this->isAllowed      = $isAllowed;
        $this->commandBus     = $commandBus;
        $this->paymentService = $paymentService;
    }

    public function open() : void
    {
        $this->opened = true;
        $this->redrawControl();
    }

    protected function beforeRender() : void
    {
        $group = $this->paymentService->getGroup($this->groupId);

        if ($group === null) {
            throw new BadRequestException('Skupina plateb neexistuje');
        }

        $this->template->setFile(__DIR__ . '/templates/RemoveGroupDialog.latte');
        $this->template->setParameters(['groupName' => $group->getName()]);
    }

    protected function createComponentForm() : BaseForm
    {
        $form = new BaseForm();

        $form->addSubmit('delete', 'Smazat')
            ->setAttribute('class', 'btn-danger');

        $form->onSuccess[] = function () : void {
            if (! $this->isAllowed) {
                throw new BadRequestException('Nemáte oprávnění smazat tuto skupinu', IResponse::S403_FORBIDDEN);
            }

            $this->commandBus->handle(new RemoveGroup($this->groupId));

            $this->flashMessage('Skupina plateb byla odstraněna', 'success');
            $this->getPresenter()->redirect('default');
        };

        return $form;
    }
}
