<?php

class Guesttypes_api extends Model
{
    public function get($params)
    {
        if (Api_vkye::check_access($params[0], $params[1]) == true)
        {
            if (!empty($params[2]))
            {
                if (!empty($params[3]))
                {
                    $query = Functions::get_json_decoded_query($this->database->select('guest_types', [
                        '[>]settings' => [
                            'account' => 'account'
                        ]
                    ], [
                        'guest_types.id',
                        'guest_types.name',
                    ], [
                        'AND' => [
                            'guest_types.id' => $params[3],
                            'settings.zv' => true
                        ]
                    ]));

                    return !empty($query) ? $query[0] : 'No se encontraron registros';
                }
                else
                {
                    $query = Functions::get_json_decoded_query($this->database->select('guest_types', [
                        '[>]settings' => [
                            'account' => 'account'
                        ]
                    ], [
                        'guest_types.id',
                        'guest_types.name',
                    ], [
                        'AND' => [
                            'guest_types.account' => $params[2],
                            'settings.zv' => true
                        ]
                    ]));

                    return !empty($query) ? $query : 'No se encontraron registros';
                }
            }
            else
                return 'Cuenta de uso no definida';
        }
        else
            return 'Credenciales de acceso no válidas';
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