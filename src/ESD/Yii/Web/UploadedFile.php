<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Web;

use ESD\Core\Server\Beans\Request;
use ESD\Yii\Base\BaseObject;
use ESD\Yii\Helpers\FileHelper;
use ESD\Yii\Helpers\Html;
use ESD\Yii\Yii;

/**
 * UploadedFile represents the information for an uploaded file.
 *
 * You can call [[getInstance()]] to retrieve the instance of an uploaded file,
 * and then use [[saveAs()]] to save it on the server.
 * You may also query other information about the file, including [[name]],
 * [[tempName]], [[type]], [[size]] and [[error]].
 *
 * For more details and usage information on UploadedFile, see the [guide article on handling uploads](guide:input-file-upload).
 *
 * @property string $baseName Original file base name. This property is read-only.
 * @property string $extension File extension. This property is read-only.
 * @property bool $hasError Whether there is an error with the uploaded file. Check [[error]] for detailed
 * error code information. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author bearlord <565364226@qq.com> modified for esd framework
 * @since 2.0
 */
class UploadedFile extends BaseObject
{
    /**
     * @var string the original name of the file being uploaded
     */
    public $name;
    /**
     * @var string the path of the uploaded file on the server.
     * Note, this is a temporary file which will be automatically deleted by PHP
     * after the current request is processed.
     */
    public $tempName;
    /**
     * @var string the MIME-type of the uploaded file (such as "image/gif").
     * Since this MIME type is not checked on the server-side, do not take this value for granted.
     * Instead, use [[\ESD\Yii\Helpers\FileHelper::getMimeType()]] to determine the exact MIME type.
     */
    public $type;
    /**
     * @var int the actual size of the uploaded file in bytes
     */
    public $size;
    /**
     * @var int an error code describing the status of this file uploading.
     * @see https://secure.php.net/manual/en/features.file-upload.errors.php
     */
    public $error;

    /**
     * @var array Files
     */
    private $_files;

    /**
     * @var string Root Path
     */
    private $rootPath;

    /**
     * @var string Upload Base Path
     */
    private $uploadBasePath;

    /**
     * @var string Upload save path
     */
    private $uploadSavePath;

    /**
     * @return string
     */
    public function getRootPath(): string
    {
        if (empty($this->rootPath)) {
            return rtrim(ROOT_DIR, '/') . '/web/';
        }
        return $this->rootPath;
    }

    /**
     * @param string $rootPath
     */
    public function setRootPath(string $rootPath): void
    {
        $this->rootPath = $rootPath;
    }

    /**
     * @return string
     */
    public function getUploadBasePath(): string
    {
        return $this->uploadBasePath;
    }

    /**
     * @param string $uploadBasePath
     */
    public function setUploadBasePath(string $uploadBasePath): void
    {
        $this->uploadBasePath = $uploadBasePath;
    }

    /**
     * @return string
     */
    public function getUploadSavePath(): string
    {
        return $this->getRootPath() . $this->getUploadBasePath();
    }

    /**
     * String output.
     * This is PHP magic method that returns string representation of an object.
     * The implementation here returns the uploaded file's name.
     * @return string the string representation of the object
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Returns an uploaded file for the given model attribute.
     * The file should be uploaded using [[\ESD\Yii\Widgets\ActiveField::fileInput()]].
     * @param \ESD\Yii\Base\Model $model the data model
     * @param string $attribute the attribute name. The attribute name may contain array indexes.
     * For example, '[1]file' for tabular file uploading; and 'file[1]' for an element in a file array.
     * @return null|UploadedFile the instance of the uploaded file.
     * Null is returned if no file is uploaded for the specified model attribute.
     * @see getInstanceByName()
     */
    public function instance($model, $attribute)
    {
        $name = Html::getInputName($model, $attribute);
        return $this->getInstanceByName($name);
    }

    /**
     * Returns all uploaded files for the given model attribute.
     * @param \ESD\Yii\Base\Model $model the data model
     * @param string $attribute the attribute name. The attribute name may contain array indexes
     * for tabular file uploading, e.g. '[1]file'.
     * @return UploadedFile[] array of UploadedFile objects.
     * Empty array is returned if no available file was found for the given attribute.
     */
    public function instances($model, $attribute)
    {
        $name = Html::getInputName($model, $attribute);
        return $this->getInstancesByName($name);
    }

    /**
     * Returns an uploaded file according to the given file input name.
     * The name can be a plain string or a string like an array element (e.g. 'Post[imageFile]', or 'Post[0][imageFile]').
     * @param string $name the name of the file input field.
     * @return null|UploadedFile the instance of the uploaded file.
     * Null is returned if no file is uploaded for the specified name.
     */
    public function instanceByName($name)
    {
        $files = self::loadFiles();
        return isset($files[$name]) ? new static($files[$name]) : null;
    }

