/**
 * 后台JS主入口
 */

var layer = layui.layer,
    element = layui.element(),
    laydate = layui.laydate,
    laypage = layui.laypage,
    form = layui.form();

/**
 * AJAX全局设置
 */
$.ajaxSetup({
    type: "post",
    dataType: "json"
});
//input框值为10的倍数
function checkNumIsMul(a){
    var dataValue = a.getAttribute('data-value');
    var number = a.value;
    if (dataValue < 1) {
        var numbeRemainber = (Number(number)*100) % (Number(dataValue)*100);
    }else{
        var numbeRemainber = number % dataValue;
    }
    if (number !== '' && numbeRemainber > 0) {
        if(dataValue < 1){
            a.value = 0;
        }else{
            // layer.msg('plase input Multiple of 10');
            a.value = number - numbeRemainber;
        }
    }
}
/**
 * 后台侧边菜单选中状态
 */
$('.layui-nav-item').find('a').removeClass('layui-this');
$('.layui-nav-tree').find('a[href*="' + GV.current_controller+GV.current_mothod + '"]').parent().addClass('layui-this').parents('.layui-nav-item').addClass('layui-nav-itemed');
/*$('.layui-nav-item').each(function(){
    $(this).click(function(){
        $(this).parent('ul').find('.layui-nav-item').removeClass('layui-nav-itemed');
        $(this).addClass('layui-nav-itemed')
    })
})*/
/**
 * 通用单图上传
 */
layui.upload({
    url: "/index.php/api/upload/upload",
    type: 'image',
    ext: 'jpg|png|gif|bmp',
    success: function (data) {
        if (data.error === 0) {
            document.getElementById('thumb').value = data.url;
        } else {
            layer.msg(data.message);
        }
    }
});

function data_page(url,op,callback) {
    $.ajax({
        type: "GET",
        url: url,
        data: op,
        dataType: "json",
        success: callback,
        error:function(){
            layer.msg('稍后尝试');
            layer.closeAll('loading');
        }
    });
}

/* function bigNumberToText(val) {
     val = parseInt(val);
    var result;
    var yi = val / 100000000;
    if (yi >= 1) {
        result = yi.toFixed(0) + '亿';
        var yi_wan = (val - yi* 100000000)/10000;
        if (yi_wan >= 1)
            result += yi_wan.toFixed(0) + '万';
    } else {
        var wan = val / 10000;
        if (wan >= 1)
            result = wan.toFixed(0) + '万';
        else
            result = val;
    }
    return result;
}*/
function bigNumberToText(val) {
    var val = (val || 0).toString(),
        val_l = val.length;
        result = '',
        zhao = '',
        yi = '',
        wan = '',
        yuan = '';
    if ( val_l > 16 ) {
        result = Number(val) + '元';
    } else if ( val_l > 12 ) {
        if( Number( val.slice(0, val_l - 12) ) != 0) { zhao = Number(val.slice(0, val_l - 12)) + '兆'; }
        if( Number( val.slice(val_l - 12, val_l - 8) ) != 0) { yi = Number(val.slice(val_l - 12, val_l - 8)) + '亿'; }
        if( Number( val.slice(val_l - 8, val_l - 4) ) != 0) { wan = Number(val.slice(val_l - 8, val_l - 4)) + '万'; }
        if( Number( val.slice(-4) ) != 0) { yuan = Number(val.slice(-4)) + '元'; }
        result = zhao + yi + wan + yuan;
    } else if ( val_l > 8 ) {
        if( Number( val.slice(0, val_l - 8) ) != 0) { yi = Number(val.slice(0, val_l - 8)) + '亿'; }
        if( Number( val.slice(val_l - 8, val_l - 4) ) != 0) { wan = Number(val.slice(val_l - 8, val_l - 4)) + '万'; }
        if( Number( val.slice(-4) ) != 0) { yuan = Number(val.slice(-4)) + '元'; }
        result = yi + wan + yuan;
    } else if ( val_l > 4 ) {
        if( Number( val.slice(0, val_l - 4) ) != 0) { wan = Number(val.slice(0, val_l - 4)) + '万'; }
        if( Number( val.slice(-4) ) != 0) { yuan = Number(val.slice(-4)) + '元'; }
        result = wan + yuan;
    } else if ( val_l > 0 ) {
        result = Number(val) + '元';
    }
    return result;
}
/**
 * 通用日期时间选择
 */
