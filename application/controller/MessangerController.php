<?php

class MessangerController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        Auth::checkAuthentication();
    }

    // Zeigt alle Benutzer
    public function index()
    {
        $this->View->render('messanger/index', array(
            'users' => MessangerModel::getAllUsers()
        ));
    }

    // Zeigt Chat mit einem Benutzer
    public function chat($user_id)
    {
        $this->View->render('messanger/chat', array(
            'messages' => MessangerModel::getMessages($user_id),
            'other_user' => MessangerModel::getUser($user_id),
            'my_id' => Session::get('user_id')
        ));
    }

    // Sendet eine Nachricht
    public function send()
    {
        $receiver_id = Request::post('receiver_id');
        $text = Request::post('message_text');

        MessangerModel::sendMessage($receiver_id, $text);

        Redirect::to('messanger/chat/' . $receiver_id);
    }
}
