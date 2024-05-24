<?php

namespace NotificationChannels\Webhook\Exceptions;

use GuzzleHttp\Psr7\Response;

class CouldNotSendNotification extends \Exception
{
    private $response;

    /**
     * @param Response $response
     * @param string $message
     * @param int|null $code
     */
    public function __construct(Response $response, string $message, int $code = null)
    {
        $this->response = $response;
        $this->message = $message;
        $this->code = $code ?? $response->getStatusCode();

        parent::__construct($message, $code);
    }

    /**
     * @param Response $response
     * @return self
     */
    public static function serviceRespondedWithAnError(Response $response)
    {
        return new self(
            $response,
            sprintf('Webhook responded with an error: `%s`', $response->getBody()->getContents())
        );
    }

    public static function connectionError(Response $response)
    {
        return new self(
            $response,
            sprintf('Error sending request to webhook: `%s`', $e->getMessage()),
            999
        );
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
