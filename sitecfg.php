#!/usr/bin/env php
<?php

function run($command) {
    $output = [];
    $exitstatus = NULL;
    exec($command, $output, $exitstatus);
    echo implode("\n", $output), "\n";
    return !$exitstatus;
}

$config = file_get_contents("config.json") or die("failed to load config.json\n");
$config = json_decode($config, true) or die("failed to decode json\n");

function setupSite($site, $siteName) {
    global $config;

    $siteDir = "$config[sitesDir]/$siteName";
    run("git clone $site[gitRepo] '$siteDir'") or die("failed to clone git repo\n");
    run("chown -R $config[owner]:$config[group] '$siteDir'") or die("failed to change owner and group of directory\n");
    
    if (!file_exists("/var/www")) {
        mkdir("/var/www") or die("couldn't create /var/www\n");
        chrgrp("/var/www", $config['group']) or die("failed to chown /var/www");
    }
    
    $wwwDir = "/var/www/$siteName";
run("ln -s '$siteDir/$site[webRoot]' '$wwwDir'") or die ("failed to symlink webroot into /var/www");
    
    run("ln -s '$siteDir/$site[nginxConfigFile]' '/etc/nginx/sites-available/$siteName'") or die("failed to simlink site config file into nginx sites-available\n");
    run("ln -s '/etc/nginx/sites-available/$siteName' '/etc/nginx/sites-enabled/$siteName'") or die("failed to simlink nginx sites-available to nginx sites-enabled\n");
    run("service nginx reload") or die("failed to reload nginx\n");
    
    echo "Everything seems fine, site \"$siteName\" setup.\n";
}

function printUsage() {
    echo "php sitecfg.php <command> <arguments>\n";
    echo "Commands:\n";
    echo "    setupall - Sets up all sites\n";
    echo "    setup <sitename> - Sets up the named site\n";
    echo "    teardown <sitename> - Tears down the named site\n";
}

if ($argc < 2) {
    printUsage();
    die();
}

if (0 !== posix_getuid()) {
    die("this utility must be run as root (use sudo) to work\n");
}

$sites = file_get_contents("sites.json") or die("couldn't load sites.json\n");
$sites = json_decode($sites, true) or die("couldn't decode json\n");

switch ($command = $argv[1]) {
    case 'setupall':
        foreach ($sites as $siteName => $site) {
            setupSite($site, $siteName);
        }
        break;
    case 'setup':
    case 'teardown':
        if ($argc < 3) {
            printUsage();
            die();
        }
        $siteName = $argc[2];
        if (!isset($sites[$siteName])) {
            die("No such site \"$sitename\"\n");
        }
        $site = $sites[$siteName];
        if ($command === 'setup') {
            setupSite($site, $siteName);
            break;
        } else if ($command === 'teardown') {
            teardownSite($site);
            break;
        }
    default:
        printUsage();
        die();
}
