<?php

namespace Canopy\Users;

/**
 * UserStatus class
 *
 * Singleton utility class to help keep up with the user's login status, and a
 * central place for loading the User object for the person who's logged in.
 *
 * The singleton pattern prevents multiple trips to the database. We load the user
 * object once at initialization, and then store it in our single instance for all
 * future uses.
 *
 * @author Jeremy Booker
 * @package Canopy\Users
 */
class UserStatus {

    static $instance;

    private $user;


    /**
     * Get an instance of the UserStatus singleton
     *
     * @return UserStatus Instance of the UserStatus singleton
     */
    public static function getInstance()
    {
        if(!isset(self::$instance)){
            self::$instance = new UserStatus();
        }

        return self::$instance;
    }


    /**
     * Constructor - Private for a singleton
     * Loads the User object for the current user, if someone is signed in
     */
    private function __construct()
    {
        // If no one is logged in, then we set the user member variable to null and we're done
        if(!self::isLogged()){
            $this->user = null;
            return;
        }

        // Someone must be logged in, so let's try to load their User object
        $this->user = UserFactory::getUserById($_SESSION['CanopyUser']);
    }


    /**
     * Returns the user object of the use currently logged in, or null if no one is logged in
     *
     * @return \Canopy\User User object of the current user, or null if no one is logged in
     */
    public function getUser()
    {
        return $this->user;
    }

    public function login(User $user){
        $this->user = $user;
        $_SESSION['CanopyUser'] = $user->getId();
    }


    public function logout(){
        $localUser = $this->user;

        unset($_SESSION['CanopyUser']);
        $this->clear();

        return $localUser;
    }

    /**
     * Clears the User object and drops the reference to the singleton. This forces
     * the UserStatus object to be recreated on the next call to getInstance(), which
     * will force the constructor to examine the 'CanopyUser' session again and reload
     * the User object from the database (if someone is logged in).
     *
     * NB: This doesn't clear the session, so it doesn't actually log the user out.
     */
    public function clear()
    {
        $this->user = null;
        self::$instance = null;
    }

    /**
     * Returns true if there is a user logged in (i.e. the session key exists),
     * false otherwise.
     *
     * @return bool True if there is a user logged in
     */
    public static function isLogged()
    {
        if (isset($_SESSION['CanopyUser'])){
            return true;
        }

        return false;
    }
}
