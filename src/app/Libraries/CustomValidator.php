<?php namespace App\Libraries;

use Illuminate\Validation\Validator;

class CustomValidator extends Validator
{
    /**
     * Validate that an attribute contains only alphabetic characters.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  array   $parameters
     * @return bool
     */
    public function validateCustomAlpha($attribute, $value, $parameters): bool
    {
        return preg_match('/^\pL+$/u', $value);
    }

    public function validateCustomAlphaNum($attribute, $value, $parameters)
    {
        return preg_match('/^[\pL\pN]+$/u', $value);
    }

    public function validateCustomAlphaDash($attribute, $value, $parameters)
    {
        return preg_match('/^[\pL\pN_-]+$/u', $value);
    }

    public function validateAlphaComma($attribute, $value, $parameters)
    {
        return preg_match('/^[\pL\pN\s,_-]+$/u', $value);
    }

    public function validateAlphaSpace($attribute, $value, $parameters)
    {
        return preg_match('/^[\pL\s]+$/u', $value);
    }

    public function validateCustomPassword($attribute, $value, $parameters)
    {
        return preg_match('/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,64}+\z/i', $value);
    }

    public function validateCustomKatakana($attribute, $value, $parameters)
    {
        return preg_match('/^[ァ-ヾ 　〜ー−]+$/u', $value);
    }

    public function validateCustomZip($attribute, $value, $parameters)
    {
        return preg_match('/^\d{3}-\d{4}$/', $value);
    }

    public function validateCustomTel($attribute, $value, $parameters)
    {
        return preg_match('/^0\d{9,10}$/', $value);
    }

    public function validateCustomDateTime($attribute, $value, $parameters)
    {
        return $value === date("Y-m-d H:i:s", strtotime($value));
    }

    public function validateCustomPastDate($attribute, $value, $parameters)
    {
        return strtotime($value) < time();
    }

    public function validateReservedWord($attribute, $value, $parameters)
    {
        $words = [
            // リストは省略
        ];

        return !in_array($value, $words);
    }
}
