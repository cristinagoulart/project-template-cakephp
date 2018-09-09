<?php
namespace App\Feature;

use InvalidArgumentException;

class Config
{
    /**
     * Feature name.
     *
     * @var string
     */
    protected $name;

    /**
     * Active flag.
     *
     * @var bool
     */
    protected $active = true;

    /**
     * Constructor method.
     *
     * @param array $config Feature config
     */
    public function __construct(array $config)
    {
        $required = [
            'name' => ['type' => 'string'],
            'active' => ['type' => 'boolean']
        ];

        foreach ($required as $field => $options) {
            if (!array_key_exists($field, $config)) {
                throw new InvalidArgumentException('Missing required parameter [' . $field . '].');
            }

            $givenType = gettype($config[$field]);
            if ($givenType !== $options['type']) {
                throw new InvalidArgumentException(
                    'Parameter [' . $field . '] must be of type [' . $options['type'] . '], [' . $givenType . '] given.'
                );
            }
        }

        foreach ($config as $field => $value) {
            $this->{$field} = $value;
        }
    }

    /**
     * Class property getter method.
     *
     * @param string $field Property name
     * @return mixed
     */
    public function get($field)
    {
        $result = null;
        if (property_exists($this, $field)) {
            $result = $this->{$field};
        }

        return $result;
    }
}
