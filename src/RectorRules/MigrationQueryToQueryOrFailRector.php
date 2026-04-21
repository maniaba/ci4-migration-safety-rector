<?php

declare(strict_types=1);

namespace Maniaba\Rector\RectorRules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Replaces `$this->db->query($sql)` with `$this->queryOrFail('ClassName', $sql)`
 * in CodeIgniter 4 database migration files.
 *
 * Background
 * ----------
 * On production CI4 deployments DBDebug=false, which means $this->db->query()
 * silently returns false on SQL error instead of throwing an exception.
 * The CI4 migration runner only catches exceptions, so a failed CREATE TRIGGER
 * (or any DDL) is silently recorded as "run" while the SQL never executed.
 *
 * MigrationHelper::queryOrFail() wraps the call and throws a RuntimeException
 * on failure, making migration failures visible in deployment logs.
 *
 * Scope
 * -----
 * Only fire-and-forget DDL statements are replaced:
 *   $this->db->query('CREATE TRIGGER ...');   // replaced
 *
 * Assigned calls (SELECT queries that return a result set) are left untouched:
 *   $result = $this->db->query('SELECT ...');  // unchanged
 *
 * The rule also restricts itself to files whose path contains
 * /Database/Migrations/ — other classes that happen to use $this->db->query()
 * are not affected.
 *
 * The label (first argument to queryOrFail) is derived from the migration
 * filename with the date prefix stripped:
 *   2026-02-08-070927_TriggerCreateOrderProcessStages.php -> 'TriggerCreateOrderProcessStages'
 */
final class MigrationQueryToQueryOrFailRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            "Replace \$this->db->query(\$sql) with \$this->queryOrFail('ClassName', \$sql) in CI4 migration files",
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
                        $this->db->query('CREATE TRIGGER `trg` AFTER INSERT ON `t` FOR EACH ROW BEGIN END');
                        $this->db->query('DROP TRIGGER IF EXISTS `trg`');
                        CODE_SAMPLE,
                    <<<'CODE_SAMPLE'
                        $this->queryOrFail('MyMigration', 'CREATE TRIGGER `trg` AFTER INSERT ON `t` FOR EACH ROW BEGIN END');
                        $this->queryOrFail('MyMigration', 'DROP TRIGGER IF EXISTS `trg`');
                        CODE_SAMPLE,
                ),
            ],
        );
    }

    /**
     * @return list<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        // Match Expression *statement* nodes — not MethodCall directly.
        // An Expression wraps a call used as a fire-and-forget statement.
        // Assigned calls ($result = $this->db->query(...)) have an Assign
        // as the Expression's expr and will not match the inner instanceof check.
        return [Expression::class];
    }

    /**
     * @param Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        // Must be a bare method call, not an assignment or other expression.
        if (! $node->expr instanceof MethodCall) {
            return null;
        }

        $call = $node->expr;

        // Must be ->query(...)
        if (! $this->isName($call->name, 'query')) {
            return null;
        }

        // Caller must be a property fetch (->db)
        if (! $call->var instanceof PropertyFetch) {
            return null;
        }

        // Property must be named "db"
        if (! $this->isName($call->var->name, 'db')) {
            return null;
        }

        // Object must be $this
        if (! $call->var->var instanceof Variable) {
            return null;
        }

        if (! $this->isName($call->var->var, 'this')) {
            return null;
        }

        // Restrict to migration files only.
        $filePath = $this->getFile()->getFilePath();

        if (
            ! str_contains($filePath, DIRECTORY_SEPARATOR . 'Database' . DIRECTORY_SEPARATOR . 'Migrations' . DIRECTORY_SEPARATOR)
            && ! str_contains($filePath, '/Database/Migrations/')
        ) {
            return null;
        }

        // Derive label: strip date prefix from the migration filename.
        $baseName = pathinfo($filePath, PATHINFO_FILENAME);
        $label    = (string) preg_replace('/^\d{4}-\d{2}-\d{2}-\d{6}_/', '', $baseName);

        // Rebuild: $this->queryOrFail('Label', <original args...>)
        $node->expr = new MethodCall(
            new Variable('this'),
            new Identifier('queryOrFail'),
            [new Arg(new String_($label)), ...$call->args],
        );

        return $node;
    }
}
