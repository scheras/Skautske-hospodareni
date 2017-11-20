<?php

namespace Model\Payment;

use DateTimeImmutable;
use Model\Payment\Mailing\Payment as MailPayment;
use Model\Mail\IMailerFactory;
use Model\Payment\Payment\State;
use Model\Payment\Repositories\IBankAccountRepository;
use Model\Payment\Repositories\IGroupRepository;
use Model\Payment\Repositories\IMailCredentialsRepository;
use Model\Payment\Repositories\IPaymentRepository;
use Model\Payment\Repositories\IUserRepository;
use Model\Services\TemplateFactory;
use Nette\Mail\Message;
use Nette\Utils\Validators;

class MailingService
{

    /** @var IGroupRepository */
    private $groups;

    /** @var IMailerFactory */
    private $mailerFactory;

    /** @var IPaymentRepository */
    private $payments;

    /** @var IBankAccountRepository */
    private $bankAccounts;

    /** @var TemplateFactory */
    private $templateFactory;

    /** @var IUserRepository */
    private $users;

    /** @var IMailCredentialsRepository */
    private $credentials;

    public function __construct(
        IGroupRepository $groups,
        IMailerFactory $mailerFactory,
        IPaymentRepository $payments,
        IBankAccountRepository $bankAccounts,
        TemplateFactory $templateFactory,
        IUserRepository $users,
        IMailCredentialsRepository $credentials
    )
    {
        $this->groups = $groups;
        $this->mailerFactory = $mailerFactory;
        $this->payments = $payments;
        $this->bankAccounts = $bankAccounts;
        $this->templateFactory = $templateFactory;
        $this->users = $users;
        $this->credentials = $credentials;
    }

    /**
     * Sends email to single payment address
     * @param int $paymentId
     * @param int $userId
     * @throws InvalidEmailException
     * @throws PaymentNotFoundException
     */
    public function sendEmail(int $paymentId, int $userId): void
    {
        $payment = $this->payments->find($paymentId);
        $group = $this->groups->find($payment->getGroupId());
        $user = $this->users->find($userId);

        $this->sendForPayment($payment, $group, $user);
        $this->payments->save($payment);
    }

    public function sendEmailForGroup(int $groupId, int $userId) : int
    {
        $group = $this->groups->find($groupId);
        $payments = $this->payments->findByGroup($groupId);
        $user = $this->users->find($userId);

        $sent = 0;
        foreach($payments as $payment) {
            try {
                $this->sendForPayment($payment, $group, $user);
                $sent++;
            } catch(InvalidEmailException | PaymentClosedException $e) {}
        }

        $this->payments->saveMany($payments);

        return $sent;
    }

    /**
     * @param int $groupId
     * @param int $userId
     * @return string User's email
     * @throws EmailNotSetException
     */
    public function sendTestMail(int $groupId, int $userId): string
    {
        $group = $this->groups->find($groupId);
        $user = $this->users->find($userId);

        if($user->getEmail() === NULL) {
            throw new EmailNotSetException();
        }

        $payment = new MailPayment(
            "Testovací účel",
            $group->getDefaultAmount() ?? rand(50, 1000),
            $user->getEmail(),
            $group->getDueDate() ?? new DateTimeImmutable('+ 2 weeks'),
            rand(1000, 100000),
            $group->getConstantSymbol(),
            "obsah poznámky"
        );

        $this->send($group, $payment, $user);

        return $user->getEmail();
    }


    private function sendForPayment(Payment $paymentRow, Group $group, User $user) : void
    {
        if($paymentRow->isClosed()) {
            throw new PaymentClosedException();
        }

        $email = $paymentRow->getEmail();
        if ($email === NULL || !Validators::isEmail($email)) {
            throw new InvalidEmailException();
        }

        $this->send($group, $this->createPayment($paymentRow), $user);

        if($paymentRow->getState()->equalsValue(State::SENT)) {
            return;
        }

        $paymentRow->markSent();
    }

    private function send(Group $group, MailPayment $payment, User $user) : void
    {
        $bankAccount = $group->getBankAccountId() !== NULL
            ? $this->bankAccounts->find($group->getBankAccountId())
            : NULL;

        $emailTemplate = $group->getEmailTemplate()
            ->evaluate($group, $payment, $bankAccount !== NULL ? (string)$bankAccount->getNumber() : NULL, $user->getName());

        $template = $this->templateFactory->create(TemplateFactory::PAYMENT_DETAILS, [
            'body' => nl2br($emailTemplate->getBody(), FALSE),
        ]);

        $mail = (new Message())
            ->addTo($payment->getEmail())
            ->setFrom('platby@skauting.cz') // There must be something, but gmail overwrites it :(
            ->setSubject($emailTemplate->getSubject())
            ->setHtmlBody($template, __DIR__);

        $credentials = $this->credentials->find($group->getSmtpId());
        $this->mailerFactory->create($credentials)->send($mail);
    }

    private function createPayment(Payment $payment): MailPayment
    {
        return new MailPayment(
                $payment->getName(),
                $payment->getAmount(),
                $payment->getEmail(),
                $payment->getDueDate(),
                $payment->getVariableSymbol()->toInt(),
                $payment->getConstantSymbol(),
                $payment->getNote()
        );
    }

}
