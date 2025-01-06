# Problem Details Symfony Bundle

This bundle provides support for [RFC 9457](https://www.rfc-editor.org/rfc/rfc9457.html).

* Turns `ValidationFailedException` exceptions into a `ProblemDetails` responses complying with RFC 9457.
  * The error building is fully customizable.
* Provides a `ProblemDetailsFactory` service to create `ProblemDetails` instances.

## Installation

```bash
composer require phauthentic/problem-details-symfony-bundle
```

## Docs

```php
class ExampleController
{
    public function __construct(
        private ProblemDetailsFactoryInterface $problemDetailsFactory
    ) {
    }

    /**
     * @Route("/example", methods={"POST"})
     */
    public function exampleAction(Request $request): Response
    {
        return $this->problemDetailsFactory->createResponse(
            type: 'https://example.net/validation-error',
            detail: 'Your request is not valid.',
            status: 422,
            title: 'Validation Error',
        );
    }
}
```

## Problem Details Example

```text
HTTP/1.1 422 Unprocessable Content
Content-Type: application/problem+json
Content-Language: en

{
 "type": "https://example.net/validation-error",
 "title": "Your request is not valid.",
 "errors": [
             {
               "detail": "must be a positive integer",
               "pointer": "#/age"
             },
             {
               "detail": "must be 'green', 'red' or 'blue'",
               "pointer": "#/profile/color"
             }
          ]
}
```
## Alternatives

If you favor a different style of implementation check out the following bundles:

* [phpro/api-problem-bundle](https://github.com/phpro/api-problem-bundle)

## License

This bundle is under the [MIT license](LICENSE).

Copyright Florian Krämer
