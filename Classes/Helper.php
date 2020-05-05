<?php
/**
 * Simple OAI-PMH 2.0 Data Provider
 * Copyright (C) 2005 Heinrich Stamerjohanns <stamer@uni-oldenburg.de>
 * Copyright (C) 2011 Jianfeng Li <jianfeng.li@adelaide.edu.au>
 * Copyright (C) 2013 Daniel Neis Araujo <danielneis@gmail.com>
 * Copyright (C) 2017 Sebastian Meyer <sebastian.meyer@opencultureconsulting.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCC\OAI2;

class Helper
{
    /**
     * Get current base URL
     */
    public static function getBaseURL(): string
    {
        return self::getScheme() . $_SERVER['HTTP_HOST'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    /**
     * Get scheme URL protocol
     *
     * @return string
     */
    public static function getScheme(): string
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    }

    /**
     * Format output string
     *
     * @param string $text
     * @param string $format 'green' or 'red'
     *
     * @return string
     */
    public static function CliFormat(string $text, $format = '')
    {
        switch ($format) {
            case 'green':
                $text = "\033[0;92m$text\033[0m";
                break;
            case 'red':
                $text = "\033[1;91m$text\033[0m";
                break;
            default:
                break;
        }

        return $text;
    }

    public static function cliError(string $text)
    {
        echo self::CliFormat($text, 'red') . PHP_EOL;
        exit();
    }

    public static function cliSuccess(string $text)
    {
        echo self::CliFormat($text, 'red') . PHP_EOL;
    }
}
