<?php

namespace Salexandru\Command;

interface CommandInterface
{

    /**
     * @param Context $context
     * @return void
     */
    public function setContext(Context $context);

    /**
     * @return Context|null
     */
    public function getContext();

    /**
     * @return boolean
     */
    public function hasContext();

    /**
     * @return string
     */
    public function getName();
}
