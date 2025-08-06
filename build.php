<?php

$pharPath = __DIR__ . '/bin/deploy.phar';
$binPath = __DIR__ . '/bin/deploy';

@unlink($pharPath);
@unlink($binPath);

$phar = new Phar($pharPath);
$phar->startBuffering();

function addDirectoryToPhar(Phar $phar, string $sourceDir, string $relativeBase)
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $localPath = $relativeBase . '/' . substr($file->getRealPath(), strlen(realpath($sourceDir)) + 1);
            $phar->addFile($file->getRealPath(), $localPath);
        }
    }
}

addDirectoryToPhar($phar, __DIR__ . '/src', 'src');
addDirectoryToPhar($phar, __DIR__ . '/vendor', 'vendor');

$phar->addFile(__DIR__ . '/composer.json', 'composer.json');
$phar->addFile(__DIR__ . '/composer.lock', 'composer.lock');
$phar->addFile(__DIR__ . '/version.txt', 'version.txt');

$stub = "#!/usr/bin/env php\n" . $phar->createDefaultStub('src/Standalone.php');
$phar->setStub($stub);

$phar->setSignatureAlgorithm(Phar::SHA256);
$phar->stopBuffering();
$phar->compressFiles(Phar::GZ);

rename($pharPath, $binPath);
@chmod($binPath, 0755);

$hash = hash_file('sha256', $binPath);

file_put_contents(__DIR__ . '/build_hash.txt', $hash);

echo "PHAR created successfully.\n";
echo "Location: $binPath\n";
echo "Hash SHA-256: $hash";
