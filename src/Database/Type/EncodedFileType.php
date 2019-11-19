<?php

namespace App\Database\Type;

use Cake\Database\Driver;
use Cake\Database\Type;
use InvalidArgumentException;
use PDO;

/**
 * Base64 Encoded file type converter.
 *
 * Use to convert base64 eoncoded file data between PHP and the database types.
 */
class EncodedFileType extends Type
{
    /**
     * Casts given value from a PHP type to one acceptable by a database.
     *
     * The value must be an array with the same structure as the single
     * uploaded file in PHP.  Example:
     *
     * ```
     * [
     *     'name' => 'logo.png',
     *     'type' => 'image/png',
     *     'tmp_name' => '/tmp/xyz',
     *     'error' => 0,
     *     'size' => 1234
     * ]
     * ```
     *
     * @param mixed $value Value to be converted to a database equivalent.
     * @param \Cake\Database\Driver $driver Object from which database preferences and configuration will be extracted.
     * @return mixed Given PHP type casted to one acceptable by a database.
     */
    public function toDatabase($value, Driver $driver)
    {
        if (is_null($value)) {
            return null;
        }

        if (! is_array($value)) {
            throw new InvalidArgumentException('Encoded file value must be an array');
        }

        if (! isset($value['type'])) {
            throw new InvalidArgumentException('Encoded file "type" is not defined');
        }

        if (! isset($value['tmp_name'])) {
            throw new InvalidArgumentException('Encoded file "tmp_name" is not defined');
        }

        if (! is_file($value['tmp_name']) || ! is_readable($value['tmp_name'])) {
            throw new InvalidArgumentException("Temporary file [" . $value['tmp_name'] . "] does not exist or is not readable");
        }

        $content = file_get_contents($value['tmp_name']);
        if ($content === false) {
            throw new InvalidArgumentException("Failed to read temporary file [" . $value['tmp_name'] . "]");
        }

        return sprintf('data:%s;base64,%s', $value['type'], base64_encode($content));
    }

    /**
     * Casts given value from a database type to a PHP equivalent.
     *
     * Return the value as is, unless it is a resource.  If so, use
     * `stream_get_contents()` to read all content  and return.
     *
     * @param mixed $value Value to be converted to PHP equivalent
     * @param \Cake\Database\Driver $driver Object from which database preferences and configuration will be extracted
     * @return mixed Given value casted from a database to a PHP equivalent.
     */
    public function toPHP($value, Driver $driver)
    {
        if (is_resource($value)) {
            return stream_get_contents($value);
        }

        return $value;
    }

    /**
     * Casts given value to its Statement equivalent.
     *
     * @param mixed $value Value to be converted to PDO statement.
     * @param \Cake\Database\Driver $driver Object from which database preferences and configuration will be extracted.
     * @return mixed Given value casted to its Statement equivalent.
     */
    public function toStatement($value, Driver $driver)
    {
        return PDO::PARAM_STR;
    }

    /**
     * Marshalls flat data into PHP objects.
     *
     * Most useful for converting request data into PHP objects,
     * that make sense for the rest of the ORM/Database layers.
     *
     * @param mixed $value The value to convert.
     * @return mixed Converted value.
     */
    public function marshal($value)
    {
        return $value;
    }
}
