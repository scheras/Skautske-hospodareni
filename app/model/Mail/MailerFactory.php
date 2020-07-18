<?php

declare(strict_types=1);

namespace Model\Mail;

use Model\Payment\MailCredentials;
use Nette\Mail\IMailer;
use Nette\Mail\SmtpMailer;

class MailerFactory implements IMailerFactory
{
    private IMailer $debugMailer;

    private bool $enabled;

    public function __construct(IMailer $debugMailer, bool $enabled)
    {
        $this->debugMailer = $debugMailer;
        $this->enabled     = $enabled;
    }

    public function create(MailCredentials $credentials) : IMailer
    {
        if (! $this->enabled) {
            return $this->debugMailer;
        }

        return new SmtpMailer([
            'host' => $credentials->getHost(),
            'username' => $credentials->getUsername(),
            'password' => $credentials->getPassword(),
            'secure' => $credentials->getProtocol()->getValue(),
        ]);
    }
}
