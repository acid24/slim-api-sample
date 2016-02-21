<?php

namespace Salexandru\Command;

use Psr\Http\Message\ServerRequestInterface as HttpRequest;
use Salexandru\Command\Exception\RuntimeException;

abstract class AbstractCommand implements CommandInterface
{

    /**
     * Holds information that is available at the time the command is created
     * and may be of use when the command is handled
     *
     * @var array
     */
    protected $context = [];

    /**
     * @param HttpRequest $httpRequest
     * @return CommandInterface
     */
    public static function loadFromHttpRequest(HttpRequest $httpRequest)
    {
        return static::loadFromArray((array)$httpRequest->getParsedBody());
    }

    /**
     * @param array $data
     * @return CommandInterface
     */
    public static function loadFromArray(array $data)
    {
        $self = new static();

        foreach ($data as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($self, $setter)) {
                $self->$setter($value);
            }
        }

        $self->ensureRequiredFieldsHaveBeenProvided();

        return $self;
    }

    public function setContext(array $context)
    {
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function hasAttribute($name)
    {
        $attributes = $this->getAttributes();

        if (!is_array($attributes)) {
            return false;
        }

        return isset($attributes[$name]);
    }

    public function getAttributes()
    {
        return [self::ATTRIBUTE_LOGGABLE => true];
    }

    protected function ensureRequiredFieldsHaveBeenProvided()
    {
        foreach ($this->getRequiredFields() as $field) {
            if (!isset($this->$field)) {
                throw new RuntimeException(sprintf('"%s" property is required', $field));
            }
        }
    }

    protected function getRequiredFields()
    {
        return [];
    }
}
