<?php

namespace Model\Payment;

use DateTimeImmutable;
use Model\DTO\Payment\BankAccount as BankAccountDTO;
use Model\DTO\Payment\BankAccountFactory;
use Model\Payment\BankAccount\AccountNumber;
use Model\Payment\BankAccount\IAccountNumberValidator;
use Model\Payment\Repositories\IBankAccountRepository;

class BankAccountService
{

    /** @var IBankAccountRepository */
    private $bankAccounts;

    /** @var IAccountNumberValidator */
    private $numberValidator;

    public function __construct(IBankAccountRepository $bankAccounts, IAccountNumberValidator $numberValidator)
    {
        $this->bankAccounts = $bankAccounts;
        $this->numberValidator = $numberValidator;
    }


    public function addBankAccount(int $unitId, string $name, string $prefix, string $number, string $bankCode, ?string $token): void
    {
        $accountNumber = new AccountNumber($prefix, $number, $bankCode, $this->numberValidator);
        $account = new BankAccount($unitId, $name, $accountNumber, $token, new DateTimeImmutable());

        $this->bankAccounts->save($account);
    }


    /**
     * @param int $unitId
     * @return BankAccountDTO[]
     */
    public function findByUnit(int $unitId): array
    {
        $accounts = $this->bankAccounts->findByUnit($unitId);
        return array_map(function (BankAccount $a) { return BankAccountFactory::create($a); }, $accounts);
    }

}
