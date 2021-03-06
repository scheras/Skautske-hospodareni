<?php

declare(strict_types=1);

namespace Model\Payment\Commands\Payment;

use Cake\Chronos\Date;
use Model\Payment\VariableSymbol;

final class UpdatePayment
{
    /** @var string */
    private $name;

    /** @var string|null */
    private $email;

    /** @var float */
    private $amount;

    /** @var Date */
    private $dueDate;

    /** @var VariableSymbol|null */
    private $variableSymbol;

    /** @var int|null */
    private $constantSymbol;

    /** @var string */
    private $note;

    /** @var int */
    private $paymentId;

    public function __construct(
        int $paymentId,
        string $name,
        ?string $email,
        float $amount,
        Date $dueDate,
        ?VariableSymbol $variableSymbol,
        ?int $constantSymbol,
        string $note
    ) {
        $this->paymentId      = $paymentId;
        $this->name           = $name;
        $this->email          = $email;
        $this->amount         = $amount;
        $this->dueDate        = $dueDate;
        $this->variableSymbol = $variableSymbol;
        $this->constantSymbol = $constantSymbol;
        $this->note           = $note;
    }

    public function getPaymentId() : int
    {
        return $this->paymentId;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getEmail() : ?string
    {
        return $this->email;
    }

    public function getAmount() : float
    {
        return $this->amount;
    }

    public function getDueDate() : Date
    {
        return $this->dueDate;
    }

    public function getVariableSymbol() : ?VariableSymbol
    {
        return $this->variableSymbol;
    }

    public function getConstantSymbol() : ?int
    {
        return $this->constantSymbol;
    }

    public function getNote() : string
    {
        return $this->note;
    }
}
