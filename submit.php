<?
    // 지역 날씨 평가 서비스 
    // 아침 7시 반에 올라감.
    date_default_timezone_set('Asia/Seoul');



	mysql_query("set names utf8");
    function query($query, $column){
            $q = $query;
            $result = mysql_query($q);
            while ($row = mysql_fetch_array($result)) {
                    return $row["$column"];
            }
    }
    function query2($query) {
            return mysql_query($query);
    }

	function weatherget2($city){
                $date = date("Y-m-d");
                $url="http://weather.service.msn.com/data.aspx?weadegreetype=C&culture=ko-KR&weasearchstr=$city";
                $result = simplexml_load_file($url);
                $arr["current"] = $result->weather[0]->current->attributes()->temperature;
                $arr["status"] = $result->weather[0]->current->attributes()->skytext;
                $arr["min"] = $result->weather[0]->forecast[0]->attributes()->low;
                $arr["max"] = $result->weather[0]->forecast[0]->attributes()->high;
                $arr["city"] = $city;
                $arr["time"] = $date;
                echo "Getting Data ($city) \n";
                return $arr;
	}

    function insert_db($param) {
            foreach ($param as $pcs) {
                    $min = $pcs["min"];
                    $max = $pcs["max"];
                    $status = $pcs["status"];
                    $current = $pcs["current"];
                    $city = $pcs["city"];
                    $time = $pcs["time"];
                    $hour = date("h");      
                    $count = query("select max(id) as max from weather", "max") + 1 ;
                    $process = mysql_query("insert into weather(id, mind, maxd, status, datetime , current, city, hour) VALUES('$count', '$min', '$max' , '$status', '$time','$current', '$city', '$hour')");
            }
            
    }
    function getdist($a1, $a2) {
      if ($a1 > $a2) {
        $a1 = abs($a1);
        $a2 = abs($a2);
        return abs($a1 - $a2);
        
      } else {
        $a1 = abs($a1);
        $a2 = abs($a2);
        return abs($a2 - $a1);
        
      }
    }
    function compare_city($city) { //yesterday 날씨
            
            $hour = date("H");
            $result = mysql_query("select * from weather where datetime = subdate(current_date, 1) and hour='$hour' and city='$city'");
            while ($row = mysql_fetch_array($result)) {
                    $before = $row["current"];
                    $m = date("m");

                    $after = query("select * from weather where datetime = CURDATE() and hour='$hour' and city='$city'", "current");

                    if ($m == "3" || $m == "4" || $m == "5") { // Spring
                            if ($after > $before ) { // 오늘이 더 따뜻함
                                    $count = query("select max(id) as max from weather_st", "max");
                                    $dist = getdist($after, $before);
                                    $ints = "spring_warm_$dist";
                                    $time = date("Y-m-d");
                                    // db 기록
                            } else {  // 오늘이 더 추움
                                    $count = query("select max(id) as max from weather_st", "max");
                                    $dist = getdist($after, $before);
                                    $ints = "spring_cold_$dist";
                                    $time = date("Y-m-d");
                                     //db 기록
                            }
                    }
                    if ($m == "6" || $m == "7" || $m == "8") { // Summer
                            if ($after > $before ) { // 오늘이 더 덥
                                    $count = query("select max(id) as max from weather_st", "max");
                                    $dist = getdist($after, $before);
                                    $ints = "summer_hot_$dist";
                                    $time = date("Y-m-d");
                                    $set = query2("insert into weather_st(id, city, todayis, time) VALUES('$count','$city', '$ints', '$time')");
                            } else {  // 오늘이 더 시원
                                    $count = query("select max(id) as max from weather_st", "max");
                                    $dist = getdist($after, $before);
                                    $ints = "spring_cool_$dist";
                                    $time = date("Y-m-d");
                                     //db 기록
                            }
                    }
                    if ($m == "9" || $m == "10" || $m == "11") { // Fall
                            if ($after > $before ) { // 오늘이 더 덥
                                    $count = query("select max(id) as max from weather_st", "max");
                                    $dist = getdist($after, $before);
                                    $ints = "fall_hot_$dist";
                                    $time = date("Y-m-d");
                                     //db 기록
                            } else {  // 오늘이 더 시원
                                    $count = query("select max(id) as max from weather_st", "max");
                                    $dist = getdist($after, $before);
                                    $ints = "fall_cool_$dist";
                                    $time = date("Y-m-d");
                                    //db 기록
                            }
                    }
                    if ($m == "12" || $m == "1" || $m == "2") { // Winter
                            if ($after > $before ) { // 오늘이 더 따뜻함
                                    $count = query("select max(id) as max from weather_st", "max");
                                    $dist = getdist($after, $before);
                                    $ints = "winter_warm_$dist";
                                    $time = date("Y-m-d");
                                     //db 기록
                            } else {  // 오늘이 더 추움
                                    $count = query("select max(id) as max from weather_st", "max");
                                    $dist = getdist($after, $before);
                                    $ints = "winter_cold_$dist";
                                    $time = date("Y-m-d");
                                    //db 기록
                            }
                    }
                    $exp = explode("_", $ints);
                    $authm = $exp[0];
                    $status = $exp[1];
                    $work = $exp[2];
                    $work2 = "$work";
                    switch ($authm) {
                            case "spring":
                                    if ($status == "warm") {
                                            return "$city 의 날씨는 어제보다 $work2 도 더 따뜻합니다./1/spring";
                                    } else {
                                            return "$city 의 날씨는 어제보다 $work2 도 더 춥습니다./2/spring";
                                    }
                                    break;
                            case "summer":
                                    if ($status == "hot") {
                                            return "$city 의 날씨는 어제보다 $work2 도 더 덥습니다./3/summer";
                                    } else {
                                            return "$city 의 날씨는 어제보다 $work2 도 더 시원합니다./4/summer";
                                    }
                                    break;
                            case "fall":
                                    if ($status == "cool") {
                                            return "$city 날씨는 어제보다 $work2 도 더 쉬원합니다./5/fall";
                                    } else {
                                            return "$city 날씨는 어제보다 $work2 도 더 덥습니다.(따뜻합니다.)/6/fall";
                                    }
                                    break;
                            case "winter": 
                                    if ($status == "cold") {
                                            return "$city 날씨는 어제보다 $work2 도 더 춥습니다./7/winter";
                                    } else {
                                            return "$city 날씨는 어제보다 $work2 도 더 따뜻합니다./8/winter";
                                    }
                                    break;

                    }
            }
    }
    $citys["data1"] = weatherget2("서울");
    $citys["data2"] = weatherget2("천안");
    $citys["data3"] = weatherget2("강원도");
    $citys["data5"] = weatherget2("경상남도");
    $citys["data6"] = weatherget2("경상북도");
    $citys["data7"] = weatherget2("전라남도");
    $citys["data8"] = weatherget2("전라북도");
    $citys["data9"] = weatherget2("제주도");
    $citys["data10"] = weatherget2("울산");


    insert_db($citys);

    $citys2[0] = "서울"; // list of city
    $citys2[1] = "천안";
    $citys2[2] = "제주도";
    $citys2[3] = "경상북도";
    $citys2[4] = "강원도";
    $citys2[5] = "경상남도";
    $citys2[6] = "전라남도";
    $citys2[7] = "전라북도";
    $citys2[8] = "제주도";
    $citys2[9] = "울산";

    
    $code_spring_warm = 0;
    $code_spring_cold = 0;
    
    $code_summer_hot = 0;
    $code_summer_cool = 0;

    $code_fall_cool = 0;
    $code_fall_hot = 0;

    $code_winter_cold = 0;
    $code_winter_warm = 0;
    $step1 = "";
    foreach ($citys2 as $city) {
        $return = compare_city($city);
        $exp = explode("/", $return);
        $code = $exp[1];
        $message = $exp[0];
        $autm = $exp[2];
        $step1 .= $message . "\n";
        switch ($code) { // add messages
            case "1":
                $code_spring_warm = $code_spring_warm + 1;
                break;
            case "2":
                $code_spring_cold = $code_spring_cold + 1;
                break;
            case "3":
                $code_summer_hot = $code_summer_hot + 1;
                break;
            case "4":
                $code_summer_cool = $code_summer_cool + 1;
                break;
            case "5":
                $code_fall_cool = $code_fall_cool + 1;
                break;
            case "6":
                $code_fall_hot = $code_fall_hot + 1;
                break;
            case "7";
                $code_winter_cold = $code_winter_cold +1;
                break;
            case "8";
                $code_winter_warm = $code_winter_warm + 1;
                break;
        }
    }



    switch ($autm) { // add messages
        case "spring":
            if ($code_spring_warm > $code_spring_cold) {
                $ment =  "전국 날씨는 전체적으로 따뜻한 편";
            } else {
                $ment =  "전국 날씨는 전체적으로 추운 편";
            }
            break;
        case "summer":
            if ($code_summer_hot > $code_summer_cool) {
                $ment = "전국 날씨는 전체적으로 더운 편, 폭염예보 예상.";
            } else {
                $ment = "전국 날씨는 전체적으로 시원한 편. 비가오거나, 시원한 바람이 부는 지역이 있을것.";
            }
            break;
        case "fall":
            if ($code_fall_hot > $code_spring_cool) {
                $ment =  "전국 날씨는 전체적으로 덥거나 따뜻한 편";
            } else {
                $ment =  "전국 날씨는 전체적으로 시원하거나 추운 편";
            }
            break;
        case "winter":
            if ($code_winter_warm > $code_winter_cold) {
                $ment = "전국 날씨는 전체적으로 따뜻한 편 \n";
            } else {
                $ment =  "전국날씨는 전체적으로 추운 편 \n";                        
            }
            break;
    }
    echo "Uploading...\n";
    $content = "오늘의 아침 날씨 입니다. \n\n"  .$step1 . "\n" . $ment;


    $data['message'] = $content;
    $data['access_token'] = $page_access_token;
    $post_url = 'https://graph.facebook.com/'.$page_id.'/feed';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $post_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $return = curl_exec($ch);
    echo "\nResult : $return\n";

    curl_close($ch);

?>