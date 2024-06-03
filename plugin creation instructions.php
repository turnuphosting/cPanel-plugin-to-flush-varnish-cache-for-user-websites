Before you begin, please note that this guide assumes that you're using the jupiter theme, if you have a different theme, kindly adjust to fit your theme structure by replacing jupiter with your theme name.
Here’s a step-by-step guide:
1. Set up the Plugin Directory Structure

Create the directory structure for your plugin:
# Run the command below.
mkdir -p /usr/local/cpanel/base/frontend/jupiter/varnish_cache_flusher
cd /usr/local/cpanel/base/frontend/jupiter/varnish_cache_flusher

2. Create the Plugin Script

Create a new PHP file for your plugin. For example, flush_varnish.php to fetch all the domains associated with the cPanel user and to flush the Varnish cache for each domain:

<?php

// Ensure the script is run within the cPanel environment
if (!defined('IN_CPANEL')) {
    die('This file cannot be accessed directly.');
}

// Include cPanel's CPANEL class
require_once "/usr/local/cpanel/php/cpanel.php";

// Create a CPANEL object
$cpanel = new CPANEL();

// Get the current cPanel user
$username = $cpanel->cpanelprint('$user');

// Fetch all domains associated with the cPanel user
$domains_data = $cpanel->uapi('DomainInfo', 'list_domains');

// Extract the list of domains
$domains = array_merge(
    [$domains_data['cpanelresult']['result']['data']['main_domain']],
    $domains_data['cpanelresult']['result']['data']['addon_domains'],
    $domains_data['cpanelresult']['result']['data']['sub_domains'],
    $domains_data['cpanelresult']['result']['data']['parked_domains']
);

function flush_varnish_cache($domains) {
    $output = "";
    foreach ($domains as $domain) {
        $varnish_command = escapeshellcmd("sudo varnishadm ban req.http.host ~ .{$domain}");
        $output .= shell_exec($varnish_command);
    }
    return $output;
}

// Run the function
$result = flush_varnish_cache($domains);

// Display the result
echo "<div class='container'>";
echo "<h2>Flush Varnish Cache</h2>";
echo "<p>Varnish cache flushed for the following domains:</p>";
echo "<ul>";
foreach ($domains as $domain) {
    echo "<li>$domain</li>";
}
echo "</ul>";
echo "<p>Output: $result</p>";
echo "</div>";
?>

3. Create the Plugin Integration File

Create a varnish_cache_flusher.live.php file in the same directory:

<?php

require_once "/usr/local/cpanel/php/cpanel.php";

$cpanel = new CPANEL();

echo $cpanel->header("Varnish Cache Flusher");

$username = $cpanel->cpanelprint('$user');

// Fetch all domains associated with the cPanel user
$domains_data = $cpanel->uapi('DomainInfo', 'list_domains');

// Extract the list of domains
$domains = array_merge(
    [$domains_data['cpanelresult']['result']['data']['main_domain']],
    $domains_data['cpanelresult']['result']['data']['addon_domains'],
    $domains_data['cpanelresult']['result']['data']['sub_domains'],
    $domains_data['cpanelresult']['result']['data']['parked_domains']
);

function flush_varnish_cache($domains) {
    $output = "";
    foreach ($domains as $domain) {
        $varnish_command = escapeshellcmd("sudo varnishadm ban req.http.host ~ .{$domain}");
        $output .= shell_exec($varnish_command);
    }
    return $output;
}

// Run the function
$result = flush_varnish_cache($domains);

echo "<div class='container'>";
echo "<h2>Flush Varnish Cache</h2>";
echo "<p>Varnish cache flushed for the following domains:</p>";
echo "<ul>";
foreach ($domains as $domain) {
    echo "<li>$domain</li>";
}
echo "</ul>";
echo "<p>Output: $result</p>";
echo "</div>";

echo $cpanel->footer();

$cpanel->end();
?>

4. Create a Plugin Icon

Create an icon for your plugin. Place the icon file (e.g., icon.png) in the same directory.
5. Create Plugin Registration File

Create a plugin_file.json file to register your plugin with cPanel:

{
  "id": "varnish_cache_flusher",
  "entry": "flush_varnish.php",
  "name": "Varnish Cache Flusher",
  "description": "Flush Varnish cache for user websites.",
  "pluginType": "cjt",
  "category": {
    "id": "cPanel",
    "name": "cPanel"
  },
  "metadata": {
    "icon": "icon.png",
    "displayname": "Varnish Cache Flusher"
  },
  "version": "1.0",
  "apiVersion": 1,
  "requiredFeatures": [],
  "run": {
    "type": "application",
    "command": "/usr/local/cpanel/base/frontend/jupiter/varnish_cache_flusher/varnish_cache_flusher.live.php"
  }
}

Create an Install.json File:
The install.json should contain the metadata required by cPanel to recognize and install the plugin. Here's a basic example:

{
    "id": "varnish_cache_flusher",
    "version": "1.0",
    "name": "Varnish Cache Flusher",
    "description": "A plugin to flush Varnish cache",
    "icon": "icon.png",
    "featuremanager": {
        "vars": {
            "group": "Varnish Tools",
            "features": [
                "flush_varnish_cache"
            ]
        }
    },
    "entry": {
        "url": "flush_varnish.php",
        "require": "module=Security",
        "displayname": "Flush Varnish Cache"
    },
    "pluginType": "cjt",
    "requiredFeatures": [],
    "run": {
        "type": "application",
        "command": "/usr/local/cpanel/base/frontend/jupiter/varnish_cache_flusher/varnish_cache_flusher.live.php"
    }
}


6. Register the Plugin with cPanel

Finally, register the plugin with cPanel:

/usr/local/cpanel/scripts/install_plugin /usr/local/cpanel/base/frontend/jupiter/varnish_cache_flusher/varnish_cache_flusher.live.php --theme jupiter

Set Correct Permissions:
# Run the command below.
chown -R root:root /usr/local/cpanel/base/frontend/jupiter/varnish_cache_flusher
chmod 644 /usr/local/cpanel/base/frontend/jupiter/varnish_cache_flusher/*

Install the Plugin:
# Run the command below.
/usr/local/cpanel/scripts/install_plugin /usr/local/cpanel/base/frontend/jupiter/varnish_cache_flusher --theme jupiter

Rebuild Sprites and API:
# Run the command below.
/usr/local/cpanel/bin/rebuild_sprites
/usr/local/cpanel/bin/rebuild_apache_conf

Restart cPanel services:
# Run the command below.
/scripts/restartsrv_cpsrvd


7. Verify the Installation

Log in to your cPanel account and navigate to the Jupiter theme interface. You should see the "Varnish Cache Flusher" icon. Click it to flush the Varnish cache for your websites.
