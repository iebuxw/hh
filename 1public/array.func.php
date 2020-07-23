<?php

function array_value($arr, $key, $default = '') {
	return isset($arr[$key]) ? $arr[$key] : $default;
}

function array_filter_empty($arr) {
	foreach($arr as $k=>$v) {
		if(empty($v)) unset($arr[$k]);
	}
	return $arr;
}

//添加反斜杠
function array_addslashes(&$var) {
	if(is_array($var)) {
		foreach($var as $k=>&$v) {
			array_addslashes($v);
		}
	} else {
		$var = addslashes($var);
	}
	return $var;
}

//删除反斜杠
function array_stripslashes(&$var) {
	if(is_array($var)) {
		foreach($var as $k=>&$v) {
			array_stripslashes($v);
		}
	} else {
		$var = stripslashes($var);
	}
	return $var;
}

//返回html实体
function array_htmlspecialchars(&$var) {
	if(is_array($var)) {
		foreach($var as $k=>&$v) {
			array_htmlspecialchars($v);
		}
	} else {
		$var = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $var);
	}
	return $var;
}

//去除空格
function array_trim(&$var) {
	if(is_array($var)) {
		foreach($var as $k=>&$v) {
			array_trim($v);
		}
	} else {
		$var = trim($var);
	}
	return $var;
}

// 比较数组的值，如果不相同则保留，以第一个数组为准
function array_diff_value($arr1, $arr2) {
	foreach ($arr1 as $k=>$v) {
		if(isset($arr2[$k]) && $arr2[$k] == $v ) unset($arr1[$k]);
	}
	return $arr1;
}

// 对多维数组排序
function arrlist_multisort($arrlist, $col, $asc = TRUE) {
	$colarr = array();
	foreach($arrlist as $k=>$arr) {
		$colarr[$k] = $arr[$col];
	}
	$asc = $asc ? SORT_ASC : SORT_DESC;
	array_multisort($colarr, $asc, $arrlist);
	return $arrlist;
}

// 对数组进行查找，排序，筛选，只支持一种条件排序
function arrlist_cond_orderby($arrlist, $cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$resultarr = array();
	if(empty($arrlist)) return $arrlist;
	
	// 根据条件，筛选结果
	if($cond) {
		foreach($arrlist as $key=>$val) {
			$ok = TRUE;
			foreach($cond as $k=>$v) {
				if(!isset($val[$k]) || $val[$k] != $v) {
					$ok = FALSE;
					break;
				}
			}
			if($ok) $resultarr[$key] = $val;
		}
	} else {
		$resultarr = $arrlist;
	}

	if($orderby) {
		list($k, $v) = each($orderby);
		$resultarr = arrlist_multisort($resultarr, $k, $v == 1);
	}

	$start = ($page - 1) * $pagesize;

	$resultarr = array_assoc_slice($resultarr, $start, $pagesize);
	return $resultarr;
}

//关联数组截取
function array_assoc_slice($arrlist, $start, $length = 0) {
	if(isset($arrlist[0])) return array_slice($arrlist, $start, $length);
	$keys = array_keys($arrlist);
	$keys2 = array_slice($keys, $start, $length);
	$retlist = array();
	foreach($keys2 as $key) {
		$retlist[$key] = $arrlist[$key];
	}

	return $retlist;
}

// 将 key 更换为某一列的值，在对多维数组排序后，数字key会丢失，需要此函数
function arrlist_change_key($arrlist, $key = '', $pre = '') {
	$return = array();
	if(empty($arrlist)) return $return;
	foreach($arrlist as &$arr) {
		if(empty($key)) {
			$return[] = $arr;
		} else {
			$return[$pre.''.$arr[$key]] = $arr;
		}
	}
	//$arrlist = $return;
	return $return;
}

// 保留指定的 key
function arrlist_keep_keys($arrlist, $keys = array()) {
	!is_array($keys) AND $keys = array($keys);
	foreach($arrlist as &$v) {
		$arr = array();
		foreach($keys as $key) {
			$arr[$key] = isset($v[$key]) ? $v[$key] : NULL;
		}
		$v = $arr;
	}
	return $arrlist;
}

// 根据某一列的值进行 chunk
function arrlist_chunk($arrlist, $key)
{
    $r = array();
    if (empty($arrlist)) return $r;
    foreach ($arrlist as &$arr) {
        !isset($r[$arr[$key]]) AND $r[$arr[$key]] = array();
        $r[$arr[$key]][] = $arr;
    }
    return $r;
}