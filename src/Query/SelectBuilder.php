<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki
 * @copyright Jakub Gniecki <kubuspl@onet.eu>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace DevLancer\ORMLite\Query;


use InvalidArgumentException;

class SelectBuilder extends Builder
{
    protected array $orderBy = [];
    protected array $groupBy = [];
    protected int $limit = 0;
    protected int $offset = 0;

    public function orderBy(string $column, string $type): self
    {
        $type = strtoupper($type);
        if (!in_array($type, ['DESC', 'ASC'])) {
            throw new InvalidArgumentException("");
        }

        $this->orderBy[$type][] = $column;
        return $this;
    }

    public function groupBy(string $column): self
    {
        $this->groupBy[] = $column;

        return $this;
    }

    public function generate()
    {
        $groupBy = "";
        if ($this->groupBy !== []) {
            $groupBy = "GROUP BY " . implode(", ", $this->groupBy);
        }

        $orderBy = "";
        if (count($this->orderBy) > 0) {
            $_orderBy = [];
            if (isset($this->orderBy['ASC'])) {
                $_orderBy[] = implode(", ", $this->orderBy['ASC']) . " ASC";
            }

            if (isset($this->orderBy['DESC'])) {
                $_orderBy[] = implode(", ", $this->orderBy['DESC']) . " DESC";
            }

            $orderBy = "ORDER BY " . implode(", ", $_orderBy);
        }

        $limit = "";
        if ($this->limit > 0) {
            $limit = "LIMIT " . $this->limit;
        }

        $offset = "";
        if ($this->offset > 0)
            $offset = "OFFSET " . $offset;

        $sql = sprintf("SELECT * FROM %s %s %s %s %s %s", $this->table, $this->_generateWhere(), $groupBy, $orderBy, $limit, $offset);
        $this->setSql($sql);
    }

    public function setMaxResult(int $max): self
    {
        $this->limit = $max;
        return $this;
    }

    public function setOffest(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function getOneOrNullResult(): ?object
    {
        $this->setMaxResult(1);
        $result = $this->getResult();

        if ($result === [])
            return null;

        return $result[0];
    }
}