<?php

namespace dh2y\nft\Contracts;

interface GatewayApplicationInterface
{


    /**
     * 创建链用户
     * @param $name
     * @return mixed|array
     */
    public  function account($name);


    /**
     * 创建链分类
     * @param $params
     * @return mixed
     */
    public  function classes($params);


    /**
     * 发行NFT
     * @param $class_id
     * @param $params
     * @return mixed
     */
    public function BuildNFT($class_id,$params);

    /**
     * 转让NFT
     * @param $class_id
     * @param $params
     * @return mixed
     */
    public  function TransfersNFT($class_id,$params);

    /**
     * 销毁NFT
     * @param $class_id
     * @param $params
     * @return mixed
     */
    public function DeleteNFT($class_id,$params);
}