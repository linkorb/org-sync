<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services;

class PasswordHelper
{
    /** @var string|null */
    private $salt;

    public function __construct(?string $salt)
    {
        $this->salt = $salt;
    }

    public function getDefaultPassword(string $username): string
    {
        return substr(md5($username . $this->salt), 0, 8);
    }
}
