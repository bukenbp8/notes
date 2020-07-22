<?php

namespace Controllers;

use Core\Application;
use Core\Validate;
use Models\Notes;

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
        if ($_POST) {
            $validation = new Validate();
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

        echo $this->twig->render('/notes/newNote.html', ['errorMsg' => $errorMsg, 'title' => $title]);
    }

    public function editNote($id)
    {
        $p = $this->Notes->findNoteById($id);
        $thisPost = makeArray($p[0]);
        $user = makeArray(currentUser());

        //only the creater of notes can edit
        if ($thisPost['user_id'] == $user['id']) {

            if ($_POST) {
                $validation = new Validate();
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
                    $this->Notes->update($posted_values, $id);
                    $location = ($posted_values['public'] == 0) ? 'privateNotes' : '';
                    header("Location: /{$location}");
                } else {
                    $errorMsg = $validation->errors();
                }
            }

            if (!isset($errorMsg)) {
                $errorMsg = [];
            }

            $note = $this->Notes->findById($id);

            $title = 'Edit Note';

            echo $this->twig->render('/notes/newNote.html', ['note' => $note, 'errorMsg' => $errorMsg, 'title' => $title]);
        } else {
            header("Location: /restricted");
        }
    }

    public function deleteNote($id)
    {

        $n = $this->Notes->findNoteById($id);
        $thisPost = makeArray($n[0]);
        $user = makeArray(currentUser());

        //only the creater of notes can delete
        if ($thisPost['user_id'] == $user['id']) {
            $location = ($thisPost['public'] == 0) ? 'privateNotes' : '';
            $this->Notes->deleteNote($id);
            header("Location: /{$location}");
        } else {
            header("Location: /restricted");
        }
    }

    public function privateNotes()
    {
        $notes = $this->Notes->showPrivateNotes();

        $title = 'Private Notes';

        echo $this->twig->render('/notes/notes.html', ['notes' => $notes, 'title' => $title]);
    }
}
