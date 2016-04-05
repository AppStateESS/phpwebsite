<?php

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
 * Based on code copied from php.net.
 * I pulled out Craig's switch for echoing elsewhere.
 * Substitute function until PHP releases their version.
 *
 * @author craig at craigfrancis dot co dot uk
 */
if (!function_exists('http_response_code')) {

    function http_response_code($code = NULL)
    {
        if ($code !== NULL) {
            $text = get_status_text($code);
            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            header($protocol . ' ' . $code . ' ' . $text);

            $GLOBALS['http_response_code'] = $code;
        } else {
            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
        }
        return $code;
    }

}

/**
 * Returns the status code associated with an http code.
 * Copied from php.net
 * @author craig at craigfrancis dot co dot uk
 * @param integer $code
 */
function get_status_text($code)
{
    switch ($code) {
        case 100: $text = 'Continue';
            break;
        case 101: $text = 'Switching Protocols';
            break;
        case 200: $text = 'OK';
            break;
        case 201: $text = 'Created';
            break;
        case 202: $text = 'Accepted';
            break;
        case 203: $text = 'Non-Authoritative Information';
            break;
        case 204: $text = 'No Content';
            break;
        case 205: $text = 'Reset Content';
            break;
        case 206: $text = 'Partial Content';
            break;
        case 300: $text = 'Multiple Choices';
            break;
        case 301: $text = 'Moved Permanently';
            break;
        case 302: $text = 'Moved Temporarily';
            break;
        case 303: $text = 'See Other';
            break;
        case 304: $text = 'Not Modified';
            break;
        case 305: $text = 'Use Proxy';
            break;
        case 400: $text = 'Bad Request';
            break;
        case 401: $text = 'Unauthorized';
            break;
        case 402: $text = 'Payment Required';
            break;
        case 403: $text = 'Forbidden';
            break;
        case 404: $text = 'Not Found';
            break;
        case 405: $text = 'Method Not Allowed';
            break;
        case 406: $text = 'Not Acceptable';
            break;
        case 407: $text = 'Proxy Authentication Required';
            break;
        case 408: $text = 'Request Time-out';
            break;
        case 409: $text = 'Conflict';
            break;
        case 410: $text = 'Gone';
            break;
        case 411: $text = 'Length Required';
            break;
        case 412: $text = 'Precondition Failed';
            break;
        case 413: $text = 'Request Entity Too Large';
            break;
        case 414: $text = 'Request-URI Too Large';
            break;
        case 415: $text = 'Unsupported Media Type';
            break;
        case 500: $text = 'Internal Server Error';
            break;
        case 501: $text = 'Not Implemented';
            break;
        case 502: $text = 'Bad Gateway';
            break;
        case 503: $text = 'Service Unavailable';
            break;
        case 504: $text = 'Gateway Time-out';
            break;
        case 505: $text = 'HTTP Version not supported';
            break;
        default:
            $text = 'Unknown http status code "' . htmlentities($code) . '"';
            break;
    }
    return $text;
}
