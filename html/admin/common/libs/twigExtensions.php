<?php
use Money\Currency;
use Money\Money;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use Money\Formatter\DecimalMoneyFormatter;

$TWIG->addFilter(new \Twig\TwigFilter('timeago', function ($datetime) {
    $time = time() - strtotime($datetime);
    $units = array (
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );
    foreach ($units as $unit => $val) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return ($val == 'second')? 'a few seconds ago' :
            (($numberOfUnits>1) ? $numberOfUnits : 'a')
            .' '.$val.(($numberOfUnits>1) ? 's' : '').' ago';
    }
}));
$TWIG->addFilter(new \Twig\TwigFilter('formatsize', function ($var) {
    global $bCMS;
    return $bCMS->formatSize($var);
}));
$TWIG->addFilter(new \Twig\TwigFilter('cleanString', function ($var) {
    global $bCMS;
    return $bCMS->cleanString($var);
}));
$TWIG->addFilter(new \Twig\TwigFilter('serverPermissions', function ($permissionid) {
    global $AUTH;
    if (!$AUTH->login) return false;
    else return $AUTH->serverPermissionCheck($permissionid);
}));
$TWIG->addFilter(new \Twig\TwigFilter('instancePermissions', function ($permissionid) {
    global $AUTH;
    if (!$AUTH->login) return false;
    else return $AUTH->instancePermissionCheck($permissionid);
}));
$TWIG->addFilter(new \Twig\TwigFilter('modifyGet', function ($array) {
    global $bCMS;
    return http_build_query(($bCMS->modifyGet($array)));
}));
$TWIG->addFilter(new \Twig\TwigFilter('randomString', function ($characters) {
    global $bCMS;
    return $bCMS->randomString($characters);
}));
$TWIG->addFilter(new \Twig\TwigFilter('s3URL', function ($fileid, $size = null) {
    global $CONFIG;
    // The size parameter is no longer used, but is kept incase it is used in the future.
    return $CONFIG['ROOTURL'] . "/api/file/index.php?r&f=" . $fileid;
}));
$TWIG->addFilter(new \Twig\TwigFilter('jsonDecode', function ($raw) {
    if ($raw == null) return [];
    $data = json_decode($raw,true);
    return $data;
}));
$TWIG->addFilter(new \Twig\TwigFilter('cableColourParse', function ($raw,$length = 1,$text = false) {
    if ($raw == null) return "";
    $data = json_decode($raw,true);
    if (isset($data[$length])) return $data[$length][($text ? "text":"background")];
    else {
        $closest = null;
        foreach ($data as $key=>$item) {
            if ($closest === null || abs($length - $closest) > abs($key - $length)) {
                $closest = $key;
            }
        }
        if ($closest != null) return $data[$closest][($text ? "text":"background")];
        else return ($text ? "white":"black");
    }
}));
$TWIG->addFilter(new \Twig\TwigFilter('fontAwesomeFile', function ($extension) {
    switch (strtolower($extension)) {
        case "gif":
            return 'fa-file-image';
            break;

        case "jpeg":
            return 'fa-file-image';
            break;

        case "jpg":
            return 'fa-file-image';
            break;

        case "png":
            return 'fa-file-image';
            break;

        case "pdf":
            return 'fa-file-pdf';
            break;

        case "doc":
            return 'fa-file-word';
            break;

        case "docx":
            return 'fa-file-word';
            break;

        case "ppt":
            return 'fa-file-powerpoint';
            break;

        case "pptx":
            return 'fa-file-powerpoint';
            break;

        case "xls":
            return 'fa-file-excel';
            break;

        case "xlsx":
            return 'fa-file-excel';
            break;

        case "csv":
            return 'fa-file-csv';
            break;

        case "aac":
            return 'fa-file-audio';
            break;

        case "mp3":
            return 'fa-file-audio';
            break;

        case "ogg":
            return 'fa-file-audio';
            break;

        case "avi":
            return 'fa-file-video';
            break;

        case "flv":
            return 'fa-file-video';
            break;

        case "mkv":
            return 'fa-file-video';
            break;

        case "mp4":
            return 'fa-file-video';
            break;

        case "gz":
            return 'fa-file-archive';
            break;

        case "zip":
            return 'fa-file-archive';
            break;

        case "css":
            return 'fa-file-code';
            break;

        case "html":
            return 'fa-file-code';
            break;

        case "js":
            return 'fa-file-code';
            break;

        case "txt":
            return 'fa-file-alt';
            break;
        default:
            return 'fa-file';
            break;

    }
}));

$TWIG->addFilter(new \Twig\TwigFilter('aTag', function ($id) {
    global $bCMS;
    return $bCMS->aTag($id);
}));
$TWIG->addFilter(new \Twig\TwigFilter('md5', function ($id) {
    return md5($id);
}));


$TWIG->addFilter(new \Twig\TwigFilter('money', function ($variable,$currency = false) {
    global $AUTH;
    if (!is_object($variable)) $variable = new Money($variable, new Currency(($currency ?: $AUTH->data['instance']['instances_config_currency'])));
    $currencies = new ISOCurrencies();
    $numberFormatter = new NumberFormatter('en_GB', NumberFormatter::CURRENCY);
    $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);
    return $moneyFormatter->format($variable);
}));
$TWIG->addFilter(new \Twig\TwigFilter('moneyDecimal', function ($variable,$currency = false) {
    global $AUTH;
    if ($variable === null) return null;
    if (!is_object($variable)) $variable = new Money($variable, new Currency(($currency ?: $AUTH->data['instance']['instances_config_currency'])));
    $currencies = new ISOCurrencies();
    $moneyFormatter = new DecimalMoneyFormatter($currencies);
    return $moneyFormatter->format($variable);
}));
$TWIG->addFilter(new \Twig\TwigFilter('moneyPositive', function ($variable) {
    //TO BE USED WITH CAUTION - ONLY NORMALLY FOR CHECKING GREATER THAN 0
    if (!is_object($variable)) return ($variable > 0);
    return $variable->isPositive(); //False when 0
}));
$TWIG->addFilter(new \Twig\TwigFilter('mass', function ($variable) {
    return number_format((float)$variable, 2, '.', '') . "kg";
}));
$TWIG->addFilter(new \Twig\TwigFilter('nbsp', function ($string) {
    return str_replace(" ","&nbsp;", $string);
}, [
    'pre_escape' => 'html',
    'is_safe' => ['html'],
]));