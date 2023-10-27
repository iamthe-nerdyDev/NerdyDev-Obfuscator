<?php
/*
 _   _              _       ____             
| \ | | ___ _ __ __| |_   _|  _ \  _____   __
|  \| |/ _ \ '__/ _` | | | | | | |/ _ \ \ / /
| |\  |  __/ | | (_| | |_| | |_| |  __/\ V / 
|_| \_|\___|_|  \__,_|\__, |____/ \___| \_/  
                      |___/                  
  ___  _      __                     _                ______  _   _ ______  
 / _ \| |__  / _|_   _ ___  ___ __ _| |_ ___  _ __   / /  _ \| | | |  _ \ \ 
| | | | '_ \| |_| | | / __|/ __/ _` | __/ _ \| '__| | || |_) | |_| | |_) | |
| |_| | |_) |  _| |_| \__ \ (_| (_| | || (_) | |    | ||  __/|  _  |  __/| |
 \___/|_.__/|_|  \__,_|___/\___\__,_|\__\___/|_|    | ||_|   |_| |_|_|   | |
                                                     \_\                /_/ 
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once __DIR__ . "/Obfuscate.php";

$Obfuscate = new Obfuscate();

/**
 * returnHeader
 *
 * @param  int $code
 * @return void
 */
function returnHeader($code): void
{
    $headers = [
        200 => "OK",
        404 => "Not Found",
        401 => "Unauthorized",
        405 => "Method Not Allowed",
        400 => "Bad Request",
        500 => "Internal Server Error"
    ];

    echo header("HTTP/1.0 {$code} {$headers[$code]}");
}

/**
 * outputJSON
 *
 * @param  bool $status
 * @param  string $message
 * @param  bool|array $data
 * @return void
 */
function outputJSON(
    $status,
    $message = "unable to complete request",
    $data = false
): void {
    $response = [
        "status" => $status,
        "message" => $message,
    ];

    if (is_array($data)) {
        $response["data"] = $data;
    }

    echo json_encode($response);

    exit();
}

/**
 * extractZip
 *
 * @param  string $zipFileName
 * @param  string $newFolderName
 * @return bool|string
 */
function extractZip($zipFileName, $newFolderName): bool|string
{
    $zip = new ZipArchive();

    $zipFileName = "zips/" . $zipFileName;

    if ($zip->open($zipFileName) === TRUE) {
        $extractPath = 'extracted/' . $newFolderName . '/';

        $zip->extractTo($extractPath);

        $zip->close();

        return $extractPath;
    }

    return false;
}

/**
 * doObfuscate
 *
 * @param  mixed $language
 * @param  mixed $code
 * @return string|null
 */
function doObfuscate($language, $code): string|null
{
    global $Obfuscate;

    $obfuscatedCode = null;

    switch ($language) {
        case "php":
            $obfuscatedCode = $Obfuscate->obfuscatePHP($code);

            break;

        case "css":
            $obfuscatedCode = $Obfuscate->minifyCSS($code);

            break;

        case "html":
            $obfuscatedCode = $Obfuscate->escapeHTML($code);

            break;

        case "js":
            $obfuscatedCode = $Obfuscate->obfuscateJS($code);

            break;

        default:
            $obfuscatedCode = null;
    }

    return $obfuscatedCode;
}


if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    returnHeader(200);

    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        isset($_POST['obfuscate'])
        && is_bool($_POST['obfuscate'])
    ) {
        $allowedExtensions = [
            "zip",
            "html",
            "css",
            "js"
        ];

        if (isset($_FILES["zip"]) && $_FILES["zip"]["error"] === UPLOAD_ERR_OK) {
            $zipFileName = pathinfo($_FILES["zip"]["name"], PATHINFO_FILENAME);
            $zipFileExt = pathinfo($_FILES["zip"]["name"], PATHINFO_EXTENSION);

            if (strtolower($zipFileExt) !== "zip") {
                outputJSON(false, "Upload a .zip file please");
            }

            $newZipFileName = "files/zips/" . $zipFileName . time() . "." . $zipFileExt;

            move_uploaded_file($_FILES["zip"]["tmp_name"], $newZipFileName);

            outputJSON(false, "Unable to complete process");
        }

        if (isset($_FILES["file"]) && $_FILES["file"]["error"] === UPLOAD_ERR_OK) {
            $fileName = pathinfo($_FILES["file"]["name"], PATHINFO_FILENAME);
            $fileExt = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);

            if (
                !in_array(
                    strtolower($fileExt),
                    $allowedExtensions
                ) || strtolower($fileExt) === "zip"
            ) {
                outputJSON(false, "Upload a .css, .js, .html or .php file please");
            }

            $newFileName = $fileName . time() . "." . $fileExt;
            $fullFilePath = "files/single/" . $newFileName;

            if (move_uploaded_file($_FILES["file"]["tmp_name"], $fullFilePath)) {

                //.....try getting data from that file back to obfuscate it!
                $code = file_get_contents($fullFilePath);

                if ($code) {
                    $obfuscatedCode = doObfuscate(strtolower($fileExt), $code);

                    $obfuscatedFileName = "Obfuscated-" . $newFileName;

                    if (
                        file_put_contents(
                            "files/single/" . $obfuscatedFileName,
                            $obfuscatedCode
                        )
                    ) {
                        outputJSON(
                            true,
                            "Obfuscated successfully!",
                            ["url" => $Obfuscate->base_url . "/files/single/" . $obfuscatedFileName]
                        );
                    }

                    outputJSON(false, "Unable to complete process");
                }

                outputJSON(false, "Unable to get contents");
            }

            outputJSON(false, "Unable to complete process");
        }

        if (isset($_POST["language"]) && isset($_POST["code"])) {
            $language = $_POST["language"];
            $code = $_POST["code"];

            if (!$language || empty($language)) {
                outputJSON(false, "Select language of code");
            }

            if (!$code || empty($code)) {
                outputJSON(false, "A valid code should be passed");
            }

            $obfuscatedCode = doObfuscate($language, $code);

            if (!empty($obfuscatedCode) && $obfuscatedCode != null) {
                $fileName = "Obfuscated(" . $language . ")-" . date("Y-m-d-His") . "." . $language;

                if (file_put_contents("files/single/" . $fileName, $obfuscatedCode)) {
                    outputJSON(
                        true,
                        "Obfuscated successfully!",
                        ["url" => $Obfuscate->base_url . "/files/single/" . $fileName]
                    );
                }

                outputJSON(false, "Unable to complete process");
            }

            outputJSON(false, "Unable to obfuscate code");
        }

        outputJSON(false, "Unidentified request");
    }

    outputJSON(false, "Unable to complete request");
}

returnHeader(405);

outputJSON(false, "Method not supported");

?>