<?php
namespace RealejoZf1Test\Mapper;

use RealejoZf1\Mapper\MapperAbstract;

class MapperConcrete extends MapperAbstract
{
    protected $tableName = 'album';
    protected $tableKey  = 'id';
}
