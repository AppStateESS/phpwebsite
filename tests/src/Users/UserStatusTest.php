<?php

namespace Canopy\Users;

use PHPUnit\Framework\TestCase;

/**
 * @backupStaticAttributes enabled
 */
class UserStatusTest extends TestCase
{
    protected $backupGlobalsBlacklist = array('_SESSION');

    public function setUp()
    {
        //global $_SESSION;
        //$_SESSION = array();
    }

    public function testGetInstance()
    {
        global $_SESSION;
        $_SESSION = array();

        $instance = UserStatus::getInstance();
        $this->assertInstanceOf(UserStatus::class, $instance);
    }

    public function testLoginUser(){
        $authn = new Authentication\LocalAuthentication();
        $authz = new Authorization\LocalUserPermissions();
        $user = new User('testuser', 'testuser@appstate.edu', 'Test User', $authn, $authz, false);
        $user->setId(1);

        $instance = UserStatus::getInstance();
        $instance->login($user);

        $this->assertInstanceOf(User::class, $instance->getUser());
        $this->assertTrue(UserStatus::isLogged());
    }

    /**
     * @depends testLoginUser
     */
    public function testLoggedOutUser()
    {
        $authn = new Authentication\LocalAuthentication();
        $authz = new Authorization\LocalUserPermissions();
        $user = new User('testuser', 'testuser@appstate.edu', 'Test User', $authn, $authz, false);
        $user->setId(1);

        $instance = UserStatus::getInstance();
        $instance->login($user);

        $user = $instance->logout();
        $this->assertFalse(UserStatus::isLogged());
        $this->assertInstanceOf(User::class, $user);
    }
}
