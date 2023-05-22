<?php

$path = getcwd();
printf("The project will be created at the current location, are you sure (y/n)?\n");
if (count($argv) < 2 || $argv[1] != '-y') {
    $read = read();
    if (strtolower($read) != 'y') {
        exit();
    }
}
copy_dir(__DIR__ . "/install/resources", $path . '/resources');
copy_dir(__DIR__ . "/install/src", $path . '/src');
copy(__DIR__ . '/install/server.php', $path . '/server.php');
updateComposer();


exec("composer dump", $output);
printf("File server.php in the root directory is the startup file, have fun!.\n");
exit();

function read()
{
    $fp = fopen('php://stdin', 'r');
    $input = fgets($fp, 255);
    fclose($fp);
    $input = chop($input);
    return $input;
}

function copy_dir($src, $dst, $force = false)
{
    $dir = opendir($src);
    if (!$dir) {
        printf("%s Permission problem or illegal directory, incorrect installation\n", $src);
        return false;
    }
    if (file_exists($dst) && $force == false) {
        printf("%s Directory already exists (skip)\n", $dst);
        return false;
    }
    @mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                copy_dir($src . '/' . $file, $dst . '/' . $file);
                continue;
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
    printf("%s directory created\n", $dst);
    return true;
}

function updateComposer()
{
    global $path;
    if (!$composer = file_get_contents($path . '/composer.json')) {
        exit('composer.json not found');
    }

    $composer = json_decode($composer, true);
    $composer['autoload']['psr-4']['App\\'] = 'src/';
    file_put_contents($path . '/composer.json', json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

