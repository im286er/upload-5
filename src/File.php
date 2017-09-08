<?php
namespace Itxiao6\Upload;
use SplFileInfo;
use InvalidArgumentException;
# 存储接口
use Itxiao6\Upload\Storage\Base as Storage_Base;
# 验证接口
use Itxiao6\Upload\Validation\Base as Validation_Base;

/**
 * 文件处理
 * Class File
 * @package Itxiao6\Upload
 */
class File extends SplFileInfo
{
    /********************************************************************************
    * 静态属性
    *******************************************************************************/

    /**
     * 上传错误消息
     * @var array
     */
    protected static $errorCodeMessages = array(
        1 => '上传的文件在php.ini中超过upload_max_filesize设定',
        2 => '上传的文件超过 max_file_siz e指令是在HTML表单中指定的大小',
        3 => '上传的文件只是部分上传。',
        4 => '没有上传文件',
        6 => '缺少临时文件夹',
        7 => '无法将文件写入磁盘',
        8 => 'PHP扩展停止了文件上传。'
    );

    /**
     * 文件大小转换单位
     * @var array
     */
    protected static $units = array(
        'b' => 1,
        'k' => 1024,
        'm' => 1048576,
        'g' => 1073741824
    );

    /********************************************************************************
    * 接口属性
    *******************************************************************************/

    /**
     * 存储
     * @var array
     */
    protected $storage;

    /**
     * 验证
     * @var array
     */
    protected $validations;

    /**
     * 验证错误信息
     * @var array
     */
    protected $errors;

    /**
     * 上传文件的原始文件名（仅限于内部使用）
     * @var string
     */
    protected $originalName;

    /**
     * 文件名(无拓展名)
     * @var string
     */
    protected $name;

    /**
     * 文件拓展名 (不带点)
     * @var string
     */
    protected $extension;

    /**
     * 文件类型 (e.g. "image/png")
     * @var string
     */
    protected $mimetype;

    /**
     * 文件上传错误代码 (内部使用)
     * @var  int
     * @link http://www.php.net/manual/en/features.file-upload.errors.php
     */
    protected $errorCode;

    /**
     *
     * 文件上传处理类 .构造方法
     * @param $key
     * @param Storage_Base $storage
     * @throws \Itxiao6\Upload\Exception\UploadException If file uploads are disabled in the php.ini file
     * @throws \InvalidArgumentException         If $_FILES key does not exist
     */
    public function __construct($key, Storage_Base $storage)
    {
        if (!isset($_FILES[$key])) {
            throw new InvalidArgumentException("Cannot find uploaded file identified by key: $key");
        }
        $this->storage = $storage;
        $this->validations = array();
        $this->errors = array();
        $this->originalName = $_FILES[$key]['name'];
        $this->errorCode = $_FILES[$key]['error'];
        parent::__construct($_FILES[$key]['tmp_name']);
    }

    /**
     * 获取文件名
     * @return string
     */
    public function getName()
    {
        if (!isset($this->name)) {
            $this->name = pathinfo($this->originalName, PATHINFO_FILENAME);
        }

        return $this->name;
    }

    /**
     * 设置文件名 (没有拓展名)
     * @param  string  $name
     * @return \Itxiao6\Upload\File     Self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * 获取文件名带后缀名
     * @return string
     */
    public function getNameWithExtension()
    {
        return sprintf('%s.%s', $this->getName(), $this->getExtension());
    }

    /**
     * 获取拓展名 (没有点)
     * @return string
     */
    public function getExtension()
    {
        if (!isset($this->extension)) {
            $this->extension = strtolower(pathinfo($this->originalName, PATHINFO_EXTENSION));
        }

        return $this->extension;
    }

