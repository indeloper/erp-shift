<?php

namespace App\Http\Controllers\System;

use App\Domain\DTO\Bitrix\BitrixEventRequestData;
use App\Http\Controllers\Controller;
use App\Http\Requests\BitrixEventRequest;
use App\Services\Bitrix\BitrixServiceInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class BitrixWebhookController extends Controller
{

    /**
     * @param  BitrixServiceInterface  $bitrixService
     */
    public function __construct(
        private BitrixServiceInterface $bitrixService,
    ) {}

    /**
     * @param  \App\Http\Requests\BitrixEventRequest  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function handleIncomingRequest(BitrixEventRequest $request
    ): Response {
        $data = new BitrixEventRequestData(
            $request->get('event'),
            $request->get('event_id'),
            (int) Arr::get(
                $request->get('data.FIELDS_AFTER.ID'),
                'FIELDS_AFTER.ID'
            )
        );

        $this->bitrixService->dispatch(
            $data
        );

        return response()->noContent();
    }

}
