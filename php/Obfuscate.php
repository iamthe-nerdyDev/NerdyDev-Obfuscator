<?php
date_default_timezone_set("Africa/Lagos");
error_reporting(E_ALL & ~E_NOTICE);

/**
 * Obfuscate
 */
class Obfuscate
{
    /**
     * endpoint
     *
     * @var mixed
     */
    protected $endpoint;

    /**
     * base_url
     *
     * @var string
     */
    public $base_url;

    /**
     * __construct
     *
     * @param string $env
     * @return void
     */
    public function __construct($env = "dev")
    {
        if ($env == "dev") {
            $this->endpoint = "http://localhost:8080/obfuscate";
            $this->base_url = "http://localhost:8000";
        }

        if ($env == "prod") {
            $this->endpoint = "xxxxxxxxxx";
            $this->base_url = "xxxxxxxxxx";
        }
    }

    /**
     * obfuscateJS
     *
     * @param  mixed $code
     * @return bool|string
     */
    public function obfuscateJS($code)
    {
        if ($code && !empty($code)) {
            $response = $this->postRequest($this->endpoint, ["code" => $code]);

            if ($response["status"] && $response["status"] = true) {
                $obfuscatedCode = $response["data"]["code"];

                return $obfuscatedCode;
            }

            return $response["message"] ?? "Unable to complete request";
        }

        return false;
    }

    /**
     * obfuscateHTML
     *
     * @param  mixed $code
     * @return string
     */
    public function escapeHTML($code): string
    {
        $escapedHTML = '';

        for ($i = 0; $i < strlen($code); $i++) {
            $escapedHTML .= '%' . bin2hex($code[$i]);
        }

        return '<script type="text/javascript">document.write(unescape("' . $escapedHTML . '"));</script>';
    }

    /**
     * minifyCSS
     *
     * @param  mixed $code
     * @return string
     */
    public function minifyCSS($code): string
    {
        $search = array(
            '/\s+/',
            '/\/\*.*?\*\//',
        );

        $replace = array(
            '',
            '',
        );

        return preg_replace($search, $replace, $code);
    }

    /**
     * obfuscatePHP
     *
     * @param  mixed $code
     * @return string
     */
    public function obfuscatePHP($code): string
    {
        include_once __DIR__ . '/ObfuscatorPHP.php';

        $packer = new ObfuscatorPHP();

        $obfuscatedCode = $packer->populateCode($code)->pack()->code();

        return $obfuscatedCode;
    }

    /**
     * postRequest
     *
     * @param string $url
     * @param array $data
     * @param bool $header
     * @return array|string
     */
    private function postRequest(
        $url,
        $data,
        $header = true
    ) {
        $jsonData = $data;

        if ($header) {
            $jsonData = json_encode($data);
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        if ($header) {
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array(
                    "Content-Type: application/json"
                )
            );
        }

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            $result = "" . curl_error($ch);
            error_log("" . $result);

            return false;
        }

        curl_close($ch);

        return json_decode($result, true);
    }
}
?>