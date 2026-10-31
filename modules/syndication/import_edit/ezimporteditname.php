<?php
//
// Definition of eZImportEditName class
//
// Created on: <14-Nov-2004 22:33:26 hovik>
//
// Copyright (C) 1998 - 2026 Exponential Foundation and 7x. All rights reserved.
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

/*! \file ezimporteditname.php
*/

/*!
  \class eZImportEditName ezimporteditname.php
  \brief The class eZImportEditName does

*/

class eZImportEditName extends eZImportEditWizard
{
    /*!
     Constructor
    */
    function __construct( &$tpl, &$module, $storageName = false )
    {
        parent::__construct( $tpl, $module, $storageName );
    }

    /*
     \reimp
    */
    function postCheck()
    {
        $valid = true;
        $syndicationImport = $this->attribute( 'syndication_import' );
        $name = '';
        $server = '';

        if ( $this->HTTP->hasPostVariable( 'Name' ) )
        {
            $name = trim( $this->HTTP->postVariable( 'Name' ) );
        }
        $syndicationImport->setAttribute( 'name', $name );
        if ( strlen( $name ) == 0 )
        {
            $this->WarningList[] = ezpI18n::tr( 'extension/syndication', 'Please enter valid Name' );
            $valid = false;
        }

        if ( $this->HTTP->hasPostVariable( 'Server' ) )
        {
            $server = $this->HTTP->postVariable( 'Server' );
        }
        $syndicationImport->setAttribute( 'server', $server );

        $optionArray = $syndicationImport->attribute( 'option_array' );
        if ( $this->HTTP->hasPostVariable( 'Login' ) )
        {
            $optionArray['login'] = trim( $this->HTTP->postVariable( 'Login' ) );
        }
        if ( $this->HTTP->hasPostVariable( 'Password' ) && $this->HTTP->postVariable( 'Password' ) !== '' )
        {
            $optionArray['password'] = $this->HTTP->postVariable( 'Password' );
        }
        $syndicationImport->setAttribute( 'options', serialize( $optionArray ) );

        // Keep what was typed, also when a check below fails.
        $syndicationImport->sync();

        $url = parse_url( $server );

        if ( !isset( $url['scheme'] ) ||
             !isset( $url['host'] ) )
        {
            $this->WarningList[] = ezpI18n::tr( 'extension/syndication', 'Warning, server is not valid URL !' );
            return false;
        }

        // Get syndication feed list
        $client = $syndicationImport->attribute( 'soap_client' );
        $request = new eZSOAPRequest( "fetchSyndicationFeedList", "http://ez.no/syndication" );
        $response = $client->send( $request );

        // An exporting site on Exponential 6.0.14 or older has no index_soap.php (its SOAP server is soap.php), so an
        // address that names index_soap.php ends at that site's index.php. Try soap.php once, and keep that address
        // when it answers with a feed list.
        $fallbackServer = eZSyndicationUI::soapFallbackServer( $server );
        if ( $fallbackServer !== false && !self::isFeedListResponse( $response ) )
        {
            $syndicationImport->setAttribute( 'server', $fallbackServer );
            $fallbackClient = $syndicationImport->attribute( 'soap_client' );
            $fallbackResponse = $fallbackClient->send( $request );
            if ( self::isFeedListResponse( $fallbackResponse ) )
            {
                eZDebug::writeNotice( 'No SOAP server answered at index_soap.php, soap.php did (an exporting site on '
                                      . 'Exponential 6.0.14 or older): the import keeps the address with soap.php',
                                      __METHOD__ );
                $server = $fallbackServer;
                $client = $fallbackClient;
                $response = $fallbackResponse;
                $syndicationImport->sync();
            }
            else
            {
                $syndicationImport->setAttribute( 'server', $server );
            }
        }

	if( !is_int( $response ) )
	{
           $responseCode = $response->faultCode();
           $responseValue = $response->value();
           $this->TPL->setVariable( 'response', $responseValue );
	}
	else
	{
	   $responseCode = false;
           $responseValue = false;
	}

        if ( is_int( $response ) || $responseCode !== false || $responseValue === false || $responseValue === '' )
        {
            $this->WarningList[] = ezpI18n::tr( 'extension/syndication',
                                           'Warning, SOAP feed list request to "%server" did not return a valid result',
                                           '',
                                           array( '%server' => $server ) );
            return false;
        }

        // Get server HostID
        $request = new eZSOAPRequest( "hostID", "http://ez.no/syndication" );
        $response = $client->send( $request );

        if ( is_int( $response ) ||
             ( !is_int( $response ) && ( $response->faultCode() !== false || $response->value() === false || $response->value() === '' ) ) )
        {
            $this->WarningList[] = ezpI18n::tr( 'extension/syndication',
                                           'Warning, SOAP feed list request to "%server" did not return a valid result #2',
                                           '',
                                           array( '%server' => $server ) );
            return false;
        }
        $syndicationImport->setAttribute( 'host_id', $response->value() );
        $syndicationImport->sync();

        return $valid;
    }

    /*!
     \static
     Whether the answer to fetchSyndicationFeedList is a feed list: a SOAP response without a fault and with a value.
     eZSOAPClient::send() returns an int when the request failed.
    */
    static function isFeedListResponse( $response )
    {
        return is_object( $response ) && $response->faultCode() === false
               && $response->value() !== false && $response->value() !== '';
    }
}

?>