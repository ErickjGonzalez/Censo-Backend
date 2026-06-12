<?php

namespace App\Services\EncryptionServices;
use Illuminate\Support\Facades\Crypt;

class EncryptService{

    public function Encrypt($data)//encripta con laravel los id de una coleccion paginada
    {
        $data->setCollection(
            $data->getCollection()->transform(function ($item) {
                $item->encrypted_id = Crypt::encrypt($item->id);
                return $item;
            })
        );

        return $data;
    }


    /* ecriptación de los selectores */
    public function Encryptselectors($data)
{
    return $data->transform(function ($item) {
        $item->encrypted_id = Crypt::encrypt($item->id);
        return $item;
    });
}

}