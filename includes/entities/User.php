<?php

//user data from db
class User {
    public int    $id;
    public string $email;
    public string $fullName;
    public string $role;
    public bool   $isPremium;
    public bool   $isSuspended;
    public string $createdAt;

    public function __construct(array $row) {
        $this->id          = (int)$row['id'];
        $this->email       = $row['email'];
        $this->fullName    = $row['full_name'] ?? '';
        $this->role        = $row['role']       ?? 'free';
        $this->isPremium   = (bool)($row['is_premium']   ?? false);
        $this->isSuspended = (bool)($row['is_suspended'] ?? false);
        $this->createdAt   = $row['created_at'] ?? '';
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
