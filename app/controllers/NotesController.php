<?php

class NotesController extends Application
{
    public function __construct()
    {
        $this->loadTwig();
        $this->load_model('Users');
        $this->load_model('Notes');

        if (!currentUser()) {
            header('Location: /restricted');
        }
    }

    public function newNote()
    {
        $validation = new Validate();
        $posted_values = ['title' => '', 'body' => ''];
        if ($_POST) {
            $posted_values = posted_values($_POST);
            $validation->check($_POST, [
                'title' => [
                    'display' => 'Title',
                    'required' => true,
                    'min' => 3
                ],
                'body' => [
                    'display' => 'Text',
                    'required' => true,
                    'min' => 6
                ]
            ]);
            if ($validation->passed()) {
                $newNote = new Notes();
                $newNote->createNewNote($posted_values);
                $location = ($posted_values['public'] == 0) ? 'privateNotes' : 'publicNotes';
                header("Location: /{$location}");
            } else {
                $errorMsg = $validation->errors();
            }
        }

        if (!isset($errorMsg)) {
            $errorMsg = [];
        }

        $title = "New Note";

        $action = 'newNote';

        echo $this->twig->render('/notes/newNote.html', ['errorMsg' => $errorMsg, 'title' => $title, 'action' => $action]);
    }

    public function publicNotes()
    {
        $notes = $this->NotesModel->showPublicNotes();

        $title = 'Public Notes';

        echo $this->twig->render('/notes/notes.html', ['notes' => $notes, 'title' => $title]);
    }

    public function privateNotes()
    {
        $notes = $this->NotesModel->showPrivateNotes();

        $title = 'Private Notes';

        echo $this->twig->render('/notes/notes.html', ['notes' => $notes, 'title' => $title]);
    }

    public function editNote($id)
    {
        $validation = new Validate();
        $posted_values = ['title' => '', 'body' => ''];

        if ($_POST) {
            $posted_values = posted_values($_POST);
            $validation->check($_POST, [
                'title' => [
                    'display' => 'Title',
                    'required' => true,
                    'min' => 3
                ],
                'body' => [
                    'display' => 'Text',
                    'required' => true,
                    'min' => 6
                ]
            ]);
            if ($validation->passed()) {
                $this->NotesModel->update($id, $posted_values);
                $location = ($posted_values['public'] == 0) ? 'privateNotes' : 'publicNotes';
                header("Location: /{$location}");
            } else {
                $errorMsg = $validation->errors();
            }
        }

        if (!isset($errorMsg)) {
            $errorMsg = [];
        }

        $note = $this->NotesModel->findById($id);

        $title = 'Edit Note';

        $action = '#';

        echo $this->twig->render('/notes/newNote.html', ['note' => $note, 'errorMsg' => $errorMsg, 'action' => $action, 'title' => $title]);
    }

    public function deleteNote($id)
    {

        $p = $this->NotesModel->findNoteById($id);
        $thisPost = json_decode(json_encode($p[0]), true);
        $user = json_decode(json_encode(currentUser()), true);

        if ($thisPost['user_id'] == $user['id']) {
            $location = ($thisPost['public'] == 0) ? 'privateNotes' : 'publicNotes';
            $this->NotesModel->deleteNote($id);
            header("Location: /{$location}");
        } else {
            header("Location: /restricted");
        }
    }
}
