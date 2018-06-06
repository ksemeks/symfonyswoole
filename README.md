run symfony in swoole

1 install 
```text
    composer require lifeworks/swooleforsymfony:dev-master
```

2 register in config/bundles.php
```php
    return [
        ...
        Swoole\HttpServerBundle\SwooleHttpServerBundle::class => ['all' => true],
        ...
    ];
```

3 swoole http server command
```text
    * bin/console swoole:start --evn=prod
    * bin/console swoole:status
    * bin/console swoole:stop
    * bin/console swoole:reload
```

4 nginx proxy config
```text
    server {
        listen       80;
        server_name  youdomain.com;
        location / {
            proxy_connect_timeout 300;
            proxy_send_timeout 300;
            proxy_read_timeout 300;
            send_timeout 300;
            proxy_set_header X-Real-IP  $remote_addr;
            proxy_set_header Host $host;
            proxy_pass http://127.0.0.1:2345/;
            proxy_redirect off;
        }
    }
```
