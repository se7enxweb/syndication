<?php
//
// Copyright (C) 1998 - 2026 7x & Exponential Foundation. All rights reserved.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 (or any later version).
//

/*!
  \class eZSyndicationRunner ezsyndicationrunner.php
  \brief The work of the two cronjob parts as methods, so that the cronjobs and the admin's "run now" actions
         do the same thing, and the time and result of the last run of each part.
*/

class eZSyndicationRunner
{
    const LAST_EXPORT = 'syndication_last_export';
    const LAST_IMPORT = 'syndication_last_import';

    /*!
     \static
     Remember the time and the result of a run.

     \param $key LAST_EXPORT or LAST_IMPORT
     \param $result array of numbers and an optional 'error' text
     \param $by 'cron' or the name of the user
    */
    static function recordRun( $key, $result, $by )
    {
        $result['time'] = time();
        $result['by'] = (string)$by;
        $json = json_encode( $result );
        $data = eZSiteData::fetchByName( $key );
        if ( !$data )
        {
            $data = eZSiteData::create( $key, $json );
        }
        else
        {
            $data->setAttribute( 'value', $json );
        }
        $data->store();
    }

    /*!
     \static
     \return array of the last run ( 'time', 'by', ... ), false when there is none
    */
    static function lastRun( $key )
    {
        $data = eZSiteData::fetchByName( $key );
        if ( !$data )
        {
            return false;
        }
        $result = json_decode( (string)$data->attribute( 'value' ), true );
        return is_array( $result ) && isset( $result['time'] ) ? $result : false;
    }

    /*!
     \static
     Write the cache of the objects of one feed.

     \param $feed eZSyndicationFeed
     \param $cli eZCLI or false, receives the progress lines
     \param $maxObjects stop after this many written objects, 0 for all
     \param $dryRun count what would be written, write nothing
     \return array( 'sources', 'checked', 'generated', 'more' ), 'more' is true when it stopped early
    */
    static function exportFeed( $feed, $cli = false, $maxObjects = 0, $dryRun = false )
    {
        $result = array( 'sources' => 0, 'checked' => 0, 'generated' => 0, 'more' => false );
        $feedID = $feed->attribute( 'id' );
        $cacheManager = eZSyndicationFeedCacheManager::initialize( $feedID );
        foreach ( $feed->sourceList() as $exportSource )
        {
            $exportSourceNode = $exportSource->attribute( 'node' );
            if ( !$exportSourceNode )
            {
                if ( $cli )
                {
                    $cli->output( '  Skipping source: node ' . $exportSource->attribute( 'node_id' ) . ' does not exist' );
                }
                continue;
            }
            ++$result['sources'];
            if ( $cli )
            {
                $cli->output( '  Processing source : ' . $exportSourceNode->attribute( 'name' ) );
            }
            foreach ( $exportSource->remoteIDArray() as $remoteID => $contentNode )
            {
                ++$result['checked'];
                $contentObject = $contentNode->attribute( 'object' );
                $cacheTS = $cacheManager->objectCacheTS( $remoteID );
                $cacheInfo = $cacheManager->cacheInfo( $remoteID );
                $generate = !$cacheTS || $cacheTS < $contentObject->attribute( 'modified' ) ||
                            ( $cacheInfo && $cacheInfo['is_related'] );
                if ( !$generate )
                {
                    continue;
                }
                if ( $maxObjects && $result['generated'] >= $maxObjects )
                {
                    $result['more'] = true;
                    break 2;
                }
                if ( !$dryRun )
                {
                    $cacheManager->cacheObject( $remoteID, $exportSource );
                    eZSyndicationFeedItemExport::update( $feedID, $contentNode );
                }
                ++$result['generated'];
            }
        }
        if ( !$dryRun )
        {
            $cacheManager->storeInfo();
        }
        return $result;
    }

