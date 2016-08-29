<?php
namespace RealejoZf1Test\Service;

use RealejoZf1\Test\BaseTestCase;

class ServiceTest extends BaseTestCase
{
    /**
     * @var string
     */
    protected $tableName = 'album';

    /**
     * @var string
     */
    protected $tableKeyName = 'id';

    protected $tables = array('album');

    /**
     * @var \RealejoZf1Test\Service\ServiceConcrete
     */
    private $Service;

    protected $defaultValues = array(
        array(
            'id' => 1,
            'artist' => 'Rush',
            'title' => 'Rush',
            'deleted' => 0
        ),
        array(
            'id' => 2,
            'artist' => 'Rush',
            'title' => 'Moving Pictures',
            'deleted' => 0
        ),
        array(
            'id' => 3,
            'artist' => 'Dream Theater',
            'title' => 'Images And Words',
            'deleted' => 0
        ),
        array(
            'id' => 4,
            'artist' => 'Claudia Leitte',
            'title' => 'Exttravasa',
            'deleted' => 1
        )
    );

    /**
     * @return self
     */
    public function insertDefaultRows()
    {
        foreach ($this->defaultValues as $row) {
            $this->getAdapter()->query("INSERT into {$this->tableName}({$this->tableKeyName}, artist, title, deleted)
                                        VALUES (
                                            {$row[$this->tableKeyName]},
                                            '{$row['artist']}',
                                            '{$row['title']}',
                                            {$row['deleted']}
                                        );");
        }
        return $this;
    }

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->dropTables()->createTables()->insertDefaultRows();

        $this->Service = new \RealejoZf1Test\Service\ServiceConcrete();

        // Remove as pastas criadas
        $this->clearApplicationData();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->dropTables();

        unset($this->Service);

        $this->clearApplicationData();
    }

