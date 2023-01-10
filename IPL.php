<?php

function locateIP($ip, $type = 'normal')
{
    // Countries data
    $countries = json_decode(file_get_contents('Countries.json'), true);
    // Whois IP zones' servers list 
    $whoisServers = array(
        "whois.lacnic.net", // Latin America and Caribbean - returns data for ALL locations worldwide
        "whois.apnic.net", // Asia/Pacific only
        "whois.arin.net", // North America only
        "whois.ripe.net", // Europe, Middle East and Central Asia only
        "whois.afrinic.net" // Africa 
    );
    
    // Fetching each whois server
    $results = array();
    foreach ($whoisServers as $whoisServer) {
        $result = WhoisQuery($whoisServer, $ip);
        if ($result && !in_array($result, $results)) {
            // If the query is not false
            if (!strstr($result, "IPv4 address block not managed by the RIPE NCC") 
                && !strstr($result, "address range is not administered by APNIC")
                && strstr($result, "country")) {
                $results[$whoisServer] = $result;
            }
        }
    }
    
    // Converting the results into a single string
    $res = implode("\n", $results);
    $lines = explode("\n", $res);
    
    $output = array();
    foreach ($lines as $line) {
        list($key, $value) = explode(': ', $line, 2);
        
        if (isset($output[$key])) {
            if (!is_array($output[$key])) {
                $tmpVal = $output[$key];
                $output[$key] = array($tmpVal);
            }
            $output[$key][] = $value;
        } else {
            $output[$key] = $value;
        }
        
        // Adding extra information: Country name, Country language, Website of the ISP
        $output['country-name'] = $countries[str_replace(' ', '', $output['country'])]['cn'];
        $output['language'] = $countries[str_replace(' ', '', $output['country'])]['lc'];
        $output['website'] = (isset($output['abuse-mailbox']) ? substr(strrchr($output['abuse-mailbox'], "@"), 1) : 
            (!is_array($output['e-mail']) ? substr(strrchr($output['e-mail'], "@"), 1) : 
                (isset($output['e-mail']) ? substr(strrchr($output['e-mail'][0], "@"), 1) : substr(strrchr($output['notify'], "@"), 1))));
    }
    
    // Cleaning original data and cutting off unnecessary data that came from the Whois server
    $normal = array();
    $normal['country'] = is_array($output['country']) ? $output['country'][0] : $output['country'];
    $normal['country-name'] = $output['country-name'];
    $normal['language'] = $output['language'];
    $normal['person'] = is_array($output['person']) ? implode(" OR ", $output['person']) : $output['person'];
    $normal['abuse-mailbox'] = is_array($output['abuse-mailbox']) ? implode(" | ", $output['abuse-mailbox']) : $output['abuse-mailbox'];
    $normal['phone'] = is_array($output['phone']) ? implode(" | ", $output['phone']) : $output['phone'];
    $normal['website'] = is_array($output['website']) ? implode(" | ", $output['website']) : $output['website'];
    $normal['descr'] = is_array($output['descr']) ? implode(" | ", $output['descr']) : $output['descr'];
    // Return the cleaned up data
    return $normal;
}

// Performing the whois query from the ip zones
function WhoisQuery($whoisserver, $ip)
{
    $fp = @fsockopen($whoisserver, 43, $errno, $errstr, 10) or die("Socket Error " . $errno . " - " . $errstr);
    fputs($fp, $ip . "\r\n");
    $out = "";
    while (!feof($fp)) {
        $out .= fgets($fp);
    }
    fclose($fp);
    
    $res = "";
    if (strpos(strtolower($out), "error") === FALSE && strpos(strtolower($out), "not allocated") === FALSE) {
        $rows = explode("\n", $out);
        foreach ($rows as $row) {
            if (trim($row) != '' && $row[0] != '#' && $row[0] != '%') {
                $res .= $row . "\n";
            }
        }
    }
    return $res;
}

?>
