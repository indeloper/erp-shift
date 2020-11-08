<?php
namespace App\Services\TechAccounting;

use App\Models\TechAcc\OurTechnicTicket;

class TechnicTicketStatusCalculatorService
{
    protected $statuses;
    protected $types;
    protected $failure_statuses = [
        'reject' => 3,
        'hold' => 4,
        'rollback' => 2,
    ];
    protected $typePath = [
        1 => [1, 5, 7, 8],
        2 => [1, 2, 6, 8],
        3 => [1, 2, 6, 5, 7, 8],
    ];

    public function __construct()
    {
        $this->statuses = (new OurTechnicTicket())->statuses;
        $this->types = (new OurTechnicTicket())->types;
    }


    public function getIncreasedStatus($ourTechnicTicket, $result = 'confirm', $steps = 1)
    {
        if (array_key_exists($result, $this->failure_statuses)) {
            return $this->getFailureStatus($result);
        } else {
            $ticket_path = $this->typePath[$ourTechnicTicket->type];

            $current_step = array_search($ourTechnicTicket->status, $ticket_path);
            if ($current_step === false and in_array($ourTechnicTicket->status, $this->failure_statuses))
            {
                $current_step = array_search(2, $ticket_path);
            }

            return $ticket_path[$current_step + $steps];
        }
    }

    public function getFailureStatus($status = 'reject')
    {
        return $this->failure_statuses[$status];
    }
}