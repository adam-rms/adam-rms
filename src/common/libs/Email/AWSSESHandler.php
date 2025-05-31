<?php

namespace Common\Libs\Email;

// Assuming EmailHandler exists in the same namespace or is properly imported
// use Common\Libs\Email\EmailHandler;

// Assuming SesClient and other AWS SDK classes are available via composer's autoloader
use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

class AWSSESEmailHandler extends EmailHandler
{
    private $sesClient;
    private $config; // To store AWS credentials and region

    /**
     * Constructor
     *
     * @param array $config Configuration array containing AWS credentials and region
     *                      Example: [
     *                          'aws_access_key_id' => 'YOUR_ACCESS_KEY',
     *                          'aws_secret_access_key' => 'YOUR_SECRET_KEY',
     *                          'aws_region' => 'YOUR_AWS_REGION'
     *                      ]
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        // It's good practice to validate required config keys
        if (empty($this->config['aws_access_key_id']) ||
            empty($this->config['aws_secret_access_key']) ||
            empty($this->config['aws_region'])) {
            throw new \InvalidArgumentException('AWS credentials (access key, secret key) and region must be provided.');
        }

        $this->sesClient = new SesClient([
            'version' => 'latest',
            'region'  => $this->config['aws_region'],
            'credentials' => [
                'key'    => $this->config['aws_access_key_id'],
                'secret' => $this->config['aws_secret_access_key'],
            ],
        ]);
    }

    /**
     * Sends an email using AWS SES.
     *
     * @param string $to The recipient's email address.
     * @param string $subject The subject of the email.
     * @param string $body The HTML body of the email.
     * @param string $from The sender's email address.
     * @param string|null $replyTo Optional. The reply-to email address.
     * @param array $headers Optional. Additional email headers.
     * @return bool True if the email was sent successfully, false otherwise.
     * @throws \Exception If there is an error sending the email.
     */
    public function sendEmail(string $to, string $subject, string $body, string $from, ?string $replyTo = null, array $headers = []): bool
    {
        $destination = [
            'ToAddresses' => [$to],
        ];

        $message = [
            'Body' => [
                'Html' => [
                    'Charset' => 'UTF-8',
                    'Data' => $body,
                ],
                // You can also add a 'Text' part for non-HTML email clients
                // 'Text' => [
                //     'Charset' => 'UTF-8',
                //     'Data' => strip_tags($body), // Simple text version
                // ],
            ],
            'Subject' => [
                'Charset' => 'UTF-8',
                'Data' => $subject,
            ],
        ];

        $source = $from;

        $emailParams = [
            'Destination' => $destination,
            'Message' => $message,
            'Source' => $source,
        ];

        if ($replyTo) {
            $emailParams['ReplyToAddresses'] = [$replyTo];
        }

        // AWS SES expects headers in a specific format if you want to add custom ones.
        // For simplicity, this example doesn't add custom headers beyond standard ones.
        // If $headers were to be used, they'd need to be formatted like:
        // $emailParams['Headers'] = [
        //     ['Name' => 'HeaderName1', 'Value' => 'HeaderValue1'],
        //     ['Name' => 'HeaderName2', 'Value' => 'HeaderValue2'],
        // ];
        // However, standard headers like To, From, Subject are handled by the main parameters.

        try {
            $result = $this->sesClient->sendEmail($emailParams);
            // You might want to log the message ID or other details from $result
            // error_log('Email sent! Message ID: ' . $result->get('MessageId'));
            return true;
        } catch (AwsException $e) {
            // Log the error from AWS SDK
            // error_log("AWS SES Error: " . $e->getAwsErrorMessage());
            // You could throw a more specific exception or handle it as needed
            throw new \Exception("Email sending failed via AWS SES: " . $e->getMessage(), 0, $e);
        } catch (\Exception $e) {
            // Catch any other exceptions
            // error_log("Error sending email: " . $e->getMessage());
            throw new \Exception("An unexpected error occurred while sending email: " . $e->getMessage(), 0, $e);
        }

        return false; // Should not be reached if exceptions are thrown
    }
}

// Placeholder for the EmailHandler class if it's not autoloaded or defined elsewhere.
// This is just for context and would typically be in its own file.
if (!class_exists('Common\Libs\Email\EmailHandler')) {
    abstract class EmailHandler
    {
        /**
         * Abstract method for sending an email.
         *
         * @param string $to The recipient's email address.
         * @param string $subject The subject of the email.
         * @param string $body The body of the email (HTML or text).
         * @param string $from The sender's email address.
         * @param string|null $replyTo Optional. The reply-to email address.
         * @param array $headers Optional. Additional email headers.
         * @return bool True if the email was sent successfully, false otherwise.
         */
        abstract public function sendEmail(string $to, string $subject, string $body, string $from, ?string $replyTo = null, array $headers = []): bool;
    }
}
?>
