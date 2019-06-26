<?php

use BrosSquad\MicroORM\CustomUpdate;
use BrosSquad\MicroORM\Model;

class User extends Model implements CustomUpdate
{
    protected $name;
    protected $surname;

    public function setUpdate(): string
    {
        return "UPDATE users SET name=:name, surname=:surname WHERE id=:id;";
    }

    public function setUpdateBindings(): array
    {
        return [
            // Id field is inherited from the model class
            ':id' => $this->id,
            ':name' => $this->name,
            ':surname' => $this->surname,
        ];
    }
}
