<?php
//
// Copyright (C) 1998 - 2026 7x & Exponential Foundation. All rights reserved.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 (or any later version).
//

/*! \file job.php
    The state of a background run as JSON: status (starting, running, done, failed), the result and the last log lines.
*/

$jobID = (string)$Params['JobID'];
$state = eZSyndicationJob::isID( $jobID ) ? eZSyndicationJob::status( $jobID ) : false;

header( 'Content-Type: application/json; charset=utf-8' );
header( 'Cache-Control: no-store' );
if ( !$state )
{
    header( 'HTTP/1.1 404 Not Found' );
    echo json_encode( array( 'status' => 'unknown' ) );
}
else
{
    echo json_encode( $state );
}
eZExecution::cleanExit();

?>
