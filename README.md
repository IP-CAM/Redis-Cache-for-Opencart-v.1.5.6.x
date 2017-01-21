# opencart-redis

OpenCart 1.5.x compatible!

Using Redis driver:

1. Modify Cache.php library for using Redis server.

2. Parameters for it in Config.php:

  // CACHE
  define('CACHE_DRIVER', 'redis');
  define('REDIS_HOSTNAME', 'localhost');
  define('REDIS_PORT', '6379');
 