    /**
     * Tests Base->fetchAll()
     */
    public function testFindAll()
    {
         // O padrão é não usar o campo deleted
        $albuns = $this->Service->findAll();
        $this->assertCount(4, $albuns, 'showDeleted=false, useDeleted=false');

        // Marca para mostrar os removidos e não usar o campo deleted
        $this->Service->getMapper()->setShowDeleted(true)->setUseDeleted(false);
        $this->assertCount(4, $this->Service->findAll(), 'showDeleted=true, useDeleted=false');

        // Marca pra não mostar os removidos e usar o campo deleted
        $this->Service->getMapper()->setShowDeleted(false)->setUseDeleted(true);
        $this->assertCount(3, $this->Service->findAll(), 'showDeleted=false, useDeleted=true');

        // Marca pra mostrar os removidos e usar o campo deleted
        $this->Service->getMapper()->setShowDeleted(true)->setUseDeleted(true);
        $albuns = $this->Service->findAll();
        $this->assertCount(4, $albuns, 'showDeleted=true, useDeleted=true');

        // Marca não mostrar os removios
        $this->Service->getMapper()->setUseDeleted(true)->setShowDeleted(false);

        $albuns = $this->defaultValues;
        unset($albuns[3]); // remove o deleted=1

        $findAll = $this->Service->findAll();
        foreach($findAll as $id=>$row) {
            $findAll[$id] = $row->toArray();
        }
        $this->assertEquals($albuns, $findAll);

        // Marca mostrar os removios
        $this->Service->getMapper()->setShowDeleted(true);

        $findAll = $this->Service->findAll();
        foreach($findAll as $id=>$row) {
            $findAll[$id] = $row->toArray();
        }
        $this->assertEquals($this->defaultValues, $findAll);
        $this->assertCount(4, $this->Service->findAll());
        $this->Service->getMapper()->setShowDeleted(false);
        $this->assertCount(3, $this->Service->findAll());

        // Verifica o where
        $this->assertCount(2, $this->Service->findAll(array('artist'=>$albuns[0]['artist'])));
        $this->assertNull($this->Service->findAll(array('artist'=>$this->defaultValues[3]['artist'])));

        // Verifica o paginator com o padrão
        $paginator = $this->Service->findPaginated();
        $paginator = $paginator->toJson();

        // Tem um bug no \Zend_Paginator
        //http://framework.zend.com/issues/browse/ZF-9731
        $paginator = (array)json_decode($paginator);
        $temp = array();
        foreach($paginator as $p) {
            $temp[] = $p;
        }
        $paginator = json_encode($temp);

        $findAll = $this->Service->findAll();
        foreach($findAll as $id=>$row) {
            $findAll[$id] = $row->toArray();
        }
        $paginator = json_encode($temp);
        $this->assertNotEquals(json_encode($this->defaultValues), $paginator);
        $this->assertEquals(json_encode($findAll), $paginator, 'retorno do paginator é igual');

        // Verifica o paginator alterando o paginator
        $this->Service->getPaginatorOptions()
                      ->setPageRange(2)
                      ->setCurrentPageNumber(1)
                      ->setItemCountPerPage(2);
        $paginator = $this->Service->findPaginated();
        $paginator = $paginator->toJson();

        // Tem um bug no \Zend_Paginator
        //http://framework.zend.com/issues/browse/ZF-9731
        $paginator = (array)json_decode($paginator);
        $temp = array();
        foreach($paginator as $p) {
            $temp[] = $p;
        }
        $paginator = json_encode($temp);

        $this->assertNotEquals(json_encode($this->defaultValues), $paginator);
        $fetchAll = $this->Service->findPaginated(null, null, 2);
        $temp = array();
        foreach($fetchAll as $p) {
            $temp[] = $p->toArray();
        }
        $fetchAll = $temp;
        $this->assertEquals(json_encode($fetchAll), $paginator);

        // Apaga qualquer cache
        $this->assertTrue($this->Service->getCache()->clean(), 'apaga o cache');

        // Define exibir os deletados
        $this->Service->getMapper()->setShowDeleted(true);

        // Liga o cache
        $this->Service->setUseCache(true);
        $findAll = $this->Service->findAll();
        $temp = array();
        foreach($findAll as $p) {
            $temp[] = $p->toArray();
        }
        $findAll = $temp;
        $this->assertEquals($this->defaultValues, $findAll, 'fetchAll está igual ao defaultValues');
        $this->assertCount(4, $findAll, 'Deve conter 4 registros');

        // Grava um registro "sem o cache saber"
        $this->Service->getMapper()->getTableGateway()->insert(array('id'=>10, 'artist'=>'nao existo por enquanto', 'title'=>'bla bla', 'deleted' => 0));

        $this->assertCount(4, $this->Service->findAll(), 'Deve conter 4 registros depois do insert "sem o cache saber"');
        $this->assertTrue($this->Service->getCache()->clean(), 'limpa o cache');
        $this->assertCount(5, $this->Service->findAll(), 'Deve conter 5 registros');

        // Define não exibir os deletados
        $this->Service->getMapper()->setShowDeleted(false);
        $this->assertCount(4, $this->Service->findAll(), 'Deve conter 4 registros showDeleted=false');

        // Apaga um registro "sem o cache saber"
        $this->Service->getMapper()->getTableGateway()->delete("id=10");
        $this->Service->getMapper()->setShowDeleted(true);
        $this->assertCount(5, $this->Service->findAll(), 'Deve conter 5 registros');
        $this->assertTrue($this->Service->getCache()->clean(), 'apaga o cache');
        $this->assertCount(4, $this->Service->findAll(), 'Deve conter 4 registros 4');

    }

    /**
     * Tests Base->fetchRow()
     */
    public function testFindOne()
    {
        // Marca pra usar o campo deleted
        $this->Service->getMapper()->setUseDeleted(true);

        // Verifica os itens que existem
        $this->assertInstanceOf('\RealejoZf1\Stdlib\ArrayObject', $this->Service->findOne(1));
        $this->assertEquals($this->defaultValues[0], $this->Service->findOne(1)->toArray());
        $this->assertInstanceOf('\RealejoZf1\Stdlib\ArrayObject', $this->Service->findOne(2));
        $this->assertEquals($this->defaultValues[1], $this->Service->findOne(2)->toArray());
        $this->assertInstanceOf('\RealejoZf1\Stdlib\ArrayObject', $this->Service->findOne(3));
        $this->assertEquals($this->defaultValues[2], $this->Service->findOne(3)->toArray());
        $this->assertEmpty($this->Service->findOne(4));

        // Verifica o item removido
        $this->Service->getMapper()->setShowDeleted(true);
        $findOne = $this->Service->findOne(4);
        $this->assertInstanceOf('\RealejoZf1\Stdlib\ArrayObject', $findOne);
        $this->assertEquals($this->defaultValues[3], $findOne->toArray());
        $this->Service->getMapper()->setShowDeleted(false);
    }

