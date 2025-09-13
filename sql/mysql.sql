
-- Syndication setup tables

CREATE TABLE ezsyndication_feed_cache (
  id int(11) NOT NULL auto_increment,
  feed_id int(11) NOT NULL default '0',
  cache_ts int(11) NOT NULL default '0',
  PRIMARY KEY( id )
);

CREATE TABLE ezsyndication_feed (
  id int(11) NOT NULL auto_increment,
  status int(11) NOT NULL default '0',
  creator_id int(11) NOT NULL default '0',
  created_ts int(11) NOT NULL default '0',
  enabled int(1)  NOT NULL default '0', 
  object_expiry_time int(11) NOT NULL default '0',
  cache_timeout int(11) NOT NULL default '0',
  force_cronjob_cache int(11) NOT NULL default '0',
  object_count int(11) NOT NULL default '0',
  name varchar(255) default '',
  identifier varchar(255) default '',
  private_comment longtext default '',
  public_comment longtext default '',
  PRIMARY KEY( id, status ) );

CREATE TABLE ezsyndication_feed_source (
  id int(11) NOT NULL auto_increment,
  status int(11) NOT NULL default '0',
  feed_id int(11) NOT NULL default '0',
  node_id int(11) NOT NULL default '0',
  type int(11) NOT NULL default '0',
  PRIMARY KEY( id, status ) );

CREATE TABLE ezsyndication_feed_source_filter (
  id int(11) NOT NULL auto_increment,
  status int(11) NOT NULL default '0',
  feed_source_id int(11) NOT NULL default '0',
  filter_id int(11) NOT NULL default '0',
  PRIMARY KEY( id, status )
);

CREATE TABLE ezsyndication_filter (
  id int(11) NOT NULL auto_increment,  
  status int(11) NOT NULL default '0',
  data_int_1 int(11) NOT NULL default '0',
  data_int_2 int(11) NOT NULL default '0',
  data_int_3 int(11) NOT NULL default '0',
  type varchar(255) NOT NULL default '',
  class_name varchar(255) NOT NULL default '',
  data_text_1 longtext NOT NULL default '',
  data_text_2 longtext NOT NULL default '',
  data_text_3 longtext NOT NULL default '',
  PRIMARY KEY( id, status )
);

CREATE TABLE ezsyndication_feed_item (
  id int(11) NOT NULL auto_increment,
  feed_id int(11) NOT NULL default '0',
  host_id varchar(255) NOT NULL default '',
  depth int(11) NOT NULL default '0',
  remote_id varchar(255) NOT NULL default '',
  contentobject_version int(11) NOT NULL default '0',
  modified int(11) NOT NULL default '0',
  options longtext NOT NULL default '',
  PRIMARY KEY( id ) );

CREATE TABLE ezsyndication_feed_item_export (
  id int(11) NOT NULL auto_increment,
  feed_id int(11) NOT NULL default '0',
  host_id varchar(255) NOT NULL default '',
  depth int(11) NOT NULL default '0',
  remote_id varchar(255) NOT NULL default '',
  contentobject_version int(11) NOT NULL default '0',
  modified int(11) NOT NULL default '0',
  options longtext NOT NULL default '',
  PRIMARY KEY( id ) );

CREATE TABLE ezsyndication_feed_item_status (
  id int(11) NOT NULL auto_increment,
  feed_item_id int(11) NOT NULL default '0',
  modified int(11) NOT NULL default '0',
  created int(11) NOT NULL default '0',
  published int(11) NOT NULL default '0',
  options longtext NOT NULL default '',
  status int(11) NOT NULL default '0',
  PRIMARY KEY( id ) );

-- Syndication import tables

CREATE TABLE ezsyndication_import (
  id int(11) NOT NULL auto_increment,
  status int(11) NOT NULL default '0',
  creator_id int(11) NOT NULL default '0',
  created_ts int(11) NOT NULL default '0',
  enabled int(11) NOT NULL default '0',
  feed_id int(11) NOT NULL default '0',
  object_count int(11) NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  comment longtext NOT NULL default '',
  server longtext default '',
  host_id varchar(255) NOT NULL default '',
  placement_node_id int(11) NOT NULL default '0',
  related_node_id int(11) NOT NULL default '0',
  options longtext NOT NULL default '',
  PRIMARY KEY( id, status )
);

CREATE TABLE ezsyndication_import_filter (
  id int(11) NOT NULL auto_increment,
  status int(11) NOT NULL default '0',
  import_id int(11) NOT NULL default '0',
  filter_id int(11) NOT NULL default '0',
  PRIMARY KEY( id, status )
);

-- Table keeping track of import/exported data.

CREATE TABLE ezx_ezpnet_soap_log (
  id int(11) NOT NULL auto_increment,
  remote_host varchar(255) NOT NULL default '',
  remote_value int(11) NOT NULL default '0',
  local_value varchar(255) NOT NULL default '',
  class_name varchar(255) NOT NULL default '',
  key_name varchar(255) NOT NULL default '',
  timestamp int(11) NOT NULL default '0',
  remote_modified int(11) NOT NULL default '0',
  extended_filter varchar(255) NOT NULL default '',
  PRIMARY KEY ( id ),
  KEY idx_remote_val_host ( remote_host, remote_value ),
  KEY idx_remote_val ( remote_value ),
  KEY idx_remote_modified ( remote_modified ) );

-- CREATE TABLE ezsyndicate_export_event (
--  id int(11) NOT NULL auto_increment,
-- feed_id int(11) NOT NULL default '0',
--  subscriber_id int(11) NOT NULL default '0',
--  timestamp int(11) NOT NULL default '0',
--  PRIMARY KEY( id )
-- );

-- CREATE TABLE ezsyndicate_export_event_object (
--  id int(11) NOT NULL auto_increment,
-- export_event_id int(11) NOT NULL default '0',
--  timestamp int(11) NOT NULL default '0',
--  object_id int(11) NOT NULL default '0',
--  locat_path varchar(255) NOT NULL default '',
--  remote_id int(11) NOT NULL default '0',
--  PRIMARY KEY( id )
-- );
