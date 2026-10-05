<?php
//
// Copyright (C) 1998 - 2026 7x & Exponential Foundation. All rights reserved.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 (or any later version).
//

/*!
  \class eZSyndicationDashboard ezsyndicationdashboard.php
  \brief What the start page of the module shows: what is exported, what is imported, the last runs and the
         problems found.
*/

class eZSyndicationDashboard
{
    /*!
     \static
     \return array with the keys 'tables_missing', 'feeds', 'imports', 'items', 'runs', 'problems', 'soap'
    */
    static function summary()
    {
        $summary = array( 'tables_missing' => eZSyndicationInstaller::missingTables(),
                          'feeds' => array(), 'imports' => array(), 'items' => array(),
                          'runs' => array(), 'problems' => array(), 'soap' => array() );

        $soapIni = eZINI::instance( 'soap.ini' );
        $summary['soap'] = array( 'enabled' => $soapIni->variable( 'GeneralSettings', 'EnableSOAP' ) == 'true',
                                  'extension' => in_array( 'syndication', (array)$soapIni->variable( 'ExtensionSettings', 'SOAPExtensions' ), true ),
                                  'url' => eZSys::serverURL() . eZSys::wwwDir() . '/soap.php' );

        if ( $summary['tables_missing'] )
        {
            $summary['problems'][] = array( 'level' => 'error', 'code' => 'tables',
                                            'text' => ezpI18n::tr( 'extension/syndication', 'The tables of the extension are not created yet: %tables.', null,
                                                                   array( '%tables' => implode( ', ', $summary['tables_missing'] ) ) ),
                                            'url' => false );
            return $summary;
        }

        $feeds = eZPersistentObject::fetchObjectList( eZSyndicationFeed::definition(), null,
                                                      array( 'status' => eZSyndicationFeed::STATUS_PUBLISHED ) );
        $imports = eZPersistentObject::fetchObjectList( eZSyndicationImport::definition(), null,
                                                        array( 'status' => eZSyndicationImport::STATUS_PUBLISHED ) );
        $feeds = is_array( $feeds ) ? $feeds : array();
        $imports = is_array( $imports ) ? $imports : array();

        $db = eZDB::instance();
        $sourceCount = 0;
        $feedsWithoutSource = array();
        foreach ( $feeds as $feed )
        {
            $count = count( $feed->sourceList( eZSyndicationFeed::STATUS_PUBLISHED ) );
            $sourceCount += $count;
            if ( !$count )
            {
                $feedsWithoutSource[] = $feed;
            }
        }
        $exported = $db->arrayQuery( 'SELECT COUNT(*) AS c FROM ezsyndication_feed_item_export' );
        $summary['feeds'] = array( 'total' => count( $feeds ),
                                   'enabled' => count( array_filter( $feeds, function ( $f ) { return (bool)$f->attribute( 'enabled' ); } ) ),
                                   'sources' => $sourceCount,
                                   'items' => $exported ? (int)$exported[0]['c'] : 0 );
        $summary['imports'] = array( 'total' => count( $imports ),
                                     'enabled' => count( array_filter( $imports, function ( $i ) { return (bool)$i->attribute( 'enabled' ); } ) ) );

        $rows = $db->arrayQuery( 'SELECT status.status AS s, COUNT(*) AS c FROM ezsyndication_feed_item_status status GROUP BY status.status' );
        $map = array( eZSyndicationFeedItemStatus::STATUS_NONE => 'none', eZSyndicationFeedItemStatus::STATUS_PENDING => 'pending',
                      eZSyndicationFeedItemStatus::STATUS_INSTALLING => 'installing', eZSyndicationFeedItemStatus::STATUS_INSTALLED => 'installed',
                      eZSyndicationFeedItemStatus::STATUS_FAILED => 'failed' );
        $items = array( 'none' => 0, 'pending' => 0, 'installing' => 0, 'installed' => 0, 'failed' => 0, 'other' => 0 );
        foreach ( (array)$rows as $row )
        {
            $key = isset( $map[(int)$row['s']] ) ? $map[(int)$row['s']] : 'other';
            $items[$key] += (int)$row['c'];
        }
        $summary['items'] = $items;

        $summary['runs'] = array( 'export' => eZSyndicationRunner::lastRun( eZSyndicationRunner::LAST_EXPORT ),
                                  'import' => eZSyndicationRunner::lastRun( eZSyndicationRunner::LAST_IMPORT ) );

        // problems
        $problems =& $summary['problems'];
        if ( $feeds && !$summary['soap']['enabled'] )
        {
            $problems[] = array( 'level' => 'warning', 'code' => 'soap',
                                 'text' => ezpI18n::tr( 'extension/syndication', 'SOAP is disabled (soap.ini, EnableSOAP): other sites cannot fetch the feeds of this site.' ), 'url' => false );
        }
        foreach ( $feedsWithoutSource as $feed )
        {
            $problems[] = array( 'level' => 'warning', 'code' => 'feed_source',
                                 'text' => ezpI18n::tr( 'extension/syndication', 'The feed "%name" has no source, so nothing is exported.', null, array( '%name' => $feed->attribute( 'name' ) ) ),
                                 'url' => 'syndication/edit/' . $feed->attribute( 'id' ) );
        }
        foreach ( $feeds as $feed )
        {
            if ( !$feed->attribute( 'enabled' ) )
            {
                $problems[] = array( 'level' => 'info', 'code' => 'feed_disabled',
                                     'text' => ezpI18n::tr( 'extension/syndication', 'The feed "%name" is not active; the cronjob skips it.', null, array( '%name' => $feed->attribute( 'name' ) ) ),
                                     'url' => 'syndication/edit/' . $feed->attribute( 'id' ) );
            }
        }
        foreach ( $imports as $import )
        {
            $url = parse_url( (string)$import->attribute( 'server' ) );
            $id = $import->attribute( 'id' );
            if ( !isset( $url['scheme'] ) || !isset( $url['host'] ) )
            {
                $problems[] = array( 'level' => 'error', 'code' => 'import_server',
                                     'text' => ezpI18n::tr( 'extension/syndication', 'The import "%name" has no valid server address.', null, array( '%name' => $import->attribute( 'name' ) ) ),
                                     'url' => 'syndication/import_edit/' . $id );
            }
            if ( !$import->attribute( 'feed_id' ) )
            {
                $problems[] = array( 'level' => 'warning', 'code' => 'import_feed',
                                     'text' => ezpI18n::tr( 'extension/syndication', 'The import "%name" has no feed selected.', null, array( '%name' => $import->attribute( 'name' ) ) ),
                                     'url' => 'syndication/import_edit/' . $id );
            }
            if ( !$import->attribute( 'placement_node_id' ) )
            {
                $problems[] = array( 'level' => 'warning', 'code' => 'import_placement',
                                     'text' => ezpI18n::tr( 'extension/syndication', 'The import "%name" has no location for the imported content.', null, array( '%name' => $import->attribute( 'name' ) ) ),
                                     'url' => 'syndication/import_edit/' . $id );
            }
        }
        if ( $items['failed'] )
        {
            $problems[] = array( 'level' => 'error', 'code' => 'failed',
                                 'text' => ezpI18n::tr( 'extension/syndication', '%count imported items failed; the next import run tries them again.', null, array( '%count' => $items['failed'] ) ),
                                 'url' => 'syndication/import_list' );
        }
        if ( $feeds && !$summary['runs']['export'] )
        {
            $problems[] = array( 'level' => 'info', 'code' => 'no_export_run',
                                 'text' => ezpI18n::tr( 'extension/syndication', 'The export has not run yet. Put "php runcronjobs.php export_feed" in cron.' ), 'url' => false );
        }
        if ( $imports && !$summary['runs']['import'] )
        {
            $problems[] = array( 'level' => 'info', 'code' => 'no_import_run',
                                 'text' => ezpI18n::tr( 'extension/syndication', 'The import has not run yet. Put "php runcronjobs.php import_feed" in cron.' ), 'url' => false );
        }
        unset( $problems );
        return $summary;
    }
}

?>
