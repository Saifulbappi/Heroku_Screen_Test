

<?php
error_reporting(0);
include_once 'tempfunc.php';

$url = $_SERVER['REQUEST_URI'];

$decodedurl = url_decode_it($url);
//echo $decodedurl;


//handling the  decodedurl


    if (preg_match('/greetings\?q=/',$decodedurl))
    {
        //echo "its a greeeting";
         my_json_response(0,$decodedurl);

    }
    else if(preg_match('/weather\?q=/',$decodedurl))
    {
       // echo "its a weather question";
        my_json_response(1,$decodedurl);

    }
    else if(preg_match('/qa\?q=/',$decodedurl))
    {
        //echo "its a normal question";

        my_json_response(2,$decodedurl);
    }
    else{

        echo "<h1>How May I be of Your service Sire ?!</h1> </br> please enter your query like---</br>
        /greetings?q=Hello!%20How%20are%20you?
         ";

    }

?>







