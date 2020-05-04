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

class Data
{
    private static $instance;

    private $records = [];
    private $deleted = [];
    private $timestamps = [];
    private $sets = [];

    private $earliest;

    /**
     * @return Data
     */
    public static function getInstance(): Data
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->earliest = time();
    }

    private function __clone()
    {
    }

    public function populateSets()
    {
        $config = Config::getInstance();

        foreach ($config->getConfigValue('metadataPrefix') as $prefix => $uris) {
            $directory = rtrim($config->getConfigValue('dataDirectory'), '/') . '/' . $prefix;

            $all_files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            $xml_files = new \RegexIterator($all_files, '/\.xml$/');

            foreach ($xml_files as $fileInfo) {
                $file = $fileInfo->getPathname();

                // Identity sets
                if (basename($file) === $config->getConfigValue('setDefinition')) {
                    // Build set name
                    $setName = str_replace($directory, '', $fileInfo->getPath());
                    $setName = trim($setName, '/');
                    $setName = str_replace('/', ':', $setName);

                    $this->sets[] = $setName;
                }
            }
        }
    }

    public function resetRecords()
    {
        $this->records = array();
        $this->deleted = array();
        $this->timestamps = array();
        $this->earliest = time();
    }

    public function populateRecords($set = '')
    {
        $this->resetRecords();

        $config = Config::getInstance();

        foreach ($config->getConfigValue('metadataPrefix') as $prefix => $uris) {
            $directory = rtrim($config->getConfigValue('dataDirectory'), '/') . '/' . $prefix;

            $all_files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            $xml_files = new \RegexIterator($all_files, '/\.xml$/');

            foreach ($xml_files as $fileInfo) {
                $filePath = $fileInfo->getPathname();
                $fileName = $fileInfo->getBasename();

                if (basename($filePath) === $config->getConfigValue('setDefinition')) {
                    continue;
                }

                // Build set name
                $setName = str_replace($directory, '', $fileInfo->getPath());
                $setName = trim($setName, '/');
                $setName = str_replace('/', ':', $setName);

                // Filter to a set
                if (!empty($set) && $setName !== $set) {
                    continue;
                }

                $this->records[$prefix][$fileName] = $filePath;
                $this->deleted[$prefix][$fileName] = !filesize($filePath);
                $this->timestamps[$prefix][filemtime($filePath)][] = $fileName;
                // TODO: Bug with element on doublon
                if (filemtime($filePath) < $this->earliest) {
                    $this->earliest = filemtime($filePath);
                }
            }

            if (isset($this->records[$prefix])) {
                ksort($this->records[$prefix]);
                reset($this->records[$prefix]);
                ksort($this->timestamps[$prefix]);
                reset($this->timestamps[$prefix]);
            }
        }
    }

    /**
     * @return array
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * @return array
     */
    public function getDeleted(): array
    {
        return $this->deleted;
    }

    /**
     * @return array
     */
    public function getTimestamps(): array
    {
        return $this->timestamps;
    }

    /**
     * @return int
     */
    public function getEarliest(): int
    {
        return $this->earliest;
    }

    /**
     * @return array
     */
    public function getSets(): array
    {
        return $this->sets;
    }

    public function getSetFile(string $set)
    {
        $config = Config::getInstance();

        $setName = $config->getConfigValue('setDefinition');

        foreach ($config->getConfigValue('metadataPrefix') as $prefix => $uris) {
            $file = rtrim($config->getConfigValue('dataDirectory'), '/') . '/' . $prefix . "/$set/$setName";
            if (is_file($file)) {
                return $file;
            }
        }

        return false;
    }
}
