<?php

use think\Db;

/**
 * 获取分类所有子分类
 * @param int $cid 分类ID
 * @return array|bool
 */
function get_category_children($cid)
{
    if (empty($cid)) {
        return false;
    }

    $children = Db::name('category')->where(['path' => ['like', "%,{$cid},%"]])->select();

    return array2tree($children);
}

/**
 * 根据分类ID获取文章列表（包括子分类）
 * @param int   $cid   分类ID
 * @param int   $limit 显示条数
 * @param array $where 查询条件
 * @param array $order 排序
 * @param array $filed 查询字段
 * @return bool|false|PDOStatement|string|\think\Collection
 */
function get_articles_by_cid($cid, $limit = 10, $where = [], $order = [], $filed = [])
{
    if (empty($cid)) {
        return false;
    }

    $ids = Db::name('category')->where(['path' => ['like', "%,{$cid},%"]])->column('id');
    $ids = (!empty($ids) && is_array($ids)) ? implode(',', $ids) . ',' . $cid : $cid;

    $fileds = array_merge(['id', 'cid', 'title', 'introduction', 'thumb', 'reading', 'publish_time'], (array)$filed);
    $map    = array_merge(['cid' => ['IN', $ids], 'status' => 1, 'publish_time' => ['<= time', date('Y-m-d H:i:s')]], (array)$where);
    $sort   = array_merge(['is_top' => 'DESC', 'sort' => 'DESC', 'publish_time' => 'DESC'], (array)$order);

    $article_list = Db::name('article')->where($map)->field($fileds)->order($sort)->limit($limit)->select();

    return $article_list;
}

/**
 * 根据分类ID获取文章列表，带分页（包括子分类）
 * @param int   $cid       分类ID
 * @param int   $page_size 每页显示条数
 * @param array $where     查询条件
 * @param array $order     排序
 * @param array $filed     查询字段
 * @return bool|\think\paginator\Collection
 */
function get_articles_by_cid_paged($cid, $page_size = 15, $where = [], $order = [], $filed = [])
{
    if (empty($cid)) {
        return false;
    }

    $ids = Db::name('category')->where(['path' => ['like', "%,{$cid},%"]])->column('id');
    $ids = (!empty($ids) && is_array($ids)) ? implode(',', $ids) . ',' . $cid : $cid;

    $fileds = array_merge(['id', 'cid', 'title', 'introduction', 'thumb', 'reading', 'publish_time'], (array)$filed);
    $map    = array_merge(['cid' => ['IN', $ids], 'status' => 1, 'publish_time' => ['<= time', date('Y-m-d H:i:s')]], (array)$where);
    $sort   = array_merge(['is_top' => 'DESC', 'sort' => 'DESC', 'publish_time' => 'DESC'], (array)$order);

    $article_list = Db::name('article')->where($map)->field($fileds)->order($sort)->paginate($page_size);

    return $article_list;
}

/**
 * 数组层级缩进转换
 * @param array $array 源数组
 * @param int   $pid
 * @param int   $level
 * @return array
 */
function array2level($array, $pid = 0, $level = 1)
{
    static $list = [];
    foreach ($array as $v) {
        if ($v['pid'] == $pid) {
            $v['level'] = $level;
            $list[]     = $v;
            array2level($array, $v['id'], $level + 1);
        }
    }

    return $list;
}

/**
 * 构建层级（树状）数组
 * @param array  $array          要进行处理的一维数组，经过该函数处理后，该数组自动转为树状数组
 * @param string $pid_name       父级ID的字段名
 * @param string $child_key_name 子元素键名
 * @return array|bool
 */
function array2tree(&$array, $pid_name = 'pid', $child_key_name = 'children')
{
    $counter = array_children_count($array, $pid_name);
    if (!isset($counter[0]) || $counter[0] == 0) {
        return $array;
    }
    $tree = [];
    while (isset($counter[0]) && $counter[0] > 0) {
        $temp = array_shift($array);
        if (isset($counter[$temp['id']]) && $counter[$temp['id']] > 0) {
            array_push($array, $temp);
        } else {
            if ($temp[$pid_name] == 0) {
                $tree[] = $temp;
            } else {
                $array = array_child_append($array, $temp[$pid_name], $temp, $child_key_name);
            }
        }
        $counter = array_children_count($array, $pid_name);
    }

    return $tree;
}

