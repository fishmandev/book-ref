# Development Rules

## Thin Controllers

- Keep controllers thin: they should handle HTTP concerns only, such as reading request input, invoking an application service, and returning the response.
- Do not put business logic, database query composition, aggregation, or response mapping in controllers.
- Place use-case logic in an application service and keep reusable response shaping in a dedicated resource, presenter, or DTO.