    /**
     * Tests Base->findAssoc()
     */
    public function testFindAssoc()
    {
        // O padrão é não usar o campo deleted
        $albuns = $this->Service->findAssoc();
        $this->assertCount(4, $albuns, 'showDeleted=false, useDeleted=false');
        $this->assertInstanceOf('\RealejoZf1\Stdlib\ArrayObject', $albuns[1]);
        $this->assertEquals($this->defaultValues[0], $albuns[1]->toArray());
        $this->assertInstanceOf('\RealejoZf1\Stdlib\ArrayObject', $albuns[2]);
        $this->assertEquals($this->defaultValues[1], $albuns[2]->toArray());
        $this->assertInstanceOf('\RealejoZf1\Stdlib\ArrayObject', $albuns[3]);
        $this->assertEquals($this->defaultValues[2], $albuns[3]->toArray());
        $this->assertInstanceOf('\RealejoZf1\Stdlib\ArrayObject', $albuns[4]);
        $this->assertEquals($this->defaultValues[3], $albuns[4]->toArray());

        // Marca para mostrar os removidos e não usar o campo deleted
        $this->Service->getMapper()->setShowDeleted(true)->setUseDeleted(false);
        $this->assertCount(4, $this->Service->findAssoc(), 'showDeleted=true, useDeleted=false');

        // Marca pra mostrar os removidos e usar o campo deleted
        $this->Service->getMapper()->setShowDeleted(true)->setUseDeleted(true);
        $albuns = $this->Service->findAssoc();
        $this->assertCount(4, $albuns, 'showDeleted=true, useDeleted=true');
        $this->assertInstanceOf('\RealejoZf1\Stdlib\ArrayObject', $albuns[1]);
        $this->assertEquals($this->defaultValues[0], $albuns[1]->toArray());
        $this->assertInstanceOf('\RealejoZf1\Stdlib\ArrayObject', $albuns[2]);
        $this->assertEquals($this->defaultValues[1], $albuns[2]->toArray());
        $this->assertInstanceOf('\RealejoZf1\Stdlib\ArrayObject', $albuns[3]);
        $this->assertEquals($this->defaultValues[2], $albuns[3]->toArray());
        $this->assertInstanceOf('\RealejoZf1\Stdlib\ArrayObject', $albuns[4]);
        $this->assertEquals($this->defaultValues[3], $albuns[4]->toArray());
    }

    /**
     * Tests Base->findAssoc()
     */
    public function testFindAssocWithMultipleKeys()
    {
        $this->Service->getMapper()->setTableKey(array($this->tableKeyName, 'naoexisto'));

        // O padrão é não usar o campo deleted
        $albuns = $this->Service->findAssoc();
        $this->assertCount(4, $albuns, 'showDeleted=false, useDeleted=false');
        $this->assertInstanceOf('\RealejoZf1\Stdlib\ArrayObject', $albuns[1]);
        $this->assertEquals($this->defaultValues[0], $albuns[1]->toArray());
        $this->assertInstanceOf('\RealejoZf1\Stdlib\ArrayObject', $albuns[2]);
        $this->assertEquals($this->defaultValues[1], $albuns[2]->toArray());
        $this->assertInstanceOf('\RealejoZf1\Stdlib\ArrayObject', $albuns[3]);
        $this->assertEquals($this->defaultValues[2], $albuns[3]->toArray());
        $this->assertInstanceOf('\RealejoZf1\Stdlib\ArrayObject', $albuns[4]);
        $this->assertEquals($this->defaultValues[3], $albuns[4]->toArray());

        // Marca para mostrar os removidos e não usar o campo deleted
        $this->Service->getMapper()->setShowDeleted(true)->setUseDeleted(false);
        $this->assertCount(4, $this->Service->findAssoc(), 'showDeleted=true, useDeleted=false');

        // Marca pra mostrar os removidos e usar o campo deleted
        $this->Service->getMapper()->setShowDeleted(true)->setUseDeleted(true);
        $albuns = $this->Service->findAssoc();
        $this->assertCount(4, $albuns, 'showDeleted=true, useDeleted=true');
        $this->assertInstanceOf('\RealejoZf1\Stdlib\ArrayObject', $albuns[1]);
        $this->assertEquals($this->defaultValues[0], $albuns[1]->toArray());
        $this->assertInstanceOf('\RealejoZf1\Stdlib\ArrayObject', $albuns[2]);
        $this->assertEquals($this->defaultValues[1], $albuns[2]->toArray());
        $this->assertInstanceOf('\RealejoZf1\Stdlib\ArrayObject', $albuns[3]);
        $this->assertEquals($this->defaultValues[2], $albuns[3]->toArray());
        $this->assertInstanceOf('\RealejoZf1\Stdlib\ArrayObject', $albuns[4]);
        $this->assertEquals($this->defaultValues[3], $albuns[4]->toArray());
    }

