﻿.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. _sorting:

Sorting
=======

You may define the sorting method in the plugin configuration. Available options are “relevance”, “titel” and “date”.
More options may be added through third party extensions (see below “adding your own sorting options”).

There are two sorting method options, one if a searchword was given and one if only filters have been used without
a searchword. The reason for that is without a searchword, you don't have a relevance ranking.

Default sorting is “relevance descending” if a searchword has been given and “date descending” if no
searchword has been given.

.. image:: ../Images/Configuration/sorting-plugin-settings.png

You may also activate the “frontend sorting” feature. This allows the visitor to decide for a sorting method.

.. image:: ../Images/Configuration/sorting-links.png

You may then choose the fields you want to allow sorting for. By default these are “relevancy”, “date” and “title”.

Adding your own sorting options
-------------------------------

If you want other sorting options than relevance, date or title, you will have to

* Extend the table “tx_kesearch_index” by the fields you want to use for sorting (for example “mysortfield”) (ext_tables.sql, TCA configuration).
* Register your sorting fields by hook „registerAdditionalFields“, so that they are written to the database.
* Write your own indexer or extend an existing one that fills your new field during the indexing process.

Note: If you add an "additional field" to the index **every** indexer must set this field. So make sure you use the
provided hooks for every indexer you use.

You can find an example in the extension ke_search_hooks: https://github.com/teaminmedias-pluswerk/ke_search_hooks

.. code-block:: none

    in ext_localconf.php:

    // Register hook to register additional fields in the index table
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['registerAdditionalFields'][] =
    \MyVendor\KeSearchHooks\AdditionalIndexerFields::class;

.. code-block:: none

    <?php
    namespace MyVendor\KeSearchHooks;

    /**
     * Class AdditionalIndexerFields
     * @package MyVendor\KeSearchHooks
     */
    class AdditionalIndexerFields {
        public function registerAdditionalFields(&$additionalFields) {
            $additionalFields[] = 'mysorting';
        }
    }

Your new database field will automatically appear in the backend selection of sorting fields!

You will have to add a locallang-value to your typoscript setup:

.. code-block:: none

	plugin.tx_kesearch_pi2 {
		_LOCAL_LANG.default {
			orderlink_mysorting = My sort field label
		}
	}

