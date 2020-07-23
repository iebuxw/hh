<?php
// 3DES 加密类

class CryptModel{
    
    // 3DES 加密解密 KEY[长度24字节]
    const THREE_DES_KEY = "aq@5Zp5Lu&5GF5C65q6*h6CB";
    
    /*
     * 函数:    pkcs5_pad($text, $blocksize)
     * 功能:
     */
    static protected function pkcs5_pad($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
    
    /*
     * 函数:    pkcs5_unpad($text)
     * 功能:
     */
    static protected function pkcs5_unpad($text) {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }
    
    /*
     * 函数:  threeDesEncode($string);
     * 功能:  3des 加密字符串
     */
    static protected function threeDesEncode($input_string){
        $size = mcrypt_get_block_size(MCRYPT_3DES, 'ecb');
        $input_string = self::pkcs5_pad($input_string, $size);
        $key = str_pad(self::THREE_DES_KEY, 24, '0');
        $td = mcrypt_module_open(MCRYPT_3DES, '', 'ecb', '');
        $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        @mcrypt_generic_init($td, $key, $iv);
        $output_string = mcrypt_generic($td, $input_string);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        
        $output_string = base64_encode($output_string);
        return $output_string;
    }
    
    /*
     * 函数: threeDesDecode($input_string);
     * 功能: 3des 解密字符串
     */
    static protected function threeDesDecode($encrypted){
        $encrypted = base64_decode($encrypted);
        $key = str_pad(self::THREE_DES_KEY, 24, '0');
        $td = mcrypt_module_open(MCRYPT_3DES, '', 'ecb', '');
        $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $ks = mcrypt_enc_get_key_size($td);
        @mcrypt_generic_init($td, $key, $iv);
        $decrypted = mdecrypt_generic($td, $encrypted);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $output = self::pkcs5_unpad($decrypted);
        return $output;
    }
    
    /*
     * 函数:   strEncode
     * 功能:   加密函数(threeEnsEncode 别名)
     */
    static public function strEncode($string){
        return self::threeDesEncode($string);
    }
    
    /*
     * 函数:   strDecode
     * 功能:   解密函数(threeDesEncode 别名)
     */
    static public function strDecode($string){
        return self::threeDesDecode($string);
    }
    
}