/**
 * 子元素计数器
 * @param array $array
 * @param int   $pid
 * @return array
 */
function array_children_count($array, $pid)
{
    $counter = [];
    foreach ($array as $item) {
        $count = isset($counter[$item[$pid]]) ? $counter[$item[$pid]] : 0;
        $count++;
        $counter[$item[$pid]] = $count;
    }

    return $counter;
}

/**
 * 把元素插入到对应的父元素$child_key_name字段
 * @param        $parent
 * @param        $pid
 * @param        $child
 * @param string $child_key_name 子元素键名
 * @return mixed
 */
function array_child_append($parent, $pid, $child, $child_key_name)
{
    foreach ($parent as &$item) {
        if ($item['id'] == $pid) {
            if (!isset($item[$child_key_name]))
                $item[$child_key_name] = [];
            $item[$child_key_name][] = $child;
        }
    }

    return $parent;
}

/**
 * 循环删除目录和文件
 * @param string $dir_name
 * @return bool
 */
function delete_dir_file($dir_name)
{
    $result = false;
    if (is_dir($dir_name)) {
        if ($handle = opendir($dir_name)) {
            while (false !== ($item = readdir($handle))) {
                if ($item != '.' && $item != '..') {
                    if (is_dir($dir_name . DS . $item)) {
                        delete_dir_file($dir_name . DS . $item);
                    } else {
                        unlink($dir_name . DS . $item);
                    }
                }
            }
            closedir($handle);
            if (rmdir($dir_name)) {
                $result = true;
            }
        }
    }

    return $result;
}

/**
 * 判断是否为手机访问
 * @return  boolean
 */
function is_mobile()
{
    static $is_mobile;

    if (isset($is_mobile)) {
        return $is_mobile;
    }

    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        $is_mobile = false;
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false
    ) {
        $is_mobile = true;
    } else {
        $is_mobile = false;
    }

    return $is_mobile;
}

/**
 * 手机号格式检查
 * @param string $mobile
 * @return bool
 */
function check_mobile_number($mobile)
{
    if (!is_numeric($mobile)) {
        return false;
    }
    $reg = '#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#';

    return preg_match($reg, $mobile) ? true : false;
}
function number_to_word($num,$is_z = false, $h = '--'){
    $symbol = '';
    if($num < 0){
        $symbol = '-';
    }
    $num = abs($num);
    $result = 0;
    $yi = floor($num / 100000000);
    if ($yi >= 1) {
        $result = $yi. '亿';
        $yi_wan = ($num - $yi* 100000000)/10000;

        if ($yi_wan > 1)
            $result .= $yi_wan. '万';
    } else {
        $wan = $num / 10000;
        if ($wan > 1)
            $result = $wan . '万';
        else
            $result = $num;
    }
    if($is_z && $num == 0){
        return $h;
    }
    return $symbol.$result;
}
function number_to_word2($num,$is_z = false, $h = '--'){
    $symbol = '';
    if($num < 0){
        $symbol = '-';
    }
    $num = abs($num);
    $result = 0;

    $wan = $num / 10000;
    $result = $wan;

    if($is_z && $num == 0){
        return $h;
    }
    return $symbol.$result;
}
/*
 *@$type
 *bank_name 银行名称
 *parent_code 父银行编码
 *bank_short_name 银行短名
 *bank_eng_name 银行英文名
 *bank_eng_short_name 英文短名
 *bank_py 银行拼音
 *bank_logo 银行logo
 * bank_subordinate 所属
 * */