    public function testHtmlSelectGettersSetters()
    {
        $this->assertEquals('{nome}', $this->Service->getHtmlSelectOption(), 'padrão {nome}');
        $this->assertInstanceOf('\RealejoZf1\Service\ServiceAbstract', $this->Service->setHtmlSelectOption('{title}'), 'setHtmlSelectOption() retorna RW_App_Model_Base');
        $this->assertEquals('{title}', $this->Service->getHtmlSelectOption(), 'troquei por {title}');
    }

    public function testHtmlSelectWhere()
    {
        $id = 'teste';
        $this->Service->setHtmlSelectOption('{title}');

        $select = $this->Service->getHtmlSelect($id, null, array('where'=>array('artist'=>'Rush')));
        $this->assertNotEmpty($select);
        $dom = new \Zend_Dom_Query($select);

        $options = $dom->query("option");
        $this->assertCount(3, $options, " 3 opções encontradas");

        $this->assertEmpty($options->current()->nodeValue, "primeiro é vazio 1");
        $this->assertEmpty($options->current()->getAttribute('value'), "o valor do primeiro é vazio 1");

        $options->next();
        $this->assertEquals($this->defaultValues[0]['title'], $options->current()->nodeValue, "nome do segundo ok 1");
        $this->assertEquals($this->defaultValues[0]['id'], $options->current()->getAttribute('value'), "valor do segundo ok 1");

        $options->next();
        $this->assertEquals($this->defaultValues[1]['title'], $options->current()->nodeValue, "nome do terceiro ok 1");
        $this->assertEquals($this->defaultValues[1]['id'], $options->current()->getAttribute('value'), "valor do terceiro ok 1");


        $select = $this->Service->getHtmlSelect($id, 1, array('where'=>array('artist'=>'Rush')));
        $this->assertNotEmpty($select);
        $dom = new \Zend_Dom_Query($select);

        $options = $dom->query("option");
        $this->assertCount(2, $options, " 2 opções encontradas");

        $this->assertNotEmpty($options->current()->nodeValue, "primeiro não é vazio 2");
        $this->assertNotEmpty($options->current()->getAttribute('value'), "o valor do primeiro não é vazio 2");

        $this->assertEquals($this->defaultValues[0]['title'], $options->current()->nodeValue, "nome do segundo ok 2");
        $this->assertEquals($this->defaultValues[0]['id'], $options->current()->getAttribute('value'), "valor do segundo ok 2");

        $options->next();
        $this->assertEquals($this->defaultValues[1]['title'], $options->current()->nodeValue, "nome do terceiro ok 2");
        $this->assertEquals($this->defaultValues[1]['id'], $options->current()->getAttribute('value'), "valor do terceiro ok 2");
    }

