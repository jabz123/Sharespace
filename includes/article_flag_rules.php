<?php

function article_flag_reason_options(): array
{
    return [
        'Inappropriate language',
        'Misinformation',
        'Hate speech',
        'Violence',
        'Advertising',
        'Wrong category',
    ];
}

function validate_article_flag_details(string $details, int $minLen = 20, int $maxLen = 100): ?string
{
    $trimmed = trim($details);

    if ($trimmed === '') {
        return 'Details cannot be empty.';
    }

    $length = function_exists('mb_strlen')
        ? mb_strlen($trimmed)
        : strlen($trimmed);

    if ($length < $minLen) {
        return "Details must be at least {$minLen} characters.";
    }

    if ($length > $maxLen) {
        return "Details must be {$maxLen} characters or fewer.";
    }

    $lower = function_exists('mb_strtolower')
        ? mb_strtolower($trimmed)
        : strtolower($trimmed);

    $junkExact = [
        'test', 'testing', 'asdf', 'asdasd', 'qwer', 'qwerty', 'lol', 'lmao',
        'idk', 'no', 'none', 'n/a', 'na', '?', '??', '???', '????',
        '!', '!!', '!!!', '....', '...',
    ];

    if (in_array($lower, $junkExact, true)) {
        return 'Details look like test or junk text. Please be specific.';
    }

    if (preg_match('/^(.)\1{5,}$/u', $trimmed)) {
        return 'Details cannot be only repeated characters.';
    }

    if (!preg_match('/[a-z0-9]/i', $trimmed)) {
        return 'Details must contain words, not only symbols or emojis.';
    }

    preg_match_all('/[a-z0-9]/i', $trimmed, $matches);
    $alnumCount = count($matches[0]);

    if ($length > 0 && ($alnumCount / $length) < 0.30) {
        return 'Details contain too many symbols. Please use words.';
    }

    return null;
}
