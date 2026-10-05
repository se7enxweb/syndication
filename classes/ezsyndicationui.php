<?php
//
// Copyright (C) 1998 - 2026 7x & Exponential Foundation. All rights reserved.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 (or any later version).
//

/*!
  \class eZSyndicationUI ezsyndicationui.php
  \brief Helpers shared by the module views: flash notices, list parameters (paging, sorting, filter) and
         the table state.
*/

class eZSyndicationUI
{
    const NOTICE_SESSION_KEY = 'SyndicationNotices';

    /*!
     \static
     Remember a message for the next page shown to this user.

     \param $type 'feedback', 'warning' or 'error' (the admin message classes)
    */
    static function notice( $type, $text )
    {
        $http = eZHTTPTool::instance();
        $list = $http->hasSessionVariable( self::NOTICE_SESSION_KEY ) ? $http->sessionVariable( self::NOTICE_SESSION_KEY ) : array();
        if ( !is_array( $list ) )
        {
            $list = array();
        }
        $list[] = array( 'type' => in_array( $type, array( 'feedback', 'warning', 'error' ), true ) ? $type : 'feedback',
                         'text' => (string)$text );
        $http->setSessionVariable( self::NOTICE_SESSION_KEY, $list );
    }

    /*!
     \static
     \return the remembered messages, which are forgotten at the same time
    */
    static function takeNotices()
    {
        $http = eZHTTPTool::instance();
        if ( !$http->hasSessionVariable( self::NOTICE_SESSION_KEY ) )
        {
            return array();
        }
        $list = $http->sessionVariable( self::NOTICE_SESSION_KEY );
        $http->removeSessionVariable( self::NOTICE_SESSION_KEY );
        return is_array( $list ) ? $list : array();
    }

    /*!
     \static
     The server address of an import without the user name and password it may carry.
    */
    static function safeServer( $import )
    {
        $server = (string)$import->attribute( 'server' );
        $url = parse_url( $server );
        if ( !$url || !isset( $url['host'] ) || ( !isset( $url['user'] ) && !isset( $url['pass'] ) ) )
        {
            return $server;
        }
        return ( isset( $url['scheme'] ) ? $url['scheme'] . '://' : '//' ) . $url['host'] .
               ( isset( $url['port'] ) ? ':' . $url['port'] : '' ) . ( isset( $url['path'] ) ? $url['path'] : '' ) .
               ( isset( $url['query'] ) ? '?' . $url['query'] : '' );
    }

    /*!
     \static
     A view that needs the tables sends the user to the start page, which offers to create them.

     \return the module's redirect result, false when the tables exist
    */
    static function redirectIfNotInstalled( $module )
    {
        if ( !eZSyndicationInstaller::missingTables() )
        {
            return false;
        }
        return $module->redirectToView( 'menu' );
    }

    /*!
     \static
     The parameters of a list view: (offset), (q), (sort), (order). Unknown or malformed values fall back
     to the defaults.

     \param $params the view's $Params array
     \param $sortFields the sort keys the view offers, the first one is the default
     \return array( 'offset', 'limit', 'q', 'sort', 'order' )
    */
    static function listParameters( $params, $sortFields, $limit = 15 )
    {
        $user = isset( $params['UserParameters'] ) && is_array( $params['UserParameters'] ) ? $params['UserParameters'] : array();
        $offset = isset( $params['Offset'] ) ? (int)$params['Offset'] : ( isset( $user['offset'] ) ? (int)$user['offset'] : 0 );
        $sort = isset( $user['sort'] ) && in_array( $user['sort'], $sortFields, true ) ? $user['sort'] : $sortFields[0];
        $order = isset( $user['order'] ) && $user['order'] === 'desc' ? 'desc' : 'asc';
        $q = isset( $user['q'] ) ? trim( rawurldecode( (string)$user['q'] ) ) : '';
        return array( 'offset' => max( 0, $offset ), 'limit' => (int)$limit, 'q' => mb_substr( $q, 0, 100 ),
                      'sort' => $sort, 'order' => $order );
    }

    /*!
     \static
     Filter, sort and cut a list of objects. The object attributes are read with attribute().

     \param $items array of objects
     \param $vp the result of listParameters()
     \param $searchFields attribute names the filter text is looked for in
     \param $total receives the number of items after the filter
     \return the items of the requested page
    */
    static function page( $items, $vp, $searchFields, &$total )
    {
        $items = is_array( $items ) ? $items : array();
        if ( $vp['q'] !== '' )
        {
            $needle = mb_strtolower( $vp['q'] );
            $items = array_values( array_filter( $items, function ( $item ) use ( $needle, $searchFields )
            {
                foreach ( $searchFields as $field )
                {
                    if ( mb_strpos( mb_strtolower( (string)$item->attribute( $field ) ), $needle ) !== false )
                    {
                        return true;
                    }
                }
                return false;
            } ) );
        }
        $sort = $vp['sort'];
        $desc = $vp['order'] === 'desc';
        usort( $items, function ( $a, $b ) use ( $sort, $desc )
        {
            $x = $a->attribute( $sort );
            $y = $b->attribute( $sort );
            $r = ( is_numeric( $x ) && is_numeric( $y ) ) ? ( $x <=> $y ) : strnatcasecmp( (string)$x, (string)$y );
            return $desc ? -$r : $r;
        } );
        $total = count( $items );
        return array_slice( $items, $vp['offset'], $vp['limit'] );
    }

    /*!
     \static
     The URL part for a list view with its parameters, for example 'syndication/list/(q)/news/(sort)/name'.
    */
    static function listURL( $view, $vp, $override = array() )
    {
        $vp = array_merge( $vp, $override );
        $url = 'syndication/' . $view;
        if ( $vp['offset'] )
        {
            $url .= '/(offset)/' . (int)$vp['offset'];
        }
        if ( $vp['q'] !== '' )
        {
            $url .= '/(q)/' . rawurlencode( $vp['q'] );
        }
        $url .= '/(sort)/' . $vp['sort'] . '/(order)/' . $vp['order'];
        return $url;
    }
}

?>
