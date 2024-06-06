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
