<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Search;

use Cake\I18n\Time;

/**
 * Class responsible for generating Magic values.
 */
final class MagicValue
{
    /**
     * Magic value wrapper identifier.
     */
    const WRAPPER = '%%';

    /**
     * User info.
     *
     * @var array
     */
    private $user = [];

    /**
     * Field value.
     *
     * @var string
     */
    private $value = '';

    /**
     * Constructor method.
     *
     * @param string $value Field value
     * @param mixed[] $user User info
     * @return void
     */
    public function __construct(string $value, array $user)
    {
        if (empty($user)) {
            throw new \InvalidArgumentException('User info are required.');
        }

        $this->user = $user;
        $this->value = $value;
    }

    /**
     * Magic value getter.
     *
     * @return mixed
     */
    public function get()
    {
        $value = str_replace(static::WRAPPER, '', $this->value);

        if (! method_exists($this, $value)) {
            return $this->value;
        }

        return $this->{$value}();
    }

    /**
     * Current user id magic value getter.
     *
     * @return string
     */
    private function me(): string
    {
        return $this->user['id'];
    }

    /**
     * Today's date magic value getter.
     *
     * @return string
     */
    private function today(): string
    {
        return (new Time('today'))->format('Y-m-d H:i:s');
    }

    /**
     * Yesterday's date magic value getter.
     *
     * @return string
     */
    private function yesterday(): string
    {
        return (new Time('yesterday'))->format('Y-m-d H:i:s');
    }
    /**
     * Tomorrow's date magic value getter.
     *
     * @return string
     */
    private function tomorrow(): string
    {
        return (new Time('tomorrow'))->format('Y-m-d H:i:s');
    }
}
