<?php

namespace App\Actions;

use App\Exceptions\DateTimeConditionException;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsTaggedItem('redirect')]
class RedirectStrategy implements ActionStrategyInterface
{
    public function handle(Request $request, array $params): Response
    {
        if (!array_key_exists('url', $params)) {
            throw new DateTimeConditionException("Parameter 'url' is missing");
        }

        if (!is_string($params['url']) || empty($params['url'])) {
            throw new DateTimeConditionException("Parameter 'url' must be not empty string");
        }

        return new RedirectResponse($params['url']);
    }
}
