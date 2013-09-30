<?php
/**
 * Created by JetBrains PhpStorm.
 * User: NaeemM
 * Date: 10/06/13
 * Time: 12:20
 * To change this template use File | Settings | File Templates.
 */

class setManagerService {

    private $urlList = 'http://euinside.k-int.com/ECKCore2/SetManager/Set/default/default/list';
    private $urlCommit = 'http://euinside.k-int.com/ECKCore2/SetManager/Set/LIBIS/LibSet/commit';
    private $urlStatus = 'http://euinside.k-int.com/ECKCore2/SetManager/Set/LIBIS/LibSet/status';





    function getList(){
        $htmlResult="";
        $url = $this->urlList;
        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $url)
        );
        $result = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($responseCode == 200){
            $jsonIterator = new RecursiveIteratorIterator(	new RecursiveArrayIterator(json_decode($result, TRUE)), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($jsonIterator as $key => $val) {
                if(is_array($val)) {
                    $htmlResult .= "$key:<br>";
                } else {
                    $htmlResult .= "$key => $val,<br>";
                }
            }
            return $htmlResult;
        }
        else{
            return $responseCode;
        }

    }

    function commit(){
        $fields = array(
            'records' => '@C:/Kaam/Projecten/Europeana/EuropeanaInside/REST Services/validationrecords/lido1.xml',
            'setDescription' => 'A lido set from Libis');
        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->urlCommit)
        );
        curl_setopt($curl,CURLOPT_POSTFIELDS, $fields);

        $result = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if($responseCode != 200)
            return $responseCode;
        else
            return $result;
    }

    function getStatus(){
        $htmlResult="";
        $url = $this->urlStatus;
        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $url)
        );
        $result = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($responseCode == 200){
            $jsonIterator = new RecursiveIteratorIterator(	new RecursiveArrayIterator(json_decode($result, TRUE)), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($jsonIterator as $key => $val) {
                if(is_array($val)) {
                    $htmlResult .= "$key:<br>";
                } else {
                    $htmlResult .= "$key => $val,<br>";
                }
            }
            return $htmlResult;
        }
        else{
            return $responseCode;
        }

    }

}