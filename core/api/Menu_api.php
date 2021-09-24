<?php

class Menu_api extends Model
{
    public function get($params)
    {
        if (!empty($params[0]))
        {
            if (!empty($params[1]))
            {
                $query = Functions::get_json_decoded_query($this->database->select('menu_products', '*', [
                    'AND' => [
                        'id' => $params[1],
                        'status' => true
                    ]
                ]));

                return !empty($query) ? $query[0] : 'No se encontraron registros';
            }
            else
            {
                $query = Functions::get_json_decoded_query($this->database->select('menu_products', '*', [
                    'AND' => [
                        'account' => $params[0],
                        'status' => true
                    ]
                ]));

                return !empty($query) ? $query : 'No se encontraron registros';
            }
        }
        else
            return 'Cuenta no establecida';
    }

    public function post($params)
    {
        return 'Ok';
    }

    public function put($params)
    {
        return 'Ok';
    }

    public function delete($params)
    {
        return 'Ok';
    }
}
