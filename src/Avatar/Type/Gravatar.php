<?php
namespace App\Avatar\Type;

use App\Avatar\AbstractAvatar;
use Cake\Core\Configure;

final class Gravatar extends AbstractAvatar
{
    /**
     * Gravatar default options.
     *
     * @var array
     */
    private $options = [
        'email' => '',
        'size' => 160,
        'default' => '404',
        'rating' => 'g',
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
        $result = false;

        $filename = $this->getAvatarUrl($this->options);
        $file = WWW_ROOT . $filename;

        if (file_exists($file)) {
            return $filename;
        }

        $imageUrl = sprintf(
            'https://www.gravatar.com/avatar/%s?size=%d&default=%s&rating=%s',
            $this->options['email'],
            $this->options['size'],
            $this->options['default'],
            $this->options['rating']
        );

        $curl = curl_init($imageUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (404 == $httpCode) {
            return $result;
        }

        $resource = imagecreatefromstring(file_get_contents($imageUrl));
        $saved = $this->processAvatarResource($file, $resource);

        if ($saved) {
            $result = $filename;
        }

        return $result;
    }
}
