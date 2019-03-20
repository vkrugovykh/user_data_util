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
            countAverageLineCount($delimiter);
            break;
        case 'replaceDates':
            replaceDates($delimiter);
            break;
    }
}


//function get username and ID | return array[$i]=>array{['id']['name']}
function getUsers($delimiter) {
    $users = array();
    $i = 0; //index

    //open the people.csv file
    $usersData = fopen('./people.csv', 'r');

    //check if people.csv exists
    $usersData or exit('No people.csv file to check.'.PHP_EOL);

    //users data from file to array
    while(!feof($usersData)) {
        $data = fgetcsv($usersData, 255, $delimiter);
        $users[$i]['id'] = $data[0];
        $users[$i]['name'] = $data[1];
        $i++;
    }

    //close the people.csv file
    fclose($usersData);

    return $users;
}


//function countAverageLineCount | echo ID; user; average number of lines | for each user
function countAverageLineCount($delimiter) {

    $users = getUsers($delimiter);

    foreach($users as $user) {
//var_dump($user);

        //try get users txt files
        $usersTextsFiles = glob('./texts/' . $user['id'] . '-*.txt');

        if ($usersTextsFiles) {

            $countLines = 0; //total lines

            foreach ($usersTextsFiles as $fileName) {
                $countLines += count(file($fileName)); //sum lines
            }

            $countFileLines = $countLines / count($usersTextsFiles); //average number of lines

            echo "ID: {$user['id']}; user: {$user['name']}; average number of lines: {$countFileLines}" . PHP_EOL;

        } else {

            // if 0 lines
            echo "ID: {$user['id']}; user: {$user['name']}; average number of lines: 0" . PHP_EOL;
        }
    }
}


//function convertDate | get dd/mm/yy format date | return mm/dd/yyyy format date
function convertDate($date) {
    return DateTime::createFromFormat('d/m/y', $date)->format('m-d-Y');
};


//function checkCurrentDate | get dd/mm/yy format date
// and check is date or not date,
// and check not php date recalculation, for example 32/01/2019 = 01/02/2019
function checkCurrentDate($date) {
    $checkDate = DateTime::createFromFormat('d/m/y', $date);
    if ($checkDate !== false && !array_sum($checkDate::getLastErrors())) {
        return true;
    } else {
        return false;
    }
};


//function replaceDatesInLine | get text like 'lorem dd/mm/yy lorem'
// and return text like 'lorem mm/dd/yyyy lorem'
// and return count replace dates
function replaceDatesInLine($line) {

    $dateFormat = '~(\d{2})\/(\d{2})\/(\d{2})~'; //pattern for date

    $countReplaceDates = 0;

    $result['line'] = preg_replace_callback($dateFormat,
        function ($matches) {
            if (checkCurrentDate($matches[0])) {
                return convertDate($matches[0]);
            }
            return $matches[0];
        },
        $line, -1,$countReplaceDates);

    $result['count'] = $countReplaceDates;

    return $result;
};


//function replaceDates | get $delimiter
// echo user; replacements
// creat file with modified dates
function replaceDates($delimiter) {

    $users = getUsers($delimiter); // get users

    foreach($users as $user) {

        $usersTextsFiles = glob('./texts/' . $user['id'] . '-*.txt');

        if ($usersTextsFiles) {

            $replacements = 0; //count replacements for this user

            foreach ($usersTextsFiles as $fileName) {

//                var_dump($name .PHP_EOL);
                foreach (file($fileName) as $index => $line) {

                    $replaceDatesInLine = replaceDatesInLine($line);

                    $replacements = $replacements + $replaceDatesInLine['count'];

                    if ($index === 0) {
                        $fpn = fopen('./output_texts/' . basename($fileName), 'w'); //rewrite or creat file
                        fwrite($fpn, $replaceDatesInLine['line']); //first line
                    } else {
                        fwrite($fpn, $replaceDatesInLine['line']); //after first line
                    }
                }

                fclose($fpn);
            }
            echo "user: {$user['name']}; replacements - {$replacements}" . PHP_EOL;
        } else {
            echo "user: {$user['name']}; replacements - 0" . PHP_EOL;
        }
    }
}