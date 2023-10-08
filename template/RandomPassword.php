<?php

    namespace App\Service;

    class RandomPassword
    {
        public function create_randomPassword()
        {

            $characters = [
                '4',
                'B',
                'c',
                'd',
                '&',
                '7',
                'G',
                'h',
                '1',
                'l',
                'm',
                'N',
                '0',
                'p',
                'Q',
                'R',
                's',
                't',
                'u',
                'v',
                'W',
                'x',
                'y',
                'Z'
            ];

            $random_password = '';

            for ($i=0; $i < 9; $i++) { 
                $random_password = $random_password.$characters[(rand(0,count($characters) - 1))];
            }        
            return $random_password;
        }
    }