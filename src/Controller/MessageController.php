<?php
declare(strict_types=1);

namespace App\Controller;

use App\DTO\MessageListRequest;
use App\Exception\ValidationException;
use App\Service\MessageService;
use App\Utils\ResponseUtils;
use App\Validator\MessageValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Log\LoggerInterface;


/**
 * Suggested Improvement:
 *
 * 1. API Platform Integration:
 *    - API Platform is a Symfony-based RESTful API framework that automates many aspects of API development, such as serialization, pagination, validation, and error responses. It greatly improves the consistency, scalability, and maintainability of your API.
 *    - This reduces manual code and makes the API more consistent and easier to maintain.
 *    - With API Platform, you gain automatic pagination and filtering capabilities without needing to write custom code for these features. It supports collections of data, with automatic pagination out-of-the-box.
 *    - It also handles HTTP status codes appropriately and allows for advanced error handling, returning detailed error messages that comply with standard API practices.
 *    - API Platform automatically generates the OpenAPI specification (Swagger) for your API, which provides clear and self-updating API documentation, making it easier for other developers or clients to interact with your API.
 *    - Using API Platform also encourages best practices in API design, which leads to more secure, scalable, and standardized code.
 *
 * 2. Test Coverage:
 *    - The `list` method is not covered by tests yet. Write tests using PHPUnit to verify its functionality.
 *
 * 3. Refactor `send` Method for Consistency:
 *    - Use proper HTTP status codes for responses. Return a `201` (Created) or `200` (OK) for successful operations, instead of using `204` which suggests no content.
 *    - Consider using `JsonResponse` for JSON responses instead of manually setting headers for consistency with Symfony's conventions.
 *    - If the message is successfully dispatched, a `202` (Accepted) response would be more appropriate.
 *    - Ensure consistent return types and response formats across all methods for ease of integration and understanding.
 *
 * 4. Error Handling with Try-Catch:
 *    - The try-catch block for error handling has been moved to the `MessageService` to ensure that the controller remains lean and focused on handling HTTP requests.
 *    - The service class should handle exceptions and log errors when dispatching messages, allowing the controller to focus on its role in handling the request.
 *    - Log any errors that occur during message dispatching using appropriate logging strategies (e.g., `LoggerInterface`).
 *
 * 5. Move Business Logic to a Service:
 *    - Consider moving the logic for dispatching the message (and error handling) into a separate `MessageService` class. This would follow the **Separation of Concerns** principle, keeping the controller focused on HTTP-related tasks and the service focused on business logic.
 *    - By creating a `MessageService`, the logic becomes more reusable, easier to test, and helps maintain the single responsibility principle.
 *    - This change also allows for better error handling, making the logic more maintainable in the long term.
 *
 * 6. Improve Data Formatting:
 *    - In the `list` method, you are manually formatting the messages into an array. While functional, this can be improved by using Symfony's built-in serialization mechanisms, such as the `SerializerInterface`. This can ensure that the response data is consistent and can be more easily adjusted for changes in the entity structure.
 *    - Ensure the response format is uniform and adheres to any API specifications you may be following (e.g., JSON:API).
 *
 * 7. Enhance Request Validation:
 *    - The `send` method checks if the `text` parameter is provided, but more validation can be added to ensure the text is of an appropriate length or format (e.g., not too long, no invalid characters, etc.).
 *    - Consider using Symfony's validation component to validate request data more systematically.
 *
 * 8. Pagination:
 *    - The `list` method calls the `by` method on the `MessageRepository` but pagination is not applied. This is a potential performance issue especially when dealing with large datasets.
 *    - Consider adding pagination and filtering options to the `list` method, so that clients can request subsets of messages (e.g., using query parameters).
 *
 * 9. Improve HTTP Status Codes for Error Cases:
 *    - The `send` method currently returns a `400` status if no text is provided, but it would be good to ensure that the error response contains additional details, such as an error message explaining why the request is invalid.
 *    - Use appropriate HTTP status codes for other error scenarios, such as `500` (Internal Server Error) if an unexpected error occurs.
 *
 * 10. Logging:
 *    - Add detailed logging for each step in the `send` and `list` methods to help with debugging and tracking. Log request parameters, any errors that occur, and responses sent back to the user.
 */

class MessageController extends AbstractController
{
    public function __construct(
        private MessageService $messageService,
        private LoggerInterface $logger,
        private MessageValidator $messageValidator
    ) {}

    #[Route('/messages', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $status = $request->get('status', '');
            $page =   $request->get('page', 1);
            $limit =  $request->get('limit', 10);

            $messageListRequest = new MessageListRequest($status, $page, $limit);
            $messages = $this->messageService->getFormattedMessagesFromRequest($messageListRequest);

            return ResponseUtils::success(['messages' => $messages]);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Error while listing messages',
                [
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                ]
            );

            return ResponseUtils::error('An error occurred while retrieving messages.');
        }
    }

    #[Route('/messages/send', methods: ['POST'])]
    public function send(Request $request): JsonResponse
    {
        try {

            $text = $request->request->get('text');
            $validatedText = $this->messageValidator->validateText($text);
            $this->messageService->dispatchMessage($validatedText);

            return ResponseUtils::success(
                ['message' => 'Message successfully sent.'],
                JsonResponse::HTTP_ACCEPTED
            );
        } catch (ValidationException $exception) {
            return ResponseUtils::error($exception->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Error while dispatching message',
                [
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                ]
            );

            return ResponseUtils::error('An error occurred while sending the message.');
        }
    }
}
