<?php

namespace Salexandru\Bootstrap;

interface ResourceInitializerInterface
{

    /**
     * @return array
     */
    public function getOptions();

    /**
     * @return void
     */
    public function run();
}
