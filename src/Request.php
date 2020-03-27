<?php

namespace Chengenius\Request;

class Request
{
    private $method;
    private $key;

    public function __construct()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $this->method = $method;
    }

    /**
     * common function for getting parameters
     *
     * @param string $key
     * @return array|bool|mixed|string
     */
    public function input($key = '')
    {
        $this->key = $key;

        switch ($this->method) {
            case 'GET':
                $param = $this->get();
                break;
            case 'POST':
                $param = $this->post();
                break;
            default:
                $param = $this->other();
        }

        return $param;
    }

    /**
     * handle get request
     */
    private function get()
    {
        $param = $_GET;

        if ($this->key != '') {
            $param = isset($_GET[$this->key]) ? $_GET[$this->key] : '';
        }

        return $param;
    }


    /**
     * handle post request
     */
    private function post()
    {
        $param = $_POST;

        if ($this->key != '') {
            $param = isset($_POST[$this->key])  ? $_POST[$this->key] : '';
        }

        return $param;
    }

    /**
     * handle other request
     */
    private function other()
    {
        try{
            $putData = file_get_contents("php://input");
            $resultData = json_decode($putData,true);
            if(is_array($resultData)){
                //解析IOS提交的PUT数据
                $res_data = $resultData;
            } elseif (!strstr($putData,"\r\n")) {
                //解析本地测试工具提交的PUT数据
                parse_str($putData,$putData);
                $res_data = $putData;
            } else {
                //解析PHP CURL提交的PUT数据
                $putData = explode("\r\n",$putData);
                $resultData = [];
                foreach($putData as $key=>$data){
                    if(substr($data,0,20) == 'Content-Disposition:'){
                        preg_match('/.*\"(.*)\"/',$data,$matchName);
                        $resultData[$matchName[1]] = $putData[$key+2];
                    }
                }
                $res_data = $resultData;
            }
            if ($this->key != '') {
                $res_data = isset($res_data[$this->key]) ? $res_data[$this->key] : '';
            }
            return $res_data;
        }catch (Exception $e){
            return '';
        }
    }
}