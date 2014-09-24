sitecfg
=======

sitecfg is a small PHP script I use to manage my websites. It automatically checks out the git repository, symlinks its webroot in `/var/www`, symlinks its nginx config file to `/etc/nginx/sites-available` and `-enabled`, and reloads nginx.

For usage, see `php sitecfg.php`.

Configuration
-------------

There are two configuration files. The first is `config.json` (copy `config.example.json` to get started), which contains these keys:

* `"owner"` and `"group"` - The username (probably your username) and group name (most likely `www-data` or `nginx`) that the git repository will be `chmod`ed to be owned by
* `"sitesDir"` - The directory that the git repositories will be put in, e.g. `/home/your_username/sites`

The second is `sites.json` (copy `sites.example.json` to get started), which contains a key for each site. Each site's object contains these keys:

* `"gitRepo"` - The URL of a git repository which will be cloned (from GitHub, for example)
* `"nginxConfigFile"` - The path within that git repository of the nginx configuration file (which should use `/var/www/site_name` for its webroot), e.g. `example.com.cfg`
* `"webroot"` - The path within that git repository of the webroot, e.g. `htdocs` (`/var/www/site_name` will be symlinked to point to this)

That's it! 
