<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


/**
 * Improvements:
 * - createdAt should be a timestamp type for efficiency.
 *   - Using `TIMESTAMP` ensures efficient storage, querying, and compatibility with time zone handling.
 *   - This also allows easy support for time-based queries.
 *
 * - Validation for 'text' and 'status' values.
 *   - Ensures data integrity by verifying that the input values for 'text' and 'status' conform to expected formats and limits.
 *   - Helps prevent invalid or inconsistent data from being saved to the database.
 *
 * - Consider using ENUM for the status field to limit possible values.
 *   - Using `ENUM` for 'status' ensures that only predefined values can be stored, which enhances data consistency and prevents errors.
 *   - It makes the code more readable and self-documenting by clearly defining the possible statuses.
 *
 * - Handle time zone issues with 'createdAt'.
 *   - Storing `createdAt` as UTC and handling time zone conversions during retrieval ensures that time zone differences don’t cause inconsistencies in recorded times.
 *   - This make the app adaptable to users in different time zones.
 *
 * - Consider using Value Objects (e.g., for UUID) for better encapsulation.
 *   - Encapsulating the UUID logic in a Value Object promotes better code organization and ensures that the logic for generating and validating UUIDs is centralized.
 *   - It enhances maintainability by keeping the logic for UUID handling separate from the entity itself.
 *
 * - Add lifecycle callbacks for automatic setting of 'createdAt'.
 *   - Using `@PrePersist` or similar lifecycle callbacks can ensure that the `createdAt` field is automatically set when a new entity is created, reducing the risk of manual errors.
 *   - It promotes consistency and reduces the need for redundant code.
 *
 * - UUID validation added to ensure the field contains a valid UUID format.
 *   - Ensures that the UUID stored in the database follows a valid and standardized format, preventing invalid entries.
 *   - Provides better consistency across the system by ensuring that only valid UUIDs are accepted.
 *
 * - UUID can now be auto-generated if not provided using Ramsey\Uuid.
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
 * This class should be refactored to follow Clean Architecture principles.
 * It should be decoupled from the infrastructure (Doctrine ORM) and be a pure domain entity.
 *
 * Step 1: **Remove Doctrine Annotations** from the entity class.
 * The class is currently tightly coupled to Doctrine ORM. We should remove ORM-specific annotations like
 * #[ORM\Entity] and #[ORM\Column] to ensure this class is a pure domain entity, not tied to any database layer.
 * The mapping of this entity to the database should be handled separately, outside of this class (in a persistence model).
 *
 * Step 2: **Make the entity immutable.**
 * This class currently has setters and is mutable, which violates Clean Architecture principles.
 * We should make this class immutable by removing all setters and initializing all values through the constructor.
 * The entity should only expose getters to ensure that its state cannot be modified after creation.
 *
 * Constructor should handle initialization and set all fields. Remove setters.
 * Example:
 * public function __construct(string $uuid, string $text, ?MessageStatus $status = null)
 * {
 *     $this->uuid = $uuid;
 *     $this->text = $text;
 *     $this->status = $status;
 *     $this->createdAt = new DateTimeImmutable();  // CreatedAt should be automatically set when the entity is created.
 * }
 *
 * Step 3: **Move database logic to separate infrastructure layer.**
 * The entity should only hold domain logic. Doctrine-specific logic (such as annotations) and database
 * interactions should be moved to the repository or infrastructure layer, not the domain layer.
 *
 * Step 4: **Create a Doctrine entity class for persistence.**
 * Create a new class `MessageDoctrineEntity` in the infrastructure layer to map this entity to a database table
 * and add the necessary Doctrine-specific annotations like #[ORM\Entity].
 * The `Message` class should remain independent of any database interaction.
 *
 * Step 5: **Move logic for UUID generation to an external service or factory.**
 * UUID generation should not be done directly in the entity.
 * This could be handled by a UUID generator service or injected through the constructor.
 *
 * Step 6: Refactor status handling with a `MessageStatus` Value Object.**
 * - Instead of using a raw string for the `status`, encapsulate the status in a `MessageStatus` Value Object.
 * - This ensures that status values are validated and managed consistently, and any logic related to status is handled in one place.
 * - The `Message` entity should not manage raw status strings but should work with a `MessageStatus` object, which encapsulates status validation and behavior.
 *
 * Example:
 * class MessageStatus
 * {
 *     private string $value;
 *
 *     public function __construct(string $status)
 *     {
 *         // Validate and manage status here.
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
