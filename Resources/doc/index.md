Installation
============

## Install it via composer

```shell
php composer.phar require lexik/maintenance-bundle
```


## Register the bundle

You must register the bundle in your kernel:

    <?php

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Lexik\Bundle\MaintenanceBundle\LexikMaintenanceBundle(),
        );
        // ...
    }

-----------------------

Use
===

you have several options for each driver.

Here the complete configuration with the `example` of each pair of class / options.

The ttl (time to live) option is optional everywhere, it is used to indicate the duration in `second` of the maintenance.

    #app/config.yml
    lexik_maintenance:
        authorized:
            path: /path                                                         # Optional. Authorized path, accepts regexs
            host: your-domain.com                                               # Optional. Authorized domain, accepts regexs
            ips: ['127.0.0.1', '172.123.10.14']                                 # Optional. Authorized ip addresses
            query: { foo: bar }                                                 # Optional. Authorized request query parameter (GET/POST)
            cookie: { bar: baz }                                                # Optional. Authorized cookie
            route:                                                              # Optional. Authorized route name
            attributes:                                                         # Optional. Authorized route attributes
        driver:
            ttl: 3600                                                                  # Optional ttl option, can be not set

             # File driver
            class: '\Lexik\Bundle\MaintenanceBundle\Drivers\FileDriver'                # class for file driver
            options: {file_path: %kernel.root_dir%/cache/lock}                         # file_path is the complete path for create the file

             # Shared memory driver
            class: '\Lexik\Bundle\MaintenanceBundle\Drivers\ShmDriver'                 # class for shared memory driver

             # MemCache driver
            class: Lexik\Bundle\MaintenanceBundle\Drivers\MemCacheDriver               # class for MemCache driver
            options: {key_name: 'maintenance', host: 127.0.0.1, port: 11211}           # need to define a key_name, the host and port

            # Database driver:
            class: 'Lexik\Bundle\MaintenanceBundle\Drivers\DatabaseDriver'             # class for database driver

            # Option 1 : for doctrine
            options: {connection: custom}                                            # Optional. You can choice an other connection. Without option it's the doctrine default connection who will be used

            # Option 2 : for dsn, you must have a column ttl type datetime in your table.
            options: {dsn: "mysql:dbname=maintenance;host:localhost", table: maintenance, user: root, password: root}  # the dsn configuration, name of table, user/password

        #Optional. response code and status of the maintenance page
        response:
            code: 503
            status: "Service Temporarily Unavailable"


### Commands

There are four commands:
* lexik:maintenance:lock
* lexik:maintenance:unlock
* lexik:maintenance:schedule-lock
* lexik:maintenance:unschedule-lock


    lexik:maintenance:lock [ttl]

This command will enable the maintenance according with your configuration. You can pass the time to live of the maintenance in parameter, ``this doesn't work with file and shm driver``.

    lexik:maintenance:unlock

This command will disable the maintenance.

    lexik:maintenance:schedule-lock [delay] [--startdate|-s] [--ttl|t]

Schedule maintenance mode to start after a delay or at a specific date, ``this does, so far, only work with database driver without dsn``.

Example for a maintenance mode for one hour at Christmas, starting 18 o'clock: 

    lexik:maintenance:schedule-lock 3600 -s "2017-12-24 18:00"

The following command will unschedule a scheduled maintenance mode.

    lexik:maintenance:unschedule-lock



You can execute all these commands without a warning message which you need to interact with, e.g.:

    lexik:maintenance:lock --no-interaction

Or (with the optional ttl overwriting):

    lexik:maintenance:lock 3600 -n

---------------------

Custom error page 503
---------------------

In the listener, an exception is thrown when web site is under maintenance. This exception is a 'This exception is a 'HttpException' (status 503), to custom your error page
 you need to create a error503.html.twig (if you use twig) in:
    app/Resources/TwigBundle/views/Exception

#### Important

.. note::

    You must remember that this only works if Symfony2 works.

----------------------

Using with a Load Balancer
---------------------
Some load balancers will monitor the status code
of the http response to stop forwarding traffic
to your nodes.  If you are using a load balancer
you may want to change the status code of the
maintenance page to 200 so your users will still see
something. You may change the response code of the status page from 503 by changing the **response.code** configuration.


Service
--------

You can use the ``lexik_maintenance.driver.factory`` service anyway in your app and call ``lock`` and ``unlock`` methods.
For example, you can build a backend module to activate maintenance mode.
In your controller:

    $driver = $this->get('lexik_maintenance.driver.factory')->getDriver();
    $message = "";
    if ($action === 'lock') {
        $message = $driver->getMessageLock($driver->lock());
    } else {
        $message = $driver->getMessageUnlock($driver->unlock());
    }

    $this->get('session')->setFlash('maintenance', $message);

    return new RedirectResponse($this->generateUrl('_demo'));


**Warning**: Make sure you have allowed IP addresses if you run maintenance from the backend, otherwise you will find yourself blocked on page 503.
