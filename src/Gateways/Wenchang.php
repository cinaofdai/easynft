<?php
namespace dh2y\nft\Gateways;


use dh2y\nft\Contracts\GatewayApplicationInterface;
use dh2y\nft\Supports\Config;

class Wenchang implements  GatewayApplicationInterface
{


    private $domain = "";

    private $baseUrl = [
        'dev' => 'https://stage.apis.avata.bianjie.ai',
        'pro' => 'https://apis.avata.bianjie.ai'
    ];


    public $config;

    public function __construct(Config $config){
         $this->config = $config;
         $this->domain = $this->config->app_env=='pro'?$this->baseUrl['pro']:$this->baseUrl['dev'];
    }


    /**
     * 创建链账号
     * @param $name
     * @return mixed|void
     */
    public function account($name)
    {
        $body = [
            "name" => $name,
            "operation_id" => "operationid" .$this->getMillisecond(),
        ];

        $res = $this->request("/v1beta1/account", [], $body, "POST");
        return $res;
    }

    /**
     * 创建NFT分类
     * @param $params
     * @return mixed
     */
    public  function classes($params){
        $body = [
            "name" => $params['name'],
            "class_id" => 'nftclassid'.$params['class_id'],
            "owner" => $params['owner'],
            "operation_id" => "operationid".$this->getMillisecond(),
        ];

        $res = $this->request("/v1beta1/nft/classes", [], $body, "POST");
        return $res;
    }

    /**
     * 发布NFT
     * @param $class_id
     * @param $params
     * @return mixed
     */
    public function BuildNFT($class_id,$params){
        $body = [
            "name" => $params['name'],
            "uri" =>  $params['uri'],
            "uri_hash" => md5($params['uri']),
            "data" => $params['num'],
            "operation_id" => "operationid".$this->getMillisecond(),
        ];

        $res = $this->request("/v1beta1/nft/nfts/nftclassid{$class_id}", [], $body, "POST");
        return $res;
    }


    /**
     * 查询NFT
     * @param $operation_id
     * @return mixed|array
     */
    public function SearchResult($operation_id){
        $res = $this->request("/v1beta1/tx/{$operation_id}", [], [], "GET");
        return $res;
    }

    /**
     * NFT详情
     * @param $class_id
     * @param $nft_id
     * @return mixed
     */
    public  function  NftDetails($class_id,$nft_id){
        $res = $this->request("/v1beta1/nft/nfts/nftclassid{$class_id}/{$nft_id}", [], [], "GET");
        return $res;
    }

    /**
     * 转让NFT
     * @return mixed|array
     */
    public function TransfersNFT($class_id,$params){
        $body = [
            "recipient" => $params['recipient'],
            "operation_id" => "operationid".$this->getMillisecond(),
        ];

        $res = $this->request("/v1beta1/nft/nft-transfers/nftclassid{$class_id}/{$params['owner']}/{$params['nft_id']}", [], $body, "POST");
        return $res;
    }

    /**
     * 销毁NFT
     * @param $class_id
     * @param $params
     * @return mixed|array
     */
    public function DeleteNFT($class_id,$params){
        $body = [
            "operation_id" => "operationid".$this->getMillisecond(),
        ];
        $res = $this->request("/v1beta1/nft/nfts/nftclassid{$class_id}/{$params['owner']}/{$params['nft_id']}", [], $body, "DELETE");
        return $res;
    }

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
        $hexHash = hash("sha256", "{$timestamp}" . $this->config->apiSecret);
        if (count($params) > 0) {
            // 序列化且不编码
            $s = json_encode($params,JSON_UNESCAPED_UNICODE);
            $hexHash = hash("sha256", stripcslashes($s . "{$timestamp}" . $this->config->apiSecret));
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiGateway);
        $header = [
            "Content-Type:application/json",
            "X-Api-Key:{$this->config->apiKey}",
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

        // 部分PHP版本curl默认不验证https证书，返回NULL,可添加以下配置或更换版本尝试
        if(substr($this->domain, 0, 5) == 'https'){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response, true);

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