<?php
/**
 * 文字列暗号化及び復号を行うクラス
 */
class EncryptManager {
    const KEY = "aJ6CfzDUUdukkSh8"; //暗号鍵
    const IV  = "U9rW9LbBZKJk=Hwy"; //初期ベクトル

    // 暗号化
    public function Encrypt ($targetString) {
        $key = self::KEY;
        $method = 'aes-128-cbc';
        $options = 0;
        $iv = self::IV;
        $encryptData = openssl_encrypt(
            $targetString,
            $method,
            $key,
            $options,
            $iv
        );
        return $encryptData;
    }

    // 復号
    public function Decrypt ($targetEncryptString) {
        $key = self::KEY;
        $method = 'aes-128-cbc';
        $options = OPENSSL_RAW_DATA;
        $iv = self::IV;
        $decryptData = openssl_decrypt(
            base64_decode($targetEncryptString),
            $method,
            $key,
            $options,
            $iv
        );
        return $decryptData;
    }
}
