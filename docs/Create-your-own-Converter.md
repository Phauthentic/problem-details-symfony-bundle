## How to Implement and Use Your Own Exception Converters

Implementing and using custom exception converters can make exception handling in your application more structured and versatile. With the `ProblemDetailsSymfonyBundle`, it is possible to extend and customize the way exceptions are converted to `ProblemDetails` responses. Here is how you can create and use your own exception converters:

### Steps to Implement a Custom Exception Converter
1. **Understand the Exception Conversion**
    - Exception converters are responsible for transforming an exception into a structured `ProblemDetails` response adhering to RFC 9457.
    - The `ProblemDetailsFactory` can be used to create such responses within the converter.
2. **Create Your Custom Exception Converter**
    - Create a class that handles the logic for converting specific exception types or scenarios into `ProblemDetails`.

```php
   namespace App\ExceptionConverter;

   use Psr\Log\LoggerInterface;
   use Phauthentic\ProblemDetails\ExceptionConverterInterface;
   use Phauthentic\ProblemDetails\ProblemDetailsFactoryInterface;
   use Symfony\Component\HttpFoundation\Response;

   class CustomExceptionConverter implements ExceptionConverterInterface
   {
       public function __construct(
           private ProblemDetailsFactoryInterface $problemDetailsFactory,
           private LoggerInterface $logger
       ) {
       }

       /**
        * Converts the given exception to a ProblemDetails instance.
        */
       public function convert(\Throwable $exception): Response
       {
           // Example exception check
           if ($exception instanceof \DomainException) {
               $this->logger->error('Domain Exception occurred: '.$exception->getMessage());

               return $this->problemDetailsFactory->createResponse(
                   type: 'https://example.net/domain-error',
                   detail: $exception->getMessage(),
                   status: 400,
                   title: 'Domain Error'
               );
           }

           // Default: throw the exception further if it cannot be converted
           throw $exception;
       }
   }
```

3. **Register the Exception Converter in Your Application**
    - Register your custom exception converter as a service in Symfony, and ensure it integrates into the exception handling workflow.

```yaml
   # config/services.yaml
   services:
       App\ExceptionConverter\CustomExceptionConverter:
           arguments:
               $problemDetailsFactory: '@Phauthentic\ProblemDetails\ProblemDetailsFactoryInterface'
               $logger: '@logger'
```