$('.datetime').on('click', function () {
    laydate({
        elem: this,
        istime: true,
        format: 'YYYY-MM-DD hh:mm:ss'
    })
});

/**
 * 通用表单提交(AJAX方式)
 */
form.on('submit(*)', function (data) {
    $.ajax({
        url: data.form.action,
        type: data.form.method,
        data: $(data.form).serialize(),
        success: function (info) {
            if (info.code === 1) {
                setTimeout(function () {
                    location.href = info.url;
                }, 1000);
            }
            layer.msg(info.msg);
        }
    });

    return false;
});

/**
 * 通用表单提交(接口向)
 */
form.on('submit(interface)', function (data) {
    $.ajax({
        url: data.form.action,
        type: data.form.method,
        data: $(data.form).serialize(),
        success: function (info) {
            if (info.err_code == 0) {
                setTimeout(function () {
                    location.href = info.url;
                }, 1000);
                info.err_msg = '提交成功'
            }
            layer.msg(info.err_msg);
        }
    });

    return false;
});


/**
 * 通用批量处理（审核、取消审核、删除）
 */
$('.ajax-action').on('click', function () {
    var _action = $(this).data('action');
    layer.open({
        shade: false,
        content: '确定执行此操作？',
        btn: ['确定', '取消'],
        yes: function (index) {
            $.ajax({
                url: _action,
                data: $('.ajax-form').serialize(),
                success: function (info) {
                    if (info.code === 1) {
                        setTimeout(function () {
                            location.href = info.url;
                        }, 1000);
                    }
                    layer.msg(info.msg);
                }
            });
            layer.close(index);
        }
    });

    return false;
});

/**
 * 通用全选
 */
$('.check-all').on('click', function () {
    $(this).parents('table').find('input[type="checkbox"]').prop('checked', $(this).prop('checked'));
});

/**
 * 退出
 */
$('#logout').on('click', function () {
    var _href = $(this).attr('href');
    layer.open({
        content: '确定退出吗？',
        btn: ['确定', '取消'],
        yes: function (index) {
            $.ajax({
                url: _href,
                type: "get",
                success: function (info) {
                    if (info.code === 1) {
                        setTimeout(function () {
                            location.href = info.url;
                        }, 1000);
                    }
                    layer.msg(info.msg);
                }
            });
            layer.close(index);
        }
    });

    return false;
});

/**
 * 通用删除
 */
$('.ajax-delete').on('click', function () {
    var _href = $(this).attr('href');
    layer.open({
        shade: false,
        content: '确定删除？',
        btn: ['确定', '取消'],
        yes: function (index) {
            $.ajax({
                url: _href,
                type: "get",
                success: function (info) {
                    if (info.code === 1) {
                        setTimeout(function () {
                            location.href = info.url;
                        }, 1000);
                    }
                    layer.msg(info.msg);
                }
            });
            layer.close(index);
        }
    });

    return false;
});

/**
 * 清除缓存
 */
$('#clear-cache').on('click', function () {
    var _url = $(this).data('url');
    if (_url !== 'undefined') {
        $.ajax({
            url: _url,
            success: function (data) {
                if (data.code === 1) {
                    setTimeout(function () {
                        location.href = location.pathname;
                    }, 1000);
                }
                layer.msg(data.msg);
            }
        });
    }

    return false;
});

function GetDateStr(AddDayCount) {
    var dd = new Date();
    dd.setDate(dd.getDate()+AddDayCount);//获取AddDayCount天后的日期
    var y = dd.getFullYear();
    var m = (dd.getMonth()+1)<10?'0'+(dd.getMonth()+1):(dd.getMonth()+1);//获取当前月份的日期
    var d = dd.getDate()<10?'0'+dd.getDate():dd.getDate();
    return y+"-"+m+"-"+d;
}
function get_time(date){
    return Date.parse(new Date(date.replace(/-/g,"/")))/1000;
}
function check_date(begin_time,end_time){
    if($.trim(begin_time) == ''){
        layer.msg('开始时间不能为空');
        return false;
    }
    if($.trim(end_time) == ''){
        layer.msg('结束时间不能为空');
        return false;
    }
    if(get_time(begin_time) >get_time(end_time)){
        layer.msg('开始时间不能大于结束时间');
        return false;
    }
    return true;
}

