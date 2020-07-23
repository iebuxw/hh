<?php

$lock_model = new LockModel('AUDITING');
$row_name = 'all';
// 检查是否有 记录id 在审核
$lock_val = $lock_model->lockValue($row_name);
$lock_arr = empty($lock_val) ? array() : @json_decode($lock_val, true);
$intersect = array_intersect($lock_arr, $apply_ids);
if( !empty($intersect) ){//有交集，退出
    $res['code'] = 0;
    $res['msg'] = '有任务记录正在审核中';
    $res['url'] = '/admin/gaoe/applylist/';
    echo json_encode($res);
    exit();
}
// 加锁
$lock_new_arr = array_merge($lock_arr, $apply_ids);
$lock_model->lock($row_name, json_encode($lock_new_arr));

//数据操作处理

// 解锁
$lock_val = @json_decode($lock_model->lockValue($row_name), true);
$diff = array_diff($lock_val, $apply_ids);
if( empty($diff) )
    $lock_model->unlock($row_name);
else
    $lock_model->lock($row_name, json_encode($diff));

// 操作记录
$admin_action_model = new AdminActionHistoryModel();