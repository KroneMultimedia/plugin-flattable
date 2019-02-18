<?php
$config = new KRN\CS\Php73;
$finder = $config->getFinder();
$finder->in([
    'src',
    'tests'
]);
return $config;

