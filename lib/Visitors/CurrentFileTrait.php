<?php

namespace ReCompiler\Visitors;

trait CurrentFileTrait
{
    /** @var string */
    protected static $currentFile = "";
    public static function setCurrentFile(string $file) : void
    {
        static::$currentFile = $file;
    }
    public static function getCurrentFile() : string
    {
        return static::$currentFile;
    }
}