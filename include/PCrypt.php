<?php
/**
 * @author Rafique Mohammed
 * @copyright 2014
 * */
class PCrypt
{
    protected $key;

    function __construct()
    {
        $this->key = "Rafique Mohammed 1st June 2014";

    }

    function encrypt($str)
    {
        $string = trim($str);
        $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($this->key), $string,
            MCRYPT_MODE_CBC, md5(md5($this->key))));
        return $encrypted;
    }
    function decrypt($str)
    {
        $string = trim($str);
        $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($this->key),
            base64_decode($string), MCRYPT_MODE_CBC, md5(md5($this->key))), "\0");
        return $decrypted;
    }
}

?>