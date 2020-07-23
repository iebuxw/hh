<?php

/**
*  加锁
*/
function task_lock($taskid){
    $expire = 2;
     $lock_key ='task_get_reward_'.$this->uid.'_'.$taskid;
    $lock = $this->redis->setNX($lock_key , time());//设当前时间
    if($lock){
        $this->redis->expire($lock_key,  $expire); //如果没执行完 2s锁失效
    }
    if(!$lock){//如果获取锁失败 检查时间
        $time = $this->redis->get($lock_key);
        if(time() - $time  >=  $expire){//添加时间戳判断为了避免expire执行失败导致死锁 当然可以用redis自带的事务来保证
            $this->redis->rm($lock_key);
        }
        $lock =  $this->redis->setNX($lock_key , time());
        if($lock){
            $this->redis->expire($lock_key,  $expire); //如果没执行完 2s锁失效
        }
    }
    return $lock;
}
/**
 *  解锁
 */
function task_unlock($taskid){
    $this->set_redis();
    $lock_key = 'task_get_reward_'.$this->uid.'_'.$taskid;
    $this->redis->rm($lock_key);
}


if (task_lock(111)) {
    //todo
    //task_unlock(111)
} else {
    exit('请等待');
}
