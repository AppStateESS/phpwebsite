<?php

namespace Canopy\Users;

class UserFactory {

    /**
     * Loads a user from the database and returns a UserDbRestored object
     * corresponding to the user ID given. Returns null if no user exists with
     * the given id.
     *
     * @param $id int User ID of the user to load
     * @return UserDbRestored Object representing the requested user, or null
     */
    public static function getUserById($id)
    {
        $db = \phpws2\Database::newDB()->getPdo();

        $stmt = $db->prepare("SELECT * FROM users_new WHERE id = :userId");
        $stmt->execute(array('userId'=>$id));

        $stmt->setFetchMode(\PDO::FETCH_CLASS, '\Canopy\Users\UserDbRestored');

        $result = $stmt->fetch();

        // If there's no matching user, return null
        if($result === false){
            return null;
        }

        return $result;
    }

    public static function getUserByUsername($username)
    {
        $db = \phpws2\Database::newDB()->getPdo();

        $stmt = $db->prepare("SELECT * FROM users_new WHERE username = :username");
        $stmt->execute(array('username'=>$username));

        $stmt->setFetchMode(\PDO::FETCH_CLASS, '\Canopy\Users\UserDbRestored');

        $result = $stmt->fetch();

        // If there's no matching user, return null
        if($result === false){
            return null;
        }

        return $result;
    }

    public static function getUserByEmail($email)
    {
        $db = \phpws2\Database::newDB()->getPdo();

        $stmt = $db->prepare("SELECT * FROM users_new WHERE email = :email");
        $stmt->execute(array('email'=>$email));

        $stmt->setFetchMode(\PDO::FETCH_CLASS, '\Canopy\Users\UserDbRestored');

        $result = $stmt->fetch();

        // If there's no matching user, return null
        if($result === false){
            return null;
        }

        return $result;
    }

    /**
     * Saves a Canopy\Users\User object.
     *
     * @param User $user User object to save
     */
    public static function save(User $user)
    {
        $db = \phpws2\Database::newDB()->getPdo();

        $params = array(
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'full_name' => $user->getFullName(),
            'is_deity' => $user->isDeity(),
            'authentication_method_name' => $user->getAuthenticationMethodName(),
            'authorization_method_name' => $user->getAuthorizationMethodName(),
            'last_login_time' => $user->getLastLoginTime(),
            'login_count' => $user->getLoginCount(),
            'created_on_time' => $user->getCreatedOnTime(),
            'last_modified_time' => $user->getLastModifiedTime()
        );

        if($user->getId() == null){
            echo "user ID is null <br />";
            // Inset, because this object doesn't have an ID yet
            // TODO: Figure out if this is MySQL or Postgresql and use the right "new id" syntax
            $idField = 'NULL'; // For MySQL autoincrement
            //$idField = "nextval('users_new_seq')"; // For Postgreqsl sequences
            $stmt = $db->prepare("INSERT INTO users_new (
                                    id,
                                    username,
                                    email,
                                    full_name,
                                    is_deity,
                                    authentication_method_name,
                                    authorization_method_name,
                                    last_login_time,
                                    login_count,
                                    created_on_time,
                                    last_modified_time
                                ) VALUES (
                                    $idField,
                                    :username,
                                    :email,
                                    :full_name,
                                    :is_deity,
                                    :authentication_method_name,
                                    :authorization_method_name,
                                    :last_login_time,
                                    :login_count,
                                    :created_on_time,
                                    :last_modified_time
                                )");
        } else {
            // Update, because the user object has an existing id
            $params['id'] = $user->getId();

            $stmt = $db->prepare("UPDATE users_new set
                                        username = :username,
                                        email = :email,
                                        full_name = :full_name,
                                        is_deity = :is_deity,
                                        authentication_method_name = :authentication_method_name,
                                        authorization_method_name = :authorization_method_name,
                                        last_login_time = :last_login_time,
                                        login_count = :login_count,
                                        created_on_time = :created_on_time,
                                        last_modified_time = :last_modified_time
                                    WHERE id = :id");
        }

        $stmt->execute($params);

        if($user->getId() == null){
            $user->setId($db->lastInsertId());
        }
    }
}
