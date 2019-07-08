<?php

function LocateIP($ip, $type)
{
    // Convert a country code into country name
    $countries = json_decode(file_get_contents('Countries.json'), true);
    //Whois ip zones's servers list 
    $whoisservers = array(
        "whois.lacnic.net", // Latin America and Caribbean - returns data for ALL locations worldwide
        "whois.apnic.net", // Asia/Pacific only
        "whois.arin.net", // North America only
        "whois.ripe.net", // Europe, Middle East and Central Asia only
        "whois.afrinic.net" // Africa 
    );
    
    // Fetching each whois server.
    $results = array();
    foreach ($whoisservers as $whoisserver) {
        $result = WhoisQuery($whoisserver, $ip);
        if ($result && !in_array($result, $results)) {
            
            // Checking if the query is not false
            if (strstr($result, "IPv4 address block not managed by the RIPE NCC") || strstr($result, "address range is not administered by APNIC")) {
                // Do nothing
            } elseif (strstr($result, "country")) {
                $results[$whoisserver] = $result;
            }
            
        }
    }
    
    // Converting the results into array
    foreach ($results as $whoisserver => $result) {
        $res .= $result;
    }
    
    $lines  = explode("\n", $res);
    $output = array();
    foreach ($lines as $line) {
        list($key, $value) = explode(': ', $line, 2);
        if (isset($output[$key])) {
            if (!is_array($output[$key])) {
                $tmp_val      = $output[$key];
                $output[$key] = array(
                    $tmp_val
                );
            }
            $output[$key][] = $value;
        } else {
            $output[$key] = $value;
        }
        // Adding extra information: Country name, Country language, Website of the ISP
        $output['country-name'] = $countries[str_replace(' ', '', $output['country'])]['cn'];
        $output['language'] = $countries[str_replace(' ', '', $output['country'])]['lc'];
        $output['website'] = (isset($output['abuse-mailbox']) ? substr(strrchr($output['abuse-mailbox'], "@"), 1) : (!is_array($output['e-mail']) ? substr(strrchr($output['e-mail'], "@"), 1) : (isset($output['e-mail']) ? substr(strrchr($output['e-mail'][0], "@"), 1) : substr(strrchr($output['notify'], "@"), 1))));
    }
    
    // Cleaning orginal data and cutting of unnecessary data that came from the Whois server
    $normal                  = array();
    $normal['country']       = is_array($output['country']) ? $output['country'][0] : $output['country'];
    $normal['country-name']  = $output['country-name'];
    $normal['language']      = $output['language'][0];
    $normal['person']        = is_array($output['person']) ? join(" OR ", $output['person']) : $output['person'];
    $normal['abuse-mailbox'] = is_array($output['abuse-mailbox']) ? join(" | ", $output['abuse-mailbox']) : $output['abuse-mailbox'];
    $normal['phone']         = is_array($output['phone']) ? join(" | ", $output['phone']) : $output['phone'];
    $normal['website']       = is_array($output['website']) ? join(" | ", $output['website']) : $output['website'];
    $normal['descr']         = is_array($output['descr']) ? join(", ", $output['descr']) : $output['descr'];
    $normal['e-mail']        = is_array($output['e-mail']) ? join(", ", $output['e-mail']) : $output['e-mail'];
    $normal['notify']        = is_array($output['notify']) ? join(" | ", $output['notify']) : $output['notify'];
    $normal['remarks']       = is_array($output['remarks']) ? join(", ", $output['remarks']) : $output['remarks'];
    $normal['netname']       = is_array($output['netname']) ? $output['netname'][0] : $output['netname'];
    $normal['address']       = is_array($output['address']) ? join(", ", $output['address']) : $output['address'];
    $normal['source']        = is_array($output['source']) ? $output['source'][0] : $output['source'];
    
    // To return data based on the specifed type on LocateIP('Your_IP', 'full')
    return ($type == 'normal' ? $normal : ($type == 'full' ? $output : (array_key_exists($type, $output) ? $output["$type"] : "The data type You requested was not found, please try: full, normal, or specify a valid row name ")));
}

// Performing the whois query from the ip zones
function WhoisQuery($whoisserver, $ip)
{
    $port    = 43;
    $timeout = 10;
    $fp = @fsockopen($whoisserver, $port, $errno, $errstr, $timeout) or die("Socket Error " . $errno . " - " . $errstr);
    fputs($fp, $ip . "\r\n");
    $out = "";
    while (!feof($fp)) {
        $out .= fgets($fp);
    }
    fclose($fp);
    
    $res = "";
    if ((strpos(strtolower($out), "error") === FALSE) && (strpos(strtolower($out), "not allocated") === FALSE)) {
        $rows = explode("\n", $out);
        foreach ($rows as $row) {
            $row = trim($row);
            if (($row != '') && ($row{0} != '#') && ($row{0} != '%')) {
                $res .= $row . "\n";
            }
        }
    }
    return $res;
}

?>
