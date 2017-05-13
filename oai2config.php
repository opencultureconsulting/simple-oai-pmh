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

/**
 * This file contains all configuration you need to change according to your preferences
 */

$config = array();

// A human readable name for the repository
$config['repositoryName'] = 'German Literature Archive, Marbach';

// The base URL of the repository, i.e. the directory you deployed the files to
$config['baseURL'] = 'http://www.dla-marbach.de/oai2/';

// Email address for contacting the repository owner
$config['adminEmail'] = 'info@dla-marbach.de';

// Metadata format, schema and namespace of your records
$config['metadataFormat'] = 'ead';
$config['metadataSchema'] = 'https://www.loc.gov/ead/ead.xsd';
$config['metadataNamespace'] = 'urn:isbn:1-931666-22-9';

// Maximum number of records to return before giving a resumption token
$config['maxRecords'] = 100;

// Path and prefix for saving resumption tokens
// (Make sure the given path is writable)
$config['tokenPrefix'] = '/tmp/oai2-';

// Number of seconds a resumption token should be valid
$config['tokenValid'] = 86400; // 24 hours
