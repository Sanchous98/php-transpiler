<?php

namespace ReCompiler\Visitors\Extensions;

interface HashExtContent
{
    public const FUNCTIONS = ["\\hash_algos", "\\hash_copy", "\\hash_equals", "\\hash_file", "\\hash_final", "\\hash_hkdf", "\\hash_hmac_algos", "\\hash_hmac_file", "\\hash_hmac", "\\hash_init", "\\hash_pbkdf2", "\\hash_update_file", "\\hash_update_stream", "\\hash_update", "\\hash"];
    public const CLASSES = ["\\HashContext"];
    public const CONSTANTS = ["\\HASH_HMAC"];
    public const ALL = [...self::FUNCTIONS, ...self::CLASSES, ...self::CONSTANTS];
}