<?php

class Email
{
    public $transport, $mailer;

    public function __construct()
    {
        $this->transport = (new Swift_SmtpTransport('smtp.gmail.com', 465, 'ssl'))
            ->setUsername(EMAIL_USER)
            ->setPassword(EMAIL_PWD);
        $this->mailer = new Swift_Mailer($this->transport);
    }

    public function registrationEmail($email, $fname, $lname, $id, $token)
    {
        $msg = new Swift_Message('Verify Account - NotesApp');
        $msg->setFrom(['test@test.com' => 'Administrator'])
            ->setTo([$email => "{$fname} {$lname}"])
            ->setBody("
            Hello {$fname} {$lname},
            <br><br>
            Thank you for your registration! 
            <br><br>
            Please verify your account by using the provided link: 
            <a href='http://localhost:8000/verify/{$id}/{$token}'>Click here.</a>", 'text/html');
        $this->mailer->send($msg);
    }

    public function retrievePW($email, $fname, $lname, $id, $token)
    {
        $msg = new Swift_Message('Retrieve Password - NotesApp');
        $msg->setFrom(['test@test.com' => 'Administrator'])
            ->setTo([$email => "{$fname} {$lname}"])
            ->setBody("
            Hello {$fname} {$lname},
            <br><br>
            
            Here is the link for resetting your password: 
            <a href='http://localhost:8000/retrieve/{$id}/{$token}'>Click here.</a>", 'text/html');
        $this->mailer->send($msg);
    }
}
