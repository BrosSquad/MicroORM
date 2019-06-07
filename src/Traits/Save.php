<?php


namespace Dusan\PhpMvc\Database\Traits;


use Dusan\PhpMvc\Database\CustomInsert;
use Dusan\PhpMvc\Database\CustomUpdate;
use Dusan\PhpMvc\Database\Driver;
use PDO;
use PDOException;

trait Save
{

    /**
     * Method for saving the record to the database
     * On success in returns true adn on failure throws the error
     * It differs from it's counterpart save() method which returns false on failure
     *
     * @api
     * @throws \PDOException
     * @see DatabaseModelOLD::save()
     * @return void
     */
    public final function saveOrFail(): void
    {
        $statement = static::$database->transaction(function (Driver $database) {
            $customBindings = false;
            $update = false;
            $insert = false;
            $bindings = [];
            $this->changed = array_unique($this->changed);
            if ($this->getId() !== NULL) {
                if ($this->observerInstance) {
                    $this->observerInstance->updating();
                }
                if ($this instanceof CustomUpdate) {
                    $database->sql($this->setUpdate());
                    $bindings = $this->setUpdateBindings();
                    $customBindings = true;
                } else {
                    $database->sql($this->update());
                }
                $update = true;
            } else {
                if ($this->observerInstance) {
                    $this->observerInstance->creating();
                }
                if ($this instanceof CustomInsert) {
                    $database->sql($this->setInsert());
                    $bindings = $this->setInsertBindings();
                    $customBindings = true;
                } else {
                    $database->sql($this->insert());
                }
                $insert = true;
            }
            $bind = [];
            if ($update) {
                $bind = $this->changed;
                $bind[$this->primaryKey] = ':' . $this->primaryKey;
            }

            if ($insert) {
                foreach ($this->protected as $value) {
                    $bind[$value] = ':' . $value;
                }
            }
            if ($customBindings) {
                $bind = $bindings;
            }

            foreach ($bind as $member => $binding) {
                $database->bindValue($binding, $this->__get($member), $this->memberTypeBindings[$member] ?? PDO::PARAM_STR);
            }
            $database->execute(NULL, true);
            if ($this->observerInstance !== NULL) {
                if ($insert) {
                    $this->observerInstance->created($this);
                } else if ($update) {
                    $this->observerInstance->updated($this);
                }
            }
            return $insert;
        });
        if ($statement) {
            $output = static::$database
                ->bindToClass($this->getClass())
                ->getLastInsertedRow($this->getTable(), $this->primaryKey);
            if (count($output) === 1) {
                $object = $output[0];
                array_splice($this->restricted, array_search('id', $this->restricted));
                $array = $this->diff(get_object_vars($object), $this->restricted);
                foreach ($array as $item => $value) {
                    $this->lock(function () use ($item, $value) {
                        $this->__set($item, $value);
                    });
                }
            }
        }
    }

    /**
     * Method for saving record to database
     * On successful insert/update <b>true</b> is returned from this method and on failure <b>false</b> is returned
     *
     * @api
     * @see DatabaseModelOLD::saveOrFail()
     * @return bool
     */
    public final function save()
    {
        try {
            $this->saveOrFail();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

}
