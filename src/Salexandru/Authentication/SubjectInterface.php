<?php

namespace Salexandru\Authentication;

interface SubjectInterface
{

    public function getCredentials();
    public function getAttributes();
    public function setAttributes(array $attributes);
}
