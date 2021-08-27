<?php

namespace ReCompiler\Visitors;

use ParseError;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use ReCompiler\DocParserFactory;
use ReCompiler\Exceptions\UnavailableException;
use Serializable;
use function array_filter;
use function array_values;
use function count;
use function in_array;
use function sprintf;
/**
 * @todo Preprocess type variants
 * @todo Preprocess ReflectionReference
 * @todo Add ext-hash to composer.json
 */
class PHP73Visitor extends PHP74Visitor
{
    /** @var bool */
    protected $hashExtUsageFound = false;
    public const PHP_VERSION = "7.3";
    public const SERIALIZE = "__serialize";
    public const SLEEP = "__sleep";
    public const UNSERIALIZE = "__unserialize";
    /**
     * @throws UnavailableException
     */
    public function enterNode(Node $node) : ?Node
    {
        $node = parent::enterNode($node);
        if (null === $node) {
            return null;
        }
        if ($node instanceof ArrowFunction) {
            $node = $this->preprocessArrowFunctions($node);
        }
        if ($node instanceof Property) {
            $node = $this->preprocessPropertyType($node);
        }
        if ($node instanceof AssignOp\Coalesce) {
            $node = $this->convertNullCoalescingAssignToOperator($node);
        }
        if ($node instanceof Class_ || $node instanceof Interface_) {
            if ($node->extends) {
                $node = $this->preprocessExtensionVariants($node);
            }
            if ($node instanceof Class_ && $node->implements) {
                $node = $this->preprocessImplementationVariants($node);
            }
        }
        if ($node instanceof Const_ && $node->value instanceof Array_) {
            if (array_filter($node->value->items, function (?ArrayItem $item) {
                return isset($item) ? $item->unpack : false;
            })) {
                throw new ParseError(sprintf("Cannot convert array unpack on constant %s in %s", (string) $node->name, static::getCurrentFile()));
            }
        }
        if ($node instanceof Array_) {
            $node = $this->convertArraysWithUnpacks($node);
        }
        if ($node instanceof ClassMethod && self::TO_STRING_METHOD === $node->name->name) {
            $node = $this->removeExceptionsFromToString($node);
        }
        if ($node instanceof Name && in_array("FFI", $node->parts, true)) {
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
        $closure->stmts[] = new Return_($arrowFunction->expr);
        return $closure;
    }
    protected function preprocessPropertyType(Property $property) : Property
    {
        if (!isset($property->type)) {
            return $property;
        }
        $propType = $property->type;
        $type = "";
        if ($propType instanceof NullableType) {
            $propType = $propType->type;
            $type .= "null|";
        }
        if (!$propType instanceof Identifier && $propType->isFullyQualified()) {
            $type .= "\\";
        }
        if ($propType instanceof Node\UnionType) {
            $type .= implode("|", array_map(/** @param Identifier|Name $type */function ($type) {
                return (string) $type;
            }, $propType->types));
        } else {
            $type .= (string) $propType;
        }
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
        $arrayMerge = new FuncCall(new Name("array_merge"));
        foreach ($args as $arg) {
            $arrayMerge->args[] = new Arg($arg);
        }
        return $arrayMerge;
    }
    protected function removeExceptionsFromToString(ClassMethod $method) : ClassMethod
    {
        if (isset($method->stmts)) {
            foreach ($method->stmts as $index => $stmt) {
                if ($stmt instanceof Throw_) {
                    unset($method->stmts[$index]);
                }
            }
            $method->stmts = array_values($method->stmts);
        }
        return $method;
    }
    protected function preprocessSerializeAndUnserialize(Class_ $class) : Class_
    {
        if ($class->getMethod(self::SERIALIZE) !== null) {
            if ($class->getMethod(self::SLEEP) === null) {
                $class->stmts[] = new ClassMethod(self::SLEEP, ["stmts" => [new Return_(new MethodCall(new Variable("this"), self::SERIALIZE))], "flags" => Class_::MODIFIER_PUBLIC]);
            }
            if ($class->getMethod("serialize") === null) {
                $class->stmts[] = new ClassMethod("serialize", ["stmts" => [new Return_(new FuncCall(new Name("serialize"), [new Arg(new MethodCall(new Variable("this"), self::SERIALIZE))]))], "flags" => Class_::MODIFIER_PUBLIC]);
            }
        }
        if ($class->getMethod(self::UNSERIALIZE) !== null && $class->getMethod("unserialize") === null) {
            $class->stmts[] = new ClassMethod("unserialize", ["params" => [new Param(new Variable("data"))], "stmts" => [new Expression(new MethodCall(new Variable("this"), self::UNSERIALIZE, [new Arg(new FuncCall(new Name("unserialize"), [new Arg(new Variable("data"))]))]))], "flags" => Class_::MODIFIER_PUBLIC]);
        }
        if (!count(array_filter($class->implements, function (Name $name) {
            return $name->parts[0] === Serializable::class;
        })) && $class->getMethod("serialize") !== null && $class->getMethod("unserialize") !== null) {
            $class->implements[] = new Name("\\Serializable");
        }
        return $class;
    }
    /**
     * @psalm-param Class_|Interface_ $node
     */
    protected function preprocessExtensionVariants(ClassLike $node) : ClassLike
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor($this);

        return $node;
    }
    protected function preprocessImplementationVariants(Class_ $node) : Class_
    {
        return $node;
    }
}