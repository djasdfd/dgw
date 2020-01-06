<?php

/**

 * 分页

 * @param $page

 * @param $page_size

 * @param $count

 * @param $data_list

 * @param array $param

 * @return array

 */

function paginate($page, $page_size, $count, $data_list, $param = array())

{

    return array_merge(array(

        'total'=> $count,

        'per_page'=> $page_size,

        'current_page' => $page,

        'last_page' =>  ceil( $count /  $page_size),

        'data'      => $data_list

    ), $param);

}

/*
***请求接口，返回JSON数据
***@url:接口地址
***@params:传递的参数
***@ispost:是否以POST提交，默认GET
*/
function XiusoftCurl($url,$params=false,$ispost=0,$https=false){
    $httpInfo = array();
    $ch = curl_init();

    curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_0 );
    curl_setopt( $ch, CURLOPT_USERAGENT , 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22' );
    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 30 );
    curl_setopt( $ch, CURLOPT_TIMEOUT , 30);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER,true );
    if($https){
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER , false );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST , false );
    }
    if($ispost)
    {
        curl_setopt( $ch , CURLOPT_POST , true );
        curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
        curl_setopt( $ch , CURLOPT_URL , $url );
    }
    else
    {
        if($params){
            curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
        }else{
            curl_setopt( $ch , CURLOPT_URL , $url);
        }
    }
    $response = curl_exec( $ch );
    if ($response === FALSE) {
        echo "cURL Error: " . curl_error($ch);
        return false;
    }
    $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
    $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
    curl_close( $ch );
    return $response;
}

/**

 * curl函数

 * @param $url

 * @return mixed

 */

function http_curl($url){

    //用curl传参

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

    //关闭ssl验证

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

    curl_setopt($ch,CURLOPT_HEADER, 0);

    $output = curl_exec($ch);

    curl_close($ch);

    return json_decode($output, true);

}



/**

 * 获取用户id

 * @return int|mixed

 */

function cmf_get_account_id(){

    $account_id = session('user.account_id');

    return empty($account_id)?0:$account_id;

}

//验证身份证号码
function checkIdCard($idCard){
    // 只能是18位
    if(strlen($idCard)!=18){
        return false;
    }
    // 取出本体码
    $idCard_base = substr($idCard, 0, 17);
    // 取出校验码
    $verify_code = substr($idCard, 17, 1);
    // 加权因子
    $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
    // 校验码对应值
    $verify_code_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
    // 根据前17位计算校验码
    $total = 0;
    for($i=0; $i<17; $i++){
        $total += substr($idCard_base, $i, 1)*$factor[$i];
    }
    // 取模
    $mod = $total % 11;
    // 比较校验码
    if($verify_code == $verify_code_list[$mod]){
        return true;
    }else{
        return false;
    }
}