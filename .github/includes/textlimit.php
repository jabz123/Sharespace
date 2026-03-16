<!-- a function to help limit words. using for the articles card title in layout.php-->

<?php

function limit_words($text, $limit) {

    $words = explode(" ", trim($text));

    if (count($words) > $limit) {
        $text = implode(" ", array_slice($words, 0, $limit)) . "...";
    }

    return $text;
}

?>