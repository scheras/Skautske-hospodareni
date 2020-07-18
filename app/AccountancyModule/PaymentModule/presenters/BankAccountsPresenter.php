<?php

declare(strict_types=1);

namespace App\AccountancyModule\PaymentModule;

use App\AccountancyModule\PaymentModule\Components\PairButton;
use App\AccountancyModule\PaymentModule\Factories\BankAccountForm;
use App\AccountancyModule\PaymentModule\Factories\IBankAccountFormFactory;
use DateTimeImmutable;
use Model\Auth\Resources\Unit as UnitResource;
use Model\BankTimeLimit;
use Model\BankTimeout;
use Model\Common\UnitId;
use Model\DTO\Payment\BankAccount;
use Model\DTO\Payment\Payment;
use Model\Payment\BankAccount\BankAccountId;
use Model\Payment\BankAccountNotFound;
use Model\Payment\BankAccountService;
use Model\Payment\ReadModel\Queries\CountGroupsWithBankAccountQuery;
use Model\Payment\ReadModel\Queries\GetGroupList;
use Model\Payment\ReadModel\Queries\PairedPaymentsQuery;
use Model\Payment\TokenNotSet;
use Model\Unit\ReadModel\Queries\SubunitListQuery;
use Model\Unit\Unit;
use Model\User\ReadModel\Queries\ActiveSkautisRoleQuery;
use Model\User\SkautisRole;
use Nette\Application\BadRequestException;
use function array_keys;
use function assert;
use function sprintf;

class BankAccountsPresenter extends BasePresenter
{
    private const DAYS_BACK = 60;

    private IBankAccountFormFactory $formFactory;

    private BankAccountService $accounts;

    private int $id;

    public function __construct(IBankAccountFormFactory $formFactory, BankAccountService $accounts)
    {
        parent::__construct();
        $this->formFactory = $formFactory;
        $this->accounts    = $accounts;
    }

    public function handleAllowForSubunits(int $id) : void
    {
        $this->assertCanEditBankAccount($id);

        try {
            $this->accounts->allowForSubunits($id);
            $this->flashMessage('Bankovní účet zpřístupněn', 'success');
        } catch (BankAccountNotFound $e) {
            $this->flashMessage('Bankovní účet neexistuje', 'danger');
        }

        $this->redirect('this');
    }

    public function handleDisallowForSubunits(int $id) : void
    {
        $this->assertCanEditBankAccount($id);

        try {
            $this->accounts->disallowForSubunits($id);
            $this->flashMessage('Bankovní účet znepřístupněn', 'success');
        } catch (BankAccountNotFound $e) {
            $this->flashMessage('Bankovní účet neexistuje', 'danger');
        }

        $this->redirect('this');
    }

    public function handleRemove(int $id) : void
    {
        $this->assertCanEditBankAccount($id);

        try {
            $this->accounts->removeBankAccount($id);
            $this->flashMessage('Bankovní účet byl odstraněn', 'success');
        } catch (BankAccountNotFound $e) {
        }

        $this->redirect('default');
    }

    public function handleImport() : void
    {
        if (! $this->canEdit()) {
            $this->noAccess();
        }

        try {
            $this->accounts->importFromSkautis($this->getUnitId());
            $this->flashMessage('Účty byly importovány', 'success');
        } catch (BankAccountNotFound $e) {
            $this->flashMessage('Nenalezeny žádné účty', 'warning');
        }

        $this->redirect('this');
    }

    public function actionEdit(int $id) : void
    {
        $this->assertCanEditBankAccount($id);
        $this->id = $id;
    }

    public function renderEdit(int $id) : void
    {
        $this->template->setParameters([
            'account' => $this->findBankAccount($id),
            'groupsCount' => $this->queryBus->handle(new CountGroupsWithBankAccountQuery(new BankAccountId($id))),
        ]);
    }

