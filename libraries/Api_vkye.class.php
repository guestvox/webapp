<?php

defined('_EXEC') or die;

class Api_vkye
{
    public function __construct()
    {
        header('Content-type: application/json');
        header('Access-Control-Allow-Origin: *');
    }

    public function main($params)
    {
        if (isset($params[0]))
        {
            $access = false;

            $users = [
                'zaviapms' => 'y329-gfc=7mq}qy(',
                'mit' => 'qrB3svqybxLrMas6',
                'siteminder' => 'V97+pf=:z4?Hm|0i',
                'adminit' => 'rR17P2FP0Mv8KKq1'
            ];

            if ($params[0] == 'siteminder')
            {
                // $_POST['message'] =
                // '<soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                //     <soap-env:Header xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                //         <wsse:Security xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" soap:mustunderstand="1">
                //             <wsse:UsernameToken>
                //                 <wsse:Username>siteminder</wsse:Username>
                //                 <wsse:Password type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#passwordtext">V97+pf=:z4?Hm|0i</wsse:Password>
                //             </wsse:UsernameToken>
                //         </wsse:Security>
                //     </soap-env:Header>
                //     <soap-env:Body xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                //         <OTA_HotelResNotifRQ xmlns="http://www.opentravel.org/OTA/2003/05" Version="1.001" EchoToken="879791878" ResStatus="Commit" TimeStamp="2014-10-09T18:51:45">OK</OTA_HotelResNotifRQ>
                //     </soap-env:Body>
                // </soap-env:Envelope>';

                $xml = simplexml_load_string($_POST['message'], null, null, 'http://schemas.xmlsoap.org/soap/envelope/');
                $xml->registerXPathNamespace('soap-env', 'http://schemas.xmlsoap.org/soap/envelope/');
                $xml->registerXPathNamespace('wsse', 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd');
                $username = $xml->xpath('/soap-env:Envelope/soap-env:Header/wsse:Security/wsse:UsernameToken/wsse:Username');
                $username = !empty($username[0]) ? json_decode(json_encode($username[0]), 1) : '';
                $username = !empty($username[0]) ? $username[0] : '';
                $password = $xml->xpath('/soap-env:Envelope/soap-env:Header/wsse:Security/wsse:UsernameToken/wsse:Password');
                $password = !empty($password[0]) ? json_decode(json_encode($password[0]), 1) : '';
                $password = !empty($password[0]) ? $password[0] : '';
            }
            else if ($params[0] == 'mit')
            {
                $username = 'mit';
                $password = 'qrB3svqybxLrMas6';
            }
            else
            {
                $username = !empty($_SERVER['HTTP_USERNAME']) ? $_SERVER['HTTP_USERNAME'] : '';
                $password = !empty($_SERVER['HTTP_PASSWORD']) ? $_SERVER['HTTP_PASSWORD'] : '';
            }

            if (array_key_exists($username, $users))
            {
                if ($password == $users[$username])
                    $access = true;
            }

            if ($access == true)
            {
                $call_api = ucwords($params[0] . '_api');
                $path = Security::DS(PATH_CORE . 'api/' . $call_api . '.php');

                if (file_exists($path))
                {
                    require_once $path;

                    $class = new $call_api();
                    $_params = [];

                    switch ($_SERVER['REQUEST_METHOD'])
                    {
                        case 'GET':
                            $method = 'GET';
                            break;

                        case 'POST':
                            $method = 'POST';
                            $_POST = (file_get_contents("php://input")) ? json_decode(file_get_contents("php://input"), true) : $_POST;
                            break;

                        case 'PUT':
                            $method = 'PUT';
                            $_REQUEST = [];
                            $this->parse_raw_http_request($_REQUEST);
                            break;

                        case 'DELETE':
                            $method = 'DELETE';
                            $_REQUEST = [];
                            $this->parse_raw_http_request($_REQUEST);
                            break;

                        default:
                            $method = 'UNKNOWN';
                            break;
                    }

                    unset($params[0]);

                    foreach ($params as $value)
                        $_params[] = $value;

                    if (isset($method) && method_exists($class, strtolower($method)))
                    {
                        $response = $class->{$method}($_params);
                        $this->response($response);
                    }
                    else
                        $this->response(false, 405, 'Método no permitido');
                }
                else
                    $this->response(false, 400, 'API no disponible');
            }
            else
                $this->response(false, 405, 'Acceso no permitido');
        }
        else
            $this->response(false, 400, 'No se solicitó ninguna API');
    }

    private function response($data = '', $code = 200, $status = 'OK', $message = '')
    {
        http_response_code($code);

        $argv = [
            'status' => $status
        ];

        if (!empty($data))
            $argv['data'] = $data;

        if (!empty($message))
            $argv['message'] = $message;

        echo json_encode($argv, JSON_PRETTY_PRINT);
    }

    private function parse_raw_http_request(array &$a_data)
    {
        $input = file_get_contents('php://input');

        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);

        $boundary = $matches[1];

        $a_blocks = preg_split("/-+$boundary/", $input);

        array_pop($a_blocks);

        foreach ($a_blocks as $id => $block)
        {
            if (empty($block))
                continue;

            if (strpos($block, 'application/octet-stream') !== FALSE)
                preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
            else
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);

            $a_data[$matches[1]] = $matches[2];
        }
    }
}
