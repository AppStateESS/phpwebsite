<?php

namespace Canopy\Users;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateUser()
    {
        $user = new User('testuser', 'testuser@appstate.edu', 'Test User', 'SomeAuthnMethod', 'SomeAuthZMethod', false);

        $this->assertNull($user->getId());
        $this->assertEquals($user->getUsername(), 'testuser');
        $this->assertEquals($user->getEmail(), 'testuser@appstate.edu');
        $this->assertEquals($user->getFullName(), 'Test User');
        $this->assertFalse($user->isDeity());
    }
}
