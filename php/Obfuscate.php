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
    public $base_url = "bla";

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
        }

        if ($env == "prod") {
            $this->endpoint = "https://obfuscator-node.onrender.com/obfuscate";
        }
    }

    /**
     * obfuscateJS
     *
     * @param  mixed $code
     * @return bool|string
     */
    public function obfuscateJS($code): bool|string
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
     * @return ObfuscatorPHP|bool
     */
    public function obfuscatePHP($code): ObfuscatorPHP|bool
    {
        include __DIR__ . '/php-obfuscator/obfuscator.php';

        $packer = new ObfuscatorPHP();

        return $packer->populateCode($code)->pack()->code();
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
    ): array|string {
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