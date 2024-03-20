<?php
namespace GDO\Mail\Method;

use GDO\Core\GDO_DBException;
use GDO\Cronjob\MethodCronjob;
use GDO\Date\Time;
use GDO\Mail\GDO_Mail;
use GDO\Mailer\Mailer;

final class MailCronjob extends MethodCronjob
{

    /**
     * @throws GDO_DBException
     */
    public function run(): void
    {
        $result = GDO_Mail::table()->select()->where("mail_sent IS NOT NULL")->exec();
        while ($mail = $result->fetchObject())
        {
            $this->sendMail($mail);
        }
    }

    /**
     * @throws GDO_DBException
     */
    private function sendMail(GDO_Mail $mail): void
    {
        if (Mailer::send($mail->gdoValue('mail_data')))
        {
            $mail->saveVar('mail_sent', Time::getDate());
        }
    }

}
