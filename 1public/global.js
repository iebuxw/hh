(function(t, o, a, s) {
    "use strict";
    var i = i || {};
    i.Eles = {
        body: "body",
        serverLink: "#serverLink",
        rocketMod: "#rocketMod",
        xyurl: "#xyurl",
        isnull: "#isnull",
        nullTask: "#null_task",
        asoTaskContent: "#aso_task_content",
        aso_task: "#aso_task",
        future_task: "#future_task",
        rocketModType: "#rocketModType",
        copyDialog: "#copyDialog",
        copyDialogKeyword: "#keyword",
        copyDialogRank: "#keywordRank",
        copyDialogOpenAppStor: "#copyDialogOpenAppStor",
        timeoutNum: "#timeout",
        timeoutBox: ".timeoutBox",
        appPic: "#appPic",
        successReward: "#successReward",
        successRewardKeyword: "#successRewardKeyword",
        successRewardMoney: "#successRewardMoney",
        asoTaskNum: "#asoTaskNum",
        lostConnect: "#lostConnect",
        popAlertCourse: "#popAlertCourse",
        hideurl: "#hideurl",
        openKeyBtn: ".openKeyBtn",
        updateUrl: "#updateUrl",
        future_price: ".future_price",
        future_time: ".future_time",
        nobindPop: "#nobindPop",
        rocketModCheck: "#rocketModCheck",
        close_popup_box: ".close_popup_box"
    };
    i.list = {
        init: function() {
            this.eventsMap = {
                "click #serverEntr": "serverClick",
                "click .lock_btn": "lockBgShow",
                "click .dialogCanCloseBg": "dialogCanCloseBgClick",
                "click .cancelBubble": "cancelBubble",
                "click .rocket_btn": "rocketBtn",
                "click .remain_task_link": "remainTask",
                "click .praise_task_link": "praiseTask",
                "click .asoTaskLink": "asoTaskLink",
                "click #confirmTask": "confirmTaskFun",
                "click #cancelTask": "cancelTaskFun",
                "click .accept_btn": "acceptTaskBtnFun",
                "click .open_btn": "openAppBtnFun",
                "click .reward_btn": "rewardBtnFun",
                "click #copyDialogOpenAppStor": "copyDialogOpenAppStorFun",
                "click #copyDialogCancel": "copyDialogCancelFun",
                "click #successRewardBtn": "successRewardFun",
                "click #trustBtn": "trustBtnClick",
                "click .openKeyBtn": "openKeyBtnClick",
                "click .nobindBtn": "nobindBtnFun",
                "change #rocketModCheck": "changeRocketModFun",
                "click .close_popup_box": "closePopup"
            };
            this._initializeElements();
            this._loadAsoTask();
            this._appListener();
            this._openRocketModOrNot();
            FastClick.attach(document.body)
        },
        _initializeElements: function(t) {
            var a = i.Eles;
            for (var e in a) {
                if (a.hasOwnProperty(e)) {
                    this[e] = o(a[e])
                }
            }
            this._scanEventsMap(this.eventsMap, true)
        },
        _scanEventsMap: function(t, a) {
            var e = /^(\S+)\s*(.*)$/;
            var s = a ? this._delegate: this._undelegate;
            for (var i in t) {
                if (t.hasOwnProperty(i)) {
                    var n = i.match(e);
                    s(n[1], n[2], this[t[i]].bind(this))
                }
            }
        },
        _delegate: function(t, a, e) {
            s.on(t, a, e)
        },
        _undelegate: function(t, a, e) {
            s.off(t, a, e)
        },
        _appListener: function() {
            var e = this;
            s.on("click", "#openapp",
            function() {
                var t = e.hideurl.val();
                var a = e.updateUrl.val();
                xyApplication.openAppListener(t,
                function(t) {
                    if (!t) {
                        window.location.href = a;
                        setTimeout(function() {
                            e.lostConnect.addClass("hidden");
                            e.popAlertCourse.removeClass("hidden");
                            var t = 4;
                            var a = setInterval(function() {
                                if (t > 0) {
                                    o("#timeOut5sNum").text(t)
                                }
                                t--;
                                if (t < 0) {
                                    clearInterval(a);
                                    o("#trustBtn").removeClass("unClick");
                                    o(".timeOut5s").empty()
                                }
                            },
                            1e3)
                        },
                        3e3)
                    }
                })
            })
        },
        _openRocketModOrNot: function() {
            var t = this.rocketModType.val();
            if (t == "isOn") {
                this.rocketModCheck.removeAttr("checked")
            } else {
                this.aso_task.addClass("hideRocketbtn")
            }
        },
        emptyShow: function() {
            if (this.isnull.val() == "true") {
                this.nullTask.removeClass("hidden");
                this.asoTaskContent.addClass("hidden")
            }
        },
        _loadAsoTask: function() {
            var t = this.xyurl.val();
            var r = this.rocketModType.val();
            var l = this;
            o.ajax({
                type: "GET",
                timeout: 5e3,
                url: "https://www.jinzhuzhuan.com:20909/xyurl?xyurl=" + t,
                dataType: "jsonp",
                success: function(t) {
                    if (t.return_code != 200) {
                        xyApplication.setAlert(t.return_msg, "出错了", "error",
                        function() {
                            window.location.href = "/v4/taskaso/index/"
                        });
                        return
                    }
                    var a = "";
                    var e = t.return_data.applist;
                    if (e.length <= 0) {
                        l.emptyShow()
                    } else {
                        l.aso_task.removeClass("hidden");
                        l.asoTaskNum.text(e.length)
                    }
                    for (var s in e) {
                        var i = e[s];
                        if (!i.title) {
                            return false
                        }
                        a += '<div class="aso_task_li asoTaskLink" data-taskid="' + i.id + '" data-bundle="' + i.bundle_id + '" data-tipbtn="' + i.tip_btn + '"  data-tip="' + i.tip + '" data-price="' + Number(i.price).toFixed(1) + '" >' + '<div class="aso_task_top">' + '<div class="task_logo">' + '<img src="' + i.logo + '" alt="">' + "</div>" + '<div class="task_desc_box">' + '<div class="task_name">' + i.title + "</div>" + '<div class="task_desc"><span class="task_desc_tips">剩' + i.remain_num + "份</span>";
                        if (i.need_reg > 0) {
                            a += '<span class="task_desc_tips red_text">需注册</span>'
                        }
                        if (i.need_pay > 0) {
                            a += '<span class="task_desc_tips red_text">需付费' + i.need_pay + "</span>"
                        }
                        if (i.remain_tag > 0) {
                            a += '<span class="task_desc_tips red_text">有后续</span>'
                        }
                        if (typeof i.cust_tag != "undefined" && i.cust_tag != "") {
                            a += '<span class="task_desc_tips">' + i.cust_tag + "</span>"
                        }
                        a += "</div></div>";
                        if (i.apply_status <= -1) {
                            a += '<div class="task_price task_price_complete">已完成</div>'
                        } else if (i.apply_status == 2) {
                            a += '<div class="task_price task_price_ing" data-taskstatus="doing">进行中</div>'
                        } else {
                            var n = new Number(i.price);
                            a += '<div class="task_price">' + '<span class="litter_tx">+&nbsp;</span>' + n.toFixed(1) + '<span class="litter_tx">&nbsp;元</span>' + "</div>"
                        }
                        a += '</div><div class="aso_task_bottom">App Store排名约在第<span class="markTx">' + i.rank + "</span>位";
                        if (r == "isOff") {
                            a += '<div class="default_btn aso_btn lock_btn"></div>'
                        } else {
                            if (i.btn_state == 0) {
                                a += '<div class="default_btn aso_btn accept_btn">极速领取</div>'
                            } else if (i.btn_state == 1) {
                                a += '<div class="default_btn aso_btn open_btn">打开应用</div>'
                            } else if (i.btn_state == 2) {
                                a += '<div class="default_btn aso_btn reward_btn" data-canclick="on">领取奖励</div>'
                            } else {
                                a += '<div class="default_btn aso_btn accept_btn">极速领取</div>'
                            }
                        }
                        a += "</div> </div>"
                    }
                    l.aso_task.append(a);
                    var o = t.return_data.futurelist;
                    a = "";
                    var c = Number(t.return_data.total_future).toFixed(1);
                    l.future_price.text(c);
                    if (o.length > 0) {
                        l.future_task.removeClass("hidden");
                        for (var s in o) {
                            var i = o[s];
                            if (s == 0) {}
                            a += '<div class="future_task_li">' + '<div class="task_logo future_task_time_box" style="background:url(/v4/images/common/qn_' + (s % 5 + 1) + '.png) no-repeat 100% 100%;height:1.466rem;">' + '<div class="future_task_time">' + i.show_time + "</div>" + "</div>" + '<div class="task_desc_box">' + '<div class="task_name">？？？</div>' + ' <div class="task_desc">剩' + i.remain_num + "个</div>" + '</div><div class="task_price">' + '<span class="litter_tx">+&nbsp;</span>' + new Number(i.price).toFixed(1) + '<span class="litter_tx">&nbsp;元</span>' + "</div></div>"
                        }
                    }
                    l.future_task.append(a)
                },
                error: function(t, a) {
                    if (a == "timeout") {
                        xyApplication.setAlert("请求任务列表超时", "请求超时", "error")
                    } else {
                        xyApplication.checkIsOnline()
                    }
                    return false
                }
            })
        },
        serverClick: function() {
            window.location.href = this.serverLink.val();
            return false
        },
        isLockMod: function() {
            var t = this.rocketModType.val();
            if (t == "isOff") {
                return true
            } else {
                return false
            }
        },
        lockBgShow: function(t) {
            this.rocketMod.removeClass("hidden");
            this.cancelBubble(t)
        },
        dialogCanCloseBgClick: function(t) {
            o(t.currentTarget).addClass("hidden");
            return false
        },
        cancelBubble: function(t) {
            t.stopPropagation();
            return false
        },
        rocketBtn: function(t) {
            this.rocketMod.addClass("hidden");
            this.cancelBubble(t)
        },
        remainTask: function(t) {
            var a = o(t.currentTarget);
            xyApplication.openApp(a.data("bundle"),
            function(t) {
                if (t.result == "200") {
                    o.ajax({
                        type: "GET",
                        timeout: 5e3,
                        url: "/v4/taskaso/openremain?id=" + a.data("taskid"),
                        dataType: "json",
                        success: function(t) {
                            if (t.code != "0") {
                                xyApplication.setAlert(t.msg, "提示", "error")
                            } else {
                                xyApplication.setAlert("", "发奖成功", "success",
                                function() {
                                    window.location.href = "/v4/taskaso/list"
                                })
                            }
                        },
                        error: function(t, a) {
                            xyApplication.setAlert("网络连接失败", "连接错误", "error")
                        }
                    })
                } else {
                    xyApplication.setAlert("无法打开应用,前往App Store重新下载", "出错了", "error",
                    function() {
                        window.location.href = a.data("url")
                    })
                }
            })
        },
        praiseTask: function(t) {
            var a = o(t.currentTarget);
            var e = a.data("id");
            window.location.href = "/v4/taskpraise/dtl/?appid=" + e
        },
        asoTaskLink: function(t) {
            var a = this;
            var e = o(t.currentTarget);
            var s = e.data("taskid");
            if (e.find(".task_price").data("taskstatus") == "doing") {
                window.location.href = "/v4/taskaso/asodtl/?appid=" + s
            } else if (e.data("tipbtn") == 1) {
                var i = e.data("tip");
                var n = e.find(".task_logo").children("img").attr("src");
                a.eggsDialog(n, i,
                function() {
                    xyApplication.startTask(s,
                    function() {
                        if (!a.isLockMod()) {
                            e.find(".default_btn").removeClass("accept_btn").addClass("open_btn").text("打开应用")
                        }
                        e.find(".task_price").html('<div class="task_price task_price_ing">进行中</div>');
                        e.find(".task_price").data("taskstatus", "doing");
                        window.location.href = "/v4/taskaso/asodtl/?appid=" + s
                    })
                })
            } else {
                xyApplication.startTask(s,
                function() {
                    if (!a.isLockMod()) {
                        e.find(".default_btn").removeClass("accept_btn").addClass("open_btn").text("打开应用")
                    }
                    e.find(".task_price").html('<div class="task_price task_price_ing">进行中</div>');
                    e.find(".task_price").data("taskstatus", "doing");
                    window.location.href = "/v4/taskaso/asodtl/?appid=" + s
                })
            }
        },
        eggsDialog: function(t, a, e) {
            var s = t;
            o(".taskTipIcon img").attr("src", s);
            o(".taskTipText").text(a);
            o("#taskTipPop").removeClass("hidden");
            o(".taskTipStartTask").off();
            o(".taskTipStartTask").on("click",
            function() {
                o("#taskTipPop").addClass("hidden");
                if (typeof e == "function") {
                    e()
                }
            })
        },
        acceptTaskBtnFun: function(t) {
            var s = this;
            s.cancelBubble(t);
            var a = o(t.currentTarget);
            var i = a.parents(".asoTaskLink");
            var n = i.data("taskid");
            xyApplication.startTask(n,
            function(e) {
                a.removeClass("accept_btn").addClass("open_btn").text("打开应用");
                i.find(".task_price").html('<div class="task_price task_price_ing">进行中</div>');
                a.parents(".asoTaskLink").find(".task_price").data("taskstatus", "doing");
                if (e.ad_type == "1") {
                    xyApplication.setAlert("领取成功", "正在跳转...", "success");
                    setTimeout(function() {
                        window.location.href = e.short_link
                    },
                    2e3)
                } else {
                    xyApplication.copyValue(e.keyword,
                    function() {
                        s.copyDialogKeyword.text(e.keyword);
                        s.copyDialogRank.text(e.rank);
                        s.copyDialog.data("taskid", n);
                        s.appPic.attr("src", e.logo);
                        s.copyDialog.removeClass("hidden");
                        s.copyDialogOpenAppStor.addClass("btn_disable");
                        s.timeoutBox.removeClass("hidden");
                        s.timeoutNum.text(3);
                        var t = 2;
                        var a = setInterval(function() {
                            if (t > 0) {
                                s.timeoutNum.text(t)
                            }
                            t--;
                            if (t < 0) {
                                clearInterval(a);
                                s.copyDialogOpenAppStor.removeClass("btn_disable");
                                s.timeoutBox.addClass("hidden")
                            }
                        },
                        1e3)
                    })
                }
            })
        },
        openAppBtnFun: function(a) {
            this.cancelBubble(a);
            var t = o(a.currentTarget).parents(".asoTaskLink");
            xyApplication.openApp(t.data("bundle"),
            function(t) {
                if (t.result == "200") {
                    o(a.currentTarget).removeClass("open_btn").addClass("reward_btn").text("领取奖励").data("canclick", "on")
                } else {
                    if (o("#failToOpenApp").length > 0) {
                        o("#failToOpenApp").removeClass("hidden")
                    } else {
                        xyApplication.setAlert("无法打开应用,请重新下载或手动打开", "出错了", "error",
                        function() {
                            xyApplication.openApp("com.apple.AppStore")
                        })
                    }
                }
            })
        },
        copyDialogOpenAppStorFun: function(t) {
            var a = this;
            var e = o(t.currentTarget);
            var s = a.copyDialog.data("taskid");
            var i = a.copyDialogRank.text();
            if (!e.hasClass("btn_disable")) {
                xyApplication.openApp("com.apple.AppStore",
                function() {
                    a.copyDialog.addClass("hidden");
                    o(".asoTaskLink[data-taskid=" + s + "]").find(".markTx").text(i)
                })
            }
        },
        copyDialogCancelFun: function(t) {
            var a = this;
            var e = o(t.currentTarget);
            var s = e.parents(".popup_box").data("taskid");
            if (s) {
                xyApplication.cancelTask(s,
                function() {
                    a.refreshTaskStatus(function() {
                        a.copyDialog.addClass("hidden")
                    });
                    xyApplication.setAlert("放弃任务成功", "成功", "error")
                })
            } else {
                xyApplication.setAlert("请联系客服", "出错了", "error")
            }
        },
        rewardBtnFun: function(t) {
            var e = this;
            e.cancelBubble(t);
            var s = o(t.currentTarget);
            if (s.data("canclick") == "on") {
                s.data("canclick", "off");
                var i = o(t.currentTarget).parents(".asoTaskLink");
                xyApplication.verifyTask(i.data("taskid"),
                function(t, a) {
                    if (a != "error") {
                        e.successRewardShow(t.data);
                        i.remove();
                        if (o(".asoTaskLink").length <= 0) {
                            e.aso_task.addClass("hidden")
                        }
                        if (o(".new_task").length <= 0 && o(".remain_task").length <= 0 && o(".asoTaskLink").length <= 0 && o(".praise_task").length <= 0) {
                            e.nullTask.removeClass("hidden")
                        }
                    } else {
                        xyApplication.setAlert(t.msg, "出错了", "error",
                        function() {
                            if (t.url) {
                                window.location.href = t.url
                            }
                        })
                    }
                    s.data("canclick", "on")
                })
            }
        },
        successRewardShow: function(t) {
            this.successRewardKeyword.text(t.keyword);
            this.successRewardMoney.text(t.money);
            this.successReward.removeClass("hidden")
        },
        successRewardFun: function() {
            this.successReward.addClass("hidden")
        },
        confirmTaskFun: function() {
            var t = this;
            var a = o("#conflictTaskBox").data("conflicid");
            o("#cancelPopList").remove();
            xyApplication.cancelTask(a,
            function() {
                t.refreshTaskStatus();
                t.copyDialog.addClass("hidden");
                xyApplication.setAlert("放弃任务成功", "成功", "success")
            });
            o("#conflictTaskBox").addClass("hidden")
        },
        cancelTaskFun: function() {
            o("#conflictTaskBox").addClass("hidden")
        },
        refreshTaskStatus: function(t) {
            var a = this;
            if (!a.isLockMod()) {
                o(".asoTaskLink").each(function() {
                    var t = o(this);
                    if (t.find(".task_price").data("taskstatus") == "doing") {
                        var a = t.data("price");
                        t.find(".task_price").removeClass("task_price_ing").html('<span class="litter_tx">&nbsp;+</span>' + a + '<span class="litter_tx">&nbsp;元</span>');
                        t.find(".default_btn").removeClass("open_btn").addClass("accept_btn").text("极速领取");
                        t.find(".task_price").data("taskstatus", "unApply")
                    }
                })
            } else {
                o(".asoTaskLink").each(function() {
                    var t = o(this);
                    var a = t.data("price");
                    if (t.find(".task_price").data("taskstatus") == "doing") {
                        t.find(".task_price").data("taskstatus", "unApply");
                        t.find(".task_price").removeClass("task_price_ing").html('<span class="litter_tx">&nbsp;+</span>' + a + '<span class="litter_tx">&nbsp;元</span>')
                    }
                })
            }
            typeof t === "function" && t.call()
        },
        trustBtnClick: function(t) {
            var a = this;
            var e = o(t.currentTarget);
            if (!e.hasClass("unClick")) {
                setTimeout(function() {
                    a.openKeyBtn.removeClass("unClick")
                },
                2e3);
                window.location.href = "http://www.xiaoyuzhuanqian.com/QM_iOS_comxiaoyuqian.mobileprovision"
            }
        },
        openKeyBtnClick: function(t) {
            var a = this;
            var e = o(t.currentTarget);
            if (!e.hasClass("unClick")) {
                var s = a.hideurl.val();
                window.location.href = s
            }
        },
        nobindBtnFun: function() {
            this.nobindPop.addClass("hidden")
        },
        changeRocketModFun: function(t) {
            var a = this.rocketModType.val();
            var e = o(".aso_task_bottom .default_btn");
            var s = this;
            var i = o(t.currentTarget);
            if (!i.is(":checked")) {
                if (a == "isOff") {
                    s.rocketMod.removeClass("hidden");
                    i.prop("checked", true)
                } else {
                    this.aso_task.removeClass("hideRocketbtn")
                }
            } else {
                this.aso_task.addClass("hideRocketbtn")
            }
        },
        closePopup: function(t) {
            o(t.currentTarget).parents(".popup_box").addClass("hidden")
        }
    };
    o(function() {
        i.list.init()
    })
})(this, this.jQuery, this._, this.jQuery(document));