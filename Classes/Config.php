<?php
/**
 * Simple OAI-PMH 2.0 Data Provider
 * Copyright (C) 2005 Heinrich Stamerjohanns <stamer@uni-oldenburg.de>
 * Copyright (C) 2011 Jianfeng Li <jianfeng.li@adelaide.edu.au>
 * Copyright (C) 2013 Daniel Neis Araujo <danielneis@gmail.com>
 * Copyright (C) 2017 Sebastian Meyer <sebastian.meyer@opencultureconsulting.com>
 * Copyright (C) 2020 Amaury BALMER <amaury@beapi.fr>
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

class Config
{
    private static $instance;

    private $config;

    /**
     * @return Config
     */
    public static function getInstance(): Config
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    private function __construct()
    {
        // Load configuration
        if (is_file(ABSPATH . '/../Configuration/Main.php')) {
            $config = include ABSPATH . '/../Configuration/Main.php';
        } elseif (is_file(ABSPATH . '/Configuration/Main.php')) {
            $config = include ABSPATH . '/Configuration/Main.php';
        } else {
            throw new \RuntimeException('Missing configuration file');
        }

        if (1 === $config) {
            throw new \RuntimeException('Configuration file must contain a return array');
        }

        $this->config = $config;

        if (true === $config['debug']) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            error_reporting(0);
        }
    }

    private function __clone()
    {
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param string $key
     * @return mixed
     *
     */
    public function getConfigValue(string $key)
    {
        return $this->config[$key] ?? null;
    }

    /**
     * Test if a metadata format exist!
     *
     * @param string $prefix
     * @return bool
     */
    public function metadataPrefixExists(string $prefix): bool
    {
        return !empty($this->config['metadataPrefix'][$prefix]);
    }
}
