<?php

namespace ReCompiler;

use Exception;
use PhpParser\Error;
use Composer\Script\Event;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
class Composer
{
    const PSR4 = "psr-4";
    const PSR0 = "psr-0";
    const CLASSMAP = "classmap";
    const FILES = "files";
    /** @throws Exception */
    public static function postInstallCmd(Event $event) : void
    {
        $composer = $event->getComposer();
        $mapping = array_merge_recursive($composer->getPackage()->getAutoload(), $composer->getPackage()->getDevAutoload());
        $requires = $composer->getPackage()->getRequires();
        if (!isset($requires["php"])) {
            return;
        }
        $phpVersion = $requires["php"]->getConstraint()->getLowerBound()->getVersion();
        [$major, $minor] = explode(".", $phpVersion);
        $traverser = new NodeTraverser();
        /** @psalm-suppress MixedArgument, MixedMethodCall */
        $files = static::getFileList($mapping, dirname($composer->getConfig()->getConfigSource()->getName()));
        $traverser->addVisitor(TraverserFactory::factory($major, $minor));
        foreach ($files as $file) {
            include_once $file;
        }
        foreach ($files as $file) {
            try {
                $ast = (new ParserFactory())->create(ParserFactory::PREFER_PHP7)->parse(file_get_contents($file));
            } catch (Error $error) {
                echo "Parse error: {$error->getMessage()}\n";
                return;
            }
            $ast = $traverser->traverse($ast);
            $printer = new Standard();
            file_put_contents($file, $printer->prettyPrintFile($ast));
        }
    }
    /**
     * @param  array  $mapping
     * @param  string  $projectDir
     * @return string[]
     */
    private static function getFileList(array $mapping, string $projectDir) : array
    {
        $sourceCodePaths = [];
        $files = [];
        foreach ($mapping as $type => $paths) {
            foreach ($paths as $path) {
                $path = "{$projectDir}/{$path}";
                switch ($type) {
                    case self::PSR0:
                    case self::PSR4:
                        $path = rtrim($path, "/");
                        $sourceCodePaths[] = "{$path}/**/*.php";
                        $sourceCodePaths[] = "{$path}/*.php";
                        break;
                    case self::CLASSMAP:
                        $sourceCodePaths[] = $path;
                        break;
                    case self::FILES:
                        $files[] = $path;
                }
            }
        }
        foreach ($sourceCodePaths as $path) {
            foreach (glob($path) as $file) {
                $files[] = $file;
            }
        }
        return $files;
    }
}