function number_format(number, decimals, dec_point, thousands_sep,roundtag) {
    /*
     * 参数说明：
     * number：要格式化的数字
     * decimals：保留几位小数
     * dec_point：小数点符号
     * thousands_sep：千分位符号
     * roundtag:舍入参数，默认 "ceil" 向上取,"floor"向下取,"round" 四舍五入
     * */
    number = ''+number+'';
    if (number.indexOf('.') > 0) {
        decimals = Number(number.length) - Number(number.indexOf('.')) - 1;
        if (number.substring(number.indexOf('.')) == 2){
            decimals = 2;
        }
    }
    number = (number + '').replace(/[^0-9+-Ee.]/g, '');
    roundtag = roundtag || "ceil"; //"ceil","floor","round"
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {

            var k = Math.pow(10, prec);
            console.log();

            return '' + (parseFloat(Math[roundtag](parseFloat((n * k).toFixed(prec*2))).toFixed(prec*2)) / k);
        };
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    var re = /(-?\d+)(\d{3})/;
    while (re.test(s[0])) {
        s[0] = s[0].replace(re, "$1" + sep + "$2");
    }

    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return (s.join(dec));
}
    function returnFloat(value){
        var reg = RegExp(/,/);
        if (reg.test(value)) {
           var value=value.replace(/,/g,''); 
        }
         
        var value=Math.round(parseFloat(value)*100)/100;
        var xsd=value.toString().split(".");
        var re=/(?=(?!(\b))(\d{3})+$)/g; 
        if(xsd.length==1){
            value=value.toString().replace(re,",")+".00"; 
            return value;
        }
        if(xsd.length>1){
            if(xsd[1].length<2){
                value=value.toString().replace(re,",")+"0"; 
                return value;
            }
        }
    }

/**
*设置时间
* */
function getDate (str){
    var y = str.getFullYear();
    var m = Number(str.getMonth())+1;
    if(m.toString().length<2){
        m = '0'+m;
    }
    var d = str.getDate();
    if(d.toString().length<2){
        d = '0'+d;
    }
    var date = y + '-' + m + '-' + d;
    return date;
}

//计算总金额-任务
function publicAmount(_this){
    $('b[class="amountTip"]').remove();
    var showVal = number_format($(_this).val(),2,'.',',');
    var nextL = $(_this).next().length;
    if( showVal != 0){
        if(nextL == 0){
            $(_this).after('<b class="amountTip">'+showVal+'</b>')
        }else{
            if($(_this).next().attr('class') == 'amountTip'){
                $(_this).next().html(showVal);
            }
        }
    }
}
$('body').click(function(){
    $('b[class="amountTip"]').remove();
});
$('.amount-input-hastip').blur(function(){
    console.log('type 2');
    var _this = this;
    publicAmount(_this);
});
$('.amount-input-hastip').click(function(){
    console.log('type 4');
    var _this = this;
    publicAmount(_this);
});
$('.amount-input-hastip').on('keypress',function(){
    console.log('type 3');
    var _this = this;
    publicAmount(_this);
});
$('.amount-input-hastip').bind('input propertychange', function() {
    console.log('type 1');
    var _this = this;
    publicAmount(_this);
});

/*格式化时间*/
Date.prototype.Format = function (fmt) { // author: meizz
    var o = {
        "M+": this.getMonth() + 1, // 月份
        "d+": this.getDate(), // 日
        "h+": this.getHours(), // 小时
        "m+": this.getMinutes(), // 分
        "s+": this.getSeconds(), // 秒
        "q+": Math.floor((this.getMonth() + 3) / 3), // 季度
        "S": this.getMilliseconds() // 毫秒
    };
    if (/(y+)/.test(fmt))
        fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
}
