<?php

namespace Phroper\Fields;

class RandomKey extends Field {
    public function __construct(array $data = null) {
        parent::__construct([
            "type" => "text",
            "readonly" => true,
            "auto" => true,

            "sql_type" => "VARCHAR",
            "sql_length" => 16,
            "default" => fn () => $this->getRandomText(),
        ]);
        $this->updateData($data);
    }

    protected function getRandomText($length = null) {
        if (!$length) $length = $this->data["sql_length"];

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
