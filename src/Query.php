<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 4.0.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url;

use ArrayIterator;
use InvalidArgumentException;
use League\Url\Interfaces\Component;
use League\Url\Interfaces\Query as QueryInterface;
use League\Url\Util;
use Traversable;

/**
 * An abstract class to ease component creation
 *
 * @package  League.url
 * @since  1.0.0
 */
class Query implements QueryInterface
{
    /**
     * The Component Data
     *
     * @var array
     */
    protected $data = [];

    use Util\StringValidator;

    /**
     * a new instance
     *
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        if (null !== $data) {
            $this->data = $this->validate($data);
        }
    }

    /**
     * sanitize the submitted data
     *
     * @param mixed $data
     *
     * @return array
     */
    protected function validate($data)
    {
        if (is_null($data)) {
            return [];
        }

        if (is_array($data)) {
            return $data;
        }

        if ($data instanceof Traversable) {
            return iterator_to_array($data, true);
        }

        return $this->validateStringQuery($data);
    }

    /**
     * sanitize the submitted data
     *
     * @param string $str
     *
     * @throws InvalidArgumentException If the submitted data is not stringable
     *
     * @return array
     */
    public function validateStringQuery($str)
    {
        $str = $this->validateString($str);
        $str = ltrim($str, '?');
        $str = preg_replace_callback('/(?:^|(?<=&))[^=|&[]+/', function ($match) {
            return bin2hex(urldecode($match[0]));
        }, $str);
        parse_str($str, $arr);

        $arr = array_combine(array_map('hex2bin', array_keys($arr)), $arr);

        return array_filter($arr, function ($value) {
            return ! is_null($value);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        if (empty($this->data)) {
            return null;
        }

        return http_build_query($this->data, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $res = $this->__toString();
        if (empty($res)) {
            return $res;
        }

        return '?'.$res;
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(Component $component)
    {
        return $component->__toString() == $this->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys($value = null)
    {
        if (is_null($value)) {
            return array_keys($this->data);
        }

        return array_keys($this->data, $value, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($key, $default = null)
    {
        if ($this->hasKey($key)) {
            return $this->data[$key];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function hasKey($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function mergeWith($data = null)
    {
        return new static(array_merge($this->data, $this->validate($data)));
    }

    /**
     * {@inheritdoc}
     */
    public function withValue($value = null)
    {
        return new static($value);
    }
}
