<?php

// Save this in a file called "ray.php"

return [
    /*
    * This settings controls whether data should be sent to Ray.
    */
    'enable' => true,

    /*
     *  The host used to communicate with the Ray app.
     */
    'host' => 'localhost',

    /*
     *  The port number used to communicate with the Ray app.
     */
    'port' => 23517,

    /*
     *  Absolute base path for your sites or projects in Homestead, Vagrant, Docker, or another remote development server.
     */
    'remote_path' => __DIR__,

    /*
     *  Absolute base path for your sites or projects on your local computer where your IDE or code editor is running on.
     */
    'local_path' => __DIR__,

    /*
     * When this setting is enabled, the package will not try to format values sent to Ray.
     */
    'always_send_raw_values' => false,
];
