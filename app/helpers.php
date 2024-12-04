<?php

if (!function_exists('removeBookRoomTag')) {
    /**
     * #book-room を本文から削除する
     */
    function removeBookRoomTag($text)
    {
        return preg_replace('/\s?#bookroom\s?/i', '', $text);
    }

}

if (!function_exists('makekLinks')){
    function makeLinks($text)
    {
        return preg_replace(
            '@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.#-]*(\?\S+)?)?)?)@',
            '<a href="$1" target="_blank">$1</a>',
            e($text)
        );
    }
}
