<?php

namespace Bolt\Extension\pygillier\IftttMaker;

if (isset($app)) {
    $app['extensions']->register(new Extension($app));
}

