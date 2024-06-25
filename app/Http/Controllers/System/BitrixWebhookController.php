<?php

namespace App\Http\Controllers\System;

use App\Domain\DTO\Bitrix\BitrixEventRequestData;
use App\Http\Controllers\Controller;
use App\Http\Requests\BitrixEventRequest;
use App\Services\Bitrix\BitrixServiceInterface;
use Illuminate\Http\Response;

class BitrixWebhookController extends Controller
{

    /**
     * @param  BitrixServiceInterface  $bitrixService
     */
    public function __construct(
        private BitrixServiceInterface $bitrixService,
    ) {}

    public function handleIncomingRequest(BitrixEventRequest $request): Response
    {
        $id = null;

        $data = $request->get('data');

        if (isset($data['FIELDS'])) {
            $id = $data['FIELDS']['ID'];
        }

        if (isset($data['FIELDS_AFTER'])) {
            $id = $data['FIELDS_AFTER']['ID'];
        }

        $data = new BitrixEventRequestData(
            $request->get('event'),
            $request->get('event_id'),
            $id
        );

        $this->bitrixService->dispatchEvent(
            $data
        );

        return response()->noContent();
    }

}