    public function testHtmlSelectSemOptionValido()
    {
        $id = 'teste';

        $select = $this->Service->getHtmlSelect($id);
        $this->assertNotEmpty($select);
        $dom = new \Zend_Dom_Query($select);

        $options = $dom->query("option");
        $this->assertCount(5, $options, " 5 opções encontradas");

        $this->assertEmpty($options->current()->nodeValue, "primeiro é vazio 1");
        $this->assertEmpty($options->current()->getAttribute('value'), "o valor do primeiro é vazio 1");

        $options->next();
        $this->assertEmpty($options->current()->nodeValue, "nome do segundo ok 1");
        $this->assertEquals($this->defaultValues[0]['id'], $options->current()->getAttribute('value'), "valor do segundo ok 1");

        $options->next();
        $this->assertEmpty($options->current()->nodeValue, "nome do terceiro ok 1");
        $this->assertEquals($this->defaultValues[1]['id'], $options->current()->getAttribute('value'), "valor do terceiro ok 1");

        $options->next();
        $this->assertEmpty($options->current()->nodeValue, "nome do quarto ok 1");
        $this->assertEquals($this->defaultValues[2]['id'], $options->current()->getAttribute('value'), "valor do quarto ok 1");

        $options->next();
        $this->assertEmpty($options->current()->nodeValue, "nome do quinto ok 1");
        $this->assertEquals($this->defaultValues[3]['id'], $options->current()->getAttribute('value'), "valor do quinto ok 1");

        $select = $this->Service->setHtmlSelectOption('{nao_existo}')->getHtmlSelect($id);
        $this->assertNotEmpty($select);
        $dom = new \Zend_Dom_Query($select);

        $options = $dom->query("option");
        $this->assertCount(5, $options, " 5 opções encontradas");

        $this->assertEmpty($options->current()->nodeValue, "primeiro é vazio 2");
        $this->assertEmpty($options->current()->getAttribute('value'), "o valor do primeiro é vazio 2");

        $options->next();
        $this->assertEmpty($options->current()->nodeValue, "nome do segundo ok 2");
        $this->assertEquals($this->defaultValues[0]['id'], $options->current()->getAttribute('value'), "valor do segundo ok 2");

        $options->next();
        $this->assertEmpty($options->current()->nodeValue, "nome do terceiro ok 2");
        $this->assertEquals($this->defaultValues[1]['id'], $options->current()->getAttribute('value'), "valor do terceiro ok 2");

        $options->next();
        $this->assertEmpty($options->current()->nodeValue, "nome do quarto ok 2");
        $this->assertEquals($this->defaultValues[2]['id'], $options->current()->getAttribute('value'), "valor do quarto ok 2");

        $options->next();
        $this->assertEmpty($options->current()->nodeValue, "nome do quinto ok 2");
        $this->assertEquals($this->defaultValues[3]['id'], $options->current()->getAttribute('value'), "valor do quinto ok 2");
    }

    public function testHtmlSelectOption()
    {
        $id = 'teste';

        $select = $this->Service->setHtmlSelectOption('{artist}')->getHtmlSelect($id);
        $this->assertNotEmpty($select);
        $dom = new \Zend_Dom_Query($select);
        $this->assertCount(1, $dom->query("#$id"), "id #$id existe");
        $this->assertCount(1, $dom->query("select[name=\"$id\"]"), "placeholder select[name=\"$id\"] encontrado");
        $options = $dom->query("option");
        $this->assertCount(5, $options, " 5 opções encontradas");

        $this->assertEmpty($options->current()->nodeValue, "primeiro é vazio");
        $this->assertEmpty($options->current()->getAttribute('value'), "o valor do primeiro é vazio");

        $options->next();
        $this->assertEquals($this->defaultValues[0]['artist'], $options->current()->nodeValue, "nome do segundo ok");
        $this->assertEquals($this->defaultValues[0]['id'], $options->current()->getAttribute('value'), "valor do segundo ok");

        $options->next();
        $this->assertEquals($this->defaultValues[1]['artist'], $options->current()->nodeValue, "nome do terceiro ok");
        $this->assertEquals($this->defaultValues[1]['id'], $options->current()->getAttribute('value'), "valor do terceiro ok");

        $options->next();
        $this->assertEquals($this->defaultValues[2]['artist'], $options->current()->nodeValue, "nome do quarto ok");
        $this->assertEquals($this->defaultValues[2]['id'], $options->current()->getAttribute('value'), "valor do quarto ok");

        $options->next();
        $this->assertEquals($this->defaultValues[3]['artist'], $options->current()->nodeValue, "nome do quinto ok");
        $this->assertEquals($this->defaultValues[3]['id'], $options->current()->getAttribute('value'), "valor do quinto ok");
    }

