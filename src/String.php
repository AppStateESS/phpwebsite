<?php
namespace Canopy;

/*
 * Copyright (C) 2016 Matthew McNaney <mcnaneym@appstate.edu>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

class String {
    /**
     * Returns true if variable is a string, numeric OR an object with a toString method
     * @param mixed $variable
     * @return boolean
     */
    public static function is_string_like($variable)
    {
        return (is_string($variable) || is_numeric($variable) || (is_object($variable) && method_exists($variable,
                        '__toString')));
    }

    /**
     * Returns a string composed of characters. For passwords consider confusables
     * set to FALSE.
     *
     * @param integer $characters Number of characters in string
     * @param boolean $confusables If true, use letters O and L and numbers 0 and 1
     * @param boolean $uppercase If true, include uppercase letters
     * @return string
     */
    public static function randomString($characters = 8, $confusables = false, $uppercase = false)
    {
        $characters = (int) $characters;
        $alpha = '0123456789abcdefghijklmnopqrstuvwxyz';

        if ($uppercase) {
            $alpha .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        if (!$confusables) {
            $alpha = preg_replace('/[1l0oO]/', '', $alpha);
        }

        srand((double) microtime() * 1000000);

        $char_count = strlen($alpha);

        for ($i = 0; $i < $characters; $i++) {
            $char = rand() % $char_count;
            $str[] = substr($alpha, $char, 1);
        }
        return implode('', $str);
    }

    /**
     * Returns a string describing a current regular expression error.
     *
     * @param integer $code
     * @return string
     */
    public static function preg_error_msg($code)
    {
        switch ($code) {
            case PREG_NO_ERROR:
                return 'no error';

            case PREG_INTERNAL_ERROR:
                return 'internal PCRE error';

            case PREG_BACKTRACK_LIMIT_ERROR:
                return 'backtrack limit error';

            case PREG_RECURSION_LIMIT_ERROR:
                return 'recursion limit error';

            case PREG_BAD_UTF8_ERROR:
                return 'bad UTF-8 error';

            case PREG_BAD_UTF8_OFFSET_ERROR:
                return 'bad UTF-8 offset error';

            default:
                return 'unknown regular expression error';
        }
    }
}
