# Sitemap Generator

# usage

`php sitemap.php site=https://www.example.com/`

## Features
 - Actually crawls webpages like Google would
 - Zero dependencies
 - Generates a seperate XML file which gets updated every time the script gets executed (Runnable via CRON)
 - Crawls faster than online services
 - Verbose logging
 - Completely usable through CLI
 
## Usage
Usage is pretty straight forward:
 - Configure the crawler by modifying the config file `sitemap.config.php`
    - Select the file to which the sitemap will be saved
    - Select URL to crawl
    - Configure blacklists, accepts the use of wildcards (example: http://example.com/private/* and *.jpg)
 - Generate sitemap
    - Either send a GET request to this script or use it from the CLI as seen below
    - A sitemap will be generated and saved
    - Submit to Google
 - For better results
    - Setup a CRON Job to execute the php script

# CLI Usage

Sometimes you need to run the script for a large number of domains (If you are a webhost for example). This sitemap generator allows you to override any variable on-the-fly in CLI.

## Basic usage

Scan `http://www.mywebsite.com/` and output the sitemap to `/home/user/public_html/sitemap.xml`:

`php sitemap.php file=/home/user/public_html/sitemap.xml site=http://www.mywebsite.com/`

## Advanced usage

While the above is the most common use-case, sometimes you need to modify other things such as `$debug` or `$blacklist`. I will do a bit of explaining about how shells work so you don't mess up.

Lets start with the blacklist which is a one-dimensional array. This is how you would pass an array as a `GET` request.

~~`php sitemap.php blacklist[]="foo"&blacklist[]="bar"`~~

Shells are different however as `[]` is parsed as a shell expansion and `&` as a fork-to-background. You want neither of those things. As such, you want to escape both of them resulting in the following:

`php sitemap.php blacklist\[]="foo"\&blacklist\[]="bar"`

Next, let's tackle the `$debug` variable. All the same concepts apply but the syntax is slightly different:

`php sitemap.php debug\["add"]=true\&debug\["warn"]=false\&debug\["reject"]=true`

**Important note**: Setting debug flags will require setting _all_ of them. Partial application is not supported.

