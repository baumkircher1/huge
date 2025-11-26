<?php

/**
 * Handles all data manipulation of the admin part
 */
class AdminModel
{
    /**
     * Sets the deletion and suspension values
     *
     * @param $suspensionInDays
     * @param $softDelete
     * @param $userId
     */
    public static function setAccountSuspensionAndDeletionStatus($suspensionInDays, $softDelete, $userId)
    {

        // Prevent to suspend or delete own account.
        // If admin suspend or delete own account will not be able to do any action.
        if ($userId == Session::get('user_id')) {
            Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_CANT_DELETE_SUSPEND_OWN'));
            return false;
        }

        if ($suspensionInDays > 0) {
            $suspensionTime = time() + ($suspensionInDays * 60 * 60 * 24);
        } else {
            $suspensionTime = null;
        }

        // FYI "on" is what a checkbox delivers by default when submitted. Didn't know that for a long time :)
        if ($softDelete == "on") {
            $delete = 1;
        } else {
            $delete = 0;
        }

        // write the above info to the database
        self::writeDeleteAndSuspensionInfoToDatabase($userId, $suspensionTime, $delete);

        // if suspension or deletion should happen, then also kick user out of the application instantly by resetting
        // the user's session :)
        if ($suspensionTime != null OR $delete = 1) {
            self::resetUserSession($userId);
        }
    }

    /**
     * Simply write the deletion and suspension info for the user into the database, also puts feedback into session
     *
     * @param $userId
     * @param $suspensionTime
     * @param $delete
     * @return bool
     */
    private static function writeDeleteAndSuspensionInfoToDatabase($userId, $suspensionTime, $delete)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("UPDATE users SET user_suspension_timestamp = :user_suspension_timestamp, user_deleted = :user_deleted  WHERE user_id = :user_id LIMIT 1");
        $query->execute(array(
                ':user_suspension_timestamp' => $suspensionTime,
                ':user_deleted' => $delete,
                ':user_id' => $userId
        ));

        if ($query->rowCount() == 1) {
            Session::add('feedback_positive', Text::get('FEEDBACK_ACCOUNT_SUSPENSION_DELETION_STATUS'));
            return true;
        }
    }

    /**
     * Kicks the selected user out of the system instantly by resetting the user's session.
     * This means, the user will be "logged out".
     *
     * @param $userId
     * @return bool
     */
    private static function resetUserSession($userId)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("UPDATE users SET session_id = :session_id  WHERE user_id = :user_id LIMIT 1");
        $query->execute(array(
                ':session_id' => null,
                ':user_id' => $userId
        ));

        if ($query->rowCount() == 1) {
            Session::add('feedback_positive', Text::get('FEEDBACK_ACCOUNT_USER_SUCCESSFULLY_KICKED'));
            return true;
        }
    }

    /**
     * Creates a new user account (admin function)
     * Admins can create users without email verification
     *
     * @return bool
     */
    public static function createUserByAdmin()
    {
        // clean the input
        $user_name = strip_tags(Request::post('user_name'));
        $user_email = strip_tags(Request::post('user_email'));
        $user_password = Request::post('user_password_new');
        $user_account_type = (int)Request::post('user_account_type');

        // validate account type (1 = basic, 2 = premium, 7 = admin)
        if (!in_array($user_account_type, [1, 2, 7])) {
            Session::add('feedback_negative', Text::get('FEEDBACK_INVALID_ACCOUNT_TYPE'));
            return false;
        }

        // validate username
        if (!RegistrationModel::validateUserName($user_name)) {
            return false;
        }

        // validate email (without repeat check for admin)
        if (empty($user_email)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_EMAIL_FIELD_EMPTY'));
            return false;
        }

        if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_EMAIL_DOES_NOT_FIT_PATTERN'));
            return false;
        }

        // validate password (minimum length)
        if (empty($user_password)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_PASSWORD_FIELD_EMPTY'));
            return false;
        }

        if (strlen($user_password) < 6) {
            Session::add('feedback_negative', Text::get('FEEDBACK_PASSWORD_TOO_SHORT'));
            return false;
        }

        // check if username already exists
        if (UserModel::doesUsernameAlreadyExist($user_name)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_ALREADY_TAKEN'));
            return false;
        }

        // check if email already exists
        if (UserModel::doesEmailAlreadyExist($user_email)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_USER_EMAIL_ALREADY_TAKEN'));
            return false;
        }

        // crypt the password
        $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT);

        // write user to database (user is active immediately, no email verification needed)
        if (!self::writeNewUserToDatabaseByAdmin($user_name, $user_password_hash, $user_email, $user_account_type, time())) {
            Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_CREATION_FAILED'));
            return false;
        }

        Session::add('feedback_positive', Text::get('FEEDBACK_ACCOUNT_SUCCESSFULLY_CREATED'));
        return true;
    }

    /**
     * Writes a new user to the database (admin function)
     *
     * @param $user_name
     * @param $user_password_hash
     * @param $user_email
     * @param $user_account_type
     * @param $user_creation_timestamp
     *
     * @return bool
     */
    private static function writeNewUserToDatabaseByAdmin($user_name, $user_password_hash, $user_email, $user_account_type, $user_creation_timestamp)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "INSERT INTO users (user_name, user_password_hash, user_email, user_account_type, user_creation_timestamp, user_active, user_provider_type)
                    VALUES (:user_name, :user_password_hash, :user_email, :user_account_type, :user_creation_timestamp, 1, :user_provider_type)";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':user_name' => $user_name,
            ':user_password_hash' => $user_password_hash,
            ':user_email' => $user_email,
            ':user_account_type' => $user_account_type,
            ':user_creation_timestamp' => $user_creation_timestamp,
            ':user_provider_type' => 'DEFAULT'
        ));

        $count = $query->rowCount();
        if ($count == 1) {
            return true;
        }

        return false;
    }
}
