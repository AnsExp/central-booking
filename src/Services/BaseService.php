<?php
namespace CentralTickets\Services;

use CentralTickets\Operator;
use CentralTickets\Persistence\Repository;
use CentralTickets\Persistence\BaseRepository;
use CentralTickets\Services\PackageData\PackageData;

/**
 * @template T
 */
abstract class BaseService
{
    /**
     * @var BaseRepository<T>
     */
    protected BaseRepository $repository;
    public array $error_stack = [];

    /**
     * @param Repository<T> $repository
     */
    protected function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param PackageData<T> $request
     * @return ?T
     */
    public function save($request, int $id = 0)
    {
        $entity = $request->get_data();
        if ($id > 0) {
            if (!$this->repository->exists($id)) {
                return null;
            }
            if ($entity instanceof Operator)
                $entity->ID = $id;
            else
                $entity->id = $id;
        }
        if (!$this->verify($entity)) {
            return null;
        }
        $entity_saved = $this->repository->save($entity);
        if ($entity_saved === null) {
            $this->error_stack[] = $this->repository->error_message;
        }
        return $entity_saved;
    }

    /**
     * @param int $id
     * @return ?T
     */
    public function find(int $id)
    {
        return $this->repository->find($id);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function remove(int $id)
    {
        return $this->repository->remove($id);
    }

    /**
     * @param array $filter
     * @return T[]
     */
    public function list(array $filter = [])
    {
        return $this->repository->find_by(
            $filter,
            $filter['order_by'] ?? 'id',
            $filter['order'] ?? 'ASC'
        );
    }

    /**
     * @param array $filter
     * @param string $order_by
     * @param string $order
     * @param int $page_number
     * @param int $page_size
     * @return array{data: T[], pagination: array{current_page: int, total_elements: int, total_pages: int}}
     */
    public function paginated(array $filter = [], string $order_by = 'id', string $order = 'ASC', int $page_number = 1, int $page_size = 10): array
    {
        $data = $this->repository->find_by(
            $filter,
            $order_by,
            $order,
            $page_size,
            ($page_number - 1) * $page_size
        );
        $total_registers = $this->repository->count($filter);
        $total_pages = ceil($total_registers / $page_size);
        return [
            'pagination' => [
                'current_page' => $page_number,
                'total_pages' => $total_pages === 0 ? 1 : $total_pages,
                'total_elements' => $total_registers,
            ],
            'data' => $data
        ];
    }

    /**
     * @param T $entity
     * @param string $field
     * @return bool
     */
    protected function verify_field($entity, string $field): bool
    {
        $found = $this->repository->find_first([$field => $entity->{$field}]);
        if ($found === null) {
            return true;
        }
        if ($found->id === $entity->id) {
            return true;
        }
        return false;
    }

    /**
     * @param T $entity
     * @return bool
     */
    abstract protected function verify($entity);
}
