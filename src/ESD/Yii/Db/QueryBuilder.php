<?php /** @noinspection Annotator */

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Db;

use ESD\Yii\Base\BaseObject;
use ESD\Yii\Base\InvalidArgumentException;
use ESD\Yii\Base\NotSupportedException;
use ESD\Yii\Db\Conditions\ConditionInterface;
use ESD\Yii\Db\Conditions\HashCondition;
use ESD\Yii\Helpers\StringHelper;

/**
 * QueryBuilder builds a SELECT SQL statement based on the specification given as a [[Query]] object.
 *
 * SQL statements are created from [[Query]] objects using the [[build()]]-method.
 *
 * QueryBuilder is also used by [[Command]] to build SQL statements such as INSERT, UPDATE, DELETE, CREATE TABLE.
 *
 * For more details and usage information on QueryBuilder, see the [guide article on query builders](guide:db-query-builder).
 *
 * @property string[] $conditionClasses Map of condition aliases to condition classes. For example: ```php
 * ['LIKE' => ESD\Yii\Db\condition\LikeCondition::class] ``` . This property is write-only.
 * @property string[] $expressionBuilders Array of builders that should be merged with the pre-defined ones in
 * [[expressionBuilders]] property. This property is write-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends BaseObject
{
    /**
     * The prefix for automatically generated query binding parameters.
     */
    const PARAM_PREFIX = ':qp';

    /**
     * @var Connection the database connection.
     */
    public $db;
    /**
     * @var string the separator between different fragments of a SQL statement.
     * Defaults to an empty space. This is mainly used by [[build()]] when generating a SQL statement.
     */
    public $separator = ' ';
    /**
     * @var array the abstract column types mapped to physical column types.
     * This is mainly used to support creating/modifying tables using DB-independent data type specifications.
     * Child classes should override this property to declare supported type mappings.
     */
    public $typeMap = [];

    /**
     * @var array map of query condition to builder methods.
     * These methods are used by [[buildCondition]] to build SQL conditions from array syntax.
     * @deprecated since 2.0.14. Is not used, will be dropped in 2.1.0.
     */
    protected $conditionBuilders = [];
    /**
     * @var array map of condition aliases to condition classes. For example:
     *
     * ```php
     * return [
     *     'LIKE' => ESD\Yii\Db\condition\LikeCondition::class,
     * ];
     * ```
     *
     * This property is used by [[createConditionFromArray]] method.
     * See default condition classes list in [[defaultConditionClasses()]] method.
     *
     * In case you want to add custom conditions support, use the [[setConditionClasses()]] method.
     *
     * @see setConditonClasses()
     * @see defaultConditionClasses()
     * @since 2.0.14
     */
    protected $conditionClasses = [];
    /**
     * @var string[]|ExpressionBuilderInterface[] maps expression class to expression builder class.
     * For example:
     *
     * ```php
     * [
     *    ESD\Yii\Db\Expression::class => ESD\Yii\Db\ExpressionBuilder::class
     * ]
     * ```
     * This property is mainly used by [[buildExpression()]] to build SQL expressions form expression objects.
     * See default values in [[defaultExpressionBuilders()]] method.
     *
     *
     * To override existing builders or add custom, use [[setExpressionBuilder()]] method. New items will be added
     * to the end of this array.
     *
     * To find a builder, [[buildExpression()]] will check the expression class for its exact presence in this map.
     * In case it is NOT present, the array will be iterated in reverse direction, checking whether the expression
     * extends the class, defined in this map.
     *
     * @see setExpressionBuilders()
     * @see defaultExpressionBuilders()
     * @since 2.0.14
     */
    protected $expressionBuilders = [];


    /**
     * Constructor.
     * @param Connection $connection the database connection.
     * @param array|null $config name-value pairs that will be used to initialize the object properties
     */
    public function __construct(Connection $connection, ?array $config = [])
    {
        $this->db = $connection;
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $this->expressionBuilders = array_merge($this->defaultExpressionBuilders(), $this->expressionBuilders);
        $this->conditionClasses = array_merge($this->defaultConditionClasses(), $this->conditionClasses);
    }

    /**
     * Contains array of default condition classes. Extend this method, if you want to change
     * default condition classes for the query builder. See [[conditionClasses]] docs for details.
     *
     * @return array
     * @see conditionClasses
     * @since 2.0.14
     */
    protected function defaultConditionClasses(): array
    {
        return [
            'NOT' => 'ESD\Yii\Db\Conditions\NotCondition',
            'AND' => 'ESD\Yii\Db\Conditions\AndCondition',
            'OR' => 'ESD\Yii\Db\Conditions\OrCondition',
            'BETWEEN' => 'ESD\Yii\Db\Conditions\BetweenCondition',
            'NOT BETWEEN' => 'ESD\Yii\Db\Conditions\BetweenCondition',
            'IN' => 'ESD\Yii\Db\Conditions\InCondition',
            'NOT IN' => 'ESD\Yii\Db\Conditions\InCondition',
            'LIKE' => 'ESD\Yii\Db\Conditions\LikeCondition',
            'NOT LIKE' => 'ESD\Yii\Db\Conditions\LikeCondition',
            'OR LIKE' => 'ESD\Yii\Db\Conditions\LikeCondition',
            'OR NOT LIKE' => 'ESD\Yii\Db\Conditions\LikeCondition',
            'EXISTS' => 'ESD\Yii\Db\Conditions\ExistsCondition',
            'NOT EXISTS' => 'ESD\Yii\Db\Conditions\ExistsCondition',
        ];
    }

    /**
     * Contains array of default expression builders. Extend this method and override it, if you want to change
     * default expression builders for this query builder. See [[expressionBuilders]] docs for details.
     *
     * @return array
     * @see $expressionBuilders
     * @since 2.0.14
     */
    protected function defaultExpressionBuilders(): array
    {
        return [
            'ESD\Yii\Db\Query' => 'ESD\Yii\Db\QueryExpressionBuilder',
            'ESD\Yii\Db\PdoValue' => 'ESD\Yii\Db\PdoValueBuilder',
            'ESD\Yii\Db\Expression' => 'ESD\Yii\Db\ExpressionBuilder',
            'ESD\Yii\Db\Conditions\ConjunctionCondition' => 'ESD\Yii\Db\Conditions\ConjunctionConditionBuilder',
            'ESD\Yii\Db\Conditions\NotCondition' => 'ESD\Yii\Db\Conditions\NotConditionBuilder',
            'ESD\Yii\Db\Conditions\AndCondition' => 'ESD\Yii\Db\Conditions\ConjunctionConditionBuilder',
            'ESD\Yii\Db\Conditions\OrCondition' => 'ESD\Yii\Db\Conditions\ConjunctionConditionBuilder',
            'ESD\Yii\Db\Conditions\BetweenCondition' => 'ESD\Yii\Db\Conditions\BetweenConditionBuilder',
            'ESD\Yii\Db\Conditions\InCondition' => 'ESD\Yii\Db\Conditions\InConditionBuilder',
            'ESD\Yii\Db\Conditions\LikeCondition' => 'ESD\Yii\Db\Conditions\LikeConditionBuilder',
            'ESD\Yii\Db\Conditions\ExistsCondition' => 'ESD\Yii\Db\Conditions\ExistsConditionBuilder',
            'ESD\Yii\Db\Conditions\SimpleCondition' => 'ESD\Yii\Db\Conditions\SimpleConditionBuilder',
            'ESD\Yii\Db\Conditions\HashCondition' => 'ESD\Yii\Db\Conditions\HashConditionBuilder',
            'ESD\Yii\Db\Conditions\BetweenColumnsCondition' => 'ESD\Yii\Db\Conditions\BetweenColumnsConditionBuilder',
        ];
    }

    /**
     * Setter for [[expressionBuilders]] property.
     *
     * @param string[] $builders array of builders that should be merged with the pre-defined ones
     * in [[expressionBuilders]] property.
     * @since 2.0.14
     * @see expressionBuilders
     */
    public function setExpressionBuilders(array $builders)
    {
        $this->expressionBuilders = array_merge($this->expressionBuilders, $builders);
    }

    /**
     * Setter for [[conditionClasses]] property.
     *
     * @param string[] $classes map of condition aliases to condition classes. For example:
     *
     * ```php
     * ['LIKE' => ESD\Yii\Db\condition\LikeCondition::class]
     * ```
     *
     * @since 2.0.14.2
     * @see conditionClasses
     */
    public function setConditionClasses(array $classes)
    {
        $this->conditionClasses = array_merge($this->conditionClasses, $classes);
    }

    /**
     * Generates a SELECT SQL statement from a [[Query]] object.
     *
     * @param Query $query the [[Query]] object from which the SQL statement will be generated.
     * @param array|null $params the parameters to be bound to the generated SQL statement. These parameters will
     * be included in the result with the additional parameters generated during the query building process.
     * @return array the generated SQL statement (the first array element) and the corresponding
     * parameters to be bound to the SQL statement (the second array element). The parameters returned
     * include those provided in `$params`.
     * @throws Exception
     */
    public function build(Query $query, ?array $params = []): array
    {
        $query = $query->prepare($this);

        $params = empty($params) ? $query->params : array_merge($params, $query->params);

        $clauses = [
            $this->buildSelect($query->select, $params, $query->distinct, $query->selectOption),
            $this->buildFrom($query->from, $params),
            $this->buildJoin($query->join, $params),
            $this->buildWhere($query->where, $params),
            $this->buildGroupBy($query->groupBy),
            $this->buildHaving($query->having, $params),
        ];

        $sql = implode($this->separator, array_filter($clauses));
        $sql = $this->buildOrderByAndLimit($sql, $query->orderBy, $query->limit, $query->offset);

        if (!empty($query->orderBy)) {
            foreach ($query->orderBy as $expression) {
                if ($expression instanceof ExpressionInterface) {
                    $this->buildExpression($expression, $params);
                }
            }
        }
        if (!empty($query->groupBy)) {
            foreach ($query->groupBy as $expression) {
                if ($expression instanceof ExpressionInterface) {
                    $this->buildExpression($expression, $params);
                }
            }
        }

        $union = $this->buildUnion($query->union, $params);
        if ($union !== '') {
            $sql = "($sql){$this->separator}$union";
        }

        return [$sql, $params];
    }

    /**
     * Builds given $expression
     *
     * @param ExpressionInterface $expression the expression to be built
     * @param array|null $params the parameters to be bound to the generated SQL statement. These parameters will
     * be included in the result with the additional parameters generated during the expression building process.
     * @return string the SQL statement that will not be neither quoted nor encoded before passing to DBMS
     * @throws InvalidArgumentException when $expression building is not supported by this QueryBuilder.
     * @see ExpressionBuilderInterface
     * @see expressionBuilders
     * @since 2.0.14
     * @see ExpressionInterface
     */
    public function buildExpression(ExpressionInterface $expression, ?array &$params = []): string
    {
        $builder = $this->getExpressionBuilder($expression);

        return $builder->build($expression, $params);
    }

    /**
     * Gets object of [[ExpressionBuilderInterface]] that is suitable for $expression.
     * Uses [[expressionBuilders]] array to find a suitable builder class.
     *
     * @param ExpressionInterface $expression
     * @return ExpressionBuilderInterface
     * @throws InvalidArgumentException when $expression building is not supported by this QueryBuilder.
     * @since 2.0.14
     * @see expressionBuilders
     */
    public function getExpressionBuilder(ExpressionInterface $expression)
    {
        $className = get_class($expression);

        if (!isset($this->expressionBuilders[$className])) {
            foreach (array_reverse($this->expressionBuilders) as $expressionClass => $builderClass) {
                if (is_subclass_of($expression, $expressionClass)) {
                    $this->expressionBuilders[$className] = $builderClass;
                    break;
                }
            }

            if (!isset($this->expressionBuilders[$className])) {
                throw new InvalidArgumentException('Expression of class ' . $className . ' can not be built in ' . get_class($this));
            }
        }

        if ($this->expressionBuilders[$className] === __CLASS__) {
            return $this;
        }

        if (!is_object($this->expressionBuilders[$className])) {
            $this->expressionBuilders[$className] = new $this->expressionBuilders[$className]($this);
        }

        return $this->expressionBuilders[$className];
    }

    /**
     * Creates an INSERT SQL statement.
     * For example,
     * ```php
     * $sql = $queryBuilder->insert('user', [
     *     'name' => 'Sam',
     *     'age' => 30,
     * ], $params);
     * ```
     * The method will properly escape the table and column names.
     *
     * @param string $table the table that new rows will be inserted into.
     * @param array|Query $columns the column data (name => value) to be inserted into the table or instance
     * of [[ESD\Yii\Db\Query|Query]] to perform INSERT INTO ... SELECT SQL statement.
     * Passing of [[ESD\Yii\Db\Query|Query]] is available since version 2.0.11.
     * @param array $params the binding parameters that will be generated by this method.
     * They should be bound to the DB command later.
     * @return string the INSERT SQL
     * @throws Exception
     */
    public function insert(string $table, $columns, array &$params): string
    {
        list($names, $placeholders, $values, $params) = $this->prepareInsertValues($table, $columns, $params);
        return 'INSERT INTO ' . $this->db->quoteTableName($table)
            . (!empty($names) ? ' (' . implode(', ', $names) . ')' : '')
            . (!empty($placeholders) ? ' VALUES (' . implode(', ', $placeholders) . ')' : $values);
    }

    /**
     * Prepares a `VALUES` part for an `INSERT` SQL statement.
     *
     * @param string $table the table that new rows will be inserted into.
     * @param array|Query $columns the column data (name => value) to be inserted into the table or instance
     * of [[ESD\Yii\Db\Query|Query]] to perform INSERT INTO ... SELECT SQL statement.
     * @param array|null $params the binding parameters that will be generated by this method.
     * They should be bound to the DB command later.
     * @return array array of column names, placeholders, values and params.
     * @throws Exception
     * @since 2.0.14
     */
    protected function prepareInsertValues(string $table, $columns, ?array $params = []): array
    {
        $schema = $this->db->getSchema();
        $tableSchema = $schema->getTableSchema($table);
        $columnSchemas = $tableSchema !== null ? $tableSchema->columns : [];
        $names = [];
        $placeholders = [];
        $values = ' DEFAULT VALUES';
        if ($columns instanceof Query) {
            list($names, $values, $params) = $this->prepareInsertSelectSubQuery($columns, $schema, $params);
        } else {
            foreach ($columns as $name => $value) {
                $names[] = $schema->quoteColumnName($name);
//                $value = isset($columnSchemas[$name]) ? $columnSchemas[$name]->dbTypecast($value) : $value;
                $value = isset($columnSchemas[$name]) ? call_user_func([$columnSchemas[$name], 'dbTypecast'], $value) : $value;

                if ($value instanceof ExpressionInterface) {
                    $placeholders[] = $this->buildExpression($value, $params);
                } elseif ($value instanceof Query) {
                    list($sql, $params) = $this->build($value, $params);
                    $placeholders[] = "($sql)";
                } else {
                    $placeholders[] = $this->bindParam($value, $params);
                }
            }
        }
        return [$names, $placeholders, $values, $params];
    }

    /**
     * Prepare select-subquery and field names for INSERT INTO ... SELECT SQL statement.
     *
     * @param Query $columns Object, which represents select query.
     * @param \ESD\Yii\Db\Schema $schema Schema object to quote column name.
     * @param array|null $params the parameters to be bound to the generated SQL statement. These parameters will
     * be included in the result with the additional parameters generated during the query building process.
     * @return array array of column names, values and params.
     * @throws Exception
     * @since 2.0.11
     */
    protected function prepareInsertSelectSubQuery(Query $columns, Schema $schema, ?array $params = []): array
    {
        if (!is_array($columns->select) || empty($columns->select) || in_array('*', $columns->select)) {
            throw new InvalidArgumentException('Expected select query object with enumerated (named) parameters');
        }

        list($values, $params) = $this->build($columns, $params);
        $names = [];
        $values = ' ' . $values;
        foreach ($columns->select as $title => $field) {
            if (is_string($title)) {
                $names[] = $schema->quoteColumnName($title);
            } elseif (preg_match('/^(.*?)(?i:\s+as\s+|\s+)([\w\-_\.]+)$/', $field, $matches)) {
                $names[] = $schema->quoteColumnName($matches[2]);
            } else {
                $names[] = $schema->quoteColumnName($field);
            }
        }

        return [$names, $values, $params];
    }

    /**
     * Generates a batch INSERT SQL statement.
     *
     * For example,
     *
     * ```php
     * $sql = $queryBuilder->batchInsert('user', ['name', 'age'], [
     *     ['Tom', 30],
     *     ['Jane', 20],
     *     ['Linda', 25],
     * ]);
     * ```
     *
     * Note that the values in each row must match the corresponding column names.
     *
     * The method will properly escape the column names, and quote the values to be inserted.
     *
     * @param string $table the table that new rows will be inserted into.
     * @param array $columns the column names
     * @param array|\Generator $rows the rows to be batch inserted into the table
     * @param array|null $params the binding parameters. This parameter exists since 2.0.14
     * @return string the batch INSERT SQL statement
     */
    public function batchInsert(string $table, array $columns, $rows, ?array &$params = []): string
    {
        if (empty($rows)) {
            return '';
        }

        $schema = $this->db->getSchema();
        if (($tableSchema = $schema->getTableSchema($table)) !== null) {
            $columnSchemas = $tableSchema->columns;
        } else {
            $columnSchemas = [];
        }

        $values = [];
        foreach ($rows as $row) {
            $vs = [];
            foreach ($row as $i => $value) {
                if (isset($columns[$i], $columnSchemas[$columns[$i]])) {
                    $value = $columnSchemas[$columns[$i]]->dbTypecast($value);
                }
                if (is_string($value)) {
                    $value = $schema->quoteValue($value);
                } elseif (is_float($value)) {
                    // ensure type cast always has . as decimal separator in all locales
                    $value = StringHelper::floatToString($value);
                } elseif ($value === false) {
                    $value = 0;
                } elseif ($value === null) {
                    $value = 'NULL';
                } elseif ($value instanceof ExpressionInterface) {
                    $value = $this->buildExpression($value, $params);
                }
                $vs[] = $value;
            }
            $values[] = '(' . implode(', ', $vs) . ')';
        }
        if (empty($values)) {
            return '';
        }

        foreach ($columns as $i => $name) {
            $columns[$i] = $schema->quoteColumnName($name);
        }

        return 'INSERT INTO ' . $schema->quoteTableName($table)
        . ' (' . implode(', ', $columns) . ') VALUES ' . implode(', ', $values);
    }

    /**
     * Creates an SQL statement to insert rows into a database table if
     * they do not already exist (matching unique constraints),
     * or update them if they do.
     *
     * For example,
     *
     * ```php
     * $sql = $queryBuilder->upsert('pages', [
     *     'name' => 'Front page',
     *     'url' => 'http://example.com/', // url is unique
     *     'visits' => 0,
     * ], [
     *     'visits' => new \ESD\Yii\Db\Expression('visits + 1'),
     * ], $params);
     * ```
     *
     * The method will properly escape the table and column names.
     *
     * @param string $table the table that new rows will be inserted into/updated in.
     * @param array|Query $insertColumns the column data (name => value) to be inserted into the table or instance
     * of [[Query]] to perform `INSERT INTO ... SELECT` SQL statement.
     * @param array|bool $updateColumns the column data (name => value) to be updated if they already exist.
     * If `true` is passed, the column data will be updated to match the insert column data.
     * If `false` is passed, no update will be performed if the column data already exists.
     * @param array $params the binding parameters that will be generated by this method.
     * They should be bound to the DB command later.
     * @return string the resulting SQL.
     * @throws NotSupportedException if this is not supported by the underlying DBMS.
     * @since 2.0.14
     */
    public function upsert(string $table, $insertColumns, $updateColumns, array &$params): string
    {
        throw new NotSupportedException($this->db->getDriverName() . ' does not support upsert statements.');
    }

    /**
     * @param string $table
     * @param array|Query $insertColumns
     * @param array|bool $updateColumns
     * @param Constraint[] $constraints this parameter recieves a matched constraint list.
     * The constraints will be unique by their column names.
     * @return array
     * @throws Exception
     * @since 2.0.14
     */
    protected function prepareUpsertColumns(string $table, $insertColumns, $updateColumns, ?array &$constraints = []): array
    {
        if ($insertColumns instanceof Query) {
            list($insertNames) = $this->prepareInsertSelectSubQuery($insertColumns, $this->db->getSchema());
        } else {
            $insertNames = array_map([$this->db, 'quoteColumnName'], array_keys($insertColumns));
        }
        $uniqueNames = $this->getTableUniqueColumnNames($table, $insertNames, $constraints);
        $uniqueNames = array_map([$this->db, 'quoteColumnName'], $uniqueNames);
        if ($updateColumns !== true) {
            return [$uniqueNames, $insertNames, null];
        }

        return [$uniqueNames, $insertNames, array_diff($insertNames, $uniqueNames)];
    }

    /**
     * Returns all column names belonging to constraints enforcing uniqueness (`PRIMARY KEY`, `UNIQUE INDEX`, etc.)
     * for the named table removing constraints which did not cover the specified column list.
     * The column list will be unique by column names.
     *
     * @param string $name table name. The table name may contain schema name if any. Do not quote the table name.
     * @param string[] $columns source column list.
     * @param Constraint[] $constraints this parameter optionally recieves a matched constraint list.
     * The constraints will be unique by their column names.
     * @return string[] column list.
     */
    private function getTableUniqueColumnNames(string $name, array $columns, ?array &$constraints = []): array
    {
        $schema = $this->db->getSchema();
        if (!$schema instanceof ConstraintFinderInterface) {
            return [];
        }

        $constraints = [];
        $primaryKey = $schema->getTablePrimaryKey($name);
        if ($primaryKey !== null) {
            $constraints[] = $primaryKey;
        }
        foreach ($schema->getTableIndexes($name) as $constraint) {
            if ($constraint->isUnique) {
                $constraints[] = $constraint;
            }
        }
        $constraints = array_merge($constraints, $schema->getTableUniques($name));
        // Remove duplicates
        $constraints = array_combine(array_map(function (Constraint $constraint) {
            $columns = $constraint->columnNames;
            sort($columns, SORT_STRING);
            return json_encode($columns);
        }, $constraints), $constraints);
        $columnNames = [];
        // Remove all constraints which do not cover the specified column list
        $constraints = array_values(array_filter($constraints, function (Constraint $constraint) use ($schema, $columns, &$columnNames) {
            $constraintColumnNames = array_map([$schema, 'quoteColumnName'], $constraint->columnNames);
            $result = !array_diff($constraintColumnNames, $columns);
            if ($result) {
                $columnNames = array_merge($columnNames, $constraintColumnNames);
            }
            return $result;
        }));
        return array_unique($columnNames);
    }

    /**
     * Creates an UPDATE SQL statement.
     *
     * For example,
     *
     * ```php
     * $params = [];
     * $sql = $queryBuilder->update('user', ['status' => 1], 'age > 30', $params);
     * ```
     *
     * The method will properly escape the table and column names.
     *
     * @param string $table the table to be updated.
     * @param array $columns the column data (name => value) to be updated.
     * @param array|string $condition the condition that will be put in the WHERE part. Please
     * refer to [[Query::where()]] on how to specify condition.
     * @param array $params the binding parameters that will be modified by this method
     * so that they can be bound to the DB command later.
     * @return string the UPDATE SQL
     */
    public function update(string $table, array $columns, $condition, array &$params): string
    {
        list($lines, $params) = $this->prepareUpdateSets($table, $columns, $params);
        $sql = 'UPDATE ' . $this->db->quoteTableName($table) . ' SET ' . implode(', ', $lines);
        $where = $this->buildWhere($condition, $params);
        return $where === '' ? $sql : $sql . ' ' . $where;
    }

    /**
     * Prepares a `SET` parts for an `UPDATE` SQL statement.
     * @param string $table the table to be updated.
     * @param array $columns the column data (name => value) to be updated.
     * @param array|null $params the binding parameters that will be modified by this method
     * so that they can be bound to the DB command later.
     * @return array an array `SET` parts for an `UPDATE` SQL statement (the first array element) and params (the second array element).
     * @since 2.0.14
     */
    protected function prepareUpdateSets(string $table, array $columns, ?array $params = []): array
    {
        $tableSchema = $this->db->getTableSchema($table);
        $columnSchemas = $tableSchema !== null ? $tableSchema->columns : [];
        $sets = [];
        foreach ($columns as $name => $value) {

            $value = isset($columnSchemas[$name]) ? $columnSchemas[$name]->dbTypecast($value) : $value;
            if ($value instanceof ExpressionInterface) {
                $placeholder = $this->buildExpression($value, $params);
            } else {
                $placeholder = $this->bindParam($value, $params);
            }

            $sets[] = $this->db->quoteColumnName($name) . '=' . $placeholder;
        }
        return [$sets, $params];
    }

    /**
     * Creates a DELETE SQL statement.
     *
     * For example,
     *
     * ```php
     * $sql = $queryBuilder->delete('user', 'status = 0');
     * ```
     *
     * The method will properly escape the table and column names.
     *
     * @param string $table the table where the data will be deleted from.
     * @param array|string $condition the condition that will be put in the WHERE part. Please
     * refer to [[Query::where()]] on how to specify condition.
     * @param array $params the binding parameters that will be modified by this method
     * so that they can be bound to the DB command later.
     * @return string the DELETE SQL
     */
    public function delete(string $table, $condition, array &$params): string
    {
        $sql = 'DELETE FROM ' . $this->db->quoteTableName($table);
        $where = $this->buildWhere($condition, $params);

        return $where === '' ? $sql : $sql . ' ' . $where;
    }

    /**
     * Builds a SQL statement for creating a new DB table.
     *
     * The columns in the new table should be specified as name-definition pairs (e.g. 'name' => 'string'),
     * where name stands for a column name which will be properly quoted by the method, and definition
     * stands for the column type which can contain an abstract DB type.
     * The [[getColumnType()]] method will be invoked to convert any abstract type into a physical one.
     *
     * If a column is specified with definition only (e.g. 'PRIMARY KEY (name, type)'), it will be directly
     * inserted into the generated SQL.
     *
     * For example,
     *
     * ```php
     * $sql = $queryBuilder->createTable('user', [
     *  'id' => 'pk',
     *  'name' => 'string',
     *  'age' => 'integer',
     * ]);
     * ```
     *
     * @param string $table the name of the table to be created. The name will be properly quoted by the method.
     * @param array $columns the columns (name => definition) in the new table.
     * @param string|null $options additional SQL fragment that will be appended to the generated SQL.
     * @return string the SQL statement for creating a new DB table.
     */
    public function createTable(string $table, array $columns, ?string $options = null): string
    {
        $cols = [];
        foreach ($columns as $name => $type) {
            if (is_string($name)) {
                $cols[] = "\t" . $this->db->quoteColumnName($name) . ' ' . $this->getColumnType($type);
            } else {
                $cols[] = "\t" . $type;
            }
        }
        $sql = 'CREATE TABLE ' . $this->db->quoteTableName($table) . " (\n" . implode(",\n", $cols) . "\n)";

        return $options === null ? $sql : $sql . ' ' . $options;
    }

    /**
     * Builds a SQL statement for renaming a DB table.
     * @param string $oldName the table to be renamed. The name will be properly quoted by the method.
     * @param string $newName the new table name. The name will be properly quoted by the method.
     * @return string the SQL statement for renaming a DB table.
     */
    public function renameTable(string $oldName, string $newName): string
    {
        return 'RENAME TABLE ' . $this->db->quoteTableName($oldName) . ' TO ' . $this->db->quoteTableName($newName);
    }

    /**
     * Builds a SQL statement for dropping a DB table.
     * @param string $table the table to be dropped. The name will be properly quoted by the method.
     * @return string the SQL statement for dropping a DB table.
     */
    public function dropTable(string $table): string
    {
        return 'DROP TABLE ' . $this->db->quoteTableName($table);
    }

    /**
     * Builds a SQL statement for adding a primary key constraint to an existing table.
     * @param string $name the name of the primary key constraint.
     * @param string $table the table that the primary key constraint will be added to.
     * @param string|array $columns comma separated string or array of columns that the primary key will consist of.
     * @return string the SQL statement for adding a primary key constraint to an existing table.
     */
    public function addPrimaryKey(string $name, string $table, $columns): string
    {
        if (is_string($columns)) {
            $columns = preg_split('/\s*,\s*/', $columns, -1, PREG_SPLIT_NO_EMPTY);
        }

        foreach ($columns as $i => $col) {
            $columns[$i] = $this->db->quoteColumnName($col);
        }

        return 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' ADD CONSTRAINT '
            . $this->db->quoteColumnName($name) . ' PRIMARY KEY ('
            . implode(', ', $columns) . ')';
    }

    /**
     * Builds a SQL statement for removing a primary key constraint to an existing table.
     * @param string $name the name of the primary key constraint to be removed.
     * @param string $table the table that the primary key constraint will be removed from.
     * @return string the SQL statement for removing a primary key constraint from an existing table.
     */
    public function dropPrimaryKey(string $name, string $table): string
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($table)
            . ' DROP CONSTRAINT ' . $this->db->quoteColumnName($name);
    }

    /**
     * Builds a SQL statement for truncating a DB table.
     * @param string $table the table to be truncated. The name will be properly quoted by the method.
     * @return string the SQL statement for truncating a DB table.
     */
    public function truncateTable(string $table): string
    {
        return 'TRUNCATE TABLE ' . $this->db->quoteTableName($table);
    }

    /**
     * Builds a SQL statement for adding a new DB column.
     * @param string $table the table that the new column will be added to. The table name will be properly quoted by the method.
     * @param string $column the name of the new column. The name will be properly quoted by the method.
     * @param string $type the column type. The [[getColumnType()]] method will be invoked to convert abstract column type (if any)
     * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
     * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
     * @return string the SQL statement for adding a new column.
     */
    public function addColumn(string $table, string $column, string $type): string
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($table)
            . ' ADD ' . $this->db->quoteColumnName($column) . ' '
            . $this->getColumnType($type);
    }

    /**
     * Builds a SQL statement for dropping a DB column.
     * @param string $table the table whose column is to be dropped. The name will be properly quoted by the method.
     * @param string $column the name of the column to be dropped. The name will be properly quoted by the method.
     * @return string the SQL statement for dropping a DB column.
     */
    public function dropColumn(string $table, string $column): string
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($table)
            . ' DROP COLUMN ' . $this->db->quoteColumnName($column);
    }

    /**
     * Builds a SQL statement for renaming a column.
     * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
     * @param string $oldName the old name of the column. The name will be properly quoted by the method.
     * @param string $newName the new name of the column. The name will be properly quoted by the method.
     * @return string the SQL statement for renaming a DB column.
     */
    public function renameColumn(string $table, string $oldName, string $newName): string
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($table)
            . ' RENAME COLUMN ' . $this->db->quoteColumnName($oldName)
            . ' TO ' . $this->db->quoteColumnName($newName);
    }

    /**
     * Builds a SQL statement for changing the definition of a column.
     * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
     * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
     * @param string $type the new column type. The [[getColumnType()]] method will be invoked to convert abstract
     * column type (if any) into the physical one. Anything that is not recognized as abstract type will be kept
     * in the generated SQL. For example, 'string' will be turned into 'varchar(255)', while 'string not null'
     * will become 'varchar(255) not null'.
     * @return string the SQL statement for changing the definition of a column.
     */
    public function alterColumn(string $table, string $column, string $type): string
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' CHANGE '
            . $this->db->quoteColumnName($column) . ' '
            . $this->db->quoteColumnName($column) . ' '
            . $this->getColumnType($type);
    }

    /**
     * Builds a SQL statement for adding a foreign key constraint to an existing table.
     * The method will properly quote the table and column names.
     * @param string $name the name of the foreign key constraint.
     * @param string $table the table that the foreign key constraint will be added to.
     * @param string|array $columns the name of the column to that the constraint will be added on.
     * If there are multiple columns, separate them with commas or use an array to represent them.
     * @param string $refTable the table that the foreign key references to.
     * @param string|array $refColumns the name of the column that the foreign key references to.
     * If there are multiple columns, separate them with commas or use an array to represent them.
     * @param string|null $delete the ON DELETE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
     * @param string|null $update the ON UPDATE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
     * @return string the SQL statement for adding a foreign key constraint to an existing table.
     */
    public function addForeignKey(string $name, string $table, $columns, string $refTable, $refColumns, ?string $delete = null, ?string $update = null): string
    {
        $sql = 'ALTER TABLE ' . $this->db->quoteTableName($table)
            . ' ADD CONSTRAINT ' . $this->db->quoteColumnName($name)
            . ' FOREIGN KEY (' . $this->buildColumns($columns) . ')'
            . ' REFERENCES ' . $this->db->quoteTableName($refTable)
            . ' (' . $this->buildColumns($refColumns) . ')';
        if ($delete !== null) {
            $sql .= ' ON DELETE ' . $delete;
        }
        if ($update !== null) {
            $sql .= ' ON UPDATE ' . $update;
        }

        return $sql;
    }

    /**
     * Builds a SQL statement for dropping a foreign key constraint.
     * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
     * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
     * @return string the SQL statement for dropping a foreign key constraint.
     */
    public function dropForeignKey(string $name, string $table): string
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($table)
            . ' DROP CONSTRAINT ' . $this->db->quoteColumnName($name);
    }

    /**
     * Builds a SQL statement for creating a new index.
     * @param string $name the name of the index. The name will be properly quoted by the method.
     * @param string $table the table that the new index will be created for. The table name will be properly quoted by the method.
     * @param string|array $columns the column(s) that should be included in the index. If there are multiple columns,
     * separate them with commas or use an array to represent them. Each column name will be properly quoted
     * by the method, unless a parenthesis is found in the name.
     * @param bool $unique whether to add UNIQUE constraint on the created index.
     * @return string the SQL statement for creating a new index.
     */
    public function createIndex(string $name, string $table, $columns, bool $unique = false): string
    {
        return ($unique ? 'CREATE UNIQUE INDEX ' : 'CREATE INDEX ')
            . $this->db->quoteTableName($name) . ' ON '
            . $this->db->quoteTableName($table)
            . ' (' . $this->buildColumns($columns) . ')';
    }

    /**
     * Builds a SQL statement for dropping an index.
     * @param string $name the name of the index to be dropped. The name will be properly quoted by the method.
     * @param string $table the table whose index is to be dropped. The name will be properly quoted by the method.
     * @return string the SQL statement for dropping an index.
     */
    public function dropIndex(string $name, string $table): string
    {
        return 'DROP INDEX ' . $this->db->quoteTableName($name) . ' ON ' . $this->db->quoteTableName($table);
    }

    /**
     * Creates a SQL command for adding an unique constraint to an existing table.
     * @param string $name the name of the unique constraint.
     * The name will be properly quoted by the method.
     * @param string $table the table that the unique constraint will be added to.
     * The name will be properly quoted by the method.
     * @param string|array $columns the name of the column to that the constraint will be added on.
     * If there are multiple columns, separate them with commas.
     * The name will be properly quoted by the method.
     * @return string the SQL statement for adding an unique constraint to an existing table.
     * @since 2.0.13
     */
    public function addUnique(string $name, string $table, $columns): string
    {
        if (is_string($columns)) {
            $columns = preg_split('/\s*,\s*/', $columns, -1, PREG_SPLIT_NO_EMPTY);
        }
        foreach ($columns as $i => $col) {
            $columns[$i] = $this->db->quoteColumnName($col);
        }

        return 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' ADD CONSTRAINT '
            . $this->db->quoteColumnName($name) . ' UNIQUE ('
            . implode(', ', $columns) . ')';
    }

    /**
     * Creates a SQL command for dropping an unique constraint.
     * @param string $name the name of the unique constraint to be dropped.
     * The name will be properly quoted by the method.
     * @param string $table the table whose unique constraint is to be dropped.
     * The name will be properly quoted by the method.
     * @return string the SQL statement for dropping an unique constraint.
     * @since 2.0.13
     */
    public function dropUnique(string $name, string $table): string
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($table)
            . ' DROP CONSTRAINT ' . $this->db->quoteColumnName($name);
    }

    /**
     * Creates a SQL command for adding a check constraint to an existing table.
     * @param string $name the name of the check constraint.
     * The name will be properly quoted by the method.
     * @param string $table the table that the check constraint will be added to.
     * The name will be properly quoted by the method.
     * @param string $expression the SQL of the `CHECK` constraint.
     * @return string the SQL statement for adding a check constraint to an existing table.
     * @since 2.0.13
     */
    public function addCheck(string $name, string $table, string $expression): string
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' ADD CONSTRAINT '
            . $this->db->quoteColumnName($name) . ' CHECK (' . $this->db->quoteSql($expression) . ')';
    }

    /**
     * Creates a SQL command for dropping a check constraint.
     * @param string $name the name of the check constraint to be dropped.
     * The name will be properly quoted by the method.
     * @param string $table the table whose check constraint is to be dropped.
     * The name will be properly quoted by the method.
     * @return string the SQL statement for dropping a check constraint.
     * @since 2.0.13
     */
    public function dropCheck(string $name, string $table): string
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($table)
            . ' DROP CONSTRAINT ' . $this->db->quoteColumnName($name);
    }

    /**
     * Creates a SQL command for adding a default value constraint to an existing table.
     * @param string $name the name of the default value constraint.
     * The name will be properly quoted by the method.
     * @param string $table the table that the default value constraint will be added to.
     * The name will be properly quoted by the method.
     * @param string $column the name of the column to that the constraint will be added on.
     * The name will be properly quoted by the method.
     * @param mixed $value default value.
     * @return string the SQL statement for adding a default value constraint to an existing table.
     * @throws NotSupportedException if this is not supported by the underlying DBMS.
     * @since 2.0.13
     */
    public function addDefaultValue(string $name, string $table, string $column, $value): string
    {
        throw new NotSupportedException($this->db->getDriverName() . ' does not support adding default value constraints.');
    }

    /**
     * Creates a SQL command for dropping a default value constraint.
     * @param string $name the name of the default value constraint to be dropped.
     * The name will be properly quoted by the method.
     * @param string $table the table whose default value constraint is to be dropped.
     * The name will be properly quoted by the method.
     * @return string the SQL statement for dropping a default value constraint.
     * @throws NotSupportedException if this is not supported by the underlying DBMS.
     * @since 2.0.13
     */
    public function dropDefaultValue(string $name, string $table): string
    {
        throw new NotSupportedException($this->db->getDriverName() . ' does not support dropping default value constraints.');
    }

    /**
     * Creates a SQL statement for resetting the sequence value of a table's primary key.
     * The sequence will be reset such that the primary key of the next new row inserted
     * will have the specified value or the maximum existing value +1.
     * @param string $table the name of the table whose primary key sequence will be reset
     * @param array|string $value the value for the primary key of the next new row inserted. If this is not set,
     * the next new row's primary key will have the maximum existing value +1.
     * @return string the SQL statement for resetting sequence
     * @throws NotSupportedException if this is not supported by the underlying DBMS
     */
    public function resetSequence(string $table, $value = null): string
    {
        throw new NotSupportedException($this->db->getDriverName() . ' does not support resetting sequence.');
    }

    /**
     * Execute a SQL statement for resetting the sequence value of a table's primary key.
     * Reason for execute is that some databases (Oracle) need several queries to do so.
     * The sequence is reset such that the primary key of the next new row inserted
     * will have the specified value or the maximum existing value +1.
     * @param string $table the name of the table whose primary key sequence is reset
     * @param array|string $value the value for the primary key of the next new row inserted. If this is not set,
     * the next new row's primary key will have the maximum existing value +1.
     * @throws NotSupportedException|Exception if this is not supported by the underlying DBMS
     * @since 2.0.16
     */
    public function executeResetSequence(string $table, $value = null)
    {
        $this->db->createCommand()->resetSequence($table, $value)->execute();
    }

    /**
     * Builds a SQL statement for enabling or disabling integrity check.
     * @param bool $check whether to turn on or off the integrity check.
     * @param string|null $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
     * @param string|null $table the table name. Defaults to empty string, meaning that no table will be changed.
     * @return string the SQL statement for checking integrity
     * @throws NotSupportedException if this is not supported by the underlying DBMS
     */
    public function checkIntegrity(bool $check = true, ?string $schema = '', ?string $table = ''): string
    {
        throw new NotSupportedException($this->db->getDriverName() . ' does not support enabling/disabling integrity check.');
    }

    /**
     * Builds a SQL command for adding comment to column.
     *
     * @param string $table the table whose column is to be commented. The table name will be properly quoted by the method.
     * @param string $column the name of the column to be commented. The column name will be properly quoted by the method.
     * @param string $comment the text of the comment to be added. The comment will be properly quoted by the method.
     * @return string the SQL statement for adding comment on column
     * @since 2.0.8
     */
    public function addCommentOnColumn(string $table, string $column, string $comment): string
    {
        return 'COMMENT ON COLUMN ' . $this->db->quoteTableName($table) . '.' . $this->db->quoteColumnName($column) . ' IS ' . $this->db->quoteValue($comment);
    }

    /**
     * Builds a SQL command for adding comment to table.
     *
     * @param string $table the table whose column is to be commented. The table name will be properly quoted by the method.
     * @param string $comment the text of the comment to be added. The comment will be properly quoted by the method.
     * @return string the SQL statement for adding comment on table
     * @since 2.0.8
     */
    public function addCommentOnTable(string $table, string $comment): string
    {
        return 'COMMENT ON TABLE ' . $this->db->quoteTableName($table) . ' IS ' . $this->db->quoteValue($comment);
    }

    /**
     * Builds a SQL command for adding comment to column.
     *
     * @param string $table the table whose column is to be commented. The table name will be properly quoted by the method.
     * @param string $column the name of the column to be commented. The column name will be properly quoted by the method.
     * @return string the SQL statement for adding comment on column
     * @since 2.0.8
     */
    public function dropCommentFromColumn(string $table, string $column): string
    {
        return 'COMMENT ON COLUMN ' . $this->db->quoteTableName($table) . '.' . $this->db->quoteColumnName($column) . ' IS NULL';
    }

    /**
     * Builds a SQL command for adding comment to table.
     *
     * @param string $table the table whose column is to be commented. The table name will be properly quoted by the method.
     * @return string the SQL statement for adding comment on column
     * @since 2.0.8
     */
    public function dropCommentFromTable(string $table): string
    {
        return 'COMMENT ON TABLE ' . $this->db->quoteTableName($table) . ' IS NULL';
    }

    /**
     * Creates a SQL View.
     *
     * @param string $viewName the name of the view to be created.
     * @param string|Query $subQuery the select statement which defines the view.
     * This can be either a string or a [[Query]] object.
     * @return string the `CREATE VIEW` SQL statement.
     * @throws Exception
     * @since 2.0.14
     */
    public function createView(string $viewName, $subQuery): string
    {
        if ($subQuery instanceof Query) {
            list($rawQuery, $params) = $this->build($subQuery);
            array_walk(
                $params,
                function(&$param) {
                    $param = $this->db->quoteValue($param);
                }
            );
            $subQuery = strtr($rawQuery, $params);
        }

        return 'CREATE VIEW ' . $this->db->quoteTableName($viewName) . ' AS ' . $subQuery;
    }

    /**
     * Drops a SQL View.
     *
     * @param string $viewName the name of the view to be dropped.
     * @return string the `DROP VIEW` SQL statement.
     * @since 2.0.14
     */
    public function dropView(string $viewName): string
    {
        return 'DROP VIEW ' . $this->db->quoteTableName($viewName);
    }

    /**
     * Converts an abstract column type into a physical column type.
     *
     * The conversion is done using the type map specified in [[typeMap]].
     * The following abstract column types are supported (using MySQL as an example to explain the corresponding
     * physical types):
     *
     * - `pk`: an auto-incremental primary key type, will be converted into "int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY"
     * - `bigpk`: an auto-incremental primary key type, will be converted into "bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY"
     * - `upk`: an unsigned auto-incremental primary key type, will be converted into "int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY"
     * - `char`: char type, will be converted into "char(1)"
     * - `string`: string type, will be converted into "varchar(255)"
     * - `text`: a long string type, will be converted into "text"
     * - `smallint`: a small integer type, will be converted into "smallint(6)"
     * - `integer`: integer type, will be converted into "int(11)"
     * - `bigint`: a big integer type, will be converted into "bigint(20)"
     * - `boolean`: boolean type, will be converted into "tinyint(1)"
     * - `float``: float number type, will be converted into "float"
     * - `decimal`: decimal number type, will be converted into "decimal"
     * - `datetime`: datetime type, will be converted into "datetime"
     * - `timestamp`: timestamp type, will be converted into "timestamp"
     * - `time`: time type, will be converted into "time"
     * - `date`: date type, will be converted into "date"
     * - `money`: money type, will be converted into "decimal(19,4)"
     * - `binary`: binary data type, will be converted into "blob"
     *
     * If the abstract type contains two or more parts separated by spaces (e.g. "string NOT NULL"), then only
     * the first part will be converted, and the rest of the parts will be appended to the converted result.
     * For example, 'string NOT NULL' is converted to 'varchar(255) NOT NULL'.
     *
     * For some of the abstract types you can also specify a length or precision constraint
     * by appending it in round brackets directly to the type.
     * For example `string(32)` will be converted into "varchar(32)" on a MySQL database.
     * If the underlying DBMS does not support these kind of constraints for a type it will
     * be ignored.
     *
     * If a type cannot be found in [[typeMap]], it will be returned without any change.
     * @param string|ColumnSchemaBuilder $type abstract column type
     * @return string physical column type.
     */
    public function getColumnType($type)
    {
        if ($type instanceof ColumnSchemaBuilder) {
            $type = $type->__toString();
        }

        if (isset($this->typeMap[$type])) {
            return $this->typeMap[$type];
        } elseif (preg_match('/^(\w+)\((.+?)\)(.*)$/', $type, $matches)) {
            if (isset($this->typeMap[$matches[1]])) {
                return preg_replace('/\(.+\)/', '(' . $matches[2] . ')', $this->typeMap[$matches[1]]) . $matches[3];
            }
        } elseif (preg_match('/^(\w+)\s+/', $type, $matches)) {
            if (isset($this->typeMap[$matches[1]])) {
                return preg_replace('/^\w+/', $this->typeMap[$matches[1]], $type);
            }
        }

        return $type;
    }

    /**
     * @param array|null $columns
     * @param array $params the binding parameters to be populated
     * @param bool|null $distinct
     * @param string|null $selectOption
     * @return string the SELECT clause built from [[Query::$select]].
     * @throws Exception
     */
    public function buildSelect(?array $columns, array &$params, ?bool $distinct = false, ?string $selectOption = null): string
    {
        $select = $distinct ? 'SELECT DISTINCT' : 'SELECT';
        if ($selectOption !== null) {
            $select .= ' ' . $selectOption;
        }

        if (empty($columns)) {
            return $select . ' *';
        }

        foreach ($columns as $i => $column) {
            if ($column instanceof ExpressionInterface) {
                if (is_int($i)) {
                    $columns[$i] = $this->buildExpression($column, $params);
                } else {
                    $columns[$i] = $this->buildExpression($column, $params) . ' AS ' . $this->db->quoteColumnName($i);
                }
            } elseif ($column instanceof Query) {
                list($sql, $params) = $this->build($column, $params);
                $columns[$i] = "($sql) AS " . $this->db->quoteColumnName($i);
            } elseif (is_string($i)) {
                if (strpos($column, '(') === false) {
                    $column = $this->db->quoteColumnName($column);
                }
                $columns[$i] = "$column AS " . $this->db->quoteColumnName($i);
            } elseif (strpos($column, '(') === false) {
                if (preg_match('/^(.*?)(?i:\s+as\s+|\s+)([\w\-_\.]+)$/', $column, $matches)) {
                    $columns[$i] = $this->db->quoteColumnName($matches[1]) . ' AS ' . $this->db->quoteColumnName($matches[2]);
                } else {
                    $columns[$i] = $this->db->quoteColumnName($column);
                }
            }
        }

        return $select . ' ' . implode(', ', $columns);
    }

    /**
     * @param array|null $tables
     * @param array $params the binding parameters to be populated
     * @return string the FROM clause built from [[Query::$from]].
     * @throws Exception
     */
    public function buildFrom(?array $tables, array &$params): string
    {
        if (empty($tables)) {
            return '';
        }

        $tables = $this->quoteTableNames($tables, $params);

        return 'FROM ' . implode(', ', $tables);
    }

    /**
     * @param array|null $joins
     * @param array $params the binding parameters to be populated
     * @return string the JOIN clause built from [[Query::$join]].
     * @throws Exception if the $joins parameter is not in proper format
     */
    public function buildJoin(?array $joins, array &$params): string
    {
        if (empty($joins)) {
            return '';
        }

        foreach ($joins as $i => $join) {
            if (!is_array($join) || !isset($join[0], $join[1])) {
                throw new Exception('A join clause must be specified as an array of join type, join table, and optionally join condition.');
            }
            // 0:join type, 1:join table, 2:on-condition (optional)
            list($joinType, $table) = $join;
            $tables = $this->quoteTableNames((array) $table, $params);
            $table = reset($tables);
            $joins[$i] = "$joinType $table";
            if (isset($join[2])) {
                $condition = $this->buildCondition($join[2], $params);
                if ($condition !== '') {
                    $joins[$i] .= ' ON ' . $condition;
                }
            }
        }

        return implode($this->separator, $joins);
    }

    /**
     * Quotes table names passed.
     *
     * @param array $tables
     * @param array $params
     * @return array
     * @throws Exception
     */
    private function quoteTableNames(array $tables, array &$params): array
    {
        foreach ($tables as $i => $table) {
            if ($table instanceof Query) {
                list($sql, $params) = $this->build($table, $params);
                $tables[$i] = "($sql) " . $this->db->quoteTableName($i);
            } elseif (is_string($i)) {
                if (strpos($table, '(') === false) {
                    $table = $this->db->quoteTableName($table);
                }
                $tables[$i] = "$table " . $this->db->quoteTableName($i);
            } elseif (strpos($table, '(') === false) {
                if ($tableWithAlias = $this->extractAlias($table)) { // with alias
                    $tables[$i] = $this->db->quoteTableName($tableWithAlias[1]) . ' ' . $this->db->quoteTableName($tableWithAlias[2]);
                } else {
                    $tables[$i] = $this->db->quoteTableName($table);
                }
            }
        }

        return $tables;
    }

    /**
     * @param string|array $condition
     * @param array $params the binding parameters to be populated
     * @return string the WHERE clause built from [[Query::$where]].
     */
    public function buildWhere($condition, array &$params): string
    {
        $where = $this->buildCondition($condition, $params);

        return $where === '' ? '' : 'WHERE ' . $where;
    }

    /**
     * @param array|null $columns
     * @return string the GROUP BY clause
     */
    public function buildGroupBy(?array $columns): string
    {
        if (empty($columns)) {
            return '';
        }
        foreach ($columns as $i => $column) {
            if ($column instanceof ExpressionInterface) {
                $columns[$i] = $this->buildExpression($column);
            } elseif (strpos($column, '(') === false) {
                $columns[$i] = $this->db->quoteColumnName($column);
            }
        }

        return 'GROUP BY ' . implode(', ', $columns);
    }

    /**
     * @param string|array $condition
     * @param array $params the binding parameters to be populated
     * @return string the HAVING clause built from [[Query::$having]].
     */
    public function buildHaving($condition, array &$params): string
    {
        $having = $this->buildCondition($condition, $params);

        return $having === '' ? '' : 'HAVING ' . $having;
    }

    /**
     * Builds the ORDER BY and LIMIT/OFFSET clauses and appends them to the given SQL.
     * @param string $sql the existing SQL (without ORDER BY/LIMIT/OFFSET)
     * @param array|null $orderBy the order by columns. See [[Query::orderBy]] for more details on how to specify this parameter.
     * @param int|null $limit the limit number. See [[Query::limit]] for more details.
     * @param int|null $offset the offset number. See [[Query::offset]] for more details.
     * @return string the SQL completed with ORDER BY/LIMIT/OFFSET (if any)
     */
    public function buildOrderByAndLimit(string $sql, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): string
    {
        $orderBy = $this->buildOrderBy($orderBy);
        if ($orderBy !== '') {
            $sql .= $this->separator . $orderBy;
        }
        $limit = $this->buildLimit($limit, $offset);
        if ($limit !== '') {
            $sql .= $this->separator . $limit;
        }

        return $sql;
    }

    /**
     * @param array|null $columns
     * @return string the ORDER BY clause built from [[Query::$orderBy]].
     */
    public function buildOrderBy(?array $columns): string
    {
        if (empty($columns)) {
            return '';
        }
        $orders = [];
        foreach ($columns as $name => $direction) {
            if ($direction instanceof ExpressionInterface) {
                $orders[] = $this->buildExpression($direction);
            } else {
                $orders[] = $this->db->quoteColumnName($name) . ($direction === SORT_DESC ? ' DESC' : '');
            }
        }

        return 'ORDER BY ' . implode(', ', $orders);
    }

    /**
     * @param int|ExpressionInterface $limit
     * @param int|ExpressionInterface $offset
     * @return string the LIMIT and OFFSET clauses
     */
    public function buildLimit($limit, $offset): string
    {
        $sql = '';
        if ($this->hasLimit($limit)) {
            $sql = 'LIMIT ' . $limit;
        }
        if ($this->hasOffset($offset)) {
            $sql .= ' OFFSET ' . $offset;
        }

        return ltrim($sql);
    }

    /**
     * Checks to see if the given limit is effective.
     * @param mixed $limit the given limit
     * @return bool whether the limit is effective
     */
    protected function hasLimit($limit): bool
    {
        return ($limit instanceof ExpressionInterface) || ctype_digit((string) $limit);
    }

    /**
     * Checks to see if the given offset is effective.
     * @param mixed $offset the given offset
     * @return bool whether the offset is effective
     */
    protected function hasOffset($offset): bool
    {
        return ($offset instanceof ExpressionInterface) || ctype_digit((string) $offset) && (string) $offset !== '0';
    }

    /**
     * @param array|null $unions
     * @param array $params the binding parameters to be populated
     * @return string the UNION clause built from [[Query::$union]].
     * @throws Exception
     */
    public function buildUnion(?array $unions, array &$params): string
    {
        if (empty($unions)) {
            return '';
        }

        $result = '';

        foreach ($unions as $i => $union) {
            $query = $union['query'];
            if ($query instanceof Query) {
                list($unions[$i]['query'], $params) = $this->build($query, $params);
            }

            $result .= 'UNION ' . ($union['all'] ? 'ALL ' : '') . '( ' . $unions[$i]['query'] . ' ) ';
        }

        return trim($result);
    }

    /**
     * Processes columns and properly quotes them if necessary.
     * It will join all columns into a string with comma as separators.
     * @param string|array $columns the columns to be processed
     * @return string the processing result
     */
    public function buildColumns($columns): string
    {
        if (!is_array($columns)) {
            if (strpos($columns, '(') !== false) {
                return $columns;
            }

            $rawColumns = $columns;
            $columns = preg_split('/\s*,\s*/', $columns, -1, PREG_SPLIT_NO_EMPTY);
            if ($columns === false) {
                throw new InvalidArgumentException("$rawColumns is not valid columns.");
            }
        }
        foreach ($columns as $i => $column) {
            if ($column instanceof ExpressionInterface) {
                $columns[$i] = $this->buildExpression($column);
            } elseif (strpos($column, '(') === false) {
                $columns[$i] = $this->db->quoteColumnName($column);
            }
        }

        return implode(', ', $columns);
    }

    /**
     * Parses the condition specification and generates the corresponding SQL expression.
     * @param string|array|ExpressionInterface $condition the condition specification. Please refer to [[Query::where()]]
     * on how to specify a condition.
     * @param array $params the binding parameters to be populated
     * @return string the generated SQL expression
     */
    public function buildCondition($condition, array &$params): string
    {
        if (is_array($condition)) {
            if (empty($condition)) {
                return '';
            }

            $condition = $this->createConditionFromArray($condition);
        }

        if ($condition instanceof ExpressionInterface) {
            return $this->buildExpression($condition, $params);
        }

        return (string) $condition;
    }

    /**
     * Transforms $condition defined in array format (as described in [[Query::where()]]
     * to instance of [[ESD\Yii\Db\condition\ConditionInterface|ConditionInterface]] according to
     * [[conditionClasses]] map.
     *
     * @param string|array $condition
     * @see conditionClasses
     * @return ConditionInterface
     * @since 2.0.14
     */
    public function createConditionFromArray($condition)
    {
        if (isset($condition[0])) { // operator format: operator, operand 1, operand 2, ...
            $operator = strtoupper(array_shift($condition));
            $className = $this->conditionClasses[$operator] ?? 'ESD\Yii\Db\Conditions\SimpleCondition';
            /** @var ConditionInterface $className */
            return $className::fromArrayDefinition($operator, $condition);
        }

        // hash format: 'column1' => 'value1', 'column2' => 'value2', ...
        return new HashCondition($condition);
    }

    /**
     * Creates a condition based on column-value pairs.
     * @param array $condition the condition specification.
     * @param array $params the binding parameters to be populated
     * @return string the generated SQL expression
     * @deprecated since 2.0.14. Use `buildCondition()` instead.
     */
    public function buildHashCondition(array $condition, array &$params): string
    {
        return $this->buildCondition(new HashCondition($condition), $params);
    }

    /**
     * Connects two or more SQL expressions with the `AND` or `OR` operator.
     * @param string $operator the operator to use for connecting the given operands
     * @param array $operands the SQL expressions to connect.
     * @param array $params the binding parameters to be populated
     * @return string the generated SQL expression
     * @deprecated since 2.0.14. Use `buildCondition()` instead.
     */
    public function buildAndCondition(string $operator, array $operands, array &$params): string
    {
        array_unshift($operands, $operator);
        return $this->buildCondition($operands, $params);
    }

    /**
     * Inverts an SQL expressions with `NOT` operator.
     * @param string $operator the operator to use for connecting the given operands
     * @param array $operands the SQL expressions to connect.
     * @param array $params the binding parameters to be populated
     * @return string the generated SQL expression
     * @throws InvalidArgumentException if wrong number of operands have been given.
     * @deprecated since 2.0.14. Use `buildCondition()` instead.
     */
    public function buildNotCondition(string $operator, array $operands, array &$params): string
    {
        array_unshift($operands, $operator);
        return $this->buildCondition($operands, $params);
    }

    /**
     * Creates an SQL expressions with the `BETWEEN` operator.
     * @param string $operator the operator to use (e.g. `BETWEEN` or `NOT BETWEEN`)
     * @param array $operands the first operand is the column name. The second and third operands
     * describe the interval that column value should be in.
     * @param array $params the binding parameters to be populated
     * @return string the generated SQL expression
     * @throws InvalidArgumentException if wrong number of operands have been given.
     * @deprecated since 2.0.14. Use `buildCondition()` instead.
     */
    public function buildBetweenCondition(string $operator, array $operands, array &$params): string
    {
        array_unshift($operands, $operator);
        return $this->buildCondition($operands, $params);
    }

    /**
     * Creates an SQL expressions with the `IN` operator.
     * @param string $operator the operator to use (e.g. `IN` or `NOT IN`)
     * @param array $operands the first operand is the column name. If it is an array
     * a composite IN condition will be generated.
     * The second operand is an array of values that column value should be among.
     * If it is an empty array the generated expression will be a `false` value if
     * operator is `IN` and empty if operator is `NOT IN`.
     * @param array $params the binding parameters to be populated
     * @return string the generated SQL expression
     * @deprecated since 2.0.14. Use `buildCondition()` instead.
     */
    public function buildInCondition(string $operator, array $operands, array &$params): string
    {
        array_unshift($operands, $operator);
        return $this->buildCondition($operands, $params);
    }

    /**
     * Creates an SQL expressions with the `LIKE` operator.
     * @param string $operator the operator to use (e.g. `LIKE`, `NOT LIKE`, `OR LIKE` or `OR NOT LIKE`)
     * @param array $operands an array of two or three operands
     *
     * - The first operand is the column name.
     * - The second operand is a single value or an array of values that column value
     *   should be compared with. If it is an empty array the generated expression will
     *   be a `false` value if operator is `LIKE` or `OR LIKE`, and empty if operator
     *   is `NOT LIKE` or `OR NOT LIKE`.
     * - An optional third operand can also be provided to specify how to escape special characters
     *   in the value(s). The operand should be an array of mappings from the special characters to their
     *   escaped counterparts. If this operand is not provided, a default escape mapping will be used.
     *   You may use `false` or an empty array to indicate the values are already escaped and no escape
     *   should be applied. Note that when using an escape mapping (or the third operand is not provided),
     *   the values will be automatically enclosed within a pair of percentage characters.
     * @param array $params the binding parameters to be populated
     * @return string the generated SQL expression
     * @throws InvalidArgumentException if wrong number of operands have been given.
     * @deprecated since 2.0.14. Use `buildCondition()` instead.
     */
    public function buildLikeCondition(string $operator, array $operands, array &$params): string
    {
        array_unshift($operands, $operator);
        return $this->buildCondition($operands, $params);
    }

    /**
     * Creates an SQL expressions with the `EXISTS` operator.
     * @param string $operator the operator to use (e.g. `EXISTS` or `NOT EXISTS`)
     * @param array $operands contains only one element which is a [[Query]] object representing the sub-query.
     * @param array $params the binding parameters to be populated
     * @return string the generated SQL expression
     * @throws InvalidArgumentException if the operand is not a [[Query]] object.
     * @deprecated since 2.0.14. Use `buildCondition()` instead.
     */
    public function buildExistsCondition(string $operator, array $operands, array &$params): string
    {
        array_unshift($operands, $operator);
        return $this->buildCondition($operands, $params);
    }

    /**
     * Creates an SQL expressions like `"column" operator value`.
     * @param string $operator the operator to use. Anything could be used e.g. `>`, `<=`, etc.
     * @param array $operands contains two column names.
     * @param array $params the binding parameters to be populated
     * @return string the generated SQL expression
     * @throws InvalidArgumentException if wrong number of operands have been given.
     * @deprecated since 2.0.14. Use `buildCondition()` instead.
     */
    public function buildSimpleCondition(string $operator, array $operands, array &$params): string
    {
        array_unshift($operands, $operator);
        return $this->buildCondition($operands, $params);
    }

    /**
     * Creates a SELECT EXISTS() SQL statement.
     * @param string $rawSql the subquery in a raw form to select from.
     * @return string the SELECT EXISTS() SQL statement.
     * @since 2.0.8
     */
    public function selectExists(string $rawSql): string
    {
        return 'SELECT EXISTS(' . $rawSql . ')';
    }

    /**
     * Helper method to add $value to $params array using [[PARAM_PREFIX]].
     *
     * @param string|null $value
     * @param array $params passed by reference
     * @return string the placeholder name in $params array
     *
     * @since 2.0.14
     */
    public function bindParam($value, array &$params): string
    {
        $phName = self::PARAM_PREFIX . count($params);
        $params[$phName] = $value;

        return $phName;
    }

    /**
     * Extracts table alias if there is one or returns false
     * @param $table
     * @return bool|array
     * @since 2.0.24
     */
    protected function extractAlias($table)
    {
        if (preg_match('/^(.*?)(?i:\s+as|)\s+([^ ]+)$/', $table, $matches)) {
            return $matches;
        }

        return false;
    }
}