    public function testHtmlSelectPlaceholder()
    {
        $ph = 'myplaceholder';
        $select = $this->Service->getHtmlSelect('nome_usado', null, array('placeholder'=>$ph));
        $this->assertNotEmpty($select);
        $dom = new \Zend_Dom_Query($select);
        $this->assertCount(1, $dom->query('#nome_usado'), 'id #nome_usado existe');
        $this->assertCount(1, $dom->query("select[placeholder=\"$ph\"]"), "placeholder select[placeholder=\"$ph\"] encontrado");
        $options = $dom->query("option");
        $this->assertCount(5, $options, " 5 opções encontradas");
        $this->assertEquals($ph, $options->current()->nodeValue, "placeholder é a primeira");
        $this->assertEmpty($options->current()->getAttribute('value'), "o valor do placeholder é vazio");
    }

    public function testHtmlSelectShowEmpty()
    {
        $select = $this->Service->getHtmlSelect('nome_usado');
        $this->assertNotEmpty($select);
        $dom = new \Zend_Dom_Query($select);
        $this->assertCount(1, $dom->query('#nome_usado'), 'id #nome_usado existe');
        $this->assertCount(5, $dom->query('option'), '5 opções existem');
        $this->assertEmpty($dom->query('option')->current()->nodeValue, "a primeira é vazia");
        $this->assertEmpty($dom->query('option')->current()->getAttribute('value'), "o valor da primeira é vazio");

        $select = $this->Service->getHtmlSelect('nome_usado', 1);
        $this->assertNotEmpty($select);
        $dom = new \Zend_Dom_Query($select);
        $this->assertCount(1, $dom->query('#nome_usado'), 'id #nome_usado existe COM valor padrão');
        $this->assertCount(4, $dom->query('option'), '4 opções existem COM valor padrão');

        $select = $this->Service->getHtmlSelect('nome_usado', null, array('show-empty'=>false));
        $this->assertNotEmpty($select);
        $dom = new \Zend_Dom_Query($select);
        $this->assertCount(1, $dom->query('#nome_usado'), 'id #nome_usado existe SEM valor padrão e show-empty=false');
        $this->assertCount(4, $dom->query('option'), '4 opções existem SEM valor padrão e show-empty=false');

        // sem mostrar o empty
        $select = $this->Service->getHtmlSelect('nome_usado', 1, array('show-empty'=>false));
        $this->assertNotEmpty($select);
        $dom = new \Zend_Dom_Query($select);
        $this->assertCount(1, $dom->query('#nome_usado'), 'id #nome_usado existe com valor padrão e show-empty=false');
        $this->assertCount(4, $dom->query('option'), '4 opções existem com valor padrão e show-empty=false');

        // sem mostrar o empty
        $select = $this->Service->getHtmlSelect('nome_usado', 1, array('show-empty'=>true));
        $this->assertNotEmpty($select);
        $dom = new \Zend_Dom_Query($select);
        $this->assertCount(1, $dom->query('#nome_usado'), 'id #nome_usado existe com valor padrão e show-empty=true');
        $this->assertCount(5, $dom->query('option'), '5 opções existem com valor padrão e show-empty=true');
        $this->assertEmpty($dom->query('option')->current()->nodeValue, "a primeira é vazia com valor padrão e show-empty=true");
        $this->assertEmpty($dom->query('option')->current()->getAttribute('value'), "o valor da primeira é vazio com valor padrão e show-empty=true");
    }

