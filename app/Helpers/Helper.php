<?php
namespace App\Helpers;
class Helper
{
    public static function getDay($day)
    {
        switch ($day) {
            case 'Monday':
                return 'Senin';
                break;
            case 'Tuesday':
                return 'Selasa';
                break;
            case 'Wednesday':
                return 'Rabu';
                break;
            case 'Thursday':
                return 'Kamis';
                break;
            case 'Friday':
                return 'Jum`at';
                break;
            case 'Saturday':
                return 'Sabtu';
                break;
            case 'Sunday':
                return 'Minggu';
                break;

            default:
                return 'Unindexed';
                break;
        }
    }

    public static function generateRandomString($length ) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

 
}
