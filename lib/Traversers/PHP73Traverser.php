<?php

namespace ReCompiler\Traversers;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use ReCompiler\DocParserFactory;
use ReCompiler\Exceptions\UnavailableException;
use Serializable;
/**
 * @todo Preprocess type variants
 * @todo Preprocess ReflectionReference
 * @todo Add ext-hash to composer.json
 */
class PHP73Traverser extends PHP74Traverser
{
    const PHP_VERSION = "7.3";
    const SERIALIZE = "__serialize";
    const SLEEP = "__sleep";
    const UNSERIALIZE = "__unserialize";
    /** @throws UnavailableException */
    public function enterNode(Node $node) : ?Node
    {
        $node = parent::enterNode($node);
        if (null === $node) {
            return null;
        }
        if ($node instanceof ArrowFunction) {
            $node = $this->preprocessArrowFunctions($node);
        }
        if ($node instanceof Node\Stmt\Property) {
            $node = $this->preprocessPropertyType($node);
        }
        if ($node instanceof AssignOp\Coalesce) {
            $node = $this->convertNullCoalescingAssignToOperator($node);
        }
        if ($node instanceof Array_) {
            $node = $this->convertArraysWithUnpacks($node);
        }
        if ($node instanceof Node\Stmt\ClassMethod && self::TO_STRING_METHOD === $node->name->name) {
            $node = $this->removeExceptionsFromToString($node);
        }
        if ($node instanceof Node\Name && in_array("FFI", $node->parts, true)) {
            throw new UnavailableException(sprintf("FFI is not available in PHP %s and there is no suggested solution", static::PHP_VERSION));
        }
        if ($node instanceof Class_) {
            $node = $this->preprocessSerializeAndUnserialize($node);
        }
        return $node;
    }
    protected function preprocessArrowFunctions(ArrowFunction $arrowFunction) : Closure
    {
        $closure = new Closure();
        foreach ($arrowFunction->getSubNodeNames() as $subNodeName) {
            $closure->{$subNodeName} = $arrowFunction->{$subNodeName};
        }
        $closure->stmts[] = new Node\Stmt\Return_($arrowFunction->expr);
        return $closure;
    }
    protected function preprocessPropertyType(Node\Stmt\Property $property) : Node\Stmt\Property
    {
        if (isset($property->type)) {
            $propType = $property->type;
            $type = "";
            if ($propType instanceof Node\NullableType) {
                $propType = $propType->type;
                $type .= "null|";
            }
            if (!$propType instanceof Node\Identifier && $propType->isFullyQualified()) {
                $type .= "\\";
            }
            $type .= $propType->toString();
            // TODO: Modify doc block if exists
            $doc = $property->getDocComment();
            $docText = "/** @var {$type} */";
            if (isset($doc)) {
                $docAst = (new DocParserFactory())->tokenize($doc->getText());
                if (count($docAst->getTagsByName("@var")) === 0) {
                    $docAst->children[] = new PhpDocTagNode("@var", new VarTagValueNode(new IdentifierTypeNode($type), "", ""));
                }
                $docText = (string) $docAst;
            }
            $property->setDocComment(new Doc($docText));
            $property->type = null;
        }
        return $property;
    }
    protected function convertNullCoalescingAssignToOperator(AssignOp\Coalesce $coalesce) : Assign
    {
        return new Assign($coalesce->var, new BinaryOp\Coalesce($coalesce->var, $coalesce->expr));
    }
    protected function convertArraysWithUnpacks(Array_ $array) : Node
    {
        $hasUnpacks = false;
        $args = $arr = [];
        foreach ($array->items as $arrayItem) {
            if (isset($arrayItem->unpack) && $arrayItem->unpack) {
                $hasUnpacks = true;
                $args[] = new Array_($arr, ["kind" => Array_::KIND_SHORT]);
                $arr = [];
                $arrayItem->unpack = false;
                $args[] = $arrayItem;
            } else {
                $arr[] = $arrayItem->value;
            }
        }
        $args[] = new Array_($arr, ["kind" => Array_::KIND_SHORT]);
        if (!$hasUnpacks) {
            return $array;
        }
        $arrayMerge = new Node\Expr\FuncCall(new Node\Name("array_merge"));
        foreach ($args as $arg) {
            $arrayMerge->args[] = new Node\Arg($arg);
        }
        return $arrayMerge;
    }
    protected function removeExceptionsFromToString(Node\Stmt\ClassMethod $method) : Node\Stmt\ClassMethod
    {
        if (isset($method->stmts)) {
            foreach ($method->stmts as $index => $stmt) {
                if ($stmt instanceof Node\Stmt\Throw_) {
                    unset($method->stmts[$index]);
                }
            }
            $method->stmts = array_values($method->stmts);
        }
        return $method;
    }
    protected function preprocessSerializeAndUnserialize(Class_ $class): Class_
    {
        if ($class->getMethod(self::SERIALIZE) !== null) {
            if ($class->getMethod(self::SLEEP) === null) {
                $class->stmts[] = new Node\Stmt\ClassMethod(self::SLEEP, ["stmts" => [new Node\Stmt\Return_(new Node\Expr\MethodCall(new Node\Expr\Variable("this"), self::SERIALIZE))], "flags" => Class_::MODIFIER_PUBLIC]);
            }
            if ($class->getMethod("serialize") === null) {
                $class->stmts[] = new Node\Stmt\ClassMethod("serialize", ["stmts" => [new Node\Stmt\Return_(new Node\Expr\FuncCall(new Node\Name("serialize"), [new Node\Arg(new Node\Expr\MethodCall(new Node\Expr\Variable("this"), self::SERIALIZE))]))], "flags" => Class_::MODIFIER_PUBLIC]);
            }
        }
        if ($class->getMethod(self::UNSERIALIZE) !== null && $class->getMethod("unserialize") === null) {
            $class->stmts[] = new Node\Stmt\ClassMethod("unserialize", ["params" => [new Node\Param(new Node\Expr\Variable("data"))], "stmts" => [new Node\Stmt\Expression(new Node\Expr\MethodCall(new Node\Expr\Variable("this"), self::UNSERIALIZE, [new Node\Arg(new Node\Expr\FuncCall(new Node\Name("unserialize"), [new Node\Arg(new Node\Expr\Variable("data"))]))]))], "flags" => Class_::MODIFIER_PUBLIC]);
        }
        if (!count(array_filter($class->implements, function (Node\Name $name) {
            return $name->parts[0] === Serializable::class;
        })) && $class->getMethod("serialize") !== null && $class->getMethod("unserialize") !== null) {
            $class->implements[] = new Node\Name("\\Serializable");
        }
        return $class;
    }
}