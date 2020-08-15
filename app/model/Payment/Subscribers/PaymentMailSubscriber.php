<?php

declare(strict_types=1);

namespace Model\Payment\Subscribers;

use Model\Common\Services\NotificationsCollector;
use Model\Google\Exception\OAuthNotSet;
use Model\Google\InvalidOAuth;
use Model\Payment\DomainEvents\PaymentWasCompleted;
use Model\Payment\EmailTemplateNotSet;
use Model\Payment\EmailType;
use Model\Payment\InvalidEmail;
use Model\Payment\MailingService;
use function sprintf;

final class PaymentMailSubscriber
{
    /** @var MailingService */
    private $mailingService;

    /** @var NotificationsCollector */
    private $notificationsCollector;

    public function __construct(MailingService $mailingService, NotificationsCollector $notificationsCollector)
    {
        $this->mailingService         = $mailingService;
        $this->notificationsCollector = $notificationsCollector;
    }

    public function __invoke(PaymentWasCompleted $event) : void
    {
        try {
            $this->mailingService->sendEmail($event->getId(), EmailType::get(EmailType::PAYMENT_COMPLETED));
        } catch (EmailTemplateNotSet | OAuthNotSet | InvalidEmail $exc) {
        } catch (InvalidOAuth $exc) {
            $this->notificationsCollector->error(
                sprintf('Email při dokončení platby nemohl být odeslán. Chyba Google serveru: %s', $exc->getMessage())
            );
        }
    }
}
