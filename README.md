# 基于PHP编写的一个文件存储包:支持LAMP\LNMP\MAMP\SWOOLE

#### 1.引入入口
```php
use Itxiao6\Upload\Upload;
```
#### 2.设置文件存储驱动(支持本地存储,七牛云oss,阿里云OSS)
```php
Upload::set_driver('Local');// Local\Qiniu\Alioss
```
#### 3.启动驱动
```php
# 本地文件存储器
    # 定义上传的文件夹
    $directory = __DIR__.'/';
    
    # 定义上传完的webUrl
    $webUrl = '/';
    
    # 启动上传组件
    Upload::start($directory,$webUrl);

# 七牛云存储器
    # 定义accessKey
    $accessKey = 'hmkss38pl8WJZjPpbbgY5Ldzj9Ma0_RsCUPezt';
    # 定义secretKey
    $secretKey = '0R2ossqsaaEqmaOZSkwHl5aSXYD4hDQxKAUQIpdvPSvt';
    # 定义桶的名字
    $Bucket_Name = 'upload';
    
    # 定义外网访问路径
    $host = 'http://ovy5w745h.bkt.clouddn.com/';
    
    # 启动上传组件
    Upload::start($accessKey,$secretKey,$Bucket_Name,$host);
```
#### 4.上传文件
```php
$data = Upload::upload('picname');
```
#### 5.处理结果
```php
# 判断是否上传成功
if($data!=false){
    # 输出图片
    echo "<img src='".$data."'>";
}else{
    # 输出错误信息
    echo Upload::get_error_message('picname');
}
```

*附录1.拓展*
1.存储器:必须使用\Itxiao6\Upload\Interfaces\Storage接口
a.设置驱动
```php
Session::set_interface('MyName',MyClass::class);    
```
b.使用驱动,只需在上文第二步替换参数即可
```php
Upload::set_driver('MyName');
```
c.设置参数,启动存储器
```php
# 启动上传组件
Upload::start('参数');// 这个参数会在存储器的create方法接收到，create指向的是 存储器的构造方法。
```
2.验证规则:必须使用Itxiao6\Upload\Interfaces\Validation接口
a.在上文第4步，上传文件时直接传入即可
```php
$data = Upload::upload('picname',[new MyValidation()]);
```
b.错误信息和上文的获取方法一样