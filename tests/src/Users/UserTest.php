<?php

namespace Canopy\Users;

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testCreateUser()
    {
        // Create a stub for the LocalAuthentication class
        //$authnStub = $this->createMock(Authentication\LocalAuthentication::class);
        //$authnStub->method('getName')->willReturn('LocalAuthentication');

        //$authzStub = $this->createMock(Authorization\LocalUserPermissions::class);
        //$authzStub->method('getName')->willReturn('LocalUserPermissions');

        $authn = new Authentication\LocalAuthentication();
        $authz = new Authorization\LocalUserPermissions();

        $user = new User('testuser', 'testuser@appstate.edu', 'Test User', $authn, $authz, false);

        $this->assertNull($user->getId());
        $this->assertEquals($user->getUsername(), 'testuser');
        $this->assertEquals($user->getEmail(), 'testuser@appstate.edu');
        $this->assertEquals($user->getFullName(), 'Test User');

        $this->assertFalse($user->isDeity());
        $this->assertNull($user->getLastLoginTime());
        $this->assertEquals($user->getLoginCount(), 0);

        $this->assertLessThanOrEqual($user->getCreatedOnTime(), time());
        $this->assertLessThanOrEqual($user->getLastModifiedTime(), time());

        $this->assertEquals($user->getAuthenticationMethodName(), 'Canopy\Users\Authentication\LocalAuthentication');
        $this->assertEquals($user->getAuthorizationMethodName(), 'Canopy\Users\Authorization\LocalUserPermissions');
    }
}
