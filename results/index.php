<?php

require_once 'telemetry_db.php';

error_reporting(0);
putenv('GDFONTPATH='.realpath('.'));

/**
 * @param string $name
 *
 * @return string|null
 */
function tryFont($name)
{
    if (is_array(imageftbbox(12, 0, $name, 'M'))) {
        return $name;
    }

    $fullPathToFont = realpath('.').'/'.$name.'.ttf';
    if (is_array(imageftbbox(12, 0, $fullPathToFont, 'M'))) {
        return $fullPathToFont;
    }

    return null;
}

/**
 * @param int|float $d
 *
 * @return string
 */
function format($d)
{
    if ($d < 10) {
        return number_format($d, 2, '.', '');
    }
    if ($d < 100) {
        return number_format($d, 1, '.', '');
    }

    return number_format($d, 0, '.', '');
}

/**
 * @param array $speedtest
 *
 * @return array
 */
function formatSpeedtestDataForImage($speedtest)
{
    // format values for the image
    $speedtest['dl'] = format($speedtest['dl']);
    $speedtest['ul'] = format($speedtest['ul']);
    $speedtest['ping'] = format($speedtest['ping']);
    $speedtest['jitter'] = format($speedtest['jitter']);
    $speedtest['timestamp'] = $speedtest['timestamp'];

    $ispinfo = json_decode($speedtest['ispinfo'], true)['processedString'];
    $dash = strpos($ispinfo, '-');
    if ($dash !== false) {
        $ispinfo = substr($ispinfo, $dash + 2);
        $par = strrpos($ispinfo, '(');
        if ($par !== false) {
            $ispinfo = substr($ispinfo, 0, $par);
        }
    } else {
        $ispinfo = '';
    }

    $speedtest['ispinfo'] = $ispinfo;

    return $speedtest;
}

/**
 * @param array $speedtest
 *
 * @return void
 */
