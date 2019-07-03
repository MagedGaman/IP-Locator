 <?php
function LocateIP($ip)
{
    //Who is servers list
    $whoisservers = array(
        "whois.lacnic.net", // Latin America and Caribbean - returns data for ALL locations worldwide
        "whois.apnic.net", // Asia/Pacific only
        "whois.arin.net", // North America only
        "whois.ripe.net", // Europe, Middle East and Central Asia only
        "whois.afrinic.net" // Africa 
    );
    
    // Fetching whois on each server.
    $results = array();
    foreach ($whoisservers as $whoisserver) {
        $result = WhoisQuery($whoisserver, $ip);
        if ($result && !in_array($result, $results)) {
            
            // Checking if the enquiry is not false
            if (strstr($result, "IPv4 address block not managed by the RIPE NCC") || strstr($result, "address range is not administered by APNIC")) {
                // Do nothing
            } elseif (strstr($result, "country")) {
                $results[$whoisserver] = $result;
            }
            
        }
    }
    
    // Converting the results in array
    foreach ($results as $whoisserver => $result) {
      $res .= $result;
    }
    
    $lines = explode("\n", $res);
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
    }
    return $output;
    
}

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

$get = LocateIP($_GET['ip']);
echo "<pre>";
print_r($get);
echo "</pre>";

?> 
