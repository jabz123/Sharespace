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

function article_flag_common_words(): array
{
    static $words = null;

    if ($words !== null) {
        return $words;
    }

    $list = [
        'a', 'about', 'abuse', 'abusive', 'accurate', 'ad', 'advert', 'advertisement', 'advertising',
        'after', 'again', 'against', 'all', 'almost', 'also', 'am', 'an', 'and', 'any', 'appears',
        'are', 'article', 'as', 'at', 'attack', 'author', 'be', 'because', 'been', 'before', 'being',
        'best', 'better', 'between', 'but', 'by', 'can', 'caption', 'category', 'claim', 'claims',
        'clear', 'clearly', 'comment', 'comments', 'completely', 'contains', 'content', 'copied',
        'copy', 'correct', 'could', 'current', 'data', 'description', 'details', 'does', 'dont', 'down',
        'evidence', 'exaggerated', 'fake', 'false', 'for', 'from', 'graphic', 'guide', 'had', 'harmful',
        'has', 'hate', 'have', 'he', 'headline', 'help', 'her', 'here', 'hidden', 'his', 'how', 'i',
        'if', 'image', 'in', 'incorrect', 'information', 'insult', 'insulting', 'into', 'is', 'it',
        'its', 'just', 'keep', 'language', 'like', 'made', 'make', 'many', 'may', 'me', 'media',
        'misleading', 'misinformation', 'more', 'most', 'my', 'name', 'need', 'news', 'no', 'not',
        'offensive', 'on', 'one', 'or', 'other', 'our', 'out', 'paragraph', 'phrasing', 'picture',
        'please', 'post', 'promotion', 'promotional', 'publish', 'published', 'quote', 'quotes', 'read',
        'reason', 'remove', 'report', 'reported', 'review', 'right', 'rude', 'same', 'says', 'seems',
        'sentence', 'serious', 'should', 'shows', 'source', 'sources', 'spam', 'speech', 'statement',
        'still', 'story', 'strong', 'suggested', 'symbol', 'symbols', 'target', 'team', 'text', 'than',
        'that', 'the', 'their', 'them', 'there', 'these', 'they', 'this', 'threat', 'title', 'to',
        'too', 'true', 'untrue', 'up', 'use', 'user', 'very', 'violence', 'violent', 'was', 'we',
        'were', 'what', 'when', 'where', 'which', 'who', 'why', 'with', 'word', 'words', 'wrong',
        'you', 'your',
    ];

    $words = array_fill_keys($list, true);

    return $words;
}

function article_flag_banned_terms(): array
{
    static $terms = null;

    if ($terms !== null) {
        return $terms;
    }

    $terms = [
        'asshole', 'bastard', 'bitch', 'blowjob', 'boner', 'boob', 'boobs', 'bullshit',
        'cock', 'cocksucker', 'cum', 'cunt', 'damn', 'dick', 'dildo', 'dumbass',
        'fag', 'faggot', 'fck', 'fucker', 'fucking', 'fuck', 'hell', 'horny',
        'jerkoff', 'motherfucker', 'nazi', 'nigga', 'nigger', 'orgasm', 'penis',
        'porn', 'pussy', 'rape', 'rapist', 'retard', 'sex', 'sexual', 'shit',
        'slut', 'spic', 'tit', 'tits', 'twat', 'vagina', 'whore',
    ];

    return $terms;
}

function validate_article_flag_language(string $details): ?string
{
    preg_match_all("/[a-zA-Z']+/u", strtolower($details), $matches);
    $tokens = $matches[0] ?? [];

    if (empty($tokens)) {
        return null;
    }

    $bannedSet = array_fill_keys(article_flag_banned_terms(), true);

    foreach ($tokens as $token) {
        if (isset($bannedSet[$token])) {
            return 'Details contain inappropriate or offensive language. Please rewrite the report respectfully.';
        }
    }

    return null;
}

function article_flag_word_is_known(string $word): bool
{
    $commonWords = article_flag_common_words();

    if (isset($commonWords[$word])) {
        return true;
    }

    foreach (['s', 'es', 'ed', 'ing', 'ly'] as $suffix) {
        if (str_ends_with($word, $suffix)) {
            $stem = substr($word, 0, -strlen($suffix));
            if ($stem !== '' && isset($commonWords[$stem])) {
                return true;
            }
        }
    }

    return false;
}

function article_flag_word_looks_like_gibberish(string $word): bool
{
    if (strlen($word) >= 4 && !preg_match('/[aeiou]/', $word)) {
        return true;
    }

    if (preg_match('/(.)\1{3,}/', $word)) {
        return true;
    }

    if (preg_match('/[bcdfghjklmnpqrstvwxyz]{5,}/', $word)) {
        return true;
    }

    return false;
}

function article_flag_word_looks_like_typo(string $word): bool
{
    if (strlen($word) < 4) {
        return false;
    }

    if (article_flag_word_is_known($word)) {
        return false;
    }

    foreach (array_keys(article_flag_common_words()) as $knownWord) {
        if ($knownWord[0] !== $word[0]) {
            continue;
        }

        if (abs(strlen($knownWord) - strlen($word)) > 2) {
            continue;
        }

        if (levenshtein($word, $knownWord) <= 2) {
            return true;
        }
    }

    return false;
}

function validate_article_flag_spelling(string $details, int $maxInvalidWords = 1): ?string
{
    preg_match_all("/[a-zA-Z']+/u", strtolower($details), $matches);
    $tokens = $matches[0] ?? [];
    $invalidWords = [];

    foreach ($tokens as $word) {
        if (strlen($word) < 4) {
            continue;
        }

        if (article_flag_word_looks_like_gibberish($word) || article_flag_word_looks_like_typo($word)) {
            $invalidWords[$word] = true;
        }
    }

    if (count($invalidWords) > $maxInvalidWords) {
        return 'Details contain too many misspelled or invalid words. Please correct the wording and try again.';
    }

    return null;
}

function validate_article_flag_details(string $details, int $minLen = 20, int $maxLen = 400): ?string
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

    $languageError = validate_article_flag_language($trimmed);
    if ($languageError !== null) {
        return $languageError;
    }

    $spellingError = validate_article_flag_spelling($trimmed);
    if ($spellingError !== null) {
        return $spellingError;
    }

    return null;
}
