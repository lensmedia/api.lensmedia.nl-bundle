<?php

namespace Lens\Bundle\LensApiBundle\Doctrine\Functions;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

use function count;
use function sprintf;

abstract class AbstractSpatialDQLFunction extends FunctionNode
{
    protected string $functionName;

    protected array $platforms = [];

    /**
     * @var Node[]
     */
    protected array $geomExpr = [];

    protected int $minGeomExpr;

    protected int $maxGeomExpr;

    public function parse(Parser $parser): void
    {
        $lexer = $parser->getLexer();

        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->geomExpr[] = $parser->ArithmeticPrimary();

        while (count($this->geomExpr) < $this->minGeomExpr || ((count($this->geomExpr) < $this->maxGeomExpr) && TokenType::T_CLOSE_PARENTHESIS !== $lexer->lookahead['type'])) {
            $parser->match(TokenType::T_COMMA);

            $this->geomExpr[] = $parser->ArithmeticPrimary();
        }

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        $this->validatePlatform($sqlWalker->getConnection()->getDatabasePlatform());

        $arguments = [];
        foreach ($this->geomExpr as $expression) {
            $arguments[] = $expression->dispatch($sqlWalker);
        }

        return sprintf('%s(%s)', $this->functionName, implode(', ', $arguments));
    }

    protected function validatePlatform(AbstractPlatform $platform): void
    {
    }
}
