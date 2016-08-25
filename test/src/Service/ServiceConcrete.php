<?php
namespace RealejoZf1Test\Service;

use RealejoZf1\Service\ServiceAbstract;

class ServiceConcrete extends ServiceAbstract
{
    /**
     * @var string
     */
    protected $mapperClass = '\RealejoZf1Test\Mapper\MapperConcrete';
}
