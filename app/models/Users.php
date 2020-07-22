<?php

namespace Models;

use Core\Model;
use Core\Session;
use Core\Cookie;

class Users extends Model
{
    private $_sessionName, $_cookieName;
    public static $currentLoggedInUser = null;

    public function __construct($user = '')
    {
        $table = 'users';
        parent::__construct($table);
        $this->_sessionName = CURRENT_USER_SESSION_NAME;
        $this->_cookieName = REMEMBER_ME_COOKIE_NAME;
        $this->_softDelete = true;
        if ($user != '') {
            if (is_int($user)) {
                $u = $this->_db->findFirst($table, ['conditions' => 'id = ?', 'bind' => [$user]]);
            } else {
                $u = $this->_db->findFirst($table, ['conditions' => 'email = ?', 'bind' => [$user]]);
            }
            if ($u) {
                foreach ($u as $key => $val) {
                    $this->$key = $val;
                }
            }
        }
    }

    public function findByEmail($email)
    {
        return $this->findFirst(['conditions' => 'email = ?', 'bind' => [$email]]);
    }

    public function getUserRole($role)
    {
        return $this->findFirst(['conditions' => 'role = ?', 'bind' => [$role]]);
    }

    public function getLastUser()
    {
        return $this->_db->query('SELECT * FROM users ORDER BY id DESC LIMIT ?', ['1'])->results();
    }

    public static function currentLoggedInUser()
    {
        if (!isset(self::$currentLoggedInUser) && Session::exists(CURRENT_USER_SESSION_NAME)) {
            $u = new Users((int)Session::get(CURRENT_USER_SESSION_NAME));
            self::$currentLoggedInUser = $u;
        }
        return self::$currentLoggedInUser;
    }

    public function login($rememberMe = false)
    {
        Session::set($this->_sessionName, $this->id);
        if ($rememberMe) {
            $hash = bin2hex(random_bytes(64));
            $user_agent = Session::uagent_no_version();
            Cookie::set($this->_cookieName, $hash, REMEMBER_ME_COOKIE_EXPIRE);
            $fields = ['session' => $hash, 'user_agent' => $user_agent, 'user_id' => $this->id];
            $this->_db->query('DELETE FROM user_sessions WHERE user_id = ? AND user_agent = ?', [$this->id, $user_agent]);
            $this->_db->insert('user_sessions', $fields);
        }
    }

    public static function loginUserFromCookie()
    {
        $userSession = UserSessions::getFromCookie();
        if ($userSession->user_id != '') {
            $user = new self((int)$userSession->user_id);
            $user->login();
            return $user;
        }
    }

    public function logout()
    {
        $user_agent = Session::uagent_no_version();
        $userSession = UserSessions::getFromCookie();
        if ($userSession) {
            $userSession->delete();
        }

        Session::delete(CURRENT_USER_SESSION_NAME);
        if (Cookie::exists(REMEMBER_ME_COOKIE_NAME)) {
            Cookie::delete(REMEMBER_ME_COOKIE_NAME, REMEMBER_ME_COOKIE_EXPIRE);
        }
        self::$currentLoggedInUser = null;
        return true;
    }

    public function registerNewUser($params)
    {
        $this->assign($params);
        $this->deleted = 0;
        $this->role = 'User';
        $this->token = bin2hex(random_bytes(16));
        $this->email_verified = 0;
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->save();
    }
}
