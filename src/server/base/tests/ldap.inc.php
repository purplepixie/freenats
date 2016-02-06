<?php // ldap.inc.php -- LDAP test module

/**
 * LDAP Test (C) Copyright 2011 Marc Franquesa
 * Provided within FreeNATS
 *
 * For more information see http://www.purplepixie.org/freenats/
 *
 * Licence: GNU GPL V3 or later
**/


global $NATS;

class LDAP_Test extends FreeNATS_Local_Test
{
    function DoTest($testname,$param,$hostname,$timeout,$params)
    {
        global $NATS;
        
        $url    = $params[0];
        $bind   = $params[1];
        $pasw   = $params[2];
        $base   = $params[3];
        $filter = $params[4];

        $ds = ldap_connect($url);
        if (!$ds) return -2;
        $ldap = ($bind && $pasw) ? ldap_bind($ds, $bind, $pasw) : ldap_bind($ds);
        if (!$ldap) return -1;
        
        if ($base && $filter) {
            $search = ldap_search($ds,$base,$filter);
            $val = ldap_count_entries($ds,$search);
        } else {
            $val = 1;
        }
        
        ldap_close($ds);
        return $val;
    }
    
    function Evaluate($result) 
    {
        if ($result<0) return 2; // failure
        if ($result==0) return 1; // warning
        return 0; // else success
    }
    
    function DisplayForm(&$row)
    {
        echo "<table border=0>";
        echo "<tr><td align=left>";
        echo "LDAP URL:";
        echo "</td><td align=left>";
        echo "<input type=text name=testparam size=30 maxlength=128 value=\"".$row['testparam']."\">";
        echo "</td><td></td></tr>";
        
        echo "<tr><td align=left>";
        echo "Bind DN:";
        echo "</td><td align=left>";
        echo "<input type=text name=testparam1 size=30 maxlength=128 value=\"".$row['testparam1']."\">";
        echo "</td><td><i>Leave empty for anonymous bind</i></td></tr>";
        
        echo "<tr><td align=left>";
        echo "Bind Password:";
        echo "</td><td align=left>";
        echo "<input type=password name=testparam2 size=30 maxlength=128 value=\"".$row['testparam2']."\">";
        echo "</td><td><i>Leave empty for anonymous bind</i></td></tr>";

        echo "<tr><td align=left>";
        echo "Search Base:";
        echo "</td><td align=left>";
        echo "<input type=text name=testparam3 size=30 maxlength=128 value=\"".$row['testparam3']."\">";
        echo "</td><td><i>Leave empty for only test bind</i></td></tr>";

        echo "<tr><td align=left>";
        echo "Search Filter:";
        echo "</td><td align=left>";
        echo "<input type=text name=testparam4 size=30 maxlength=128 value=\"".$row['testparam4']."\">";
        echo "</td><td><i>Leave empty for only test bind</i></td></tr>";
        
        echo "</table>";
    }
}

$params=array();
$NATS->Tests->Register("ldap","LDAP_Test",$params,"LDAP Bind",1,"FreeNATS LDAP Test");

?>