    /**
     * 获取文件类型
     * @return string
     */
    public function getMimetype()
    {
        if (!isset($this->mimeType)) {
            $finfo = new \finfo(FILEINFO_MIME);
            $mimetype = $finfo->file($this->getPathname());
            $mimetypeParts = preg_split('/\s*[;,]\s*/', $mimetype);
            $this->mimetype = strtolower($mimetypeParts[0]);
            unset($finfo);
        }

        return $this->mimetype;
    }

    /**
     * 获取MD5值
     * @return string
     */
    public function getMd5()
    {
        return md5_file($this->getPathname());
    }

    /**
     * 获取图像尺寸
     * @return array formatted array of dimensions
     */
    public function getDimensions()
    {
        list($width, $height) = getimagesize($this->getPathname());
        return array(
            'width' => $width,
            'height' => $height
        );
    }

    /********************************************************************************
    * 验证
    *******************************************************************************/

    /**
     * 添加文件验证规则
     * @param \Upload\Validation\Base|array[\Upload\Validation\Base] $validations
     */
    public function addValidations($validations)
    {
        if (!is_array($validations)) {
            $validations = [$validations];
        }
        foreach ($validations as $validation) {
            if ($validation instanceof Validation_Base) {
                $this->validations[] = $validation;
            }
        }
    }

    /**
     * 获取文件验证规则
     * @return array[\Itxiao6\Upload\Validation\Base]
     */
    public function getValidations()
    {
        return $this->validations;
    }

    /**
     * 验证要上传的文件
     * @return bool True if valid, false if invalid
     */
    public function validate()
    {
        // Validate is uploaded OK
        if ($this->isOk() === false) {
            $this->errors[] = self::$errorCodeMessages[$this->errorCode];
        }

        // Validate is uploaded file
        if ($this->isUploadedFile() === false) {
            $this->errors[] = 'The uploaded file was not sent with a POST request';
        }

        // User validations
        foreach ($this->validations as $validation) {
            if ($validation->validate($this) === false) {
                $this->errors[] = $validation->getMessage();
            }
        }

        return empty($this->errors);
    }

    /**
     * Get file validation errors
     * @return array[String]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Add file validation error
     * @param  string
     * @return \Itxiao6\Upload\File Self
     */
    public function addError($error)
    {
        $this->errors[] = $error;

        return $this;
    }

    /********************************************************************************
    * Upload
    *******************************************************************************/

    /**
     * Upload file (delegated to storage object)
     * @param  string $newName Give the file it a new name
     * @return bool
     * @throws \Itxiao6\Upload\Exception\UploadException If file does not validate
     */
    public function upload($newName = null)
    {
        if ($this->validate() === false) {
            throw new \Itxiao6\Upload\Exception\UploadException('File validation failed');
        }

        // Update the name, leaving out the extension
        if (is_string($newName)) {
            $this->name = pathinfo($newName, PATHINFO_FILENAME);
        }

        return $this->storage->upload($this, $newName);
    }

    /********************************************************************************
    * Helpers
    *******************************************************************************/

    /**
     * Is this file uploaded with a POST request?
     *
     * This is a separate method so that it can be stubbed in unit tests to avoid
     * the hard dependency on the `is_uploaded_file` function.
     *
     * @return  bool
     */
    public function isUploadedFile()
    {
        return is_uploaded_file($this->getPathname());
    }

    /**
     * Is this file OK?
     *
     * This method inspects the upload error code to see if the upload was
     * successful or if it failed for a variety of reasons.
     *
     * @link    http://www.php.net/manual/en/features.file-upload.errors.php
     * @return  bool
     */
    public function isOk()
    {
        return ($this->errorCode === UPLOAD_ERR_OK);
    }

    /**
     * Convert human readable file size (e.g. "10K" or "3M") into bytes
     * @param  string $input
     * @return int
     */
    public static function humanReadableToBytes($input)
    {
        $number = (int)$input;
        $unit = strtolower(substr($input, -1));
        if (isset(self::$units[$unit])) {
            $number = $number * self::$units[$unit];
        }

        return $number;
    }
}
