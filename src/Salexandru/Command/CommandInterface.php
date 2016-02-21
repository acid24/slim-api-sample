<?php

namespace Salexandru\Command;

interface CommandInterface
{

    const ATTRIBUTE_TRANSACTIONAL = 'transactional';
    const ATTRIBUTE_LOGGABLE = 'loggable';
    const ATTRIBUTE_TRIGGERS_EVENTS = 'triggersEvents';

    /**
     * @param array $context
     * @return void
     */
    public function setContext(array $context);

    /**
     * @return array
     */
    public function getContext();

    /**
     * @return array
     */
    public function getAttributes();

    /**
     * @param string $name
     * @return boolean
     */
    public function hasAttribute($name);
}