    public function testHtmlSelectGrouped()
    {
        $id = 'teste';

        $select = $this->Service->setHtmlSelectOption('{title}')->getHtmlSelect($id, 1, array('grouped'=>'artist'));
        $this->assertNotEmpty($select);
        $dom = new \Zend_Dom_Query($select);
        $this->assertCount(1, $dom->query("#$id"), "id #$id existe");

        $options = $dom->query("option");
        $this->assertCount(4, $options, " 4 opções encontradas");

        $this->assertEquals($this->defaultValues[0]['title'], $options->current()->nodeValue, "nome do primeiro ok 1");
        $this->assertEquals($this->defaultValues[0]['id'], $options->current()->getAttribute('value'), "valor do primeiro ok 1");

        $options->next();
        $this->assertEquals($this->defaultValues[1]['title'], $options->current()->nodeValue, "nome do segundo ok 1");
        $this->assertEquals($this->defaultValues[1]['id'], $options->current()->getAttribute('value'), "valor do segundo ok 1");

        $options->next();
        $this->assertEquals($this->defaultValues[2]['title'], $options->current()->nodeValue, "nome do terceiro ok 1");
        $this->assertEquals($this->defaultValues[2]['id'], $options->current()->getAttribute('value'), "valor do terceiro ok 1");

        $options->next();
        $this->assertEquals($this->defaultValues[3]['title'], $options->current()->nodeValue, "nome do quarto ok 1");
        $this->assertEquals($this->defaultValues[3]['id'], $options->current()->getAttribute('value'), "valor do quarto ok 1");

        $optgroups = $dom->query("optgroup");
        $this->assertCount(3, $optgroups, " 3 grupo de opções encontrados");

        $this->assertEquals($this->defaultValues[0]['artist'], $optgroups->current()->getAttribute('label'), "nome do primeiro grupo ok");
        $this->assertEquals(2, $optgroups->current()->childNodes->length, " 2 opções encontrados no priemiro optgroup");
        $this->assertEquals($this->defaultValues[0]['title'], $optgroups->current()->firstChild->nodeValue, "nome do primeiro ok 2");
        $this->assertEquals($this->defaultValues[0]['id'], $optgroups->current()->firstChild->getAttribute('value'), "valor do primeiro ok 2");
        $this->assertEquals($this->defaultValues[1]['title'], $optgroups->current()->firstChild->nextSibling->nodeValue, "nome do segundo ok 2");
        $this->assertEquals($this->defaultValues[1]['id'], $optgroups->current()->firstChild->nextSibling->getAttribute('value'), "valor do segundo ok 2");

        $optgroups->next();
        $this->assertEquals($this->defaultValues[2]['artist'], $optgroups->current()->getAttribute('label'), "nome do segundo grupo ok");
        $this->assertEquals(1, $optgroups->current()->childNodes->length, " 2 opções encontrados");
        $this->assertEquals($this->defaultValues[2]['title'], $optgroups->current()->firstChild->nodeValue, "nome do terceiro ok 2");
        $this->assertEquals($this->defaultValues[2]['id'], $optgroups->current()->firstChild->getAttribute('value'), "valor do terceiro ok 2");

        $optgroups->next();
        $this->assertEquals($this->defaultValues[3]['artist'], $optgroups->current()->getAttribute('label'), "nome do terceiro grupo ok");
        $this->assertEquals(1, $optgroups->current()->childNodes->length, " 2 opções encontrados");
        $this->assertEquals($this->defaultValues[3]['title'], $optgroups->current()->firstChild->nodeValue, "nome do terceiro ok 2");
        $this->assertEquals($this->defaultValues[3]['id'], $optgroups->current()->firstChild->getAttribute('value'), "valor do terceiro ok 2");

        // SELECT VAZIO!

        $select = $this->Service->setHtmlSelectOption('{title}')->getHtmlSelect($id, 1, array('grouped'=>'artist', 'where'=>array('id'=>100)));
        $this->assertNotEmpty($select);
        $dom = new \Zend_Dom_Query($select);
        $this->assertCount(1, $dom->query("#$id"), "id #$id existe");

        $this->assertCount(1, $dom->query("option"), " nenhuma option com where id = 100");
        $this->assertCount(0, $dom->query("optgroup"), " nenhuma optgroup com where id = 100");

        $this->assertEmpty($dom->query("option")->current()->nodeValue, "primeiro é vazio");
        $this->assertEmpty($dom->query("option")->current()->getAttribute('value'), "o valor do primeiro é vazio");
    }

