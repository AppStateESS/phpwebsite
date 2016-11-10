<?php

namespace Canopy\Users;

class UserFactory {

    public static function getUserById($id)
    {
        $db = \phpws2\Database::newDB()->getPdo();

        $stmt = $db->prepare("SELECT * FROM users_new WHERE id = :userId");
        $stmt->execute(array('userId'=>$id));
        $stmt->setFetchMode(PDO::FETCH_CLASS, '\Canopy\Users\UserDBRestored');

        return $stmt->fetch();
    }

    public static function getUserByUsername($username)
    {
        // TODO
    }

    // public function getUserByEmail($email)
}
