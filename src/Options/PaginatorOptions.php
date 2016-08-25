<?php
namespace RealejoZf1\Options;

class PaginatorOptions
{

    /**
     * @var number
     */
    protected $pageRange = 10;

    /**
     * @var number
     */
    protected $currentPageNumber = 1;

    /**
     * @var number
     */
    protected $itemCountPerPage = 10;

    /**
     * @param number $pageRange
     * @return \RealejoZf1\Options\PaginatorOptions
     */
    public function setPageRange($pageRange)
    {
        $this->pageRange = $pageRange;

        // Mantem a cadeia
        return $this;
    }

    /**
     * @param number $currentPageNumber
     * @return \RealejoZf1\Options\PaginatorOptions
     */
    public function setCurrentPageNumber($currentPageNumber)
    {
        $this->currentPageNumber = $currentPageNumber;

        // Mantem a cadeia
        return $this;
    }

    /**
     * @param number $itemCountPerPage
     * @return \RealejoZf1\Options\PaginatorOptions
     */
    public function setItemCountPerPage($itemCountPerPage)
    {
        $this->itemCountPerPage = $itemCountPerPage;

        // Mantem a cadeia
        return $this;
    }

    /**
     * @return number
     */
    public function getPageRange()
    {
        return $this->pageRange;
    }

    /**
     * @return number
     */
    public function getCurrentPageNumber()
    {
        return $this->currentPageNumber;
    }

    /**
     *
     * @return number
     */
    public function getItemCountPerPage()
    {
        return $this->itemCountPerPage;
    }
}
