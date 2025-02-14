<?php

class IndexerTest extends Tx_Extbase_BaseTestCase
{

    /**
     * @var tx_kesearch_indexer
     */
    public $indexer;


    public function setUp()
    {
        $this->indexer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Tpwd\KeSearch\Indexer\IndexerRunner::class);
        $this->indexer->additionalFields = array('orig_uid', 'orig_pid', 'enddate');
    }

    public function tearDown()
    {
        unset($this->indexer);
    }


    /**
     * Test additional query parts for additional fields
     * @test
     */
    public function checkGetQueryPartsForAdditionalFields()
    {
        $now = time();
        $fieldValues = array(
            'tstamp' => $now,
            'crdate' => $now,
            'title' => 'tolle Überschrift',
            'orig_uid' => '213asdf',
            'orig_pid' => 423,
            'enddate' => $now,
        );
        $fieldValues = $GLOBALS['TYPO3_DB']->fullQuoteArray($fieldValues, 'tx_kesearch_index');

        $shouldArray = array(
            'set' => ', @orig_uid = \'213asdf\', @orig_pid = \'423\', @enddate = \'' . $now . '\'',
            'execute' => ', @orig_uid, @orig_pid, @enddate'
        );

        $isArray = $this->indexer->getQueryPartForAdditionalFields($fieldValues);

        $this->assertEquals($shouldArray, $isArray);
    }


    /**
     * Test additional query parts for additional fields
     * @test
     */
    public function getTagTest()
    {
        $fields = 'uid, title, tag';
        $table = 'tx_kesearch_filteroptions';
        $where = '1=1 ';
        $where .= t3lib_befunc::BEenableFields($table, 0);
        $where .= t3lib_befunc::deleteClause($table, 0);

        $row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
            $fields,
            $table,
            $where
        );
        if (is_array($row) && count($row)) {
            $return = $this->indexer->getTag($row['uid'], false);
            $this->assertEquals($row['tag'], $return);
            $return = $this->indexer->getTag($row['uid'], true);
            $this->assertEquals($row['title'], $return);
        }
    }
}
