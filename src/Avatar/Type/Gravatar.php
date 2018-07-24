<?php
namespace App\Avatar\Type;

use App\Avatar\AvatarInterface;

final class Gravatar implements AvatarInterface
{
    /**
     * Gravatar default options.
     *
     * @var array
     */
    private $options = [
        'email' => '',
        'size' => 160,
        'default' => 'mm',
        'rating' => 'g'
    ];

    /**
     * {@inheritDoc}
     */
    public function __construct(array $options)
    {
        $this->options = array_merge($this->options, $options);

        $this->options['email'] = md5(strtolower(trim($this->options['email'])));
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        return sprintf(
            'https://www.gravatar.com/avatar/%s?size=%d&default=%s&rating=%s',
            $this->options['email'],
            $this->options['size'],
            $this->options['default'],
            $this->options['rating']
        );
    }
}
