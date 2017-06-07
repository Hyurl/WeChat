<?php
return array (
  'user' => 
  array (
    'user_id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
    'user_name' => 'VARCHAR(255) DEFAULT NULL',
    'user_nickname' => 'VARCHAR(255) DEFAULT NULL',
    'user_realname' => 'VARCHAR(255) DEFAULT NULL',
    'user_password' => 'VARCHAR(255) DEFAULT NULL',
    'user_gender' => 'VARCHAR(255) DEFAULT NULL',
    'user_avatar' => 'VARCHAR(255) DEFAULT NULL',
    'user_identity' => 'VARCHAR(255) DEFAULT NULL',
    'user_company' => 'VARCHAR(255) DEFAULT NULL',
    'user_number' => 'VARCHAR(255) DEFAULT NULL',
    'user_email' => 'VARCHAR(255) DEFAULT NULL',
    'user_tel' => 'VARCHAR(255) DEFAULT NULL',
    'user_desc' => 'VARCHAR(255) DEFAULT NULL',
    'user_protect' => 'VARCHAR(255) DEFAULT NULL',
    'user_level' => 'INTEGER DEFAULT 1',
  ),
  'file' => 
  array (
    'file_id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
    'file_name' => 'VARCHAR(255) DEFAULT NULL',
    'file_src' => 'VARCHAR(255) DEFAULT NULL',
    'file_desc' => 'VARCHAR(255) DEFAULT NULL',
  ),
  'category' => 
  array (
    'category_id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
    'category_name' => 'VARCHAR(255) DEFAULT NULL',
    'category_alias' => 'VARCHAR(255) DEFAULT NULL',
    'category_desc' => 'VARCHAR(255) DEFAULT NULL',
    'category_parent' => 'INTEGER DEFAULT 0',
    'category_posts' => 'INTEGER DEFAULT 0',
    'category_children' => 'INTEGER DEFAULT 0',
  ),
  'post' => 
  array (
    'post_id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
    'post_title' => 'VARCHAR(255) DEFAULT NULL',
    'post_content' => 'VARCHAR(16383) DEFAULT NULL',
    'post_thumbnail' => 'VARCHAR(255) DEFAULT NULL',
    'post_comments' => 'INTEGER DEFAULT 0',
    'post_time' => 'INTEGER DEFAULT 0',
    'post_link' => 'VARCHAR(255) DEFAULT NULL',
    'category_id' => 'INTEGER DEFAULT NULL',
    'user_id' => 'INTEGER DEFAULT NULL',
  ),
  'comment' => 
  array (
    'comment_id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
    'comment_content' => 'VARCHAR(1023) DEFAULT NULL',
    'comment_time' => 'INTEGER DEFAULT 0',
    'comment_parent' => 'INTEGER DEFAULT 0',
    'comment_replies' => 'INTEGER DEFAULT 0',
    'post_id' => 'INTEGER DEFAULT NULL',
    'user_id' => 'INTEGER DEFAULT NULL',
  ),
);