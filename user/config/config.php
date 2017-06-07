<?php
return array (
  'mod' => 
  array (
    'installed' => false,
    'language' => 'zh-CN',
    'timezone' => 'Asia/Shanghai',
    'outputBuffering' => 0,
    'escapeTags' => '<script><style><iframe>',
    'pathinfoMode' => false,
    'jsonSerialize' => false,
    'database' => 
    array (
      'type' => 'sqlite',
      'host' => '',
      'name' => 'modphp',
      'port' => 0,
      'username' => '',
      'password' => '',
      'prefix' => 'mod_',
    ),
    'session' => 
    array (
      'name' => 'MODID',
      'maxLifeTime' => 604800,
      'savePath' => '',
    ),
    'template' => 
    array (
      'appPath' => 'app/',
      'savePath' => '',
      'compiler' => 
      array (
        'enable' => false,
        'extraTags' => 
        array (
          0 => 'import',
          1 => 'redirect',
        ),
        'savePath' => 'tmp/',
        'stripComment' => false,
      ),
    ),
    'SocketServer' => 
    array (
      'port' => 8080,
      'maxInput' => 8388608,
    ),
    'cliCharset' => '',
    'WebSocket' => 
    array (
      'port' => 8080,
      'maxThreads' => 1,
    ),
  ),
  'site' => 
  array (
    'name' => 'ModPHP',
    'URL' => '',
    'home' => 
    array (
      'template' => 'index.php',
      'staticURI' => 'page/{page}.html',
    ),
    'errorPage' => 
    array (
      403 => '403.php',
      404 => '404.php',
      500 => '500.php',
    ),
    'maintenance' => 
    array (
      'pages' => '',
      'exception' => 'is_admin()',
      'report' => 'report_500()',
    ),
  ),
  'user' => 
  array (
    'template' => 'profile.php',
    'staticURI' => 'profile/{user_id}.html',
    'keys' => 
    array (
      'login' => 'user_name|user_email|user_tel',
      'require' => 'user_name|user_password|user_level',
      'filter' => 'user_name|user_level',
      'serialize' => 'user_protect',
    ),
    'name' => 
    array (
      'minLength' => 2,
      'maxLength' => 30,
    ),
    'password' => 
    array (
      'minLength' => 5,
      'maxLength' => 18,
    ),
    'level' => 
    array (
      'admin' => 5,
      'editor' => 4,
    ),
  ),
  'file' => 
  array (
    'keys' => 
    array (
      'require' => 'file_name|file_src',
      'filter' => 'file_src',
    ),
    'upload' => 
    array (
      'savePath' => 'upload/',
      'acceptTypes' => 'jpg|jpeg|png|gif|bmp',
      'maxSize' => 2048,
      'imageSizes' => '64|96|128',
    ),
  ),
  'category' => 
  array (
    'template' => 'category.php',
    'staticURI' => '{category_name}/page/{page}.html',
    'keys' => 
    array (
      'require' => 'category_name',
      'filter' => 'category_name',
    ),
  ),
  'post' => 
  array (
    'template' => 'single.php',
    'staticURI' => '{category_name}/{post_id}.html',
    'keys' => 
    array (
      'require' => 'post_title|post_content|post_time|category_id|user_id',
      'filter' => 'post_time|user_id',
      'search' => 'post_title|post_content',
    ),
  ),
  'comment' => 
  array (
    'keys' => 
    array (
      'require' => 'comment_content|comment_time|post_id',
      'filter' => 'comment_time|post_id|*comment_parent',
    ),
  ),
  'weixin' => 
  array (
    'adminUid' => 'ozZYvwZoAlW2Vqf_uLm3vpJmfFss',
  ),
);