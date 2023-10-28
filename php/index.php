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

$allowedExtensions = [
    "zip",
    "html",
    "css",
    "js",
    "php"
];

$currentTime = time();

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
function extractZip($zipFileName, $newFolderName)
{
    $zip = new ZipArchive();

    if ($zip->open($zipFileName) === true) {
        $extractPath = 'files/extract/' . $newFolderName . '/';

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
function doObfuscate($language, $code): mixed
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

/**
 * obfuscateFolder
 *
 * @param  string $location
 * @return bool
 */
function obfuscateFolder($location): bool
{
    global $allowedExtensions;

    if (is_dir($location)) {
        $contents = scandir($location);

        if (count($contents) <= 0) {
            return false;
        }

        foreach ($contents as $item) {
            if ($item != '.' && $item != '..') {
                $path = $location . '/' . $item;

                if (is_dir($path)) {
                    obfuscateFolder($path);
                } else {
                    $extension = strtolower(pathinfo($item, PATHINFO_EXTENSION));

                    if (
                        in_array($extension, $allowedExtensions)
                        && $extension != "zip"
                    ) {
                        $code = file_get_contents($path);

                        $obfuscatedCode = doObfuscate($extension, $code);

                        file_put_contents($path, $obfuscatedCode);
                    }
                }
            }
        }

        return true;
    }

    return false;
}

/**
 * zipFolder
 *
 * @param  mixed $location
 * @param  mixed $filename
 * @return bool|string
 */
function zipFolder($location, $filename = null)
{
    global $currentTime;

    require_once 'ZipArchiver.php';

    $zipper = new ZipArchiver;

    if ($filename === null) {
        $filename = "Obfuscated-" . $currentTime . ".zip";
    }

    $zipFileName = 'files/o-zips/' . $filename;

    $zip = $zipper->zipDir($location, $zipFileName);

    if ($zip) {
        return $zipFileName;
    }

    return false;
}


if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    returnHeader(200);

    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        isset($_POST['obfuscate'])
        && !empty($_POST['obfuscate'])
    ) {
        if (isset($_FILES["file"]) && $_FILES["file"]["error"] === UPLOAD_ERR_OK) {
            $fileName = pathinfo($_FILES["file"]["name"], PATHINFO_FILENAME);
            $fileExt = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);

            if (
                !in_array(
                    strtolower($fileExt),
                    $allowedExtensions
                )
            ) {
                outputJSON(false, "Upload a .css, .js, .html or .php file please");
            }

            $maxFileSize = 7 * 1024 * 1024;

            if ($_FILES['file']['size'] > $maxFileSize) {
                outputJSON(false, "File size should not be more than 7MB");
            }

            $newFileName = $fileName . "-" . $currentTime . "." . $fileExt;

            if (strtolower($fileExt) === "zip") {
                $newZipFileName = "files/zip/" . $newFileName;

                if (move_uploaded_file($_FILES["file"]["tmp_name"], $newZipFileName)) {
                    $folderLocation = extractZip(
                        $newZipFileName,
                        "Obfuscated-" . $fileName . "-" . $currentTime
                    );

                    if (is_bool($folderLocation)) {
                        outputJSON(false, "Unable to extract zip contents");
                    }

                    if (obfuscateFolder($folderLocation)) {
                        $zipResult = zipFolder("files/extract/Obfuscated-" . $fileName . "-" . $currentTime);

                        if (is_string($zipResult)) {
                            outputJSON(
                                true,
                                "Obfuscated successfully!",
                                ["url" => $Obfuscate->base_url . $zipResult]
                            );
                        }

                        outputJSON(false, "Unable to zip folder");
                    }

                    outputJSON(false, "Directory not found");
                }

                outputJSON(false, "Unable to complete process");
            }

            $fullFilePath = "files/single-file/" . $newFileName;

            if (move_uploaded_file($_FILES["file"]["tmp_name"], $fullFilePath)) {

                //.....try getting data from that file back to obfuscate it!
                $code = file_get_contents($fullFilePath);

                if ($code) {
                    $obfuscatedCode = doObfuscate(strtolower($fileExt), $code);

                    $obfuscatedFileName = "Obfuscated-" . $newFileName;

                    if (
                        file_put_contents(
                            "files/single-file/" . $obfuscatedFileName,
                            $obfuscatedCode
                        )
                    ) {
                        outputJSON(
                            true,
                            "Obfuscated successfully!",
                            ["url" => $Obfuscate->base_url . "/files/single-file/" . $obfuscatedFileName]
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
                $fileName = "Obfuscated(" . $language . ")-" . $currentTime . "." . $language;

                if (file_put_contents("files/single-file/" . $fileName, $obfuscatedCode)) {
                    outputJSON(
                        true,
                        "Obfuscated successfully!",
                        ["url" => $Obfuscate->base_url . "/files/single-file/" . $fileName]
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