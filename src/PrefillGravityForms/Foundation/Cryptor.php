<?php

namespace OWC\PrefillGravityForms\Foundation;

class Cryptor
{
    /** @var string */
    protected $method = 'aes-128-ctr'; // default cipher method if none supplied

    /** @var string */
    private $key;

    /**
     * @param boolean $method
     */
    public function __construct($method = false)
    {
        $key = \AUTH_KEY ?? php_uname();
        if (ctype_print($key)) {
            // convert ASCII keys to binary format
            $this->key = openssl_digest($key, 'SHA256', true);
        } else {
            $this->key = $key;
        }
        if ($method) {
            if (!in_array(strtolower($method), openssl_get_cipher_methods())) {
                throw new \Exception(__METHOD__ . ": unrecognised cipher method: {$method}");
            }
            $this->method = $method;
        }
    }

    protected function ivBytes()
    {
        return openssl_cipher_iv_length($this->method);
    }

    public function encrypt($data)
    {
        $iv = openssl_random_pseudo_bytes($this->ivBytes());
        return bin2hex($iv) . openssl_encrypt($data, $this->method, $this->key, 0, $iv);
    }

    // decrypt encrypted string
    public function decrypt($data)
    {
        $iv_strlen = 2 * $this->ivBytes();
        if (preg_match("/^(.{" . $iv_strlen . "})(.+)$/", $data, $regs)) {
            list(, $iv, $crypted_string) = $regs;
            if (ctype_xdigit($iv) && 0 == strlen($iv) % 2) {
                return openssl_decrypt($crypted_string, $this->method, $this->key, 0, hex2bin($iv));
            }
        }
        return false; // failed to decrypt
    }
}
