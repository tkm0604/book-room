<?php

if (!function_exists('removeBookRoomTag')) {
    /**
     * #book-room を本文から削除する
     */
    function removeBookRoomTag($text)
    {
        return preg_replace('/\s?#book-room\s?/i', '', $text);
    }
}
