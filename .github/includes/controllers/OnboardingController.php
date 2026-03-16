<?php
// onboarding page shown during first login of a account
// this page updates to db based on user answer on the onboarding form

require_once __DIR__ . '/../db.php';

class OnboardingController {
    //check if onboarding is completed
    public function isCompleted(int $userId): bool {
        $row = DB::first(
            "SELECT onboarding_completed FROM users WHERE id = ?",
            [$userId]
        );
        return $row && $row['onboarding_completed'] == 1;
    }

    //save onboarding preferences
    public function savePreferences(
        int $userId,
        string $ageGroup,
        string $gender,
        string $bio,
        array $interests
    ): array {

        if (!$ageGroup || !$gender) {
            return ['error' => 'Age group and gender are required.'];
        }

        if (count($interests) !== 3) {
            return ['error' => 'Please select exactly 3 interests.'];
        }

        if (strlen($bio) > 150) {
            return ['error' => 'Bio cannot exceed 150 characters.'];
        }
        if (empty(trim($bio))) {
            return ['error' => 'Please write a short bio about yourself.'];
        }

        //update user profile
        DB::execute(
            "UPDATE users
             SET age_group = ?, gender = ?, bio = ?, onboarding_completed = 1
             WHERE id = ?",
            [$ageGroup, $gender, $bio, $userId]
        );

        //insert interests
        foreach ($interests as $categoryId) {

            DB::execute(
                "INSERT INTO user_interests (user_id, category_id)
                 VALUES (?, ?)",
                [$userId, $categoryId]
            );
        }
        return ['ok' => true];
    }

}