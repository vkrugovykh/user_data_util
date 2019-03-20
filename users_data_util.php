<?php
/**
 * Created by PhpStorm.
 * User: Vassiliy
 * Date: 20.03.2019
 * Time: 19:49
 */

//var_dump($argv);

$delimiter = ';'; // default delimiter for csv file

foreach ($argv as $item) {
    switch ($item) {
        case 'comma':
            $delimiter = ',';
            break;
        case 'semicolon':
            $delimiter = ';';
            break;
        case 'countAverageLineCount':
            //countAverageLineCount($delimiter);
            break;
        case 'replaceDates':
            //replaceDates($delimiter);
            break;
    }
}


