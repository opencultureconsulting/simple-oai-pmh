<?php
/**
 * Simple OAI-PMH 2.0 Data Provider
 * Copyright (C) 2011 Jianfeng Li
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

class OAI2Exception extends Exception {

    public function __construct($code) {
        $this->errorTable = array(
            'badArgument' => array(
                'text' => 'The request includes illegal arguments, is missing required arguments, includes a repeated argument, or values for arguments have an illegal syntax.',
            ),
            'badResumptionToken' => array(
                'text' => 'The value of the resumptionToken argument is invalid or expired.',
            ),
            'badVerb' => array(
                'text' => 'Value of the verb argument is not a legal OAI-PMH verb, the verb argument is missing, or the verb argument is repeated.',
            ),
            'cannotDisseminateFormat' => array(
                'text' => 'The metadata format identified by the value given for the metadataPrefix argument is not supported by the item or by the repository.',
            ),
            'idDoesNotExist' => array(
                'text' => 'The value of the identifier argument is unknown or illegal in this repository.',
            ),
            'noRecordsMatch' => array(
                'text' => 'The combination of the values of the from, until, set and metadataPrefix arguments results in an empty list.',
            ),
            'noMetadataFormats' => array(
                'text' => 'There are no metadata formats available for the specified item.',
            ),
            'noSetHierarchy' => array(
                'text' => 'The repository does not support sets.',
            ),
        );
        parent::__construct($this->errorTable[$code]['text']);
        $this->code = $code;
    }

    public function getOAI2Code() {
        return $this->code;
    }

}