function get_bank($code,$type = 'bank_name'){
    $bank_info = get_banks_info();
    $arr = [];
    if(isset($bank_info['other'])){
        $bank_arr = [];
        foreach ($bank_info['other'] as $key => $val){
            $bank_arr[$key] = $val[$type];
        }
        $arr = $bank_arr;
    }


    $bank = '';
    if($code == 'all'){
        $bank = $arr;
    }else{
        if(isset($arr[$code])){
            $bank = $arr[$code];
        }else{
            $bank = $code;
        }
    }

    return $bank;
}
/**
* 返回银行名称
* ＠parame code 银行编码
*/
function getBankNameByCode($code = '')
{
    $data['code'] = $code;
    $res = doCurlGetRequest($data,'/getNameByCode',false,'',10,true);
    if ($res) {
        $bankName = $res;
    }else{
        $bankName = $code;
    }
    return $bankName;
}
/*跨行调款增加包状态*/
function set_status_name($type){
    $arr = array(
        '0'=>'已上传',
        '1'=>'未上传',
        '-1'=>'已删除',
    );
    return $arr[$type];
}
function get_program_type($type){
    $arr = array(
        '1'=>'商行接口',
        '2'=>'商行php',
        '3'=>'关联盒',
    );
    return $arr[$type];
}
function get_task_status($status){
    $arr = array(
        '0'=>'未审核',
        '1'=>'未出库',
        '2'=>'在途',
        '3'=>'已入库',
        '10'=>'退回中',
        '11'=>'已退回',
        '20'=>'申请变更',
        '-1'=>'已删除',
        '33'=>'准支付(预支付)',
        '31'=>'确权上链',
        '40'=>'人行终止',
        '32'=>'结算支付'
    );
    return $arr[$status];
}
function get_pack_status($status){
    $arr = array(
        '0'=>'正常',
        '1'=>'出库',
        '2'=>'入库',
        '40'=>'作废',
        '-1'=>'已拆包'
    );
    return $arr[$status];
}
function get_bundle_status($status){
    $arr = array(
        '0'=>'正常',
        '1'=>'组包或者出库',
        '40'=>'作废',
    );
    return $arr[$status];
}
function get_task_type($status){
    $arr = array(
        '0'=>'跨行调款',
        '1'=>'上交残损券',
        '2'=>'代理任务',
        '3'=>'向人行交款',
        '4'=>'从人行取款'
    );
    return $arr[$status];
}
function get_valuta_flag($status){
    $arr = array(
        '0'=>'非双流同步',
        '1'=>'双流同步',
        '2'=>'不限制',
    );
    return $arr[$status];
}
function get_balance_status($status){
    $arr = array(
        '0'=>'未审核',
        '1'=>'已审核',
        '2'=>'已入库',
    );
    return $arr[$status];
}

function get_damage_status($status){
    $arr = array(
        '0'=>'未审核',
        '1'=>'已审核',
        '2'=>'已完成',
    );
    return $arr[$status];
}

function get_damage_type($status){
    $arr = array(
        '0'=>'正常',
        '1'=>'调整',
        '2'=>'调出',
    );
    return $arr[$status];
}

function get_branch_type($type){
    $arr = array(
        '0'=>'网点',
        '1'=>'金库'
    );
    return $arr[$type];
}
function get_agent_type($status){
    $arr = array(
        '0'=>'普通',
        '1'=>'代理网点',
        '2'=>'被代理网点',
        '3'=>'代理&被代理'
    );
    return $arr[$status];
}
function get_gzh_type($status){
    $arr = array(
        '1'=>'现金收入',
        '2'=>'现金付出',
        '3'=>'清分'
    );
    return $arr[$status];
}
function get_bundle_type($status){
    if($status == 5){
        return '已拆';
    }else{
        return '未拆';
    }
}
function get_bundle_record_status($status){
    $arr = array(
        '1'=>'代理调出',
        '2'=>'代理调入',
        '3'=>'跨行调出',
        '4'=>'跨行调入',
        '5'=>'拆捆',
        '6'=>'行内调出',
        '7'=>'行内调入',
        '8'=>'清分',
        '11'=>'拆包',
        '21'=>'换封签',
        '20'=>'回退',
        '50'=>'自助设备配钞',
        '40'=>'作废'
    );
    return $arr[$status];
}
function get_pack_type($status){
    if($status == 11){
        return '已拆';
    }else{
        return '未拆';
    }
}
function get_pack_record_status($status){
    $arr = array(
        '1'=>'代理调出',
        '2'=>'代理调入',
        '3'=>'跨行调出',
        '4'=>'跨行调入',
        '5'=>'拆捆',
        '6'=>'行内调出',
        '7'=>'行内调入',
        '8'=>'清分',
        '11'=>'拆包',
        '12'=>'装包',
        '20'=>'回退',
        '50'=>'自助设备配钞',
        '40'=>'作废'
    );
    return $arr[$status];
}

