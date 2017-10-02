<?php

namespace OxCom\MagentoCurrencyServices\Model\Currency\Import;

/**
 * Class AbstractDto
 *
 * @package OxCom\MagentoCurrencyServices\Model\Currency\Import
 */
abstract class AbstractDto
{
    const DATE_FORMAT = 'Y-m-d';

    /**
     * @var object|array
     */
    protected $object;

    /**
     * User constructor.
     *
     * @param array|object|null $object
     */
    public function __construct($object = null)
    {
        $this->object = $object;
    }

    /**
     * @return object|array
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Get property or array value
     *
     * @param object|array $object
     * @param string       $key
     * @param mixed|null   $default
     *
     * @return mixed|null
     */
    protected function val($object, $key, $default = null)
    {
        if (is_object($object) && property_exists($object, $key) && !empty($object->{$key})) {
            return $object->{$key};
        } elseif (is_array($object) && !empty($object[$key])) {
            return $object[$key];
        }

        return $default;
    }
}
