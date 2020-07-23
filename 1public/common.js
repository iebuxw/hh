//验证码倒计时,只需onclick(this)
var wait=60;
$("#send_code").attr("disabled",false);
function time(o) {

    if (wait == 0) {
        o.removeAttribute("disabled");
        o.innerText="点击获取验证码";
        wait = 60;
    } else {
        o.setAttribute("disabled", true);
        o.innerText = wait + "秒后可重发";
        wait--;
        setTimeout(function(){time(o);}, 1000)//此处自定义函数带了参数，所以这样传参
    }
}