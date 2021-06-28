<?php

namespace ReCompiler;

use JetBrains\PhpStorm\Pure;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

class DocParserFactory
{
    /** @var PhpDocParser */
    protected $docParser;

    public function __construct()
    {
        $this->docParser = new PhpDocParser(new TypeParser(), new ConstExprParser());
    }

    public function tokenize(string $docComment): PhpDocNode
    {
        return $this->docParser->parse(new TokenIterator((new Lexer())->tokenize($docComment)));
    }
}