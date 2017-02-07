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

/**
 * Returns true is value parameter is an associative array.
 * Copied from the php.net website.
 *
 * Note: this function is flawed. If an array is numerically keyed from zero to
 * count($array) - 1 this function will return false. It doesn't matter if the
 * numbers are cast as strings as PHP changes them to integers. If there is a chance
 * that array(0 => 'foo', 1 => 'bar') will EVER be passed to this function,
 * do not rely on it.
 *
 * @deprecated
 * @param array $value
 * @return boolean
 * @author Anonymous
 */
function is_assoc($value)
{
    return (is_array($value) && (0 !== count(array_diff_key($value,
                            array_keys(array_keys($value)))) || count($value) == 0));
}



/**
 * Receives a printf formatted string and substitutes the values in the
 * $arr array.
 *
 * Example:
 * $arr[] = array('dogs', 'cats', 'mice');
 * $arr[] = array('cars', 'trucks', 'motorcycles');
 * $str = 'I like %s, %s, and %s.<br />';
 * echo vsprintf_array($str, $arr);
 *
 * Prints:
 * I like dogs, cats, and mice.
 * I like cars, trucks, and motorcycles.
 *
 * @param type $string
 * @param array $arr
 * @param string $join
 * @return string
 */
function vsprintf_array($string, array $arr, $join = null)
{
    if (!$join || !is_string($join)) {
        $join = "\n";
    }
    if (!is_string($string)) {
        throw \Exception('First parameter is not a string');
    }
    foreach ($arr as $values) {
        $rows[] = vsprintf($string, $values);
    }
    return implode($join, $rows);
}

/**
 * Same functionality as vsprintf_array, but echoes the result instead of
 * returning it. Not sure if replicating that function using printf instead
 * would be faster.
 *
 * @param type $string
 * @param array $arr
 * @param type $join
 */
function vprintf_array($string, array $arr, $join = null)
{
    echo vsprintf_array($string, $arr, $join);
}
