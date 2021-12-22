<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki
 * @copyright Jakub Gniecki <kubuspl@onet.eu>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace DevLancer\ORMLite;


use PDO;
use PDOStatement;

abstract class DatabaseManager
{
    private ?PDOStatement $PDOStatement = null;
    protected string $table;
    protected array $parameters = [];
    protected string $entity;
    protected string $sql = "";
    protected EntityManager $manager;

    public function __construct(EntityManager $manager, string $entity)
    {
        $this->manager = $manager;
        $this->entity = $entity;

        $metadataFactory = $this->manager->getClassMetadata($entity);
        $this->table = $metadataFactory['class']['table'];
    }

    public function setSql(string $sql)
    {
        $this->sql = $sql;
    }

    public function getSql(): string
    {
        return $this->sql;
    }

    public function setParameter(string $name, $value): self
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    public function execute(): bool
    {
        $this->generate();
        $sql = $this->getSql();
        $pdo = $this->manager->getPdo();
        $this->PDOStatement = $pdo->prepare($sql);
        return $this->PDOStatement->execute($this->parameters);
    }

    public function lastInsertId(): string
    {
        return $this->manager->getPdo()->lastInsertId();
    }

    abstract public function generate();

    public function getResult(): array
    {
        $this->execute();
        $result = $this->PDOStatement->fetchAll(PDO::FETCH_ASSOC);
        $entities = [];
        foreach ($result as $item) {
            $entities[] = $this->_createEntity($item);
        }

        return $entities;
    }

    protected function _createEntity(array $fetch): object
    {
        $metadataFactory = $this->manager->getClassMetadata($this->entity)['properties'];
        $entity = new $this->entity();

        foreach ($metadataFactory as $property) {
            $column = $property['column'];
            $type = $property['type'];
            foreach ($fetch as $col => $val) {
                if ($col === $column) {
                    $entity->{$column} = $this->convert($val, $type);
                }
            }
        }

        return $entity;
    }

    protected function _translateNativeType(string $orig) {
        $trans = array(
            'VAR_STRING' => 'string',
            'STRING' => 'string',
            'BLOB' => 'string',
            'LONGLONG' => 'int',
            'LONG' => 'int',
            'SHORT' => 'int',
            'DATETIME' => 'string',
            'DATE' => 'string',
            'DOUBLE' => 'float',
            'TIMESTAMP' => 'int',
            'NEWDECIMAL' => 'float'
        );
        return $trans[$orig];
    }

    public function convert($value, string $type)
    {
        if (!in_array(strtolower($type), ['int', 'string', 'float'])) {
            $type = $this->_translateNativeType($type);
        }

        switch ($type) {
            case "int":
                $value = (int) $value;
                break;
            case "float":
                $value = (float) $value;
                break;
            default:
                $value = (string) $value;
                break;
        }

        return $value;
    }

    public function getTable(): string
    {
        return $this->table;
    }
}