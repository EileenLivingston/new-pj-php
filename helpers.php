<?php
/**
 * Created by PhpStorm.
 * User: chien
 * Date: 9/10/2021
 * Time: 9:45 PM
 */

use MongoDB\BSON\UTCDateTime;
use Carbon\Carbon;

function convertDateFromFormatToUTC($date, $format = 'd/m/Y')
{
    try {
        return new UTCDateTime(Carbon::createFromFormat($format, $date, 'UTC'));
    } catch (Exception $exception) {
        return '';
    }
}

function convertTimestampFromFormatToUTC($timestamp)
{
    try {
        return new UTCDateTime(Carbon::createFromTimestamp($timestamp, 'UTC'));
    } catch (Exception $exception) {
        return '';
    }
}

function convertDateCarbonToUTC(Carbon $date)
{
    try {
        $format = "Y-m-d H:i:s";
        $date_string = $date->format($format);
        return new UTCDateTime(Carbon::parse($date_string, 'UTC'));
    } catch (Exception $exception) {
        return '';
    }

}

function convertUTCToDateTime($date, $format = 'Y-m-d H:i:s')
{
    $tz1 = 'UTC';
    $tz2 = 'Asia/Ho_Chi_Minh'; // UTC +7

    $d = new DateTime($date, new DateTimeZone($tz1));
    $d->setTimeZone(new DateTimeZone($tz2));

    return $d->format($format);
}

function convertUTCDateTimeToTimestamp(UTCDateTime $utcDateTimeObject){
    return Carbon::parse($utcDateTimeObject->toDateTime())->timestamp;
}

function dateNow()
{
    $date = Carbon::now();
    try {
        $format = "Y-m-d H:i:s";
        $date_string = $date->format($format);
        return new UTCDateTime(Carbon::parse($date_string, 'UTC'));
    } catch (Exception $exception) {
        return '';
    }

}

function dateFormatString($date)
{
    try {
        return Carbon::parse($date)->format('d-m-Y h:i:s');
    } catch (Exception $exception) {
        return '';
    }

}

function explodeFullnameToFirstnameLastname($fullname)
{
    if (empty($fullname)) {
        return '';
    }
    $arr_name = explode(' ', trim($fullname));
    $first_name = '';
    $last_name = '';
    for ($i = 0; $i < count($arr_name); $i++) {
        if ($i < count($arr_name) - 1) {
            if ($i < count($arr_name) - 2) {
                $last_name .= $arr_name[$i] . ' ';
            } else {
                $last_name .= $arr_name[$i];
            }

        } else {
            $first_name = $arr_name[count($arr_name) - 1];
        }
    }

    return ['firstname' => $first_name, 'lastname' => $last_name];
}

function validateEmptyData(array $data, array $param_needed_validate)
{
    $error = [];
    foreach ($param_needed_validate as $param) {
        if (empty($data[$param])) {
            $error[] = trans('api.message.param_requered', ['param' => $param]);
        }
    }
    return $error;
}

function isExtensionImage($type)
{
    $types = ['jpg', 'jpeg', 'png', 'gif', 'tiff', 'bmp', 'heic', 'heif'];

    $type = strtolower($type);

    return in_array($type, $types);
}

function randomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
}

function createFileFromContentBase64($content,$path = 'files-manager/content')
{
    $fileEntity = new \App\Entities\FileEntity();

    $filesExplode = explode('"', $content); // your base64 encoded array
    foreach ($filesExplode as $key => $fileString) {
        if (strpos($fileString, 'data:image') !== false) {

            // prepare image base64
            // get image extension
            $fileExtension = 'png';
            if (strpos($fileString, 'data:image/png;base64') !== false) {
                $fileExtension = 'png';
            }
            if (strpos($fileString, 'data:image/jpg;base64') !== false) {
                $fileExtension = 'jpg';
            }
            if (strpos($fileString, 'data:image/jpeg;base64') !== false) {
                $fileExtension = 'jpeg';
            }
            $fileBase64 = str_replace('data:image/' . $fileExtension . ';base64,', '', $fileString);
            $fileBase64 = str_replace(' ', '+', $fileBase64);


            // upload image to aws and get link
            $fileName = $fileEntity->saveFileAmazonBase64($fileBase64, randomString(10).'-'.Carbon::now()->timestamp,$path);
            if($fileName){
                $content = str_replace($fileString, $fileName, $content);
            }

        }elseif (strpos($fileString, 'data:audio') !== false) {
            // get image extension
            $fileExtension = 'mp3';
            if (strpos($fileString, 'data:audio/mp3;base64') !== false) {
                $fileExtension = 'mp3';
            }
            if (strpos($fileString, 'data:audio/x-m4a;base64') !== false) {
                $fileExtension = 'm4a';
            }
            if (strpos($fileString, 'data:audio/ogg;base64') !== false) {
                $fileExtension = 'ogg';
            }
            if (strpos($fileString, 'data:audio/oga;base64') !== false) {
                $fileExtension = 'oga';
            }
            if (strpos($fileString, 'data:audio/wav;base64') !== false) {
                $fileExtension = 'wav';
            }

            if (strpos($fileString, 'data:audio/x-m4a;base64') !== false) {
                $fileBase64 = str_replace('data:audio/x-m4a;base64,', '', $fileString);
            } else {
                $fileBase64 = str_replace('data:audio/' . $fileExtension . ';base64,', '', $fileString);
            }

            $fileBase64 = str_replace(' ', '+', $fileBase64);

            // upload image to aws and get link
            $fileName = $fileEntity->saveFileAmazonBase64($fileBase64, randomString(10).'-'.Carbon::now()->timestamp,$path, false);
            if($fileName){
                $content = str_replace($fileString, $fileName, $content);
            }
        }
    }

    return $content;
}

