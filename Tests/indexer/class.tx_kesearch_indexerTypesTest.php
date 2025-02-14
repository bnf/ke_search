<?php

class IndexerTypesTest extends Tx_Extbase_BaseTestCase
{

    /**
     * @var tx_kesearch_indexer_types_page
     */
    public $pageIndexer;

    /**
     * @var tx_kesearch_indexer_types
     */
    public $indexerTypes;


    public function setUp()
    {
        $this->indexerTypes = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Tpwd\KeSearch\Indexer\IndexerBase::class);
        $this->indexerTypes->queryGen = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\QueryGenerator::class);
        $this->pageIndexer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Tpwd\KeSearch\Indexer\Types\Page::class);
        $this->pageIndexer->pObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Tpwd\KeSearch\Indexer\IndexerRunner::class);
        $this->pageIndexer->pObj->extConf['prePostTagChar'] = '#';
    }

    public function tearDown()
    {
        unset($this->indexerTypes);
        unset($this->pageIndexer);
    }


    /**
     * Test method getPagelist
     * @test
     */
    public function getPagelistTest()
    {
        $pidArray = $this->indexerTypes->getPagelist();
        // check if it is of type array
        $this->assertInternalType('array', $pidArray);
        // this is the recursive part, so it should have 2 or more entries
        $this->assertEquals(0, count($pidArray));

        // get the rootPage UID. In most cases it should have recursive child elements
        $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            'uid',
            'pages',
            'deleted=0 AND hidden=0 AND is_siteroot=1',
            '',
            '',
            '1'
        );
        if (count($rows) > 0) {
            $rootPage = $rows[0]['uid'];
        } else {
            $rootPage = 1;
        }

        $pidArray = $this->indexerTypes->getPagelist($rootPage);
        // check if it is of type array
        $this->assertInternalType('array', $pidArray);
        // this is the recursive part, so it should have 2 or more entries
        $this->assertGreaterThanOrEqual(2, count($pidArray));

        $pidArray = $this->indexerTypes->getPagelist('', $rootPage);
        // check if it is of type array
        $this->assertInternalType('array', $pidArray);
        // this is the recursive part, so it should have 2 or more entries
        $this->assertEquals(1, count($pidArray));
    }


    /**
     * Test method getPageRecords
     * @test
     */
    public function getPageRecordsTest()
    {
        // get the rootPage UID. In most cases it should have recursive child elements
        $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            'uid',
            'pages',
            'deleted=0 AND hidden=0 AND is_siteroot=1',
            '',
            '',
            '1'
        );
        if (count($rows) > 0) {
            $rootPage = $rows[0]['uid'];
        } else {
            $rootPage = 1;
        }

        $pidArray = $this->indexerTypes->getPagelist($rootPage);
        $pageRecords = $this->indexerTypes->getPageRecords($pidArray);
        // check if it is of type array
        $this->assertInternalType('array', $pageRecords);
        // there should be at last 1 record
        $this->assertGreaterThanOrEqual(1, count($pageRecords));
        // check for some array keys which have to be present
        $this->assertArrayHasKey('uid', $pageRecords[$rootPage]);
        $this->assertArrayHasKey('title', $pageRecords[$rootPage]);
        $this->assertNotEmpty($pageRecords[$rootPage]['uid']);
        $this->assertNotEmpty($pageRecords[$rootPage]['title']);
    }


    /**
     * Test method getPidList
     * @test
     */
    public function getPidListTest()
    {
        // get the rootPage UID. In most cases it should have recursive child elements
        $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            'uid',
            'pages',
            'deleted=0 AND hidden=0 AND is_siteroot=1',
            '',
            '',
            '1'
        );
        if (count($rows) > 0) {
            $rootPage = $rows[0]['uid'];
        } else {
            $rootPage = 1;
        }

        $pidArray = $this->indexerTypes->getPidList($rootPage, '', 'tt_news');
        // check if it is of type array
        $this->assertInternalType('array', $pidArray);
        // there should be at last 1 record
        $this->assertGreaterThanOrEqual(1, count($pidArray));
        foreach ($pidArray as $pid) {
            $this->assertInternalType('integer', $pid);
        }

        $pidArray = $this->indexerTypes->getPidList('', $rootPage, 'tt_news');
        // check if it is of type array
        $this->assertInternalType('array', $pidArray);
        // there should be 1 record
        $this->assertEquals(1, count($pidArray));
        $this->assertInternalType('integer', $pidArray[0]);
    }


    /**
     * Test method addTagsToPageRecords
     * @test
     */
    public function addTagsToPageRecordsTest()
    {
        // get the rootPage UID. In most cases it should have recursive child elements
        $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            'uid',
            'pages',
            'deleted=0 AND hidden=0 AND is_siteroot=1',
            '',
            '',
            '1'
        );
        if (count($rows) > 0) {
            $rootPage = $rows[0]['uid'];
        } else {
            $rootPage = 1;
        }

        // get all pages. Regardeless if they are shortcut, sysfolder or external link
        $indexPids = $this->pageIndexer->getPagelist($rootPage);

        // add complete page record to list of pids in $indexPids
        // and remove all page of type shortcut, sysfolder and external link
        $this->pageIndexer->pageRecords = $this->pageIndexer->getPageRecords($indexPids);

        // create a new list of allowed pids
        $indexPids = array_keys($this->pageIndexer->pageRecords);

        // add the tags of each page to the global page array
        $this->pageIndexer->addTagsToPageRecords($indexPids);
    }
}