function show_business_type($type){
    $arr = array(
        '0'=>'全部',
        '1'=>'现金收入',
        '2'=>'现金支出',
    );
    return $arr[$type];
}
function show_gzh_type($type){
    $arr = array(
        '1'=>'正常',
        '2'=>'坐支',
    );
    return $arr[$type];
}   

function get_notice_type($type){
    $arr = array(
        '0'=>'正常',
        '1'=>'异常',
    );
    return $arr[$type];
}
function get_cur_bank($type = 'bank_code'){
    $bank = [];
    if(session('cur_bank')){
        $bank = session('cur_bank');
    }else{
        $banks = get_banks_info();
        if(isset($banks['local'])){
            foreach ($banks['local'] as $key => $val){
                $bank = $banks['local'][$key];
                $bank['bank_code'] = $key;
            }
        }
        session('cur_bank',$bank);
    }
    if (!isset($bank[$type])) {
        exit('接口配置错误');
    }
    return $bank[$type];
}

//获取网点
function get_bank_branch($branch_code,$type = 'branch_name',$is_cache = false){
    $branchs = session('branchs');
    if($is_cache || !$branchs){
        $result = doCurlGetRequest([],'/query_bank_branch_relation');
        if($result['err_code'] == 0){
            $branchs = $result['data']['branch'];
            session('branchs',$branchs);
        }
    }
    $branch = $branch_code;
    if(count($branchs) > 0 && $branch != ''){
        foreach ($branchs as $val){
            if($branch_code == substr($val['branch_code'],0,strlen($branch_code))){
                $branch = $val[$type];
                break;
            }
        }
    }
    return $branch;
}



function get_banks_info(){
    $bank = [];
    if(session('banks_info')){
        $bank = session('banks_info');
    }else{
        $result = doCurlGetRequest([],'/get_banks_info');
        if($result['err_code'] == 0){
            $bank = $result['data'];

        }
    }
    return $bank;
}


function query_bank_key($bank_code){
    $bank_name = $bank_code;
        $result = doCurlGetRequest(['bank_code'=>$bank_code],'/query_bank_key');
        if($result['err_code'] == 0){
            $bank_name = isset($result['data']['bank_key'][0]['bank_name']) ? $result['data']['bank_key'][0]['bank_name'] : $bank_code;
        }
    return $bank_name;
}

function get_banks(){
    $result = doCurlGetRequest([],'/query_bank_key');
    $list = [];
    if($result['err_code'] == 0 && $result['data']){
        $data = $result['data']['bank_key'];
        foreach ($data as $key => $val){
            $tmp['bank_name'] = $val['bank_name'];
            $tmp['bank_net_code'] = $val['bank_net_code'];
            $tmp['bank_code'] = $val['bank_code'];
            $list[] = $tmp;
        }
    }
    return $list;
}


function get_valuta_info($use_type = 0, $valuta_status = 0){
    $valuta_infos = [];
//    if(session('valuta_infos_'.$use_type.'_'.$valuta_status)){
//        $valuta_infos = session('valuta_infos_'.$use_type.'_'.$valuta_status);
//    }else{
        $result = doCurlGetRequest(['use_type'=>$use_type,'valuta_status'=>$valuta_status],'/query_valuta_info');
        if($result['err_code'] == 0 && isset($result['data']['valuta_info'])){
            $valuta_infos = $result['data']['valuta_info'];
            session('valuta_infos_'.$use_type.'_'.$valuta_status,$valuta_infos);
        }
//    }

    return $valuta_infos;
}
function get_valuta($valuta_code,$type = '',$use_type = 0){
    if(trim($valuta_code) == ''){
        return '币值编码空';
    }
    $valuta_infos = get_valuta_info($use_type,'');
    $valuta = [];
    if(count($valuta_infos) > 0){
        foreach ($valuta_infos as $val){
            if($valuta_code == $val['valuta_code']){
                $valuta = $val;
                break;
            }
        }
    }
    if(trim($type != '')){
        if(isset($valuta[$type])){
            return $valuta[$type];
        }else{
            return $valuta_code.'(编码无对应值)';
        }

    }
    return $valuta;
}

