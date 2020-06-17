<?php
namespace app\components;

use \app\models\SessionsModel;
use \app\models\UsersModel;

/**
 *
 */
class SessionsComponent
{
    public $id;
    public $user_id;
    public $content;
    public $del;

    public function get()
    {
        $session_model = new SessionsModel();
        $user = (new UsersModel())->getById($this->user_id);
        $session_model->user_id = ($user == false) ? $this->user_id : $user->id;
        $session = $session_model->getByKeyId();
        if ($session != false) {
            $session->content = unserialize($session->content);
        }
        return $session;
    }

    public function set()
    {
        $session_model = new SessionsModel();
        $user = (new UsersModel())->getById($this->user_id);
        $this->user_id = ($user == false) ? $this->user_id : $user->id;

        $session = self::get();
        if ($session != false) {
            $this->id = $session->id;
            self::delete();
        }

        $data = [
            'user_id' => ($user == false) ? $this->user_id : $user->id,
            'content' => serialize($this->content)
        ];
        $session = $session_model->insert($data);
        if ($session != false) {
            $session->content = unserialize($session->content);
        }
        return $session;
    }

    public function update()
    {
        $session_model = new SessionsModel();
        $session_model->id = $this->id;
        $data = [
            'content' => serialize($this->content)
        ];
        $session = $session_model->update($data);
        if ($session != false) {
            $session->content = unserialize($session->content);
        }
        return $session;
    }

    public function delete()
    {
        $session_model = new SessionsModel();
        $session_model->id = $this->id;
        $data = [
            'del' => 1
        ];
        $session = $session_model->update($data);
        if ($session != false) {
            $session->content = unserialize($session->content);
        }
        return $session;
    }

}
