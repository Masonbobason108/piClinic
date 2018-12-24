<?php
/**
 * Created by PhpStorm.
 * User: rbwatson
 * Date: 12/21/2018
 * Time: 11:01 AM
 */
/*
 *	Copyright (c) 2018, Robert B. Watson
 *
 *	This file is part of the piClinic Console.
 *
 *  piClinic Console is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  piClinic Console is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with piClinic Console software at https://github.com/MercerU-TCO/CTS/blob/master/LICENSE.
 *	If not, see <http://www.gnu.org/licenses/>.
 *
 */
/*******************
 *
 *	Utility functions used by logger resource
 *
 *********************/
require_once 'api_common.php';
exitIfCalledFromBrowser(__FILE__);

// Deifine the elements in the LoggerFieldInfo
define ("LOGGER_REQ_ARG", 0, false);        // request param name is index 0
define ("LOGGER_DB_ARG", 1, false);         // db field name is index 1
define ("LOGGER_DB_REQ_GET", 2, false);     // whether the field must appear in a GET request
define ("LOGGER_DB_QS_GET",3, false);       // variable can be used to filter GFET query
define ("LOGGER_DB_REQ_POST", 4, false);    // whether the field must appear in a POST request

/*
 * Returns an array that defines the query paramters and DB field names used by the logger
 */
function getLoggerFieldInfo() {
    $returnValue = [
        ["usertoken",   "UserToken",        false,  true,   true],
        ["module",      "sourceModule",     false,  true,   true],
        ["class",       "LogClass",         false,  true,   true],
        ["table",       "LogTable",         false,  true,   true],
        ["action",      "LogAction",        false,  true,   true],
        ["query",       "LogQueryString",   false,  false,  false],
        ["before",      "LogBeforeData",    false,  false,  false],
        ["after",       "LogAfterData",     false,  false,  false],
        ["status",      "LogStatusCode",    false,  true,   false],
        ["message",     "logStatusMessage", false,  false,  false]
    ];
    return $returnValue;
}

/*
 *  Creates a MySQL query string to retrieve logger reoords as filtered by
 *      the fields passed in the $requestParamters argument.
 *
 *      $requestParameters: the query string of an API call interpreted into an associative array
 *
 *      Returns a MySQL query string.
 */
function makeLoggerQueryStringFromRequestParameters ($requestParameters) {
    // create query string for get operation
    $queryString = "SELECT * FROM `". DB_TABLE_LOGGER . "` WHERE ";
    $paramCount = 0;

    $loggerDbFields = getLoggerFieldInfo();

    foreach ($loggerDbFields as $reqField) {
        if ($reqField[LOGGER_DB_QS_GET]) {
            if (!empty($requestParameters[$reqField[LOGGER_REQ_ARG]])) {
                $queryString .= "`". $reqField[LOGGER_DB_ARG] ."` LIKE '".$requestParameters[$reqField[LOGGER_REQ_ARG]]."' AND ";
                $paramCount += 1;
            }
        }
    }

    // if no paremeters, then select all from the most recent 100 log entries
    $queryString .= "TRUE ORDER BY `createdDate` DESC";
    $queryString .= ' '.DB_QUERY_LIMIT.';';

    return $queryString;
}
//EOF