function ext_json_decode($str, $mode=false){
    $str = trim( $str );
    $str = ltrim( $str, '(' );
    $str = rtrim( $str, ')' );
    $a = preg_split('#(?<!\\\\)\"#', $str );
    for( $i=0; $i < count( $a ); $i+=2 ){
        $s = $a[$i];
        $s = preg_replace('#([^\s\{\}\:\,]+):#', '"\1":', $s );
        $s = str_replace("'", '"', $s );
        $a[$i] = $s;
    }
    $str = implode( '"', $a );
    return json_decode($str, $mode);
}
//curl 抛出异常
function curlError($response,$ch)
{
    if (false === $response) {
        die(curl_error($ch).'请检查.env接口配置是否正确!');
        // // echo 1;
        // throw new Exception(curl_error($ch),curl_errno($ch));
    }
}

function doCurlGetRequest($data,$interface = '',$delete=false,$base_url = '',$timeout = 30,$code=false){
    if($interface == '' || $timeout <= 0){
        return [
            'err_code' => 999,
            'err_msg'   => '参数错误',
        ];
    }
    if($base_url == ''){
        $base_url = config('interface_url');
    }
    $url = $base_url.$interface;
    $url = $url.'?'.http_build_query($data);
    $con = curl_init((string)$url);
    if (class_exists('\CURLFile')) {// 这里用特性检测判断php版本
        curl_setopt($con, CURLOPT_SAFE_UPLOAD, true);
    } else {
        if (defined('CURLOPT_SAFE_UPLOAD')) {
            curl_setopt($con, CURLOPT_SAFE_UPLOAD, false);
        }
    }
    if ($delete) {
        curl_setopt($con, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    curl_setopt($con, CURLOPT_HEADER, false);
    curl_setopt($con, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($con, CURLOPT_TIMEOUT, (int)$timeout);
    $result = curl_exec($con);
    if ($code) {
       return $result;
    }
    curlError($result,$con);
    $result = json_decode($result,true);

    return $result;
}

function doCurlPostRequest($requestString,$interface,$base_url = '',$timeout = 30,$isJson=false){
    if($interface == '' || $requestString == '' || $timeout <=0){
        return [
            'err_code' => 999,
            'err_msg'   => '参数错误',
        ];
    }

    if($base_url == ''){
        $base_url = config('interface_url');
    }
    $url = $base_url.$interface;
    //$url = 'http://127.0.0.1/tangtang/public/index.php/api/bank/test';
    //$url = 'http://test.app/interface.php';
    //print_r(urldecode(http_build_query($requestString)));
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, 1 );
    curl_setopt($curl, CURLOPT_POSTFIELDS, $requestString);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT,(int)$timeout);
    if ($isJson) {
        $headers_isJson = [
            "Content-type: application/json;charset='utf-8'",
            "Accept: application/json",
            "Cache-Control: no-cache",
            "Pragma: no-cache"
        ];
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers_isJson);
    }
    $result = curl_exec($curl);
    curlError($result,$curl);
    $result = json_decode($result,true);
    return $result;
}

//大数据接口调用
//参数1：post数据(不填则为GET)，参数2：接口，参数3：自定义url，参数4：提交的$cookies,参数5：是否返回$cookies
function curl_request($data='', $interface, $is_post = false, $url = '', $cookie='', $returnCookie=0){
    if($url == ''){
        $url = config('bigdata_url');
    }
    $url = $url.$interface;
    if(!$is_post){
        $url = $url.'?'.http_build_query($data);
    }
    /*echo $url;*/
    $headers = array(
        "Content-type: application/json;charset='utf-8'",
        "Accept-Language:zh-CN,zh;q=0.8",
    );
    //print_r($data);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
    if($is_post) {
        //$data = json_encode($data);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    if($cookie) {
        curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    }
    curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);
    curlError($data,$curl);
    $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
    //print_r(curl_error($curl));
