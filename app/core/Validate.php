<?php

namespace Core;


class Validate
{
    private $_passed = true, $_errors = [], $_db = null;

    public function __construct()
    {
        $this->_db = DB::getInstance();
    }

    public function check($source, $items = [])
    {
        $this->_errors = [];
        foreach ($items as $item => $rules) {
            $item = Input::sanitize($item);
            $display = $rules['display'];
            foreach ($rules as $rule => $rule_value) {
                $value = Input::sanitize(trim($source[$item]));

                if ($rule === 'required' && empty($value)) {
                    $this->addError(["{$display} is required", $item]);
                } elseif (!empty($value)) {
                    switch ($rule) {
                        case 'min':
                            if (strlen($value) < $rule_value) {
                                $this->addError(["{$display} must be a minimum of {$rule_value} characters.", $item]);
                            }
                            break;

                        case 'max':
                            if (strlen($value) > $rule_value) {
                                $this->addError(["{$display} must be a maximum of {$rule_value} characters.", $item]);
                            }
                            break;

                        case 'matches':
                            if ($value != $source[$rule_value]) {
                                $match = $items[$rule_value]['display'];
                                $this->addError(["{$match} and {$display} must match.", $item]);
                            }
                            break;

                        case 'unique':
                            $check = $this->_db->query("SELECT {$item} FROM users WHERE {$item} = ?", [$value]);
                            if ($check->count()) {
                                $this->addError(["{$display} already exists. Please choose another {$display}", $item]);
                            }
                            break;

                        case 'must_exist':
                            $check = $this->_db->query("SELECT {$item} FROM users WHERE {$item} = ?", [$value]);
                            if (!$check->count()) {
                                $this->addError(["This {$display} doesn't exists in the database.", $item]);
                            }
                            break;

                        case 'unique_email':
                            $check = $this->_db->query("SELECT * FROM users WHERE id != ? AND email = ?", [$_POST['id'], $value]);
                            if ($check->count()) {
                                $this->addError(["{$display} already exists. Please choose another {$display}.", $item]);
                            }
                            break;

                        case 'valid_email':
                            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                $this->addError(["{$display} must be a valid email address.", $item]);
                            }
                            break;

                        case 'correct_pwd':
                            if (isset($_POST['password'])) {
                                $password = $_POST['password'];
                            } else {
                                $password = '';
                            }
                            $check = $this->_db->query("SELECT password FROM users WHERE {$item} = ?", [$value], true)->results();
                            if (!(password_verify($password, $check[0]['password']))) {
                                $this->addError(["Wrong Password. Please try again"]);
                            };
                            break;
                    }
                }
            }
        }
    }

    public function addError($error)
    {
        $this->_errors[] = $error;
        if (empty($this->_errors)) {
            $this->_passed = true;
        } else {
            $this->_passed = false;
        }
    }

    public function errors()
    {
        return $this->_errors;
    }

    public function passed()
    {
        return $this->_passed;
    }
}
