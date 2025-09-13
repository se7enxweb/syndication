<?php
//
// Definition of eZImportEditFeed class
//
// Created on: <15-Nov-2004 05:04:53 hovik>
//
// Copyright (C) 1999-2008 eZ Systems AS. All rights reserved.
//
// This source file is part of the eZ Publish (tm) Open Source Content
// Management System.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE.GPL included in
// the packaging of this file.
//
// Licencees holding valid "eZ Publish professional licences" may use this
// file in accordance with the "eZ Publish professional licence" Agreement
// provided with the Software.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "eZ Publish professional licence" is available at
// http://ez.no/products/licences/professional/. For pricing of this licence
// please contact us via e-mail to licence@ez.no. Further contact
// information is available at http://ez.no/home/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

/*! \file ezimporteditfeed.php
*/

/*!
  \class eZImportditFeed ezimporteditfeed.php
  \brief The class eZImportEditFeed does

*/

class eZImportEditFeed extends eZImportEditWizard
{
    /*!
     Constructor
    */
    function __construct( &$tpl, &$module, $storageName = false )
    {
        parent::__construct( $tpl, $module, $storageName );
    }

    /*!
     \reimp
    */
    function process()
    {
        $syndicationImport = $this->attribute( 'syndication_import' );

        $url = parse_url( $syndicationImport->attribute( 'server' ) );

        if ( !isset( $url['scheme'] ) ||
             !isset( $url['host'] ) )
        {
            $this->WarningList[] = ezpI18n::tr( 'design/standard/syndication/edit', 'Warning, server is not valid URL !' );
        }

        $client = new eZSOAPClient( $url['host'],
                                    isset( $url['path'] ) ? $url['path'] : '/',
                                    isset( $url['port'] ) ? $url['port'] : 80,
                                    isset( $url['scheme'] ) && $url['scheme'] === 'https' );
        $request = new eZSOAPRequest( "fetchSyndicationFeedList",
                                      "http://ez.no/syndication" );
        $response = $client->send( $request );

        if ( $response->faultCode() )
        {
            $this->WarningList[] = ezpI18n::tr( 'design/standard/syndication/edit',
                                           'Warning, SOAP feed list request to "%server" did not return a valid result',
                                           '',
                                           array( '%server' => $server ) );
        }

        $dom = new DOMDocument( '1.0', 'utf-8' );
        $dom->preserveWhiteSpace = false;
        eZDebug::writeDebug( $response->value() );
        $success = $dom->loadXML( $response->value() );

        if ( $success )
        {
            $this->FeedList = $this->createArrayFromDOMNode( $dom->documentElement );
        }

        $request = new eZSOAPRequest( "hostID", "http://ez.no/syndication" );
        $response = $client->send( $request );

        $syndicationImport = $this->attribute( 'syndication_import' );
        $syndicationImport->setAttribute( 'host_id', $response->value() );
        $syndicationImport->sync();

        return eZImportEditWizard::process();
    }

    /*!
     Correct xml to array conversion.
     */
    function createArrayFromDOMNode( $domNode )
    {
        if ( !$domNode )
        {
            return null;
        }

        $retArray = array();
        foreach ( $domNode->childNodes as $childNode )
        {
            if ( $childNode instanceof DOMElement )
            {
                if ( !isset( $retArray[$childNode->tagName] ) )
                {
                    $retArray[$childNode->tagName] = array();
                }

                $retArray[$childNode->tagName][] = $this->createArrayFromDOMNode( $childNode );
            }
        }

        foreach ( $domNode->attributes as $attribute )
        {
            $retArray[$attribute->name] = $attribute->value;
        }

        return $retArray;
    }

    /*!
     \reimp
    */
    function postCheck()
    {
        if ( $this->HTTP->hasPostVariable( 'FeedID' ) )
        {
            $syndicationImport = $this->attribute( 'syndication_import' );
            $syndicationImport->setAttribute( 'feed_id', $this->HTTP->postVariable( 'FeedID' ) );
            $syndicationImport->sync();

            return true;
        }

        $this->WarningList[] = ezpI18n::tr( 'design/standard/syndication/edit',
                                       'No Syndication feed selected.' );
        return false;
    }

    /*!
     \reimp
    */
    function attributes()
    {
        return array_merge( eZImportEditWizard::attributes(), array( 'feed_list' ) );
    }

    /*!
     \reimp
    */
    function attribute( $attr )
    {
        $result = false;
        switch( $attr )
        {
            case 'feed_list':
            {
                $result = $this->FeedList;
            } break;

            default:
            {
                $result = eZImportEditWizard::attribute( $attr );
            } break;
        }

        return $result;
    }

    var $FeedList = array();
}

?>