//    if (curl_errno($curl)) {
//        return curl_error($curl);
//    }
    curl_close($curl);
    if($returnCookie){
        list($header, $body) = explode("\r\n\r\n", $data, 2);
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
        $info['cookie']  = substr($matches[1][0], 1);
        $info['content'] = $body;
        return $info;
    }else{
        //print_r($data);
        $data = json_decode($data,true);
        if($httpCode != 200){
            $result = [
                'err_code' => 2,
                'http_code' => $httpCode,
                'err_msg'   => '',
            ];
            if(isset($data['msg'])){
                $result['err_msg'] = $data['msg'];
            }else{
                $result['err_msg'] = $data['message'];
            }
        }else{
            if (isset($data['totalPage'])){
                $data['total_page'] = $data['totalPage'];
                $data['cur_page'] = $data['page'];
                $data['err_code'] = 0;
            }
            $result = $data;
        }
        return $result;
    }
}

//远程下载文件到本地 浏览器下载

function httpcopy($url,$filename, $file="", $timeout=60) {
    $file = empty($file) ? pathinfo($url,PATHINFO_BASENAME) : $file;
    $dir = ROOT_PATH . 'public/uploads/temp/';
    !is_dir($dir) && @mkdir($dir,0755,true);
    $url = str_replace(" ","%20",$url);
    /*echo $url;*/
    if(function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $temp = curl_exec($ch);
        curlError($temp,$ch);
        $file_path = $dir.$file;
        if($temp == ''){
            $temp = ' ';
        }
        if(@file_put_contents($file_path, $temp) && !curl_error($ch)) {
            if(file_exists($file_path)) {
                ob_end_clean();
                header("Content-Type: application/force-download;");
                header("Content-Transfer-Encoding: binary");
                header ('Content-Disposition: attachment;filename="'.$filename.'"');
                header("Expires: 0");
                header("Cache-control: private");
                header("Pragma: no-cache");
                readfile ($file_path);
            }
            exit;
        } else {
            return false;
        }
    } else {
        $opts = array(
            "http"=>array(
                "method"=>"GET",
                "header"=>"",
                "timeout"=>$timeout)
        );
        $context = stream_context_create($opts);
        if(@copy($url, $file, $context)) {
            //$http_response_header
            return $file;
        } else {
            return false;
        }
    }
}

function get_balance_data($valuta_info,$stock_info){
    if(isset($stock_info['stock_details']) && count($stock_info['stock_details']) > 0){
        for($i=0;$i<count($valuta_info);$i++){
            if(count($stock_info['stock_details']) > 0){
                foreach($stock_info['stock_details'] as $key => $val){
                    if($valuta_info[$i]['valuta_code'] == $val['valuta_code']){
                        $valuta_info[$i]['full_amount'] = $val['full_amount'];
                        $valuta_info[$i]['damaged_amount'] = $val['damaged_amount'];
                        unset($stock_info['stock_details'][$key]);
                        break;
                    }
                }
            }else{
                break;
            }
        }
    }
    return $valuta_info;
}
//function get_valuta_info($use_type = 0, $valuta_status = 0){
//    $data = [];
//    $result = doCurlGetRequest(['use_type'=>$use_type,'valuta_status'=>$valuta_status],'/query_valuta_info');
//    if($result['err_code'] == 0 && isset($result['data']['valuta_info'])){
//        $data = $result['data']['valuta_info'];
//    }
//    return $data;
//}

function get_damage_data($valuta_info,$stock_info){
    if(isset($stock_info['stock_details']) && count($stock_info['stock_details']) > 0){
        for($i=0;$i<count($valuta_info);$i++){
            if(count($stock_info['stock_details']) > 0){
                foreach($stock_info['stock_details'] as $key => $val){
                    if($valuta_info[$i]['valuta_code'] == $val['valuta_code']){
                        $valuta_info[$i]['amount'] = $val['amount'];
                        $valuta_info[$i]['task_amount'] = $val['task_amount'];
                        $valuta_info[$i]['approve_amount'] = $val['approve_amount'];
                        unset($stock_info['stock_details'][$key]);
                        break;
                    }
                }
            }else{
                break;
            }
        }
    }
    return $valuta_info;
}


