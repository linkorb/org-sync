<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationAdapter\SetPassword;

use Gnello\Mattermost\Driver;
use Gnello\Mattermost\Models\UserModel;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\PasswordHelper;
use LinkORB\OrgSync\Services\ResponseChecker;
use LinkORB\OrgSync\SynchronizationAdapter\SetPassword\MattermostSetPasswordAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class MattermostSetPasswordAdapterTest extends TestCase
{
    /** @var MattermostSetPasswordAdapter */
    private $adapter;

    /** @var PasswordHelper|MockObject */
    private $helper;

    /** @var Driver|MockObject */
    private $driver;

    protected function setUp(): void
    {
        $this->helper = $this->createMock(PasswordHelper::class);
        $this->driver = $this->createMock(Driver::class);

        $this->adapter = new MattermostSetPasswordAdapter(
            $this->driver,
            $this->helper,
            new ResponseChecker(User::class)
        );
    }

    public function testSetPasswordWithoutPrevious()
    {
        $user = new User('testUser', 'newSecurePass');

        $defaultPassword = 'fbifhuoj;i';
        $this->helper
            ->expects($this->once())
            ->method('getDefaultPassword')
            ->with($user->getUsername())
            ->willReturn($defaultPassword);

        $userModel = $this->createMock(UserModel::class);
        $this->driver->method('getUserModel')->willReturn($userModel);

        $userId = 123;

        $userModel
            ->expects($this->once())
            ->method('getUserByUsername')
            ->with($user->getUsername())
            ->willReturn(
                $this->createConfiguredMock(ResponseInterface::class, ['getBody' => json_encode(['id' => $userId])])
            );

        $userModel
            ->expects($this->once())
            ->method('updateUserPassword')
            ->with($userId, ['current_password' => $defaultPassword, 'new_password' => $user->getPassword()])
            ->willReturn($this->createConfiguredMock(ResponseInterface::class, ['getStatusCode' => 200]));

        $this->assertSame($this->adapter, $this->adapter->setPassword($user));
    }

    public function testSetPasswordWithPrevious()
    {
        $user = new User('firstOne', 'p@sssss', null, null, null, [User::PREVIOUS_PASSWORD => 'prevP@ss']);

        $this->helper->expects($this->never())->method('getDefaultPassword');

        $userModel = $this->createMock(UserModel::class);
        $this->driver->method('getUserModel')->willReturn($userModel);

        $userModel->method('getUserByUsername')->willReturn(
            $this->createConfiguredMock(ResponseInterface::class, ['getBody' => json_encode(['id' => 132])])
        );

        $userModel
            ->expects($this->once())
            ->method('updateUserPassword')
            ->with(
                $this->anything(),
                [
                    'current_password' => $user->getProperties()[User::PREVIOUS_PASSWORD],
                    'new_password' => $user->getPassword()
                ]
            )
            ->willReturn($this->createConfiguredMock(ResponseInterface::class, ['getStatusCode' => 200]));

        $this->assertSame($this->adapter, $this->adapter->setPassword($user));
    }
}
