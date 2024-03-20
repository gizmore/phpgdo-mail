<?php
namespace GDO\Mail;

use GDO\Core\GDO;
use GDO\Core\GDT_AutoInc;
use GDO\Core\GDT_CreatedAt;
use GDO\Core\GDT_Serialize;
use GDO\Date\GDT_Timestamp;
use GDO\UI\GDT_Message;
use GDO\UI\GDT_Title;
use GDO\User\GDT_User;

final class GDO_Mail extends GDO
{

    public function gdoColumns(): array
    {
        return [
            GDT_AutoInc::make('mail_id'),
            GDT_Serialize::make('mail_data'),
            GDT_CreatedAt::make('mail_created'),
            GDT_Timestamp::make('mail_sent'),

        ];
    }

}
