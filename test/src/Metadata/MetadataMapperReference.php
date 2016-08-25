<?php
/**
 * Classe mapper para ser usada nos testes
 */
namespace RealejoZf1Test\Metadata;

use RealejoZf1\Mapper\MapperAbstract;

class MetadataMapperReference extends MapperAbstract
{
    protected $tableName = 'tblreference';
    protected $tableKey  = 'id_reference';
}
