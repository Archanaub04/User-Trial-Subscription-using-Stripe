<?php

namespace App\Repositories;

use App\Interfaces\CardInterface;
use App\Models\CardDetail;

class CardRepository implements CardInterface
{
    public function storeCardDetails($data)
    {
        // return CardDetail::create($data);
        return CardDetail::updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'card_no' => $data['card_no'],
            ],
            $data
        );
    }
}
