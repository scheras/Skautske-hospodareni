<?php

declare(strict_types=1);

namespace Model\Budget\Repositories;

use Doctrine\ORM\EntityManager;
use Model\Event\SkautisEventId;
use Model\Participant\Payment;
use Model\Participant\PaymentNotFound;

class PaymentRepository implements IPaymentRepository
{
    /** @var EntityManager */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function find(int $id) : Payment
    {
        $payment = $this->em->find(Payment::class, $id);
        if ($payment === null) {
            throw new PaymentNotFound();
        }
        return $payment;
    }

    /**
     * @return Payment[]
     */
    public function findPaymentsByEvent(SkautisEventId $actionId) : array
    {
        $res      = [];
        $payments = $this->em->getRepository(Payment::class)->findBy(['actionId' => $actionId->toInt()]);
        /** @var Payment $payment */
        foreach ($payments as $payment) {
            $res[$payment->getParticipantId()] = $payment;
        }
        return $res;
    }

    public function savePayment(Payment $payment) : void
    {
        $this->em->persist($payment);
        $this->em->flush();
    }

    public function deletePayment(Payment $payment) : void
    {
        $this->em->remove($payment);
        $this->em->flush();
    }
}