    public function testHtmlSelectMultipleKey()
    {
        // Define a chave multipla
        // como ele deve considerar apenas o primeiro o teste abaixo é o mesmo de testHtmlSelectWhere
        $this->Service->getMapper()->setTableKey(array('id', 'nao-existo'));

        $id = 'teste';
        $this->Service->setHtmlSelectOption('{title}');

        $select = $this->Service->getHtmlSelect($id, null, array('where'=>array('artist'=>'Rush')));
        $this->assertNotEmpty($select);
        $dom = new \Zend_Dom_Query($select);

        $options = $dom->query("option");
        $this->assertCount(3, $options, " 3 opções encontradas");

        $this->assertEmpty($options->current()->nodeValue, "primeiro é vazio 1");
        $this->assertEmpty($options->current()->getAttribute('value'), "o valor do primeiro é vazio 1");

        $options->next();
        $this->assertEquals($this->defaultValues[0]['title'], $options->current()->nodeValue, "nome do segundo ok 1");
        $this->assertEquals($this->defaultValues[0]['id'], $options->current()->getAttribute('value'), "valor do segundo ok 1");

        $options->next();
        $this->assertEquals($this->defaultValues[1]['title'], $options->current()->nodeValue, "nome do terceiro ok 1");
        $this->assertEquals($this->defaultValues[1]['id'], $options->current()->getAttribute('value'), "valor do terceiro ok 1");


        $select = $this->Service->getHtmlSelect($id, 1, array('where'=>array('artist'=>'Rush')));
        $this->assertNotEmpty($select);
        $dom = new \Zend_Dom_Query($select);

        $options = $dom->query("option");
        $this->assertCount(2, $options, " 2 opções encontradas");

        $this->assertNotEmpty($options->current()->nodeValue, "primeiro não é vazio 2");
        $this->assertNotEmpty($options->current()->getAttribute('value'), "o valor do primeiro não é vazio 2");

        $this->assertEquals($this->defaultValues[0]['title'], $options->current()->nodeValue, "nome do segundo ok 2");
        $this->assertEquals($this->defaultValues[0]['id'], $options->current()->getAttribute('value'), "valor do segundo ok 2");

        $options->next();
        $this->assertEquals($this->defaultValues[1]['title'], $options->current()->nodeValue, "nome do terceiro ok 2");
        $this->assertEquals($this->defaultValues[1]['id'], $options->current()->getAttribute('value'), "valor do terceiro ok 2");
    }

    public function testHtmlSelectMultipleKeyWithCast()
    {
        // Define a chave multipla
        // como ele deve considerar apenas o primeiro o teste abaixo é o mesmo de testHtmlSelectWhere
        $this->Service->getMapper()->setTableKey(array('CAST'=>'id', 'nao-existo'));

        $id = 'teste';
        $this->Service->setHtmlSelectOption('{title}');

        $select = $this->Service->getHtmlSelect($id, null, array('where'=>array('artist'=>'Rush')));
        $this->assertNotEmpty($select);
        $dom = new \Zend_Dom_Query($select);

        $options = $dom->query("option");
        $this->assertCount(3, $options, " 3 opções encontradas");

        $this->assertEmpty($options->current()->nodeValue, "primeiro é vazio 1");
        $this->assertEmpty($options->current()->getAttribute('value'), "o valor do primeiro é vazio 1");

        $options->next();
        $this->assertEquals($this->defaultValues[0]['title'], $options->current()->nodeValue, "nome do segundo ok 1");
        $this->assertEquals($this->defaultValues[0]['id'], $options->current()->getAttribute('value'), "valor do segundo ok 1");

        $options->next();
        $this->assertEquals($this->defaultValues[1]['title'], $options->current()->nodeValue, "nome do terceiro ok 1");
        $this->assertEquals($this->defaultValues[1]['id'], $options->current()->getAttribute('value'), "valor do terceiro ok 1");


        $select = $this->Service->getHtmlSelect($id, 1, array('where'=>array('artist'=>'Rush')));
        $this->assertNotEmpty($select);
        $dom = new \Zend_Dom_Query($select);

        $options = $dom->query("option");
        $this->assertCount(2, $options, " 2 opções encontradas");

        $this->assertNotEmpty($options->current()->nodeValue, "primeiro não é vazio 2");
        $this->assertNotEmpty($options->current()->getAttribute('value'), "o valor do primeiro não é vazio 2");

        $this->assertEquals($this->defaultValues[0]['title'], $options->current()->nodeValue, "nome do segundo ok 2");
        $this->assertEquals($this->defaultValues[0]['id'], $options->current()->getAttribute('value'), "valor do segundo ok 2");

        $options->next();
        $this->assertEquals($this->defaultValues[1]['title'], $options->current()->nodeValue, "nome do terceiro ok 2");
        $this->assertEquals($this->defaultValues[1]['id'], $options->current()->getAttribute('value'), "valor do terceiro ok 2");
    }
}
