<?php

class dBase implements ArrayAccess, Iterator, Countable
{
    const MODE_READ = 0;
    const MODE_WRITE = 1;
    const MODE_READ_WRITE = 2;

    public static function open($filename, $mode = self::MODE_READ)
    {
        if (!file_exists($filename)) {
            throw new RuntimeException(sprintf('Filename %s not found', $filename));
        }

        if (!function_exists('dbase_open')) {
            throw new RuntimeException(sprintf('Extension dBase not support with your PHP interpreter'));
        }

        $dbaseId = @dbase_open($filename, $mode);
        if (false === $dbaseId) {
            throw new RuntimeException(sprintf('Failed to open database file %s', $filename));
        }

        return new self($dbaseId);
    }

    public static function create($filename, array $fieldDefinitions)
    {
        if (!file_exists($filename)) {
            throw new RuntimeException(sprintf('Filename %s not found', $filename));
        }

        if (!function_exists('dbase_create')) {
            throw new RuntimeException(sprintf('Extension dBase not support with your PHP interpreter'));
        }

        $dbaseId = @dbase_create($filename, $fieldDefinitions);
        if (false === $dbaseId) {
            throw new RuntimeException(sprintf('Failed to create database file %s', $filename));
        }

        return new self($dbaseId);
    }

    private $_dbaseId;
    private $_recordNumber;

    private function __construct($dbaseId)
    {
        $this->_dbaseId = $dbaseId;
    }

    public function __destruct()
    {
        @dbase_close($this->_dbaseId);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return @dbase_numrecords($this->_dbaseId);
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return @dbase_get_record_with_names($this->_dbaseId, $this->_recordNumber);
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->_recordNumber++;
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->_recordNumber;
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return $this->_recordNumber <= count($this);
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->_recordNumber = 1;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        if (null !== $offset) {
            return (false !== @dbase_get_record_with_names($this->_dbaseId, $offset + 1));
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return @dbase_get_record_with_names($this->_dbaseId, $offset + 1);
        } else {
            throw new OutOfBoundsException(sprintf('Invalid index %s', $offset));
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if ($this->offsetExists($offset)) {
            dbase_replace_record($this->_dbaseId, $value, $offset + 1);
        } else {
            dbase_add_record($this->_dbaseId, $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        if (isset($this[$offset])) {
            dbase_delete_record($this->_dbaseId, $offset+1);
            dbase_pack($this->_dbaseId);
        } else {
            throw new OutOfRangeException(sprintf('Invalid index %s', $offset));
        }
    }
}