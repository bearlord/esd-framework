<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Mongodb\File;

use ESD\Yii\Yii;
use ESD\Yii\Base\InvalidParamException;
use ESD\Yii\Db\StaleObjectException;
use ESD\Yii\Web\UploadedFile;

/**
 * ActiveRecord is the base class for classes representing Mongo GridFS files in terms of objects.
 *
 * To specify source file use the [[file]] attribute. It can be specified in one of the following ways:
 *  - string - full name of the file, which content should be stored in GridFS
 *  - \ESD\Yii\Web\UploadedFile - uploaded file instance, which content should be stored in GridFS
 *
 * For example:
 *
 * ```php
 * $record = new ImageFile();
 * $record->file = '/path/to/some/file.jpg';
 * $record->save();
 * ```
 *
 * You can also specify file content via [[newFileContent]] attribute:
 *
 * ```php
 * $record = new ImageFile();
 * $record->newFileContent = 'New file content';
 * $record->save();
 * ```
 *
 * Note: [[newFileContent]] always takes precedence over [[file]].
 *
 * @property null|string $fileContent File content. This property is read-only.
 * @property resource $fileResource File stream resource. This property is read-only.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
abstract class ActiveRecord extends \ESD\Yii\Mongodb\ActiveRecord
{
    /**
     * {@inheritdoc}
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function find()
    {
        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
    }

    /**
     * Return the Mongo GridFS collection instance for this AR class.
     * @return Collection collection instance.
     */
    public static function getCollection()
    {
        return static::getDb()->getFileCollection(static::collectionName());
    }

    /**
     * Returns the list of all attribute names of the model.
     * This method could be overridden by child classes to define available attributes.
     * Note: all attributes defined in base Active Record class should be always present
     * in returned array.
     * For example:
     *
     * ```php
     * public function attributes()
     * {
     *     return array_merge(
     *         parent::attributes(),
     *         ['tags', 'status']
     *     );
     * }
     * ```
     *
     * @return array list of attribute names.
     */
    public function attributes(): array
    {
        return [
            '_id',
            'filename',
            'uploadDate',
            'length',
            'chunkSize',
            'md5',
            'file',
            'newFileContent'
        ];
    }

    /**
     * @see ActiveRecord::insert()
     */
    protected function insertInternal($attributes = null)
    {
        if (!$this->beforeSave(true)) {
            return false;
        }
        $values = $this->getDirtyAttributes($attributes);
        if (empty($values)) {
            $currentAttributes = $this->getAttributes();
            foreach ($this->primaryKey() as $key) {
                $values[$key] = isset($currentAttributes[$key]) ? $currentAttributes[$key] : null;
            }
        }
        $collection = static::getCollection();
        if (isset($values['newFileContent'])) {
            $newFileContent = $values['newFileContent'];
            unset($values['newFileContent']);
        }
        if (isset($values['file'])) {
            $newFile = $values['file'];
            unset($values['file']);
        }
        if (isset($newFileContent)) {
            $newId = $collection->insertFileContent($newFileContent, $values);
        } elseif (isset($newFile)) {
            $fileName = $this->extractFileName($newFile);
            $newId = $collection->insertFile($fileName, $values);
        } else {
            $newId = $collection->insert($values);
        }
        if ($newId !== null) {
            $this->setAttribute('_id', $newId);
            $values['_id'] = $newId;
        }

        $changedAttributes = array_fill_keys(array_keys($values), null);
        $this->setOldAttributes($values);
        $this->afterSave(true, $changedAttributes);

        return true;
    }

    /**
     * @see ActiveRecord::update()
     * @throws StaleObjectException
     */
    protected function updateInternal($attributes = null)
    {
        if (!$this->beforeSave(false)) {
            return false;
        }
        $values = $this->getDirtyAttributes($attributes);
        if (empty($values)) {
            $this->afterSave(false, $values);
            return 0;
        }

        $collection = static::getCollection();
        if (isset($values['newFileContent'])) {
            $newFileContent = $values['newFileContent'];
            unset($values['newFileContent']);
        }
        if (isset($values['file'])) {
            $newFile = $values['file'];
            unset($values['file']);
        }
        if (isset($newFileContent) || isset($newFile)) {
            $fileAssociatedAttributeNames = [
                'filename',
                'uploadDate',
                'length',
                'chunkSize',
                'md5',
                'file',
                'newFileContent'
            ];
            $values = array_merge($this->getAttributes(null, $fileAssociatedAttributeNames), $values);
            $rows = $this->deleteInternal();
            $insertValues = $values;
            $insertValues['_id'] = $this->getAttribute('_id');
            if (isset($newFileContent)) {
                $collection->insertFileContent($newFileContent, $insertValues);
            } else {
                $fileName = $this->extractFileName($newFile);
                $collection->insertFile($fileName, $insertValues);
            }
            $this->setAttribute('newFileContent', null);
            $this->setAttribute('file', null);
        } else {
            $condition = $this->getOldPrimaryKey(true);
            $lock = $this->optimisticLock();
            if ($lock !== null) {
                if (!isset($values[$lock])) {
                    $values[$lock] = $this->$lock + 1;
                }
                $condition[$lock] = $this->$lock;
            }
            // We do not check the return value of update() because it's possible
            // that it doesn't change anything and thus returns 0.
            $rows = $collection->update($condition, $values);
            if ($lock !== null && !$rows) {
                throw new StaleObjectException('The object being updated is outdated.');
            }
        }

        $changedAttributes = [];
        foreach ($values as $name => $value) {
            $changedAttributes[$name] = $this->getOldAttribute($name);
            $this->setOldAttribute($name, $value);
        }
        $this->afterSave(false, $changedAttributes);

        return $rows;
    }

    /**
     * Extracts filename from given raw file value.
     * @param mixed $file raw file value.
     * @return string file name.
     * @throws \ESD\Yii\Base\InvalidParamException on invalid file value.
     */
    protected function extractFileName($file)
    {
        if ($file instanceof UploadedFile) {
            return $file->tempName;
        } elseif (is_string($file)) {
            if (file_exists($file)) {
                return $file;
            }
            throw new InvalidParamException("File '{$file}' does not exist.");
        }

        throw new InvalidParamException('Unsupported type of "file" attribute.');
    }

    /**
     * Refreshes the [[file]] attribute from file collection, using current primary key.
     * @return \MongoGridFSFile|null refreshed file value.
     */
    public function refreshFile()
    {
        $mongoFile = $this->getCollection()->get($this->getPrimaryKey());
        $this->setAttribute('file', $mongoFile);

        return $mongoFile;
    }

    /**
     * Returns the associated file content.
     * @return null|string file content.
     * @throws \ESD\Yii\Base\InvalidParamException on invalid file attribute value.
     */
    public function getFileContent()
    {
        $file = $this->getAttribute('file');
        if (empty($file) && !$this->getIsNewRecord()) {
            $file = $this->refreshFile();
        }

        if (empty($file)) {
            return null;
        } elseif ($file instanceof Download) {
            $fileSize = $file->getSize();
            return empty($fileSize) ? null : $file->toString();
        } elseif ($file instanceof UploadedFile) {
            return file_get_contents($file->tempName);
        } elseif (is_string($file)) {
            if (file_exists($file)) {
                return file_get_contents($file);
            }
            throw new InvalidParamException("File '{$file}' does not exist.");
        }

        throw new InvalidParamException('Unsupported type of "file" attribute.');
    }

    /**
     * Writes the the internal file content into the given filename.
     * @param string $filename full filename to be written.
     * @return bool whether the operation was successful.
     * @throws \ESD\Yii\Base\InvalidParamException on invalid file attribute value.
     */
    public function writeFile($filename)
    {
        $file = $this->getAttribute('file');
        if (empty($file) && !$this->getIsNewRecord()) {
            $file = $this->refreshFile();
        }

        if (empty($file)) {
            throw new InvalidParamException('There is no file associated with this object.');
        } elseif ($file instanceof Download) {
            return ($file->toFile($filename) == $file->getSize());
        } elseif ($file instanceof UploadedFile) {
            return copy($file->tempName, $filename);
        } elseif (is_string($file)) {
            if (file_exists($file)) {
                return copy($file, $filename);
            }
            throw new InvalidParamException("File '{$file}' does not exist.");
        }

        throw new InvalidParamException('Unsupported type of "file" attribute.');
    }

    /**
     * This method returns a stream resource that can be used with all file functions in PHP,
     * which deal with reading files. The contents of the file are pulled out of MongoDB on the fly,
     * so that the whole file does not have to be loaded into memory first.
     * @return resource file stream resource.
     * @throws \ESD\Yii\Base\InvalidParamException on invalid file attribute value.
     */
    public function getFileResource()
    {
        $file = $this->getAttribute('file');
        if (empty($file) && !$this->getIsNewRecord()) {
            $file = $this->refreshFile();
        }

        if (empty($file)) {
            throw new InvalidParamException('There is no file associated with this object.');
        } elseif ($file instanceof Download) {
            return $file->getResource();
        } elseif ($file instanceof UploadedFile) {
            return fopen($file->tempName, 'r');
        } elseif (is_string($file)) {
            if (file_exists($file)) {
                return fopen($file, 'r');
            }
            throw new InvalidParamException("File '{$file}' does not exist.");
        }

        throw new InvalidParamException('Unsupported type of "file" attribute.');
    }
}
