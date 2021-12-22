<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki
 * @copyright Jakub Gniecki <kubuspl@onet.eu>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace DevLancer\ORMLite;


class Repository
{
    protected string $entity;
    protected EntityManager $manager;

    public function __construct(EntityManager $manager, string $entity)
    {
        $this->manager = $manager;
        $this->entity = $entity;
    }

    public function getEntityManager(): EntityManager
    {
        return $this->manager;
    }

    public function find($id): ?object
    {
        $metadataFactory = $this->manager->getClassMetadata($this->entity);
        $property = [];
        foreach ($metadataFactory['properties'] as $value) {
            if (isset($value['id'])) {
                $property = $value;
                break;
            }
        }

        if ($property === []) {
            return null;
        }

        $manager = $this->getEntityManager();
        $query = $manager->createQueryBuilder($this->entity);
        $column = $property['column'];
        $query
            ->where(sprintf('%s = :%s', $column, $column))
            ->setParameter($column, $id)
        ;

        return $query->getOneOrNullResult();

    }

    public function findBy(array $data): array
    {
        $manager = $this->getEntityManager();
        $query = $manager->createQueryBuilder($this->entity);

        foreach ($data as $col => $val) {
            $query
                ->where(sprintf('%s = :%s', $col, $col))
                ->setParameter($col, $val)
            ;
        }

        return $query->getResult();
    }

    public function findOneBy(array $data): ?object
    {
        $manager = $this->getEntityManager();
        $query = $manager->createQueryBuilder($this->entity);
        foreach ($data as $col => $val) {
            $query
                ->where(sprintf('%s = :%s', $col, $col))
                ->setParameter($col, $val)
            ;
        }

        return $query->getOneOrNullResult();
    }

    public function findAll(): array
    {
        $manager = $this->getEntityManager();
        $query = $manager->createQueryBuilder($this->entity);
        return $query->getResult();
    }

}