<?php
//
// Definition of eZSyndicationInstaller class
//
// Copyright (C) 1998 - 2026 7x & Exponential Foundation. All rights reserved.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 (or any later version).
//

/*!
  \class eZSyndicationInstaller ezsyndicationinstaller.php
  \brief Creates the extension's tables from share/db_schema.dba on any supported database and reports
         the state of the installation for the dashboard.
*/

class eZSyndicationInstaller
{
    /*!
     \return array of table names the extension needs
    */
    static function tableNames()
    {
        return array( 'ezsyndication_feed', 'ezsyndication_feed_source', 'ezsyndication_feed_source_filter',
                      'ezsyndication_filter', 'ezsyndication_feed_item', 'ezsyndication_feed_item_export',
                      'ezsyndication_feed_item_status', 'ezsyndication_feed_cache', 'ezsyndication_import',
                      'ezsyndication_import_filter', 'ezx_ezpnet_soap_log' );
    }

    /*!
     \return the schema array of share/db_schema.dba, false when it cannot be read
    */
    static function schema()
    {
        $file = 'extension/syndication/share/db_schema.dba';
        if ( !file_exists( $file ) )
        {
            return false;
        }
        $schema = eZDbSchema::readArray( $file );
        return is_array( $schema ) ? $schema : false;
    }

    /*!
     \return array of the table names that do not exist yet
    */
    static function missingTables( $db = null )
    {
        $db = $db ? $db : eZDB::instance();
        $list = $db->relationList();
        if ( !is_array( $list ) )
        {
            $list = array_keys( (array)$db->eZTableList() );
        }
        $existing = array_map( 'strtolower', $list );
        $missing = array();
        foreach ( self::tableNames() as $table )
        {
            if ( !in_array( $table, $existing, true ) )
            {
                $missing[] = $table;
            }
        }
        return $missing;
    }

    /*!
     Creates the missing tables. Existing tables and their rows are left alone.

     \param $messages receives one line per step
     \return true when every table exists afterwards
    */
    static function install( $db = null, &$messages = array() )
    {
        $db = $db ? $db : eZDB::instance();
        $missing = self::missingTables( $db );
        if ( !$missing )
        {
            $messages[] = 'The tables exist already.';
            return true;
        }
        $schema = self::schema();
        if ( !$schema )
        {
            $messages[] = 'share/db_schema.dba cannot be read.';
            return false;
        }
        $schema = array_intersect_key( $schema, array_flip( $missing ) );
        $handler = eZDbSchema::instance( array( 'instance' => $db, 'schema' => $schema ) );
        if ( !$handler )
        {
            $messages[] = 'This database type has no schema handler.';
            return false;
        }
        $ok = $handler->insertSchema( array( 'schema' => true, 'data' => false,
                                             'table_type' => $db->databaseName() == 'mysql' ? 'innodb' : '' ) );
        $messages[] = ( $ok ? 'Created ' : 'Could not create ' ) . implode( ', ', $missing );
        return $ok && !self::missingTables( $db );
    }
}

?>
