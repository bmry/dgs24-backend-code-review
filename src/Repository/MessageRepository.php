<?php

namespace App\Repository;

use App\DTO\MessageListRequest;
use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 *
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository implements MessageRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Refactor Explanation:
     * 1. Parameterized Queries: The original method used raw SQL with string interpolation, which could expose the application to SQL injection vulnerabilities. The refactored code uses Doctrine's QueryBuilder and the `setParameter()` method to safely inject parameters into the query, ensuring better security.
     *
     * 2. Pagination: The refactored method incorporates pagination based on the `page` and `limit` provided by the `MessageListRequest`. This improves performance by limiting the number of results returned, especially useful for large datasets. The `setFirstResult()` and `setMaxResults()` methods are used to apply the pagination, ensuring that only the relevant subset of results is returned for the current page.
     *
     * 3. Conditional Filter: The original method used a raw SQL query with a conditional `WHERE` clause that could potentially break if no `status` is provided. The refactored code only adds the `WHERE` clause when a `status` filter is set, making the query more flexible and reducing unnecessary conditions.
     *
     * 4. Doctrine Best Practices: The refactored method uses Doctrine’s QueryBuilder to construct the query, which is the recommended approach for building queries in Symfony/Doctrine applications. This makes the code more maintainable, as it's easier to modify or extend later (e.g., adding more filters or joins).
     */

    /**
     * Fetches messages with optional status filter, pagination, and limit.
     *
     * @param MessageListRequest $messageListRequest
     *
     * @return Message[]
     */
    public function by(MessageListRequest $messageListRequest): array
    {
        $status = $messageListRequest->getStatus();
        $page = $messageListRequest->getPage();
        $limit = $messageListRequest->getLimit();

        $offset = ($page - 1) * $limit;

        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('m')
            ->from(Message::class, 'm')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($status) {
            $queryBuilder->where('m.status = :status')
                ->setParameter('status', $status);
        }

        /** @var Message[] $result */
        $result = $queryBuilder->getQuery()->getResult();
        return $result ;
    }


}
