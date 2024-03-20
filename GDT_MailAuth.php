<?php
namespace GDO\Mail;

use GDO\Core\GDO_DBException;
use GDO\Core\GDT;
use GDO\Core\GDT_Field;
use GDO\Core\GDT_Token;
use GDO\User\GDO_User;

final class GDT_MailAuth extends GDT_Field
{

    protected function __construct()
    {
        parent::__construct();
    }

    public function isHidden(): bool
    {
        return true;
    }

    public static function generateToken(string $uid=null): string
    {
        $uid = $uid === null ? GDO_User::current()->getID() : $uid;
        return "mxauth:{$uid}:" . GDT_Token::generateToken("mxauth:{$uid}");
    }

    /**
     * @throws GDO_DBException
     */
    public function validate(float|object|array|bool|int|string|null $value): bool
    {
        $user = GDO_User::current();
        if ($user->isAuthenticated())
        {
            return true;
        }
        $data = explode(':', $value);
        $uid = $data[1];
        $token = self::generateToken($uid);
        if ($token === $value)
        {
            GDO_User::setCurrent(GDO_User::getById($uid));
            return true;
        }
        return $this->error('err_token');
    }


}
