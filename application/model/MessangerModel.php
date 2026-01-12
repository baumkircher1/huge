<?php

class MessangerModel
{
    // Holt alle Benutzer außer mi selbst
    public static function getAllUsers()
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $my_id = Session::get('user_id');

        $sql = "SELECT user_id, user_name FROM users WHERE user_id != :my_id ORDER BY user_name";
        $query = $database->prepare($sql);
        $query->execute(array(':my_id' => $my_id));

        return $query->fetchAll();
    }

    // Holt alle Nachrichten
    public static function getMessages($other_user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $my_id = Session::get('user_id');

        $sql = "SELECT * FROM messages
                WHERE (sender_id = :my_id AND receiver_id = :other_id)
                   OR (sender_id = :other_id AND receiver_id = :my_id)
                ORDER BY message_timestamp ASC";

        $query = $database->prepare($sql);
        $query->execute(array(':my_id' => $my_id, ':other_id' => $other_user_id));

        return $query->fetchAll();
    }

    // Sendet eine Nachricht
    public static function sendMessage($receiver_id, $text)
    {
        if (empty($text)) {
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();
        $my_id = Session::get('user_id');

        $sql = "INSERT INTO messages (sender_id, receiver_id, message_text, message_timestamp)
                VALUES (:sender, :receiver, :text, :time)";

        $query = $database->prepare($sql);
        $query->execute(array(
            ':sender' => $my_id,
            ':receiver' => $receiver_id,
            ':text' => $text,
            ':time' => time()
        ));

        return ($query->rowCount() == 1);
    }

    // Holt einen einzelnen Benutzer
    public static function getUser($user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT user_id, user_name FROM users WHERE user_id = :id LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(':id' => $user_id));

        return $query->fetch();
    }
}
