<?php namespace App\Validators\Api\Bases;


use Illuminate\Support\Facades\Validator;

class BaseValidator
{
    public function __construct(
    ){
    }

    // // バリデートを行う関数
    // public function validate($inputData, $validateArray)
    // {
    //     return Validator::make($inputData, $validateArray);
    // }


    public function arrayToErrorArray($array1, $errorArray)
    {
        foreach($errorArray as $key => $value) {
            $array1[$key][] = $value;
        }

        return $array1;
    }
}