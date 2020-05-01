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

    private $earliest;

    /**
     * @return Data
     */
    public static function getInstance()
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

    public function populate()
    {
        $config = Config::getInstance();

        foreach ($config->getConfigValue('metadataPrefix') as $prefix => $uris) {
            $files = glob(rtrim($config->getConfigValue('dataDirectory'), '/') . '/' . $prefix . '/*.xml');
            foreach ($files as $file) {
                $this->records[$prefix][pathinfo($file, PATHINFO_FILENAME)] = $file;
                $this->deleted[$prefix][pathinfo($file, PATHINFO_FILENAME)] = ! filesize($file);
                $this->timestamps[$prefix][filemtime($file)][]              = pathinfo($file, PATHINFO_FILENAME);
                if (filemtime($file) < $this->earliest) {
                    $this->earliest = filemtime($file);
                }
            }

            if ( ! empty($files)) {
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
}
