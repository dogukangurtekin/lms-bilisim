<?php

$vendorDir = __DIR__ . '/../vendor';
$installed = $vendorDir . '/composer/installed.php';

if (!file_exists($installed)) {
    fwrite(STDERR, "Missing vendor/composer/installed.php\n");
    exit(1);
}

$data = require $installed;
$packages = $data['versions'] ?? [];

$prefixes = [];
$classmap = [];
$files = [];

ksort($packages);
if (isset($packages['thecodingmachine/safe'])) {
    $packages = ['thecodingmachine/safe' => $packages['thecodingmachine/safe']] + $packages;
}

foreach ($packages as $name => $meta) {
    $path = $meta['install_path'] ?? null;
    if (!$path || !is_dir($path)) {
        continue;
    }

    $composerJson = $path . '/composer.json';
    if (!file_exists($composerJson)) {
        continue;
    }

    $json = json_decode(file_get_contents($composerJson), true);
    if (!is_array($json)) {
        continue;
    }

    foreach (['autoload', 'autoload-dev'] as $autoloadKey) {
        $autoload = $json[$autoloadKey] ?? [];
        foreach (($autoload['psr-4'] ?? []) as $prefix => $dirs) {
            foreach ((array) $dirs as $dir) {
                $prefixes[$prefix][] = rtrim($path . '/' . $dir, '/') . '/';
            }
        }
        foreach (($autoload['classmap'] ?? []) as $item) {
            $classmap[] = $path . '/' . $item;
        }
        foreach (($autoload['files'] ?? []) as $file) {
            $files[] = $path . '/' . $file;
        }
    }
}

foreach ($prefixes as $prefix => $dirs) {
    $prefixes[$prefix] = array_values(array_unique($dirs));
}
$classmap = array_values(array_unique(array_filter($classmap, 'file_exists')));
$files = array_values(array_unique(array_filter($files, 'file_exists')));
usort($files, static function ($a, $b) {
    $aSafe = str_contains($a, '/thecodingmachine/safe/');
    $bSafe = str_contains($b, '/thecodingmachine/safe/');
    if ($aSafe !== $bSafe) {
        return $aSafe ? -1 : 1;
    }
    return strcmp($a, $b);
});

$autoloadPhp = <<<'PHP'
<?php

spl_autoload_register(static function ($class) {
    static $prefixes = __PREFIXES__;
    static $classmap = __CLASSMAP__;
    foreach ($prefixes as $prefix => $dirs) {
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            continue;
        }
        $relative = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        foreach ($dirs as $dir) {
            $file = $dir . $relative;
            if (is_file($file)) {
                require $file;
                return true;
            }
        }
    }
    if (isset($classmap[$class]) && is_file($classmap[$class])) {
        require $classmap[$class];
        return true;
    }
    return false;
});

foreach (__FILES__ as $file) {
    require_once $file;
}

return true;
PHP;

$autoloadPhp = str_replace('__PREFIXES__', var_export($prefixes, true), $autoloadPhp);
$autoloadPhp = str_replace('__CLASSMAP__', var_export([], true), $autoloadPhp);
$autoloadPhp = str_replace('__FILES__', var_export($files, true), $autoloadPhp);

file_put_contents($vendorDir . '/autoload.php', $autoloadPhp);
echo "Wrote vendor/autoload.php\n";
