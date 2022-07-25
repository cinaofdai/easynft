<?php

namespace dh2y\nft\Gateways\Wenchang;

class ApiClient
{
    private $apiKey = "apiKey";
    private $apiSecret = "apiSecret";
    private $domain = "https://stage.apis.avata.bianjie.ai";


    function request($path, $query = [], $body = [], $method = 'GET')
    {
        $method = strtoupper($method);
        $apiGateway = rtrim($this->domain, '/') . '/' . ltrim($path,
                '/') . ($query ? '?' . http_build_query($query) : '');
        $timestamp = $this->getMillisecond();
        $params = ["path_url" => $path];
        if ($query) {
            // 组装 query
            foreach ($query as $k => $v) {
                $params["query_{$k}"] = $v;
            }
        }
        if ($body) {
            // 组装 post body
            foreach ($body as $k => $v) {
                $params["body_{$k}"] = $v;
            }
        }
        // 数组递归排序
        $this->SortAll($params);
        $hexHash = hash("sha256", "{$timestamp}" . $this->apiSecret);
        if (count($params) > 0) {
            // 序列化且不编码
            $s = json_encode($params,JSON_UNESCAPED_UNICODE);
            $hexHash = hash("sha256", stripcslashes($s . "{$timestamp}" . $this->apiSecret));
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiGateway);
        $header = [
            "Content-Type:application/json",
            "X-Api-Key:{$this->apiKey}",
            "X-Signature:{$hexHash}",
            "X-Timestamp:{$timestamp}",
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $jsonStr = $body ? json_encode($body) : ''; //转换为json格式
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($jsonStr) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            }
        } elseif ($method == 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            if ($jsonStr) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            }
        } elseif ($method == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($jsonStr) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            }
        } elseif ($method == 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            if ($jsonStr) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            }
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response, true);

        /*
         * 部分PHP版本curl默认不验证https证书，返回NULL,可添加以下配置或更换版本尝试
         * curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
         * curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
         * //默认验证正规CA颁发的https证书
         *
         * */

        return $response;

    }


    function SortAll(&$params){
        if (is_array($params)) {
            ksort($params);
        }
        foreach ($params as &$v){
            if (is_array($v)) {
                $this->SortAll($v);
            }
        }
    }

    /** get timestamp
     *
     * @return float
     */
    private function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)));
    }
}