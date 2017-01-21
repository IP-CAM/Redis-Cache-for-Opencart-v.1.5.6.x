<?php

final class Cache
{
    private $expire = 3600;
    private $serialize_type = 'json';
    private $cache_mode = 'redis';


    public function __construct()
    {
        $this->site_key = substr(md5(HTTP_SERVER), 0, 5);
        $this->redis = new Redis();
        if (!$this->redis->connect(REDIS_HOSTNAME, REDIS_PORT)) {
            $this->cache_mode = 'file';
        }

    }

    public function getCacheMode()
    {
        return $this->cache_mode;
    }

    public function setCacheMode($cache_mode = 'redis')
    {
        $this->cache_mode = $cache_mode;
    }

    public function setSerializeType($serialize_type = 'serialize')
    {
        $this->serialize_type = $serialize_type;
    }

    public function getSerializeType()
    {
        return $this->serialize_type;
    }


    public function get($key)
    {
        if ($this->cache_mode == 'redis') {
            $cache = $this->redis->get($key . '.' . $this->site_key);
        } else {
            $files = glob(DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.*');
            if ($files) {
                $cache = file_get_contents($files[0]);
                foreach ($files as $file) {
                    $time = substr(strrchr($file, '.'), 1);
                    if ($time < time()) {
                        if (file_exists($file)) {
                            unlink($file);
                        }
                    }
                }
            }
        }


        if ($this->serialize_type == 'json') {
            $data = json_decode($cache, true);
        } else {
            $data = unserialize($cache);
        }
        return $data;
    }

    public function set($key, $value, $expire = 0)
    {
        if ($expire == 0) {
            $expire = rand($this->expire, $this->expire + 400);
        }
        if ($this->cache_mode == 'redis') {
            $res = $this->redis->set($key . '.' . $this->site_key, json_encode($value), $expire);
        } else {
            $this->remove($key);

            $file = DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.' . (time() + $expire);

            $handle = fopen($file, 'w');
            if ($this->serialize_type == 'json') {
                $data = json_encode($value);
            } else {
                $data = serialize($value);
            }
            fwrite($handle, $data);
            fclose($handle);
        }
    }


    public function delete($key)
    {
        if ($this->cache_mode == 'redis') {
            $this->redis->delete($key . '.' . $this->site_key);
        } else {
            $files = glob(DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.*');
            if ($files) {
                foreach ($files as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                        clearstatcache();
                    }
                }
            }
        }

    }

}
