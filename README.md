run symfony in swoole

1 install 
```text
    composer require lifeworks/swoole-symfony-bundle
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
    * bin/console swoole:start
    * bin/console swoole:status
    * bin/console swoole:stop
```
