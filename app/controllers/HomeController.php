<?php

class HomeController extends Application
{
    public function __construct()
    {
        $this->loadTwig();
        $this->load_model('Notes');
    }

    public function publicNotes()
    {
        $notes = $this->NotesModel->showPublicNotes();

        $title = 'Public Notes';

        echo $this->twig->render('/notes/notes.html', ['notes' => $notes, 'title' => $title]);
    }
}
