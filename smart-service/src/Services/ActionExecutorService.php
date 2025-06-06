<?php

namespace App\Services;

use App\Entity\Strategy;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActionExecutorService implements ActionExecutorInterface
{
    public function __construct(
        private readonly GetActionInterface $actionResolver,
    ) {
    }

    public function execute(Strategy $strategy, Request $request): Response
    {
        $actionEntity = $strategy->getAction();
        if ($actionEntity === null) {
            throw new RuntimeException(sprintf(
                'No Action defined for strategy id \'%s\' and path \'%s\'.',
                $strategy->getId(),
                $strategy->getPath()
            ));
        }

        $tag = $actionEntity->getHandlerTag();
        $params = $actionEntity->getParameters() ?? [];

        return $this->actionResolver->getAction($tag)->handle($request, $params);
    }
}