    /*!
     \static
     Fetch the item list of one import from the exporting site.

     \return array( 'ok' => bool, 'new' => number of new or changed items, 'error' => text )
    */
    static function fetchImport( $import )
    {
        $url = parse_url( (string)$import->attribute( 'server' ) );
        if ( !isset( $url['scheme'] ) || !isset( $url['host'] ) )
        {
            return array( 'ok' => false, 'new' => 0, 'error' => 'The server address is not a valid URL.' );
        }
        $db = eZDB::instance();
        $db->begin();
        $count = $import->fetchNewItems();
        if ( $count === false )
        {
            $db->rollback();
            return array( 'ok' => false, 'new' => 0, 'error' => 'The server did not answer with a feed item list.' );
        }
        $db->commit();
        return array( 'ok' => true, 'new' => (int)$count, 'error' => '' );
    }

    /*!
     \static
     Import the pending and failed items of one import.

     \return array( 'imported', 'failed', 'objects' )
    */
    static function importPending( $import, $limit = 10, $cli = false )
    {
        $result = array( 'imported' => 0, 'failed' => 0, 'objects' => 0 );
        $db = eZDB::instance();
        $statusList = $import->fetchItemStatusList( array( eZSyndicationFeedItemStatus::STATUS_PENDING,
                                                           eZSyndicationFeedItemStatus::STATUS_FAILED ), 0, $limit );
        foreach ( (array)$statusList as $feedItemStatus )
        {
            $feedItem = $feedItemStatus->attribute( 'feed_item' );
            if ( !$feedItem )
            {
                ++$result['failed'];
                continue;
            }
            $db->begin();
            if ( $feedItem->import() )
            {
                $objects = $feedItem->importObjectCount();
                $feedItem->postImport();
                $db->commit();
                ++$result['imported'];
                $result['objects'] += (int)$objects;
                if ( $cli )
                {
                    $cli->output( 'Imported object: ' . $feedItem->attribute( 'remote_id' ) . ' ( ' . $objects . ' objects )' );
                }
            }
            else
            {
                $db->rollback();
                ++$result['failed'];
                if ( $cli )
                {
                    $cli->output( 'ContentObject import failed: ' . $feedItem->attribute( 'remote_id' ) );
                }
            }
        }
        return $result;
    }

    /*!
     \static
     Run as the user of [Syndication] CronUser in syndication.ini.
    */
    static function loginCronUser()
    {
        $userID = eZINI::instance( 'syndication.ini' )->variable( 'Syndication', 'CronUser' );
        $user = eZUser::instance( $userID );
        eZUser::setCurrentlyLoggedInUser( $user, $userID );
        return $user;
    }

    /*!
     \static
     Take the lock of a run: one export and one import at a time, whoever starts them (cron, console, admin).

     \return the lock (keep it until the run is over), false when another run holds it
    */
    static function lock( $name )
    {
        $dir = eZSys::cacheDirectory() . '/syndication';
        if ( !is_dir( $dir ) )
        {
            eZDir::mkdir( $dir, false, true );
        }
        $handle = @fopen( $dir . '/' . preg_replace( '/[^a-z_]/', '', $name ) . '.lock', 'c' );
        if ( !$handle || !flock( $handle, LOCK_EX | LOCK_NB ) )
        {
            return false;
        }
        return $handle;
    }

    /*!
     \static
     Release the lock of a run.
    */
    static function unlock( $handle )
    {
        if ( $handle )
        {
            flock( $handle, LOCK_UN );
            fclose( $handle );
        }
    }

