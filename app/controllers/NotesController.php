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
                $newNote->createNote($posted_values);
                $location = ($posted_values['public'] == 0) ? 'privateNotes' : '';
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

    public function editNote($id)
    {
        $p = $this->NotesModel->findNoteById($id);
        $thisPost = makeArray($p[0]);
        $user = makeArray(currentUser());

        if ($thisPost['user_id'] == $user['id']) {

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
                    $this->NotesModel->update($posted_values, $id);
                    $location = ($posted_values['public'] == 0) ? 'privateNotes' : '';
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
        } else {
            header("Location: /restricted");
        }
    }

    public function deleteNote($id)
    {

        $p = $this->NotesModel->findNoteById($id);
        $thisPost = makeArray($p[0]);
        $user = makeArray(currentUser());

        if ($thisPost['user_id'] == $user['id']) {
            $location = ($thisPost['public'] == 0) ? 'privateNotes' : '';
            $this->NotesModel->deleteNote($id);
            header("Location: /{$location}");
        } else {
            header("Location: /restricted");
        }
    }

    public function privateNotes()
    {
        $notes = $this->NotesModel->showPrivateNotes();

        $title = 'Private Notes';

        echo $this->twig->render('/notes/notes.html', ['notes' => $notes, 'title' => $title]);
    }
}