    /**
     * Returns an array of uploaded files corresponding to the specified file input name.
     * This is mainly used when multiple files were uploaded and saved as 'files[0]', 'files[1]',
     * 'files[n]'..., and you can retrieve them all by passing 'files' as the name.
     * @param string $name the name of the array of files
     * @return UploadedFile[] the array of UploadedFile objects. Empty array is returned
     * if no adequate upload was found. Please note that this array will contain
     * all files from all sub-arrays regardless how deeply nested they are.
     */
    public function instancesByName($name)
    {
        $files = self::loadFiles();
        if (isset($files[$name])) {
            return [new static($files[$name])];
        }
        $results = [];
        foreach ($files as $key => $file) {
            if (strpos($key, "{$name}[") === 0) {
                $results[] = new static($file);
            }
        }
        return $results;
    }

    /**
     * Cleans up the loaded UploadedFile instances.
     * This method is mainly used by test scripts to set up a fixture.
     */
    public function reset()
    {
        $this->_files = null;
    }

    /**
     * Generate filename
     *
     * @return string
     */
    public function generateFilename()
    {
        $name = sha1(microtime(true) . mt_rand(1000, 9999));
        return $name;
    }


    /**
     * Saves the uploaded file.
     * Note that this method uses php's move_uploaded_file() method. If the target file `$file`
     * already exists, it will be overwritten.
     * @param string $file the file path used to save the uploaded file
     * @param bool $deleteTempFile whether to delete the temporary file after saving.
     * If true, you will not be able to save the uploaded file again in the current request.
     * @return bool true whether the file is saved successfully
     * @see error
     */
    public function saveAs($file, $deleteTempFile = true)
    {
        $fileDirectory = dirname($file);
        if (!file_exists($fileDirectory)) {
            FileHelper::createDirectory($fileDirectory);
        }

        if ($this->error == UPLOAD_ERR_OK) {
            if ($deleteTempFile) {
                return move_uploaded_file($this->tempName, $file);
            } elseif (is_uploaded_file($this->tempName)) {
                return copy($this->tempName, $file);
            }
        }

        return false;
    }

    /**
     * Get file url
     * 
     * @param $path
     * @return string
     */
    public function getFileUrl($path)
    {
        if (empty($path)) {
            return '';
        }
        if (strpos($path, 'http') === 0) {
            return $path;
        }

        /** @var Request $request */
        $request = getDeepContextValueByClassName(Request::class);
        $_host = $request->getHeader('host');
        //Host
        $host = $_host[0];
        //Scheme
        $scheme = $request->getUri()->getScheme();
        $realpath = sprintf("%s://%s/%s", $scheme, $host, ltrim(trim($path), '/'));
        return $realpath;
    }

    /**
     * @return string original file base name
     */
    public function getBaseName()
    {
        // https://github.com/yiisoft/yii2/issues/11012
        $pathInfo = pathinfo('_' . $this->name, PATHINFO_FILENAME);
        return mb_substr($pathInfo, 1, mb_strlen($pathInfo, '8bit'), '8bit');
    }

    /**
     * @return string file extension
     */
    public function getExtension()
    {
        return strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
    }

    /**
     * @return bool whether there is an error with the uploaded file.
     * Check [[error]] for detailed error code information.
     */
    public function getHasError()
    {
        return $this->error != UPLOAD_ERR_OK;
    }

    /**
     * Creates UploadedFile instances from $_FILE.
     * @return array the UploadedFile instances
     */
    private function loadFiles()
    {
        if ($this->_files === null) {
            $this->_files = [];

            $request = getDeepContextValueByClassName(Request::class);
            $files = $request->getFiles();
            if (isset($files) && is_array($files)) {
                foreach ($files as $key => $info) {
                    $this->loadFilesRecursive($key, $info);
                }
            }
        }

        return $this->_files;
    }

    /**
     * Creates UploadedFile instances from $_FILE recursively.
     * @param string $key key for identifying uploaded file: class name and sub-array indexes
     * @param mixed $names file names provided by PHP
     * @param mixed $tempNames temporary file names provided by PHP
     * @param mixed $types file types provided by PHP
     * @param mixed $sizes file sizes provided by PHP
     * @param mixed $errors uploading issues provided by PHP
     */
    private function loadFilesRecursive($key, $info)
    {
        if (is_array($info) && empty($info['name'])) {
            foreach ($info as $i => $item) {
                $this->loadFilesRecursive($key . '[' . $i . ']', $item);
            }
        } else {
            $this->_files[$key] = [
                'name' => $info['name'],
                'tempName' => $info['tmp_name'],
                'type' => $info['type'],
                'size' => $info['size'],
                'error' => $info['error'],
            ];
        }
    }
}
