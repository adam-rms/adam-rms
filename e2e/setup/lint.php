<?php
// Static checks on the app's source, run by e2e/static/code.spec.ts. Needs no database.
// Prints JSON: {"<check>": ["<problem>", ...], ...} — an empty list means the check passed.
//   php      - every PHP file in src/ and db/ passes `php -l` on the PHP running this script
//   includes - every `require`/`include` of __DIR__ . '<literal path>' points at a file that exists
//   twig     - every template in src/ compiles: syntax, and every filter, function, test and tag it uses exists
//   templates - every template named by a string literal (in render() calls and in extends/include/embed/import/from tags) exists
require __DIR__ . '/../../vendor/autoload.php';

$root = realpath(__DIR__ . '/../..');
$src = "$root/src";

function files(string $dir, string $extension): array {
    $found = [];
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $file) {
        if ($file->getExtension() === $extension) $found[] = $file->getPathname();
    }
    sort($found);
    return $found;
}
function relative(string $path): string {
    global $root;
    return substr($path, strlen($root) + 1);
}

$problems = ["php" => [], "includes" => [], "twig" => [], "templates" => []];
$phpFiles = array_merge(files($src, "php"), files("$root/db", "php"));

foreach ($phpFiles as $file) {
    exec(escapeshellarg(PHP_BINARY) . " -l " . escapeshellarg($file) . " 2>&1", $output, $status);
    if ($status !== 0) $problems["php"][] = relative($file) . ": " . trim(implode(" ", array_filter($output, fn($line) => !str_starts_with($line, "Errors parsing"))));
    $output = [];

    $code = file_get_contents($file);
    preg_match_all("/(?:require|include)(?:_once)?\s*\(?\s*__DIR__\s*\.\s*['\"]([^'\"]+)['\"]\s*\)?\s*;/", $code, $matches);
    foreach ($matches[1] as $path) {
        if (!file_exists(dirname($file) . $path)) $problems["includes"][] = relative($file) . " includes missing file " . $path;
    }
    preg_match_all("/TWIG->render\(\s*['\"]([^'\"]+)['\"]/", $code, $matches);
    foreach ($matches[1] as $template) {
        if (!file_exists("$src/$template")) $problems["templates"][] = relative($file) . " renders missing template " . $template;
    }
}

// The same environment head.php builds in DEV_MODE, with the same extensions
$TWIG = new \Twig\Environment(new \Twig\Loader\FilesystemLoader([$src]), ['debug' => true, 'charset' => 'utf-8']);
$TWIG->addExtension(new \Twig\Extension\DebugExtension());
$TWIG->addExtension(new \Twig\Extra\String\StringExtension());
require "$src/common/libs/twigExtensions.php";
// search.php registers these itself just before rendering search.twig
foreach (['generate_result_url', 'generate_result_tag'] as $name) $TWIG->addFunction(new \Twig\TwigFunction($name, fn() => null));

foreach (files($src, "twig") as $file) {
    $name = substr($file, strlen($src) + 1);
    try {
        $TWIG->compileSource($TWIG->getLoader()->getSourceContext($name));
    } catch (\Twig\Error\Error $e) {
        $problems["twig"][] = "$name: " . $e->getMessage();
    }
    preg_match_all("/\{%-?\s*(?:extends|include|embed|import|from)\s+['\"]([^'\"]+)['\"]/", file_get_contents($file), $matches);
    foreach ($matches[1] as $template) {
        if (!file_exists("$src/$template")) $problems["templates"][] = "$name uses missing template $template";
    }
}

echo json_encode($problems);
