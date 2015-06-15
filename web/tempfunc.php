<?php
Header('Content-Type: text/html; charset=utf-8');

/*Url decode*/
function url_decode_it($url){
        $url = explode('/', $url);
        $lastPart = array_pop($url);
        //echo rawurldecode($lastPart);
        $varr = $lastPart;
        $varr = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",urldecode($varr));
        $varr = html_entity_decode($varr,null,'UTF-8');
       // echo $varr;
        return $varr;
}


/**
 * Question Handling functions
 * start

 */

function getUrl_of_Dbpedia_Abstract($term)
{
    $format = 'json';
    $query =
        "PREFIX dbp: <http://dbpedia.org/resource/>
   PREFIX dbp2: <http://dbpedia.org/ontology/>

   SELECT ?abstract
   WHERE {
      dbp:".$term." dbp2:abstract ?abstract .
      FILTER langMatches(lang(?abstract), 'en')
   }";
    $searchUrl = 'http://dbpedia.org/sparql?'
        .'query='.urlencode($query)
        .'&format='.$format;

    return $searchUrl;
}


function request_handling($url){

    if (!function_exists('curl_init')){
        die('CURL is not installed!');
    }
    $ch= curl_init();

    // set request url
    curl_setopt($ch,
        CURLOPT_URL,
        $url);

    // return response, don't print/echo
    curl_setopt($ch,
        CURLOPT_RETURNTRANSFER,
        true);

    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

/**
 * Question Handling functions
 * END

 */




function my_json_response($reqtype,$givenstring){
    header("HTTP/1.1 sdfdf");
    echo "Response: JSON\n";

    $response =array();
    header('Content-Type: application/json');
    $question=preg_replace('/greetings\?q=/','', $givenstring);
    //$question=preg_replace('/\\'/','''', $givenstring);
    //echo "$question";
    $tempqstn=strtolower($question);

        if($reqtype==0)//greetings
        {
            $message="Hello, Kitty!";

            //echo $tempqstn;


            if(preg_match('/how/',$tempqstn) && preg_match('/are/',$tempqstn) && preg_match('/you/',$tempqstn))
            {
                $message=$message." I am fine. What about you?  ";
            }
            else if(preg_match('/what/',$tempqstn) && preg_match('/is/',$tempqstn) && preg_match('/your/',$tempqstn)&& preg_match('/name/',$tempqstn))
            {
                $message=$message." Thanks for asking. I am Saiful. You have a pretty name too. ";
            }
            else if(preg_match('/good/',$tempqstn))
            {
                if(preg_match('/morning/',$tempqstn))$message=$message." Good Morning!! Wish you have a lovely day.";
                 if(preg_match('/evening/',$tempqstn))$message=$message." Good evening!! How was your day? ";
                 if(preg_match('/night/',$tempqstn))$message=$message." Good night!! Have sweet dreams! ";
                if(preg_match('/pleasure/',$tempqstn) && preg_match('/meet/',$tempqstn) && preg_match('/you/',$tempqstn))$message.="And Nice to meet You too!";

            }
            else{
                /**Question dont know handle*/
                $message.= "Sorry ! I don't know how to respond u";

            }

            $response["answer"]=$message;
        }

        if($reqtype==1)/**Weather Question Handle*/
        {

           // $request = 'http://api.openweathermap.org/data/2.5/weather/London,uk?APPID=4d5348988881cf5fb96e7aeb026166ba';
            $Explodeqstn=explode(" in ",$tempqstn);
            $cityname=$Explodeqstn[1];
            $keyquery=$Explodeqstn[0];
           // echo $cityname."city name and query".$keyquery;

            $requestopen= 'http://api.openweathermap.org/data/2.5/weather?q='.$cityname;
            $opts = array('http'=>array('header' => "User-Agent:MyAgent/1.0\r\n"));
            $context = stream_context_create($opts);
            $openresponse  = file_get_contents($requestopen,FALSE,$context);
            $jsonobj  = json_decode($openresponse,true);
            // print_r($jsonobj);
            /*Query Handle Start*/
            $message="";
            if($jsonobj["cod"]==404){
                $message="Sorry Majesty! This city name does not Exist";
                /**Question dont know handle*/
            }
            else if(preg_match('/temperature/',$keyquery))
            {
                $message=$jsonobj["main"]["temp"]." K";
            }else if(preg_match('/humidity/',$keyquery))
            {
                $message=$jsonobj["main"]["humidity"];
            }
            else{
                $todaystatus=$jsonobj["weather"][0]["main"];
                $todaystatus=strtolower($todaystatus);
                if(preg_match('/clear/',$keyquery)){
                    if($todaystatus=="clear")$message="Yes";
                    else $message="No";
                }else if(preg_match('/rain/',$keyquery)){
                    if($todaystatus=="clear")$message="Yes";
                    else $message="No";
                }else if(preg_match('/clouds/',$keyquery)){
                    if($todaystatus=="clouds")$message="Yes";
                    else $message="No";
                }
                else{
                    /*Question dont know handle*/
                    $message="Your majesty! Jon Snow knows nothing! So do I!";

                }

            }

            /*Query Handle End*/


            $response["answer"]=$message;

        }/*question weather  finished*/

        if($reqtype==2)/*question normal query   start*/
        {
            $mainqs=$question;
            $mainqs=preg_replace('/\?/','', $mainqs);
            $Explodeqstn=explode(" is ",$mainqs);
            $main_query=explode(" ",$Explodeqstn[1]);
            $loopcount=count($main_query);

            $query_term=$main_query[0];
            for($i=1;$i<$loopcount;$i++)
            {
                $query_term.="_";
                $query_term.=$main_query[$i];
            }
            //echo $query_term;

            $requestURL = getUrl_of_Dbpedia_Abstract($query_term);

            $responseArray = json_decode(request_handling($requestURL),
                true);

            $check=$responseArray["results"]["bindings"][0]["abstract"]["value"];
            if($check==null){
                $message="Your majesty! Jon Snow knows nothing! So do I!";
                /**handle dont know thing here*/
                $response["answer"]=$message;
                $response["query_suggestion"]="I have a very poor knowledge since i am using ( DBpedia ).".
                "To Extract something from my poor source.Please be specific in your question like----------------                ".
                "  < qa?q=who+is+Bill+Gates? >  ".
                "  < qa?q=where+is+Bangladesh? >  ".
                "  <  qa?q=who+is+Tom+Cruise? >  ".
                "  <  qa?q=who+is+Vladimir+Putin?  >
                ";

            }
            else{
                $message=$responseArray["results"]["bindings"][0]["abstract"]["value"];
                $response["answer"]=$message;
            }
            //print_r($responseArray);

        }


    echo json_encode($response, JSON_PRETTY_PRINT);
}

?>



