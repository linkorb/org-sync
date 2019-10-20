<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationAdapter\UserPush;

use Gnello\Mattermost\Driver;
use Gnello\Mattermost\Models\UserModel;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\PasswordHelper;
use LinkORB\OrgSync\Services\ResponseChecker;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\MattermostUserPushAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class MattermostUserPushAdapterTest extends TestCase
{
    /** @var MattermostUserPushAdapter */
    private $adapter;

    /** @var UserModel|MockObject */
    private $model;

    /** @var PasswordHelper|MockObject */
    private $helper;

    protected function setUp(): void
    {
        $this->model = $this->createMock(UserModel::class);
        $this->helper = $this->createMock(PasswordHelper::class);

        $this->adapter = new MattermostUserPushAdapter(
            new ResponseChecker(User::class, [404]),
            $this->createConfiguredMock(Driver::class, ['getUserModel' => $this->model]),
            $this->helper
        );
    }

    public function testCreateUser()
    {
        $email = 'a@a.com';
        $username = 'ttest';
        $firstName = 'User';
        $lastName = 'Test';
        $nickname = 'tuser';
        $defaultPass = 'p@ssword';

        $user = new User(
            $username,
            null,
            $email,
            $nickname,
            null,
            [User::FIRST_NAME => $firstName, User::LAST_NAME => $lastName]
        );

        $this->model
            ->expects($this->once())
            ->method('getUserByUsername')
            ->with($user->getUsername())
            ->willReturn($this->createConfiguredMock(ResponseInterface::class, ['getStatusCode' => 404]));

        $this->helper->expects($this->once())->method('getDefaultPassword')->with($username)->willReturn($defaultPass);

        $this->model
            ->expects($this->once())
            ->method('createUser')
            ->with([
                'email' => $email,
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'nickname' => $nickname,
                'password' => $defaultPass,
            ])
            ->willReturn($this->createConfiguredMock(ResponseInterface::class, ['getStatusCode' => 200]));

        $this->assertSame($this->adapter, $this->adapter->pushUser($user));
    }

    public function testUpdateUser()
    {
        $user = new User('test', null);
        $id = 111;

        $this->model
            ->method('getUserByUsername')
            ->with($user->getUsername())
            ->willReturn($this->createConfiguredMock(
                ResponseInterface::class,
                ['getStatusCode' => 200, 'getBody' => json_encode(['delete_at' => 0, 'id' => $id])]
            ));

        $this->helper->expects($this->never())->method('getDefaultPassword');

        $this->model
            ->expects($this->once())
            ->method('patchUser')
            ->with($id, $this->anything())
            ->willReturn($this->createConfiguredMock(ResponseInterface::class, ['getStatusCode' => 200]));

        $this->assertSame($this->adapter, $this->adapter->pushUser($user));
    }

    public function testUpdateDeletedUser()
    {
        $user = new User('test', null);
        $id = 2317;

        $this->model
            ->method('getUserByUsername')
            ->willReturn($this->createConfiguredMock(
                ResponseInterface::class,
                ['getStatusCode' => 200, 'getBody' => json_encode(['delete_at' => 1, 'id' => $id])]
            ));

        $this->model->expects($this->once())->method('updateUserActive')->with($id, ['active' => true]);
        $this->model
            ->method('patchUser')
            ->willReturn($this->createConfiguredMock(ResponseInterface::class, ['getStatusCode' => 200]));

        $this->assertSame($this->adapter, $this->adapter->pushUser($user));
    }
}