    public function actionDefault() : void
    {
        $accounts = $this->accounts->findByUnit($this->getCurrentUnitId());

        $this->template->setParameters([
            'accounts' => $accounts,
            'canEdit'  => $this->canEdit(),
        ]);
    }

    public function actionDetail(int $id) : void
    {
        $this->assertCanViewBankAccount($id);
    }

    public function renderDetail(int $id) : void
    {
        $account = $this->accounts->find($id);

        $templateParameters = [
            'account' => $account,
            'transactions' => null,
        ];

        try {
            $templateParameters['transactions'] = $this->accounts->getTransactions($id, self::DAYS_BACK);

            $payments = $this->queryBus->handle(
                new PairedPaymentsQuery(
                    new BankAccountId($id),
                    (new DateTimeImmutable())->modify(sprintf('- %d days', self::DAYS_BACK))->setTime(0, 0, 0),
                    new DateTimeImmutable()
                )
            );

            $paymentsByTransaction = [];

            foreach ($payments as $payment) {
                assert($payment instanceof Payment);

                $paymentsByTransaction[$payment->getTransaction()->getId()] = $payment;
            }

            $groups = $this->queryBus->handle(
                new GetGroupList(array_keys($this->unitService->getReadUnits($this->user)), false)
            );

            $groupNames = [];
            foreach ($groups as $g) {
                $groupNames[$g->getId()] = $g->getName();
            }

            $templateParameters['groupNames'] = $groupNames;

            $templateParameters['payments'] = $paymentsByTransaction;
        } catch (TokenNotSet $e) {
            $templateParameters['warningMessage'] = 'Nemáte vyplněný token pro komunikaci s FIO';
        } catch (BankTimeLimit $e) {
            $templateParameters['warningMessage'] = PairButton::TIME_LIMIT_MESSAGE;
        } catch (BankTimeout $e) {
            $templateParameters['errorMessage'] = PairButton::TIMEOUT_MESSAGE;
        }

        $this->template->setParameters($templateParameters);
    }

    protected function createComponentForm() : BankAccountForm
    {
        return $this->formFactory->create($this->id);
    }

    private function noAccess() : void
    {
        $this->setView('accessDenied');

        return;
    }

    private function canEdit(?int $unitId = null) : bool
    {
        return $this->authorizator->isAllowed(UnitResource::EDIT, $unitId ?? $this->getUnitId());
    }

    private function assertCanViewBankAccount(int $id) : void
    {
        $account = $this->accounts->find($id);

        if ($account === null) {
            throw new BadRequestException('Bankovní účet neexistuje');
        }

        if ($this->canEdit($account->getUnitId())) {
            return;
        }

        if ($account->isAllowedForSubunits() && $this->isSubunitOf($account->getUnitId())) {
            return;
        }

        $role = $this->queryBus->handle(new ActiveSkautisRoleQuery());

        assert($role instanceof SkautisRole);

        if ($role->getUnitId() === $account->getUnitId()
            && $role->isBasicUnit()
            && ($role->isAccountant() || $role->isOfficer() || $role->isEventManager())) {
            return;
        }

        $this->noAccess();
    }

    private function assertCanEditBankAccount(int $id) : void
    {
        if (! $this->canEdit()) {
            $this->noAccess();
        }

        $account = $this->findBankAccount($id);

        if ($account === null) {
            throw new BadRequestException('Bankovní účet neexistuje');
        }

        if ($this->canEdit($account->getUnitId())) {
            return;
        }

        $this->noAccess();
    }

    private function isSubunitOf(int $unitId) : bool
    {
        $currentUnitId = $this->getCurrentUnitId()->toInt();

        foreach ($this->queryBus->handle(new SubunitListQuery(UnitId::fromInt($unitId))) as $subunit) {
            assert($subunit instanceof Unit);

            if ($subunit->getId() === $currentUnitId) {
                return true;
            }
        }

        return false;
    }

    private function findBankAccount(int $id) : ?BankAccount
    {
        return $this->accounts->find($id);
    }
}
