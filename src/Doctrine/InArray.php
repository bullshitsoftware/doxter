<?php

declare(strict_types=1);

namespace App\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class InArray extends FunctionNode
{
    private ?Node $column = null;
    private ?Node $value = null;

    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->column = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->value = $parser->StringPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        $column = $this->column->dispatch($sqlWalker);
        $value = $this->value->dispatch($sqlWalker);

        return "(SELECT value FROM json_each($column) WHERE value = $value)";
    }
}
