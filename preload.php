<?php

$directories = [
    __DIR__.'/app',
    __DIR__.'/bootstrap',
    __DIR__.'/config',
    __DIR__.'/routes',
];

opcache_compile_file(__DIR__.'/vendor/autoload.php');

foreach ($directories as $directory) {
    if (! is_dir($directory)) {
        continue;
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory)
    );

    foreach ($files as $file) {
        if ($file->getExtension() === 'php') {
            opcache_compile_file($file->getPathname());
        }
    }
}
