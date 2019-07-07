<?php

function LocateIP($ip, $type)
{
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
    
    foreach ($results as $whoisserver => $result) {
        $res .= $result;
    }
    
    // Converting the results into array    
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
            $output['website'] = (isset($output['abuse-mailbox']) ? substr(strrchr($output['abuse-mailbox'], "@"), 1) : (!is_array($output['e-mail']) ? substr(strrchr($output['e-mail'], "@"), 1) : (isset($output['e-mail']) ? substr(strrchr($output['e-mail'][0], "@"), 1) : substr(strrchr($output['notify'], "@"), 1))));
    }
    
    // Cleaning orginal data and cutting of unnecessary data that came from 
    $country = is_array($output['country']) ? $output['country'][0] : $output['country'];
    $person  = is_array($output['person']) ? join(" OR ", $output['person']) : $output['person'];
    $website  = is_array($output['website']) ? join(" | ", $output['website']) : $output['website'];
    $abuse   = is_array($output['abuse-mailbox']) ? join(" | ", $output['abuse-mailbox']) : $output['abuse-mailbox'];
    $phone   = is_array($output['phone']) ? join(" | ", $output['phone']) : $output['phone'];
    $descr   = is_array($output['descr']) ? join(", ", $output['descr']) : $output['descr'];
    $email   = is_array($output['e-mail']) ? join(", ", $output['e-mail']) : $output['e-mail'];
    $notify  = is_array($output['notify']) ? join(" | ", $output['notify']) : $output['notify'];
    $remarks = is_array($output['remarks']) ? join(", ", $output['remarks']) : $output['remarks'];
    $netname = is_array($output['netname']) ? $output['netname'][0] : $output['netname'];
    $address = is_array($output['address']) ? join(", ", $output['address']) : $output['address'];
    $source  = is_array($output['source']) ? $output['source'][0] : $output['source'];
    $normal  = array(
        'country' => $country,
        'person' => $person,
        'website' => $website,
        'abuse-mailbox' => $abuse,
        'phone' => $phone,
        'descr' => $descr,
        'e-mail' => $email,
        'notify' => $notify,
        'netname' => $netname,
        'address' => $address,
        'remarks' => $remarks,
        'source' => $source
    );
    
    if ($type == 'normal') { // In case of pulling cleaned data
        return $normal;
    } elseif ($type == 'full') { // In case of pulling the orginal data
        return $output;
    } elseif (array_key_exists($type, $output)) { // In case of pulling specified row
        return $output["$type"];
    } else {
        return "Your request was not found!";
    }
    
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