    /*!
     \static
     The export part: every active feed, or one. Used by the cronjob part, the console command and the
     admin's background job.

     \param $by 'cron', 'console' or a login name, stored with the run
     \return array( 'ok' => bool, 'locked' => bool, 'feeds', 'sources', 'checked', 'generated' )
    */
    static function runExport( $cli = false, $by = 'cron', $feedID = 0, $dryRun = false, $maxObjects = 0 )
    {
        $totals = array( 'ok' => true, 'locked' => false, 'feeds' => 0, 'sources' => 0, 'checked' => 0, 'generated' => 0 );
        $lock = $dryRun ? true : self::lock( 'export' );
        if ( !$lock )
        {
            $totals['ok'] = false;
            $totals['locked'] = true;
            return $totals;
        }
        $feeds = $feedID ? array_filter( array( eZSyndicationFeed::fetch( (int)$feedID ) ) )
                         : (array)eZPersistentObject::fetchObjectList( eZSyndicationFeed::definition(), null, array( 'status' => eZSyndicationFeed::STATUS_PUBLISHED ) );
        foreach ( $feeds as $feed )
        {
            if ( !$feed->attribute( 'enabled' ) && !$feedID )
            {
                if ( $cli )
                {
                    $cli->output( 'Skipping disabled export feed: ' . $feed->attribute( 'name' ) );
                }
                continue;
            }
            if ( $cli )
            {
                $cli->output( ( $dryRun ? 'Would process' : 'Processing' ) . ' export feed: ' . $feed->attribute( 'name' ) );
            }
            $run = self::exportFeed( $feed, $cli, $maxObjects, $dryRun );
            ++$totals['feeds'];
            $totals['sources'] += $run['sources'];
            $totals['checked'] += $run['checked'];
            $totals['generated'] += $run['generated'];
        }
        if ( !$dryRun )
        {
            self::recordRun( self::LAST_EXPORT, $totals, $by );
            self::unlock( $lock );
        }
        return $totals;
    }

    /*!
     \static
     The import part: fetch the item list of every active import (or one) and import what is pending.

     \param $dryRun fetch nothing, count the waiting items
     \param $mode 'both', 'fetch' (only the item list) or 'import' (only the waiting items)
     \return array( 'ok' => bool, 'locked' => bool, 'imports', 'new', 'imported', 'failed', 'waiting', 'errors' )
    */
    static function runImport( $cli = false, $by = 'cron', $importID = 0, $dryRun = false, $limit = 10, $mode = 'both' )
    {
        $totals = array( 'ok' => true, 'locked' => false, 'imports' => 0, 'new' => 0, 'imported' => 0, 'failed' => 0, 'waiting' => 0, 'errors' => array() );
        $lock = $dryRun ? true : self::lock( 'import' );
        if ( !$lock )
        {
            $totals['ok'] = false;
            $totals['locked'] = true;
            return $totals;
        }
        $imports = $importID ? array_filter( array( eZSyndicationImport::fetch( (int)$importID ) ) )
                             : (array)eZPersistentObject::fetchObjectList( eZSyndicationImport::definition(), null, array( 'status' => eZSyndicationImport::STATUS_PUBLISHED ) );
        foreach ( $imports as $import )
        {
            if ( !$import->attribute( 'enabled' ) && !$importID )
            {
                if ( $cli )
                {
                    $cli->output( 'Skipping disabled import: ' . $import->attribute( 'name' ) );
                }
                continue;
            }
            ++$totals['imports'];
            if ( $dryRun )
            {
                $totals['waiting'] += (int)$import->attribute( 'pending_count' ) + (int)$import->attribute( 'failed_count' );
                if ( $cli )
                {
                    $cli->output( 'Would fetch import: ' . $import->attribute( 'name' ) );
                }
                continue;
            }
            $fetch = $mode == 'import' ? array( 'ok' => true, 'new' => 0, 'error' => '' ) : self::fetchImport( $import );
            if ( $fetch['ok'] )
            {
                $totals['new'] += $fetch['new'];
            }
            else
            {
                $totals['ok'] = false;
                $totals['errors'][] = $import->attribute( 'name' ) . ': ' . $fetch['error'];
                if ( $cli )
                {
                    $cli->error( $import->attribute( 'name' ) . ': ' . $fetch['error'] );
                }
                continue;
            }
            if ( $mode != 'fetch' && $import->attribute( 'placement_node_id' ) )
            {
                $run = self::importPending( $import, $limit, $cli );
                $totals['imported'] += $run['imported'];
                $totals['failed'] += $run['failed'];
            }
        }
        if ( !$dryRun )
        {
            self::recordRun( self::LAST_IMPORT, $totals, $by );
            self::unlock( $lock );
        }
        return $totals;
    }
}

?>
