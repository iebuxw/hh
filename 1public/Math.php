<?php


/**
* 
*/
class Math
{
    /**
     * 精确加法
     * @param [type] $a [description]
     * @param [type] $b [description]
     */
    public static function add($a, $b, $scale = '2') {
      return bcadd($a, $b, $scale);
    }
    /**
     * 精确减法
     * @param [type] $a [description]
     * @param [type] $b [description]
     */
    public static function sub($a, $b, $scale = '2') {
      return bcsub($a, $b, $scale);
    }
    /**
     * 精确乘法
     * @param [type] $a [description]
     * @param [type] $b [description]
     */
    public static function mul($a, $b, $scale = '2') {
      return bcmul($a, $b, $scale);
    }
    /**
     * 精确除法
     * @param [type] $a [description]
     * @param [type] $b [description]
     */
    public static function p($a, $b, $scale = '2') {
      return bcp($a, $b, $scale);
    }
    /**
     * 精确求余/取模
     * @param [type] $a [description]
     * @param [type] $b [description]
     */
    public static function mod($a, $b) {
      return bcmod($a, $b);
    }
    /**
     * 比较大小
     * @param [type] $a [description]
     * @param [type] $b [description]
     * 大于 返回 1 等于返回 0 小于返回 -1
     */
    public static function comp($a, $b, $scale = '5') {
      return bccomp($a, $b, $scale); // 比较到小数点位数
    }
}