function drawImage($speedtest)
{
    // format values for the image
    $data = formatSpeedtestDataForImage($speedtest);
    $dl = $data['dl'];
    $ul = $data['ul'];
    $ping = $data['ping'];
    $jit = $data['jitter'];
    $ispinfo = $data['ispinfo'];
    $timestamp = $data['timestamp'];

    // initialize the image - modern HAST IT design
    $SCALE = 1.5;
    $SMALL_SEP = 8 * $SCALE;
    $WIDTH = 500 * $SCALE;
    $HEIGHT = 280 * $SCALE;
    $im = imagecreatetruecolor($WIDTH, $HEIGHT);
    $BACKGROUND_COLOR = imagecolorallocate($im, 15, 23, 42); // slate-950

    // configure fonts
    $FONT_LABEL = tryFont('OpenSans-Semibold');
    $FONT_LABEL_SIZE = 12 * $SCALE;
    $FONT_LABEL_SIZE_BIG = 14 * $SCALE;

    $FONT_METER = tryFont('OpenSans-Semibold');
    $FONT_METER_SIZE = 28 * $SCALE;
    $FONT_METER_SIZE_BIG = 32 * $SCALE;

    $FONT_MEASURE = tryFont('OpenSans-Semibold');
    $FONT_MEASURE_SIZE = 11 * $SCALE;
    $FONT_MEASURE_SIZE_BIG = 12 * $SCALE;

    $FONT_ISP = tryFont('OpenSans-Semibold');
    $FONT_ISP_SIZE = 9 * $SCALE;

    $FONT_TIMESTAMP = tryFont("OpenSans-Light");
    $FONT_TIMESTAMP_SIZE = 8 * $SCALE;

    $FONT_WATERMARK = tryFont('OpenSans-Semibold');
    $FONT_WATERMARK_SIZE = 10 * $SCALE;

    // configure text colors - HAST IT Dark Mode
    $TEXT_COLOR_LABEL = imagecolorallocate($im, 148, 163, 184); // slate-400
    $TEXT_COLOR_PING_METER = imagecolorallocate($im, 251, 146, 60); // orange-400
    $TEXT_COLOR_JIT_METER = imagecolorallocate($im, 251, 146, 60); // orange-400
    $TEXT_COLOR_DL_METER = imagecolorallocate($im, 52, 211, 153); // emerald-400
    $TEXT_COLOR_UL_METER = imagecolorallocate($im, 96, 165, 250); // blue-400
    $TEXT_COLOR_MEASURE = imagecolorallocate($im, 148, 163, 184); // slate-400
    $TEXT_COLOR_ISP = imagecolorallocate($im, 100, 116, 139); // slate-500
    $SEPARATOR_COLOR = imagecolorallocate($im, 51, 65, 85); // slate-700
    $TEXT_COLOR_TIMESTAMP = imagecolorallocate($im, 100, 116, 139); // slate-500
    $TEXT_COLOR_WATERMARK = imagecolorallocate($im, 16, 185, 129); // emerald-500

    // configure positioning - modern centered layout
    $POSITION_X_PING = 185 * $SCALE;
    $POSITION_Y_PING_LABEL = 35 * $SCALE;
    $POSITION_Y_PING_METER = 75 * $SCALE;
    $POSITION_Y_PING_MEASURE = 75 * $SCALE;

    $POSITION_X_JIT = 375 * $SCALE;
    $POSITION_Y_JIT_LABEL = 35 * $SCALE;
    $POSITION_Y_JIT_METER = 75 * $SCALE;
    $POSITION_Y_JIT_MEASURE = 75 * $SCALE;

    $POSITION_X_DL = 185 * $SCALE;
    $POSITION_Y_DL_LABEL = 125 * $SCALE;
    $POSITION_Y_DL_METER = 170 * $SCALE;
    $POSITION_Y_DL_MEASURE = 200 * $SCALE;

    $POSITION_X_UL = 375 * $SCALE;
    $POSITION_Y_UL_LABEL = 125 * $SCALE;
    $POSITION_Y_UL_METER = 170 * $SCALE;
    $POSITION_Y_UL_MEASURE = 200 * $SCALE;

    $POSITION_X_ISP = 10 * $SCALE;
    $POSITION_Y_ISP = 245 * $SCALE;

    $SEPARATOR_Y = 230 * $SCALE;

    $POSITION_X_TIMESTAMP= 10 * $SCALE;
    $POSITION_Y_TIMESTAMP = 268 * $SCALE;

    $POSITION_Y_WATERMARK = 268 * $SCALE;

    // configure labels
    $MBPS_TEXT = 'Mbit/s';
    $MS_TEXT = 'ms';
    $PING_TEXT = 'Ping';
    $JIT_TEXT = 'Jitter';
    $DL_TEXT = 'Download';
    $UL_TEXT = 'Upload';
    $WATERMARK_TEXT = 'HAST IT';

    // create text boxes for each part of the image
    $mbpsBbox = imageftbbox($FONT_MEASURE_SIZE_BIG, 0, $FONT_MEASURE, $MBPS_TEXT);
    $msBbox = imageftbbox($FONT_MEASURE_SIZE, 0, $FONT_MEASURE, $MS_TEXT);
    $pingBbox = imageftbbox($FONT_LABEL_SIZE, 0, $FONT_LABEL, $PING_TEXT);
    $pingMeterBbox = imageftbbox($FONT_METER_SIZE, 0, $FONT_METER, $ping);
    $jitBbox = imageftbbox($FONT_LABEL_SIZE, 0, $FONT_LABEL, $JIT_TEXT);
    $jitMeterBbox = imageftbbox($FONT_METER_SIZE, 0, $FONT_METER, $jit);
    $dlBbox = imageftbbox($FONT_LABEL_SIZE_BIG, 0, $FONT_LABEL, $DL_TEXT);
    $dlMeterBbox = imageftbbox($FONT_METER_SIZE_BIG, 0, $FONT_METER, $dl);
    $ulBbox = imageftbbox($FONT_LABEL_SIZE_BIG, 0, $FONT_LABEL, $UL_TEXT);
    $ulMeterBbox = imageftbbox($FONT_METER_SIZE_BIG, 0, $FONT_METER, $ul);
    $watermarkBbox = imageftbbox($FONT_WATERMARK_SIZE, 0, $FONT_WATERMARK, $WATERMARK_TEXT);
    $POSITION_X_WATERMARK = $WIDTH - $watermarkBbox[4] - 4 * $SCALE;

    // put the parts together to draw the image
    imagefilledrectangle($im, 0, 0, $WIDTH, $HEIGHT, $BACKGROUND_COLOR);
    // ping
    imagefttext($im, $FONT_LABEL_SIZE, 0, $POSITION_X_PING - $pingBbox[4] / 2, $POSITION_Y_PING_LABEL, $TEXT_COLOR_LABEL, $FONT_LABEL, $PING_TEXT);
    imagefttext($im, $FONT_METER_SIZE, 0, $POSITION_X_PING - $pingMeterBbox[4] / 2 - $msBbox[4] / 2 - $SMALL_SEP / 2, $POSITION_Y_PING_METER, $TEXT_COLOR_PING_METER, $FONT_METER, $ping);
    imagefttext($im, $FONT_MEASURE_SIZE, 0, $POSITION_X_PING + $pingMeterBbox[4] / 2 + $SMALL_SEP / 2 - $msBbox[4] / 2, $POSITION_Y_PING_MEASURE, $TEXT_COLOR_MEASURE, $FONT_MEASURE, $MS_TEXT);
    // jitter
    imagefttext($im, $FONT_LABEL_SIZE, 0, $POSITION_X_JIT - $jitBbox[4] / 2, $POSITION_Y_JIT_LABEL, $TEXT_COLOR_LABEL, $FONT_LABEL, $JIT_TEXT);
    imagefttext($im, $FONT_METER_SIZE, 0, $POSITION_X_JIT - $jitMeterBbox[4] / 2 - $msBbox[4] / 2 - $SMALL_SEP / 2, $POSITION_Y_JIT_METER, $TEXT_COLOR_JIT_METER, $FONT_METER, $jit);
    imagefttext($im, $FONT_MEASURE_SIZE, 0, $POSITION_X_JIT + $jitMeterBbox[4] / 2 + $SMALL_SEP / 2 - $msBbox[4] / 2, $POSITION_Y_JIT_MEASURE, $TEXT_COLOR_MEASURE, $FONT_MEASURE, $MS_TEXT);
    // dl
    imagefttext($im, $FONT_LABEL_SIZE_BIG, 0, $POSITION_X_DL - $dlBbox[4] / 2, $POSITION_Y_DL_LABEL, $TEXT_COLOR_LABEL, $FONT_LABEL, $DL_TEXT);
    imagefttext($im, $FONT_METER_SIZE_BIG, 0, $POSITION_X_DL - $dlMeterBbox[4] / 2, $POSITION_Y_DL_METER, $TEXT_COLOR_DL_METER, $FONT_METER, $dl);
    imagefttext($im, $FONT_MEASURE_SIZE_BIG, 0, $POSITION_X_DL - $mbpsBbox[4] / 2, $POSITION_Y_DL_MEASURE, $TEXT_COLOR_MEASURE, $FONT_MEASURE, $MBPS_TEXT);
    // ul
    imagefttext($im, $FONT_LABEL_SIZE_BIG, 0, $POSITION_X_UL - $ulBbox[4] / 2, $POSITION_Y_UL_LABEL, $TEXT_COLOR_LABEL, $FONT_LABEL, $UL_TEXT);
    imagefttext($im, $FONT_METER_SIZE_BIG, 0, $POSITION_X_UL - $ulMeterBbox[4] / 2, $POSITION_Y_UL_METER, $TEXT_COLOR_UL_METER, $FONT_METER, $ul);
    imagefttext($im, $FONT_MEASURE_SIZE_BIG, 0, $POSITION_X_UL - $mbpsBbox[4] / 2, $POSITION_Y_UL_MEASURE, $TEXT_COLOR_MEASURE, $FONT_MEASURE, $MBPS_TEXT);
    // isp
    imagefttext($im, $FONT_ISP_SIZE, 0, $POSITION_X_ISP, $POSITION_Y_ISP, $TEXT_COLOR_ISP, $FONT_ISP, $ispinfo);
    // separator
    imagefilledrectangle($im, 0, $SEPARATOR_Y, $WIDTH, $SEPARATOR_Y, $SEPARATOR_COLOR);
    // timestamp
    imagefttext($im, $FONT_TIMESTAMP_SIZE, 0, $POSITION_X_TIMESTAMP, $POSITION_Y_TIMESTAMP, $TEXT_COLOR_TIMESTAMP, $FONT_TIMESTAMP, $timestamp);
    // watermark
    imagefttext($im, $FONT_WATERMARK_SIZE, 0, $POSITION_X_WATERMARK, $POSITION_Y_WATERMARK, $TEXT_COLOR_WATERMARK, $FONT_WATERMARK, $WATERMARK_TEXT);

    // send the image to the browser
    header('Content-Type: image/png');
    imagepng($im);
}

$speedtest = getSpeedtestUserById($_GET['id']);
if (!is_array($speedtest)) {
    exit(1);
}

drawImage($speedtest);
