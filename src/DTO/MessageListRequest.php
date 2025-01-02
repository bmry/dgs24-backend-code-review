<?php

declare(strict_types=1);

namespace App\DTO;

use http\Exception\InvalidArgumentException;

/**
 * Data transfer object for handling message list requests.
 */
class MessageListRequest
{
    /**
     * @var string $status The status filter for the messages.
     */
    private string $status;

    /**
     * @var int $page The page number for pagination.
     */
    private int $page;

    /**
     * @var int $limit The number of items per page.
     */
    private int $limit;

    /**
     * @param mixed $status The status filter for the messages.
     * @param mixed $page The page number for pagination.
     * @param mixed $limit The number of items per page.
     */
    public function __construct(mixed $status, mixed $page, mixed $limit)
    {
        $this->status = is_string($status) && in_array($status, ['read', 'sent']) ? $status : '';

        $this->page = is_int($page) ? $page : throw new InvalidArgumentException();
        $this->page = max(1, $this->page);

        $this->limit = is_int($limit) ? $limit : throw new InvalidArgumentException();
        $this->limit = max(1, $this->limit);
    }

    /**
     * Gets the status filter for the messages.
     *
     * @return string The status.
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Gets the page number for pagination.
     *
     * @return int The page number.
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * Gets the number of items per page.
     *
     * @return int The limit.
     */
    public function getLimit(): int
    {
        return $this->limit;
    }
}
