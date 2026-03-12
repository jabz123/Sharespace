<?php

// stores user information like email, name, role and account status
// when user data is retrieved from the database, each row is converted into this user object
// this makes it easier for pages and controllers to access user data like $user->fullName
// also includes helper functions for display such as getting the user's initial and formatting the role name

//user data from db
class User {
    public int    $id;
    public string $email;
    public string $fullName;
    public string $role;
    public bool   $isPremium;
    public bool   $isSuspended;
    public string $createdAt;
    public string $gender;

    public function __construct(array $row) {
        $this->id          = (int)$row['id'];
        $this->email       = $row['email'];
        $this->fullName    = $row['full_name'] ?? '';
        $this->role        = $row['role']       ?? 'free';
        $this->isPremium   = (bool)($row['is_premium']   ?? false);
        $this->isSuspended = (bool)($row['is_suspended'] ?? false);
        $this->createdAt   = $row['created_at'] ?? '';
        $this->gender = $row['gender'] ?? '';
    }

    //use first letter of name for default avatar cos no picture yet
    public function initial(): string {
        return strtoupper(mb_substr($this->fullName, 0, 1)) ?: '?';
    }

    //converts free to Free, admin to Admin, premium_user to Premium User
    //makes it easier to read ig
    public function roleLabel(): string {
        return ucwords(str_replace('_', ' ', $this->role));
    }
}