/*报表详情-银行是否填写*/
function get_fill_type($status){
    $arr = array(
        '0'=>'未填写',
        '1'=>'已填写'
    );
    return $arr[$status];
}

/*报表内行编码-对应文字*/
function get_report_name($num,$code){
    $data['reportNum'] = $num;
    $list = session('report_'.$num);
    if($list == ''){
        $result = doCurlGetRequest($data,'/rows',config('report_url'));
        $list = $result['data']['rowList'];
        session('report_'.$num,$list);
    }
    $name = '';
    /*print_r($result);*/
    if($list != null){
        //print_r($list);
        foreach ($list as $val){
            if($val['rowCode'] == $code){
                $name = $val['showName'];
            }
        }
    }
    return $name;
}

/*报表内行编码-对应类别*/
function set_group_type($status){
    $arr = array(
        '0'=>'纸币',
        '1'=>'硬币'
    );
    return $arr[$status];
}

/*报表对应的文件夹*/
function set_report_file($status){
    $arr = array(
        'R0001'=>'cashstock',
        'R0002'=>'smallamount',
        'R0003'=>'cashdelivery',
        'R0004'=>'cashclear',
        'R0005'=>'cashstock',
        'R0006'=>'coinloop',
        'R0007'=>'cashcollect',
        'R0008'=>'cashstock',
    );
    return $arr[$status];
}
/*报表Num对应的报表名称*/
function show_report_name($status){
    $arr = array(
        'R0001'=>'营管部季度银行业金融机构现金库存情况统计表',
        'R0002'=>'中小面额人民币投放、回笼情况统计表',
        'R0003'=>'现金投放回笼计划测算表',
        'R0004'=>'南京市银行业金融机构现金清分情况统计表',
        'R0005'=>'cashstock',
        'R0006'=>'硬币自循环情况',
        'R0007'=>'银行业金融机构现金收支情况统计表',
        'R0008'=>'cashstock',
    );
    return $arr[$status];
}

function format_rowlist_type($rowlist)
{
    $arr = [];
    if (count($rowlist) > 0) {
        foreach ($rowlist as $val) {
            if ($val['groupType'] == 1) {
                $arr['equipment'][] = $val;
            } else {
                $arr['coin'][] = $val;
            }
        }
    }
    return $arr;
}

function format_coin_type($rowlist)
{
    $arr = [];
    if (count($rowlist) > 0) {
        foreach ($rowlist as $val) {
            if ($val['groupAssist'] == 0) {
                $arr['return'][] = $val;
            } else {
                $arr['delivery'][] = $val;
            }
        }
    }
    return $arr;
}


function format_collect_type($rowlist)
{
    $arr = [];
    if (count($rowlist) > 0) {
        $last = [];
        foreach ($rowlist as $val) {
            if ($val['groupType'] == 1) {
                $arr['out'][] = $val;
                $last['out'] = $val;
            } else {
                $arr['in'][] = $val;
                $last['in'] = $val;
            }
        }
        $tmp = [];
        if($last){
            $tmp['amount'] = $last['out']['amount'] - $last['in']['amount'];
            $tmp['totalAmount'] = $last['out']['totalAmount'] - $last['in']['totalAmount'];
        }
        $arr['total'] = $tmp;
    }
    return $arr;
}

function get_agent_task_status($status){
    $arr = array(
        '0'=>'未审核',
        '1'=>'未出库',
        '2'=>'在途',
        '3'=>'已入库',
        '10'=>'退回中',
        '11'=>'已退回',
        '20'=>'申请变更',
        '-1'=>'已删除',
        '33'=>'准支付(预支付)',
        '31'=>'确权上链',
        '40'=>'人行终止',
        '32'=>'结算支付'
    );
    return $arr[$status];
}
function get_agent_appoint_status($status){
    $arr = array(
        '0'=>'待审核',
        '-1'=>'删除',
        '1'=>'审核通过',
        '2'=>'审核拒绝',
        '3'=>'人行终止',
        '4'=>'已取消'
    );
    return $arr[$status];
}

function getFilesGzhType($status){
    $arr = [
        '0' => '跨行调款',
        '2' => '代理任务',
        '3' => '向人行交款',
        '4' => '从人行取款',
    ];
    return $arr[$status];
}
