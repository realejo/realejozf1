<?php
namespace RealejoZf1\Service;

use Zend\Hydrator\ArraySerializable;
use RealejoZf1\Stdlib\ArrayObject;

class HydratorPagination extends \Zend_Paginator_Adapter_DbTableSelect
{
    /**
     * @var \RealejoZf1\Stdlib\ArrayObject
     */
    protected $hydratorEntity = null;

    /**
     * @var \Zend\Hydrator\ArraySerializable
     */
    protected $hydrator = null;

    /**
     *
     * @return \Zend\Hydrator\ArraySerializable
     */
    public function getHydrator()
    {
        if (!isset($this->hydrator)) {
            $this->hydrator = new ArraySerializable();
        }

        return $this->hydrator;
    }

    /**
     * @param \Zend\Hydrator\ArraySerializable $hydrator
     * @return \RealejoZF1\Mapper\MapperPagination
     */
    public function setHydrator(\Zend\Hydrator\ArraySerializable $hydrator = null)
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    /**
     * @return \RealejoZf1\Stdlib\ArrayObject
     */
    public function getHydratorEntity()
    {
        if (isset($this->hydratorEntity)) {
            $hydrator = $this->hydratorEntity;
            return new $hydrator();
        }

        return new ArrayObject();
    }

    /**
     * @param \RealejoZf1\Stdlib\ArrayObject $hydrator
     * @return \RealejoZF1\Mapper\MapperPagination
     */
    public function setHydratorEntity(\RealejoZf1\Stdlib\ArrayObject $hydratorEntity = null)
    {
        $this->hydratorEntity = $hydratorEntity;
        return $this;
    }

    public function getItems($offset, $itemCountPerPage)
    {
        $fetchAll = parent::getItems($offset, $itemCountPerPage);

        $hydrator = $this->getHydrator();
        if (empty($hydrator)) {
            return $fetchAll;
        }
        $hydratorEntity = $this->getHydratorEntity();

        foreach ($fetchAll as $id=>$row) {
            $fetchAll[$id] = $hydrator->hydrate($row->toArray(), new $hydratorEntity);
        }

        return $fetchAll;
    }
}
