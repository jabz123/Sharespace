<?php

require_once __DIR__ . '/article_flag_rules.php';

function comment_moderation_reject(string $comment, string $label = 'Comment'): ?string
{
    $trimmed = trim($comment);

    if ($trimmed === '') {
        return $label . ' cannot be empty.';
    }

    $length = function_exists('mb_strlen')
        ? mb_strlen($trimmed)
        : strlen($trimmed);

    if ($length < 20) {
        return $label . ' must be at least 20 characters. Current character count: ' . $length . '.';
    }

    $wordCount = moderation_word_count($trimmed);
    if ($wordCount <= 3) {
        return $label . ' must be more than 3 words. Current word count: ' . $wordCount . '.';
    }

    if (preg_match('/^(.)\1{5,}$/u', $trimmed)) {
        return $label . ' looks like spam. Please write a proper ' . strtolower($label) . '.';
    }

    if (!preg_match('/[a-z0-9]/i', $trimmed)) {
        return $label . ' must contain words, not only symbols or emojis.';
    }

    preg_match_all('/[a-z0-9]/i', $trimmed, $matches);
    $alnumCount = count($matches[0]);
    if ($length > 0 && ($alnumCount / $length) < 0.30) {
        return $label . ' contains too many symbols. Please use words.';
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
        return $label . ' looks like junk text. Please write a proper ' . strtolower($label) . '.';
    }

    $lettersOnly = preg_replace('/[^a-z]+/i', '', $lower) ?? '';
    foreach (article_flag_banned_terms() as $term) {
        $pattern = '/\b' . preg_quote($term, '/') . '\b/i';
        if (preg_match($pattern, $lower)) {
            return $label . ' contains inappropriate, sexual, or offensive language.';
        }

        $collapsedPattern = '/' . implode('\s*', str_split(preg_quote($term, '/'))) . '/i';
        if (preg_match($collapsedPattern, $lower)) {
            return $label . ' contains inappropriate, sexual, or offensive language.';
        }

        if ($term !== '' && str_contains($lettersOnly, $term)) {
            return $label . ' contains inappropriate, sexual, or offensive language.';
        }
    }

    foreach (article_flag_hate_speech_patterns() as $pattern) {
        if (preg_match($pattern, $trimmed)) {
            return $label . ' contains hateful or discriminatory language.';
        }
    }

    return null;
}
