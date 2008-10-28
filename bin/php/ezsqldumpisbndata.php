#!/usr/bin/env php
<?php
//
// Created on: <30-May-2007 16:19:12 bjorn>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ Publish
// SOFTWARE RELEASE: 4.1.x
// COPYRIGHT NOTICE: Copyright (C) 1999-2008 eZ Systems AS
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//

/*! \file writeisbn13schema.php
*/

/*!
  \class Writeisbn13schema writeisbn13schema.php
  \brief The class Writeisbn13schema does

*/

require 'autoload.php';

$fileNameDba = 'db_data.dba';
$fileNameSql = 'cleandata.sql';
$stdOutSQL = null;
$stdOutDBA = null;

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "eZ Publish SQL Isbn data dump\n\n" .
                                                        "Dump sql data to file or standard output from the tables:\n" .
                                                        "  ezisbn_group\n" .
                                                        "  ezisbn_group_range\n" .
                                                        "  ezisbn_registrant_range\n\n" .
                                                        "Default is file, wich will be written to:\n" .
                                                        "  kernel/classes/datatypes/ezisbn/sql/<database>/cleandata.sql\n" .
                                                        "  kernel/classes/datatypes/ezisbn/share/db_data.dba\n\n" .
                                                        "Script can be runned as:\n" .
                                                        "php bin/php/ezsqldumpisbndata.php --stdout-sql\n" .
                                                        "                                  --stdout-dba\n" .
                                                        "                                  --filename-sql=customname.sql\n" .
                                                        "                                  --filename-dba=customname.dba" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( "[stdout-sql][stdout-dba][filename-sql:][filename-dba:]", "",
                                array( 'stdout-sql' => "Result of sql output will be printed to standard output instead of to file.",
                                       'stdout-dba' => "Result of dba output will be printed to standard output instead of to file.",
                                       'filename-sql' => "Custom name for the sql file. Will be stored in the directory: \n" .
                                                         "kernel/classes/datatypes/ezisbn/sql/<database>/",
                                       'filename-dba' => "Custom name for the dba file. Will be stored in the directory: \n" .
                                                         "kernel/classes/datatypes/ezisbn/share/" ) );
$script->initialize();
$db = eZDB::instance();
$dbSchema = eZDbSchema::instance( $db );

if ( isset( $options['filename-sql'] ) )
{
    $fileNameSql = $options['filename-sql'];
}

if ( isset( $options['filename-dba'] ) )
{
    $fileNameDba = $options['filename-dba'];
}

if ( isset( $options['stdout-sql'] ) !== null )
{
    $stdOutSQL = $options['stdout-sql'];
}

if ( isset( $options['stdout-dba'] ) !== null )
{
    $stdOutDBA = $options['stdout-dba'];
}

$tableType = 'MyISAM';
if ( $db->databaseName() != "mysql" )
{
    $tableType = null;
}

$includeSchema = false;
$includeData = true;

$dbschemaParameters = array( 'schema' => $includeSchema,
                             'data' => $includeData,
                             'format' => 'generic',
                             'meta_data' => null,
                             'table_type' => $tableType,
                             'table_charset' => null,
                             'compatible_sql' => true,
                             'allow_multi_insert' => null,
                             'diff_friendly' => null,
                             'table_include' => array( 'ezisbn_group',
                                                       'ezisbn_group_range',
                                                       'ezisbn_registrant_range' ) );

if ( $stdOutDBA === null and $stdOutSQL === null )
{
    $path = 'kernel/classes/datatypes/ezisbn/sql/' . $db->databaseName() . '/';
    $file = $path . $fileNameSql;
    $dbSchema->writeSQLSchemaFile( $file,
                                   $dbschemaParameters );
    $cli->output( 'Write "' . $file . '" to disk.' );

    $path = 'kernel/classes/datatypes/ezisbn/share/';
    $file = $path . $fileNameDba;

    // Add the table schema.
    $dbSchema->writeArraySchemaFile( $file,
                                     $dbschemaParameters );
    $cli->output( 'Write "' . $file . '" to disk.' );
}
else
{
    $filename = 'php://stdout';
    if ( $stdOutSQL !== null )
    {
        $dbSchema->writeSQLSchemaFile( $filename,
                                       $dbschemaParameters );
    }

    if ( $stdOutDBA !== null )
    {
        $dbschemaParameters['schema'] = true;
        $dbSchema->writeArraySchemaFile( $filename,
                                         $dbschemaParameters );
    }
}


$script->shutdown();
?>
