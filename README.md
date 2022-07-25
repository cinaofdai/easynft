# EasyNFT
📦 一个 PHP NFT上链开发 SDK

## 环境需求
- PHP >= 7.2.0
- Composer >= 2.0

## 安装
```shell
composer require dh2y/easynft
```

或者
```json
"require": {
    "dh2y/easynft":"*"
},
```

## 使用示例
### 文昌链使用案例
```php
    /**
     * 创建NFT链上账户
     * @return void
     */
    public function index()
    {
        $config = [
            'apiKey' => "apiKey",
            'apiSecret' => "apiSecret"
        ];
        $res =  Nft::wenchang($config)->account('某某人');
        dump($res);die;
    }

    /**
     * 创建NFT分类
     * @return void
     */
    public function classes(){
        $config = [
            'apiKey' => "apiKey",
            'apiSecret' => "apiSecret"
        ];
        $res = Nft::wenchang($config)->classes([
            'name' =>'测试分类',
            'class_id' => 1,
            'owner' => 'xxxxxxxxxxxxx1'
        ]);
        dump($res);die;
    }


    /**
     * 发布NFT
     * @return void
     */
    public function nft(){
        $config = [
            'apiKey' => "apiKey",
            'apiSecret' => "apiSecret"
        ];
        $res = Nft::wenchang($config)->BuildNFT(1,[
            'name' =>'创世藏品',
            'uri' => 'https://doman.com/165850042962dab54d1783e.jpg'
        ]);
        dump($res);die;
    }

    /**
     * 上链交易结果查询
     * @return void
     */
    public function search(){
        $config = [
            'apiKey' => "apiKey",
            'apiSecret' => "apiSecret"
        ];
        $res = Nft::wenchang($config)->SearchResult('operationid1658755299');
        dump($res);die;
    }

    /**
     * 转让NFT
     * @return void
     */
    public function transfers(){
        $config = [
            'apiKey' => "apiKey",
            'apiSecret' => "apiSecret"
        ];
        $res = Nft::wenchang($config)->TransfersNFT(1,[
            'owner' => 'xxxxxxxxxxxxx1',      //所有人
            'nft_id' => '234wsdasd112222',
            'recipient' => 'xxxxxxxxxxxxx2',   //转让接受人
        ]);
        dump($res);die;
    }

    /**
     * 销毁NFT
     * @return void
     */
    public function delete(){
        $config = [
            'apiKey' => "apiKey",
            'apiSecret' => "apiSecret"
        ];
        $res = Nft::wenchang($config)->DeleteNFT(1,[
            'owner' => 'xxxxxxxxxxxxx2',      //所有人
            'nft_id' => '234wsdasd112222',
        ]);
        dump($res);die;
    }
```