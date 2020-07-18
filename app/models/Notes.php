<?php

class Notes extends Model
{

    public function __construct($note = '')
    {
        $table = 'notes';
        parent::__construct($table);
    }

    public function createNote($params)
    {
        $user = (array)Users::currentLoggedInUser();
        $this->user_id = $user['id'];
        $this->assign($params);
        $this->save();
    }

    public function deleteNote($id)
    {
        return $this->_db->query('DELETE FROM notes WHERE id = ?', [$id]);
    }

    public function findNoteById($id)
    {
        return $this->_db->query('SELECT * FROM notes WHERE id = ?', [$id])->results();
    }

    public function showPublicNotes()
    {
        return $this->_db->query('SELECT * FROM users INNER JOIN notes ON users.id = notes.user_id WHERE notes.public = ?', ['1'])->results();
    }

    public function showPrivateNotes()
    {
        $user = (array)currentUser();

        return $this->_db->query('SELECT * FROM users INNER JOIN notes ON users.id = notes.user_id WHERE notes.public = ? AND user_id = ?', ['0', $user['id']])->results();
    }
}
