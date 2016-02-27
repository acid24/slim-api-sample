<?php

namespace Salexandru\Api\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Salexandru\Api\Server\Response\JsonResponseTrait;
use Salexandru\CommandBus\CommandBusInterface as CommandBus;

abstract class AbstractCommandBusPoweredAction implements ActionInterface
{

    use JsonResponseTrait;

    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * Route arguments (if any)
     * @var array
     */
    protected $args;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function run(Request $req, Response $res, array $args)
    {
        $this->request = $req;
        $this->response = $res;
        $this->args = $args;

        $this->performAction();
    }

    /**
     * @param int $errorCode
     * @param null|string $errorMessage
     * @return Response
     */
    protected function badRequest($errorCode, $errorMessage = null)
    {
        return $this->buildErrorResponse($this->response, [
            'code' => $errorCode,
            'message' => $errorMessage,
            'status' => 400
        ]);
    }

    /**
     * @param int $errorCode
     * @param null|string $errorMessage
     * @return Response
     */
    protected function serverError($errorCode, $errorMessage = null)
    {
        return $this->buildErrorResponse($this->response, [
            'code' => $errorCode,
            'message' => $errorMessage,
            'status' => 500
        ]);
    }

    abstract protected function performAction();
}
