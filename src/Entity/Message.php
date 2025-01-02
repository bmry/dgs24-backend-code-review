<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


/**
 *
 * VIOLATED CLEAN ARCHITECTURE:
 * This class should be refactored to follow Clean Architecture principles,
 * ensuring it is decoupled from Doctrine ORM
 * and remains a pure domain entity.
 *
 * Missing migration: Please generate a migration for this entity.
 *
 * Date and time handling:
 *  - `createdAt` should be stored as a `TIMESTAMP` type for efficiency and compatibility with time zone handling.
 *   - Storing `createdAt` as `TIMESTAMP` ensures efficient storage, querying, and handling of time zones.
 *   - Move createdAt to a `Timestampable` Trait to automatically manage `createdAt` and avoid duplicates.
 *
 * Validation for 'text' and 'status' values:
 *   - Ensures data integrity by verifying that the input values for 'text' and 'status' conform to expected formats and limits.
 *   - Helps prevent invalid or inconsistent data from being saved to the database.
 *
 * - Consider using `ENUM` for the 'status' field:
 *   - Using `ENUM` for 'status' ensures that only predefined values can be stored, which enhances data consistency and prevents errors.
 *   - It makes the code more readable and self-documenting by clearly defining the possible statuses.
 *
 * - Consider using Value Objects (e.g., for UUID) for better encapsulation:
 *   - Encapsulating the UUID logic in a Value Object promotes better code organization and ensures that the logic for generating and validating UUIDs is centralized.
 *   - It enhances maintainability by keeping the logic for UUID handling separate from the entity itself.
 *
 * - Add lifecycle callbacks for automatic setting of 'createdAt'.
 *   - Using `@PrePersist` or similar lifecycle callbacks ensures that the `createdAt` field is automatically set when a new entity is created, reducing the risk of manual errors.
 *   - It promotes consistency and reduces the need for redundant code.
 *
 * - UUID validation should be added to ensure the field contains a valid UUID format.
 *   - Ensures that the UUID stored in the database follows a valid and standardized format, preventing invalid entries.
 *   - Provides better consistency across the system by ensuring that only valid UUIDs are accepted.
 *
 * - UUID can now be auto-generated if not provided using `Ramsey\Uuid`.
 *   - The `Ramsey\Uuid` library allows for automatic generation of UUIDs when none is provided, ensuring that all records have a valid UUID by default.
 *   - This simplifies the code and eliminates the need for manual UUID creation.
 *
 * - Auto-generation and validation of UUID to ensure consistency across the system.
 *   - Guarantees that UUIDs are both valid and consistently generated across all entities, reducing the risk of UUID-related errors in the application.
 *   - Ensures data integrity and simplifies the process of handling UUIDs across multiple services or components in the system.
 */


#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Consider enforcing UUID uniqueness by adding unique: true
    #[ORM\Column(type: Types::GUID)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255)]
    private ?string $text = null;

    // Consider changing this to use the Status enum instead of nullable string
    //#[ORM\Column(type: Types::STRING, enumType: Status::class)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    // Consider changing createdAt to timestamp (int) for timezone handling
    #[ORM\Column(type: 'datetime')]
    private DateTime $createdAt;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        // Consider validating the UUID format using a library such as Ramsey\Uuid
        // Example: if (!Uuid::isValid($uuid)) { throw new InvalidArgumentException("Invalid UUID"); }

        $this->uuid = $uuid;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        // Sanitize the text input to prevent unwanted content
        // Example: $text = htmlspecialchars($text); // Prevent HTML injection
        $this->text = $text;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        // Check if the status is one of the allowed values (e.g., 'sent', 'read')
        // Example: if (!in_array($status, ['sent', 'read'])) { throw new InvalidArgumentException("Invalid status value"); }

        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Consider adding a lifecycle callback to automatically set the createdAt timestamp
     * before persisting the entity, e.g., using @PrePersist.
     * Example:
     * @ORM\PrePersist
     * public function prePersist(): void {
     *     if (!$this->createdAt) {
     *         $this->createdAt = time(); // Set current Unix timestamp if not already set
     *     }
     * }
     */
}


/**
 * ADDITIONAL COMMENT
 * This class should be refactored to follow Clean Architecture principles, ensuring it is decoupled from infrastructure concerns
 * and remains a pure domain entity.
 *
 * Step 1: **Remove Doctrine Annotations** from the entity class.
 * The class is currently coupled to Doctrine ORM. To align with Clean Architecture, we need to remove ORM-specific annotations like
 * #[ORM\Entity] and #[ORM\Column]. These annotations tightly couple the domain entity to the persistence layer.
 * The mapping of the entity to the database should be handled in a separate infrastructure layer (e.g., a persistence model).
 *
 * Step 2: **Make the entity immutable.**
 * The class currently has setters, making it mutable and violating Clean Architecture principles. We should make the entity immutable
 * by removing setters and initializing all fields through the constructor. The entity should expose only getters to ensure its state
 * cannot be modified after creation.
 *
 * The constructor should handle initialization and set all fields. Remove setters.
 * Example:
 * public function __construct(string $uuid, string $text, ?MessageStatus $status = null)
 * {
 *     $this->uuid = $uuid;
 *     $this->text = $text;
 *     $this->status = $status;
 *     $this->createdAt = new DateTimeImmutable();  // Automatically set CreatedAt when the entity is created.
 * }
 *
 * Step 3: **Move database logic to a separate infrastructure layer.**
 * The entity should only contain domain logic. Any database-specific logic, such as Doctrine annotations, and database interactions
 * should be handled in a repository or an infrastructure layer, keeping the domain model clean and focused on business rules.
 *
 * Step 4: **Create a Doctrine entity class for persistence.**
 * In the infrastructure layer, create a new class (e.g., `MessageDoctrineEntity`) to map the entity to a database table.
 * This class will include necessary Doctrine-specific annotations like #[ORM\Entity].
 * The domain `Message` class should remain free of infrastructure concerns and should not be responsible for database interactions.
 *
 * Step 5: **Move UUID generation to an external service or factory.**
 * The entity should not handle UUID generation directly. This should be the responsibility of an external service or factory.
 * This can be injected into the constructor of the entity, ensuring the entity remains focused on business logic.
 *
 * Step 6: **Refactor status handling with a `MessageStatus` Value Object.**
 * - Instead of using a raw string for the `status` field, encapsulate the status value in a `MessageStatus` Value Object.
 * - This ensures that the status is validated and managed in a consistent and reusable manner. Any logic related to status should be
 *   handled within the `MessageStatus` object.
 * - The `Message` entity should not deal with raw status strings but should work with a `MessageStatus` object, ensuring domain logic is clean.
 *
 * Example:
 * class MessageStatus
 * {
 *     private string $value;
 *
 *     public function __construct(string $status)
 *     {
 *         // Validate status here
 *         if (!in_array($status, ['sent', 'read'])) {
 *             throw new InvalidArgumentException('Invalid status value');
 *         }
 *         $this->value = $status;
 *     }
 *
 *     public function getValue(): string
 *     {
 *         return $this->value;
 *     }
 * }
 */
